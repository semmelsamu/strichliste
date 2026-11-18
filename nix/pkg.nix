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

    src = "${php-app}/share/php/${name}-php/";

    npmDepsHash = "sha256-TZl0+GcdD+D0+ChzcAy0FoZRNZep5USDmNAnKO/SauY=";

    # Don't run the postinstall composer/npm scripts; we only want `vite build`.
    npmPackFlags = [ "--ignore-scripts" ];

    installPhase = ''
      runHook preInstall
      mkdir -p $out
      cp -r * $out/
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

    vendorHash = "sha256-tl4Fl3al2GuCcSKGBDjaOKwJ46iNOe+DoSUv8Khnt08=";

    composerNoDev = true;

    # Drop the built frontend assets into the installed app tree.
    postInstall = ''
      appDir="$out/share/php/${name}-php"
      mkdir -p "$appDir/public/build"
    '';
  };

in

assets
