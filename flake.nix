{
  description = "A very basic flake";

  inputs = {
    nixpkgs.url = "github:nixos/nixpkgs?ref=nixos-unstable";
  };

  outputs =
    { self, nixpkgs }@inputs:
    let
      pkgs = import nixpkgs { system = "x86_64-linux"; };
    in
    {
      packages.x86_64-linux.default = pkgs.callPackage ./nix/pkg.nix { };
      nixosModules.strichliste = import ./nix/options.nix self;
      devShells.x86_64-linux.default = pkgs.mkShell {
        nativeBuildInputs = with pkgs; [
          php
          phpPackages.composer
          nodejs
          vitejs
        ];
      };
    };
}
