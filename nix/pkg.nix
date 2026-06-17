{
  php,
  buildNpmPackage,
  stdenv,
  lib,
  pkgs,
  ...
}:
let
  version = "0.1";
  name = "strichliste";

  npmDrv = buildNpmPackage {

    inherit version;
    name = "${name}-npm";
    pname = "${name}-npm-${version}";

    src = ../.;

    npmDepsHash = "sha256-kkmarT3o+dTHmWyynaWQbjneCi/Z8PMNh9erwqZ0dLE=";

    npmPackFlags = [ "--ignore-scripts" ];

    installPhase = ''
      mkdir -p $out
      cp -r public/build/* $out
    '';
  };

  phpDrv = php.buildComposerProject {
    inherit version;

    src = ../.;
    name = "${name}-php";
    pname = "${name}-php-${version}";

    vendorHash = "sha256-VVISoVd1LAG1c5m8mvopGRhg/Ds8hUe0AGIHPfm77Jg=";

    nativeBuildInputs = [
      pkgs.php
    ];

    fixupPhase = ''
      php artisan optimize
      php artisan icons:cache
    '';
  };

in
stdenv.mkDerivation {
  inherit name version;

  src = phpDrv;

  installPhase = ''
    mkdir -p $out
    cp -r share/php/${name}-php-${version}/* $out

    mkdir -p $out/public/build
    cp -r ${npmDrv}/* $out/public/build
  '';

}
