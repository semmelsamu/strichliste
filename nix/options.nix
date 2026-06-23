self:
{
  lib,
  config,
  pkgs,
  ...
}:
let
  serviceName = "semmelstrichliste";
  cfg = config.services.${serviceName};

  version = toString (self.shortRev or self.dirtyShortRev or self.lastModified or "unknown");

  inherit (lib)
    mkEnableOption
    mkOption
    types
    mkIf
    ;

  mkSubmoduleOption =
    sub-cfg:
    mkOption {
      default = { };
      type = types.submodule {
        options = sub-cfg;
      };
    };

  nginxUser = config.services.nginx.user;

  isSqlite = cfg.database.type == "sqlite";
  isPgsql = cfg.database.type == "pgsql";

  appKeyFile = "${cfg.paths.rootDir}/app_key";
  setupMarker = "${cfg.paths.rootDir}/.is_setup";
  dbKeyFile = "${cfg.paths.rootDir}/db_key";

  # Everything that goes into the runtime `.env` except APP_KEY, which is
  # generated once and persisted (see the setup service below).
  envSettings = {
    APP_NAME = cfg.settings.name;
    APP_ENV = "production";
    APP_DEBUG = if cfg.settings.debug then "true" else "false";
    APP_URL = "${if cfg.settings.expectSSL then "https" else "http"}://${cfg.settings.domain}";
    APP_LOCALE = cfg.settings.locale;

    APP_VERSION = version;

    LOG_CHANNEL = "stderr";

    DB_CONNECTION = cfg.database.type;

    SESSION_DRIVER = "database";
    CACHE_STORE = "database";
    QUEUE_CONNECTION = "database";
  }
  // lib.optionalAttrs isSqlite {
    DB_DATABASE = cfg.paths.database;
  }
  // lib.optionalAttrs isPgsql {
    DB_HOST = cfg.database.postgresql.host;
    DB_PORT = toString cfg.database.postgresql.port;
    DB_DATABASE = cfg.database.postgresql.database;
    DB_USERNAME = cfg.database.postgresql.username;
    DB_SSLMODE = cfg.database.postgresql.sslMode;
  };

  envFile = pkgs.writeText "${serviceName}.env" (
    lib.concatStringsSep "\n" (lib.mapAttrsToList (name: value: "${name}=${value}") envSettings) + "\n"
  );

  php = lib.getExe pkgs.php;
  # rsync = lib.getExe pkgs.rsync;
  sudo = lib.getExe' pkgs.sudo "sudo";
  coreutils = pkgs.coreutils;
in

{
  options.services.${serviceName} = {
    enable = mkEnableOption "enable the ${serviceName}";

    stateVersion = mkOption {
      type = types.enum [ 1 ];
      description = "The stateversion of the setup";
    };

    package = mkOption {
      type = types.package;
      default = self.packages.x86_64-linux.default;
    };

    paths = mkSubmoduleOption {
      rootDir = mkOption {
        type = types.path;
        description = "Root of application data directory";
        default = "/var/lib/strichliste";
      };

      application = mkOption {
        type = types.path;
        description = "Path of the (writable) application directory";
        default = "${cfg.paths.rootDir}/application";
      };

      database = mkOption {
        type = types.nullOr types.path;
        description = "Path of the database file (only used when using sqlite)";
        default = if isSqlite then "${cfg.paths.rootDir}/database.db" else null;
      };
    };

    settings = mkSubmoduleOption {
      name = mkOption {
        type = types.str;
        description = "The application name";
        default = "Strichliste";
      };

      domain = mkOption {
        type = types.str;
        description = "The domain of the app";
      };

      expectSSL = mkOption {
        type = types.bool;
        description = "Should the app expect https or not";
        default = true;
      };

      debug = mkOption {
        type = types.bool;
        description = "Run the application in debug mode (verbose error pages)";
        default = false;
      };

      locale = mkOption {
        type = types.str;
        description = "The application locale";
        default = "de";
      };

      port = mkOption {
        type = types.port;
        description = "The port of the phpfpm pool";
        default = 9123;
      };
    };

    database = mkSubmoduleOption {
      configure = mkOption {
        type = types.bool;
        description = "Configure the database automatically";
        default = true;
      };

      type = mkOption {
        type = types.enum [
          "sqlite"
          "pgsql"
        ];
        default = "sqlite";
      };

      postgresql = mkSubmoduleOption {
        host = mkOption {
          type = types.str;
          description = "The host of the postgres server";
          default = "localhost";
        };

        port = mkOption {
          type = types.port;
          description = "The port of the postgres server";
          default = config.services.postgresql.settings.port;
        };

        database = mkOption {
          type = types.str;
          description = "The database to use";
          default = serviceName;
        };

        username = mkOption {
          type = types.str;
          description = "The user to use";
          default = serviceName;
        };

        passwordFile = mkOption {
          type = types.path;
          description = "The passwordFile to use";
        };

        sslMode = mkOption {
          type = types.str;
          description = "The sslmode for laravel to use";
          default = "disable";
        };
      };
    };
  };

  config = mkIf cfg.enable {
    # Convenience wrapper to run artisan against the live deployment, e.g.
    # `semmelstrichliste-php artisan tinker`.
    environment.systemPackages = [
      (pkgs.writeShellScriptBin "${serviceName}-php" ''
        cd "${cfg.paths.application}"
        export PATH=$PATH:${pkgs.php}/bin:${pkgs.sqlite}/bin
        exec ${php} "$@"
      '')
    ];

    services.postgresql = lib.mkIf (isPgsql && cfg.database.configure) {
      enable = true;

      ensureDatabases = [
        cfg.database.postgresql.database
      ];

      ensureUsers = [
        {
          name = cfg.database.postgresql.username;
        }
      ];

      authentication =
        let
          pgConf = cfg.database.postgresql;
        in
        ''
          local ${pgConf.database} ${pgConf.username} password
        '';
    };

    systemd.services."${serviceName}-postgresql-setup" =
      let
        pgConf = cfg.database.postgresql;
      in
      lib.mkIf (isPgsql && cfg.database.configure) {
        description = "Sets up database";

        wantedBy = [ "multi-user.target" ];
        after = [ "postgresql.service" ];
        before = [ "${serviceName}-setup.service" ];

        serviceConfig = {
          Type = "oneshot";
          RemainAfterExit = true;
        };

        script =
          let
            psql = "${sudo} -u postgres ${lib.getExe' config.services.postgresql.package "psql"}";
          in
          ''
            export PW=$(cat ${pgConf.passwordFile})
            echo "ALTER DATABASE ${pgConf.database} OWNER TO ${pgConf.username}" | ${psql}
            echo "ALTER USER ${pgConf.username} WITH PASSWORD '$PW'" | ${psql}
          '';
      };

    systemd.services."${serviceName}-setup" = {
      description = "Deploy and migrate the ${serviceName} application";
      wantedBy = [ "multi-user.target" ];
      before = [
        "phpfpm-${serviceName}.service"
        "nginx.service"
      ];

      serviceConfig = {
        Type = "oneshot";
        RemainAfterExit = true;
      };

      path = [
        pkgs.php
        pkgs.sqlite
        coreutils
      ];

      script =
        let
          deleteFolderIfExists = name: ''
            if [ -d "${name}" ]; then
                rm -fr "${name}"
            fi

          '';
          # Runs unprivileged as the nginx user, in the writable app directory.
          deployScript = pkgs.writeShellScript "${serviceName}-deploy" ''
            set -euo pipefail

            echo "Store path is: ${cfg.package}"

            # Generate and persist an application key on first run so that
            # sessions/encrypted data survive restarts and redeploys.
            if [ ! -f "${appKeyFile}" ]; then
              echo "base64:$(${coreutils}/bin/head -c 32 /dev/urandom | ${coreutils}/bin/base64)" > "${appKeyFile}"
              chmod 600 "${appKeyFile}"
            fi

            # Sync the immutable package into the writable state directory.
            mkdir -p "${cfg.paths.application}"

            ${deleteFolderIfExists "${cfg.paths.application}/public/build"}
            ${deleteFolderIfExists "${cfg.paths.application}/resources"}
            ${deleteFolderIfExists "${cfg.paths.application}/storage/framework/views"}

            cp -r ${cfg.package}/* "${cfg.paths.application}/"

            chmod -R 0755 ${cfg.paths.application}

            cd "${cfg.paths.application}"

            # Write the runtime environment file (base settings + persisted key).
            ${coreutils}/bin/cat ${envFile} > .env
            echo "APP_KEY=$(${coreutils}/bin/cat "${appKeyFile}")" >> .env

            ${lib.optionalString isPgsql ''
              echo "DB_PASSWORD=$(cat ${dbKeyFile})" >> .env
              rm ${dbKeyFile}
            ''}

            chmod 600 .env

            echo "Migrating database..."
            ${php} artisan migrate --force

            if [ ! -f "${setupMarker}" ]; then
              echo "Seeding database..."
              ${php} artisan db:seed --force
              touch "${setupMarker}"

              echo "Creating link"
              ${php} artisan storage:link
            fi

            # Cache config/routes/views/events now that a real .env exists and
            # bootstrap/cache is writable.
            echo "Optimizing..."
            ${php} artisan cache:clear
            ${php} artisan optimize:clear
            ${php} artisan optimize
            ${php} artisan icons:cache

            echo "Done :)"

            echo "Diff between ${cfg.package} and ${cfg.paths.application}"

            # ignore output
            ${lib.getExe' pkgs.diffutils "diff"} -r ${cfg.package} ${cfg.paths.application} || true

          '';
        in
        ''
          set -euo pipefail

          mkdir -p "${cfg.paths.rootDir}"
          cp ${cfg.database.postgresql.passwordFile} ${dbKeyFile}

          chown -R ${nginxUser} "${cfg.paths.rootDir}"

          ${sudo} -u ${nginxUser} ${deployScript}
        '';
    };

    services.phpfpm.pools.${serviceName} = {
      user = nginxUser;

      phpPackage = pkgs.php.buildEnv {
        extensions = ({ enabled, all }: enabled ++ (with all; [ imagick ]));
      };

      settings = {
        "listen" = "127.0.0.1:${toString cfg.settings.port}";
        "listen.owner" = nginxUser;
        "listen.mode" = "0600";
        "pm" = "dynamic";
        "pm.max_children" = 32;
        "pm.max_requests" = 500;
        "pm.start_servers" = 2;
        "pm.min_spare_servers" = 2;
        "pm.max_spare_servers" = 5;
        "php_admin_value[error_log]" = "stderr";
        "php_admin_flag[log_errors]" = true;
        "catch_workers_output" = true;
      };

    };

    services.nginx = {
      enable = true;
      virtualHosts.${cfg.settings.domain} = {
        root = "${cfg.paths.application}/public";

        extraConfig = ''
          add_header X-Frame-Options "SAMEORIGIN";
          add_header X-Content-Type-Options "nosniff";

          index index.php;
          charset utf-8;

          location = /favicon.ico { access_log off; log_not_found off; }
          location = /robots.txt { access_log off; log_not_found off; }

          error_page 404 /index.php;
        '';

        locations = {
          "/" = {
            tryFiles = "$uri $uri/ /index.php?$query_string";
          };

          "~ ^/index\\.php(/|$)" = {
            fastcgiParams = lib.mkForce {
              SCRIPT_FILENAME = "$realpath_root$fastcgi_script_name";
            };

            extraConfig = ''
              fastcgi_pass 127.0.0.1:${toString cfg.settings.port};
              include ${config.services.nginx.package}/conf/fastcgi_params;
              fastcgi_hide_header X-Powered-By;
            '';
          };

          "~ /\\.(?!well-known).*" = {
            extraConfig = ''
              deny all;
            '';
          };
        };
      };
    };
  };
}
