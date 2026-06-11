self:
{
  lib,
  config,
  pkgs,
  ...
}:
let
  cfg = config.services.semmelstrichliste;

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
in

{
  options.services.semmelstrichliste = {
    enable = mkEnableOption "enable the strichliste";

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

      storage = mkOption {
        type = types.path;
        description = "Path of the storage directory";
        default = "${cfg.paths.rootDir}/storage";
      };

      database = mkOption {
        type = types.path;
        description = "Path of the database file (only used when using sqlite)";
        default =
          if (cfg.database.configure && cfg.database.type == "sqlite") then
            "${cfg.paths.rootDir}/database.db"
          else
            null;
      };
    };

    settings = mkSubmoduleOption {
      domain = mkOption {
        type = types.str;
        description = "The domain of the app";
      };

      expectSSL = mkOption {
        type = types.bool;
        description = "Should the app expect https or not";
      };

      port = mkOption {
        type = types.port;
        description = "The port of phpfpm pool";
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
          "postgres"
        ];
      };
    };
  };

  config =
    let
      envVars = {

        APP_URL = "${if cfg.settings.expectSSL then "https" else "http"}://${cfg.settings.domain}";
        DB_CONNECTION = "${cfg.database.type}";
        DB_DATABASE = "${cfg.paths.database}";
        APP_BASE_PATH = "${cfg.paths.storage}";
        # APP_LOCALE = "de";
        # APP_CURRENCY = "EUR";
      };
    in
    mkIf cfg.enable {
      environment.systemPackages = [
        (pkgs.writeShellScriptBin "semmelstrichliste-php" ''
          ${lib.concatMapAttrsStringSep "\n" (name: value: "export ${name}=${value}") envVars}

          cd "${cfg.package}"

          export PATH=$PATH:${pkgs.php}/bin:${pkgs.sqlite}/bin

          "$@"
        '')
      ];
      systemd.services."strichliste-setup" = {
        environment = envVars;

        script =
          let
            php = lib.getExe pkgs.php;
            sudo = lib.getExe' pkgs.sudo "sudo";

            nonRootScript = pkgs.writeShellScript "setup" ''
              set -euo pipefail

              if [ -f "${cfg.paths.rootDir}/.is_setup" ]; then
              exit
              fi

              mkdir -p ${cfg.paths.storage}
              cp -u -r ${cfg.package}/storage/* "${cfg.paths.storage}"

              mkdir -p ${cfg.paths.storage}/bootstrap/
              cp -r ${cfg.package}/bootstrap/* ${cfg.paths.storage}/bootstrap


              chmod -R 755 ${cfg.paths.storage}

              cd "${cfg.package}"

              echo "Migrating..."
              ${php} artisan migrate --force

              echo "Seeding..."
              ${php} artisan db:seed --force

              echo "Done :)"

              cd "${cfg.paths.rootDir}"
              touch .is_setup
            '';
          in
          ''
            set -euo pipefail

            if [ ! -d "${cfg.paths.rootDir}" ]; then
                mkdir "${cfg.paths.rootDir}"
            fi

            chown ${config.services.nginx.user} ${cfg.paths.rootDir}

            ${sudo} -E -u ${config.services.nginx.user} ${nonRootScript}

          '';

        wantedBy = [ "multi-user.target" ];
      };
      services.nginx = {
        enable = true;
        virtualHosts = {
          ${cfg.settings.domain} = {
            root = "${cfg.package}/public";

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
                fastcgiParams = {
                  SCRIPT_FILENAME = "$realpath_root$fastcgi_script_name";

                }
                // envVars;

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

      services.phpfpm.pools.strichliste = {
        user = config.services.nginx.user;

        settings = {
          "listen" = "127.0.0.1:${toString cfg.settings.port}";
          "listen.owner" = config.services.nginx.user;
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
    };
}
