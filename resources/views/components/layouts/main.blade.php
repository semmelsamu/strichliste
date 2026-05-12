<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ $title }} | Strichliste der FSIM</title>

    @vite (['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="bg-fsim-dark mx-auto flex h-screen max-w-5xl flex-col overflow-y-hidden"
>
    {{ $slot }}
</body>
</html>
