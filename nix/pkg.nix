{
  php,
  buildNpmPackage,
  stdenvNoCC,
  ...
}:
let
  version = "1.1.0";
  name = "semmel-strichliste";

  # Frontend assets (Vite -> public/build). Built independently of PHP.
  assets = buildNpmPackage {
    pname = "${name}-assets";
    inherit version;

    src = ../.;

    npmDepsHash = "sha256-lWnpET0U/gEBUE8jr4orQXBnluSM4cAvskQff+pW0jk=";

    # Don't run the postinstall composer/npm scripts; we only want `vite build`.
    npmPackFlags = [ "--ignore-scripts" ];

    installPhase = ''
      runHook preInstall
      mkdir -p $out
      cp -r public/build/* $out/
      runHook postInstall
    '';
  };

  # PHP application (composer, production deps only).
  #
  # NOTE: We deliberately do NOT run `php artisan optimize` / `config:cache`
  # here. Caching config at build time would (a) bake values from the empty
  # build sandbox and (b) make Laravel ignore the runtime environment entirely.
  # All artisan caching happens at runtime in the NixOS module, once a real
  # `.env` and writable `bootstrap/cache` exist.
  php-app = php.buildComposerProject {
    pname = "${name}-php";
    inherit version;

    src = ../.;

    vendorHash = "sha256-k1VTqHv66gUk7mHDtHNSQTh3opv861SlgX+HpTgWPWM=";

    composerNoDev = true;

    # Drop the built frontend assets into the installed app tree.
    postInstall = ''
      appDir="$out/share/php/${name}-php"
      mkdir -p "$appDir/public/build"
      cp -r ${assets}/* "$appDir/public/build/"
    '';
  };

in
# Thin wrapper so the application lives at the package root, which keeps the
# NixOS module paths simple (`${cfg.package}/public`, `${cfg.package}/artisan`).
stdenvNoCC.mkDerivation {
  pname = name;
  inherit version;

  dontUnpack = true;

  installPhase = ''
    runHook preInstall
    mkdir -p $out
    cp -r ${php-app}/share/php/${name}-php/. $out/
    runHook postInstall
  '';
}
