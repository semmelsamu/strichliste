{
  php,
  buildNpmPackage,
  lib,
  pkgs,
  ...
}:
let
  frontend = buildNpmPackage rec {

    name = "strichliste-frontend";
    version = "0.1";
    pname = "${name}-${version}";

    src = ../.;

    npmDepsHash = "sha256-fRxzAHfc0z52QJWWYByLbSzHmunOdjy4xQX1IspM0Fg=";

    npmPackFlags = [ "--ignore-scripts" ];

    installPhase = ''
      mkdir -p $out
      cp -r public/build/* $out
    '';
  };
in

php.buildComposerProject rec {
  src = ../.;
  name = "strichliste";
  version = "0.1";
  pname = "${name}-${version}";
  vendorHash = "sha256-VVISoVd1LAG1c5m8mvopGRhg/Ds8hUe0AGIHPfm77Jg=";

  nativeBuildInputs = with pkgs; [
    php
  ];

  installPhase = ''
    mkdir -p $out

    cp -r * $out

    mkdir -p $out/public/build
    cp -r ${frontend}/* $out/public/build/
  '';

  # fixupPhase = ''
  #   php artisan optimize
  # '';
}
