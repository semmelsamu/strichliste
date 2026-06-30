<p align="center"><a href="https://strichliste.fsim-ev.de" target="_blank">
<img src="banner.jpg" alt="Strichliste der Fachschaft IM" 
style="padding-top: 1rem;"></a></p>

## About

This is a digital tally sheet ("Strichliste") for our snack / drink bar. Users can create a "prepaid" account, deposit money and buy articles with their balance. Admins can manage articles, categories and users. Some easter eggs are also installed :)

## Technical Overview

This application runs on the TALL Stack:

- [Laravel](https://laravel.com/) as the full-stack, batteries included framework
- [Livewire](https://livewire.laravel.com/) for server-side interactivity
- [Alpine.js](https://alpinejs.dev/) for lightweight interactivity
- [Tailwind CSS](https://tailwindcss.com/) as a CSS framework

Additional technologies used:

- [Vite](https://vite.dev/) for asset bundling in TypeScript
- [Pulse](https://pulse.laravel.com/) for real-time vitals

The production system deployment is set up as follows:

- [Nix](https://nixos.org/) for building the application
- [PostgreSQL](https://www.postgresql.org/) as the production database

## Development

Make sure to install [PHP](https://php.net/), [Composer](https://getcomposer.org/), [Node and NPM](https://nodejs.org/).

Clone the repo and copy your `.env.example` to `.env` and fill in the missing keys.

The application expects a storage directory to be present in the public folder. You should symlink it like so:

```sh
php artisan storage:link
```

Next, set up the database by running the migrations and initial seeders:

```sh
php artisan migrate:fresh --seed
```

If you want the application to contain some additional dummy data, also run the dummy data seeder:

```sh
php artisan db:seed --class=DummyDataSeeder
```

Start the development server with:

```sh
composer run dev
```

The application should now run on [localhost:8000](http://localhost:8000/). A root user is already created. You can log in as that user with the following credentials:

```
Username: root
Password: root
```

## Deployment

Deployment is automated with Nix. We deploy on our own dedicated server running NixOS. General deployment information is available in the [Laravel documentation](https://laravel.com/docs/13.x/deployment).
