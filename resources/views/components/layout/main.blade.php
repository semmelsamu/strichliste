@use (\App\Models\Sound)

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ $title }} | Strichliste der FSIM</title>

    @fonts
    @vite (['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="relative mx-auto flex max-w-6xl flex-col bg-fsim-dark">
    <x-version-badge />

    {{ $slot }}

    <x-toast.toaster>
        @if (session('toast'))
            <x-toast :type="session('toast.type')">
                {{ session('toast.message') }}
            </x-toast>
        @endif
        @foreach (isset($errors) ? $errors->all() : [] as $error)
            <x-toast type="error">{{ $error }}</x-toast>
        @endforeach
    </x-toast.toaster>

    @if (session('sound') && Sound::get(session("sound")) != null)
        <audio
            id="sound"
            preload="auto"
            src="{{ Sound::get(session("sound"))->url() }}"
        ></audio>
        <script>
            document.addEventListener("DOMContentLoaded", () =>
                document
                    .getElementById("sound")
                    .play()
                    .catch(function (e) {
                        // Autoplay still blocked (e.g. user never interacted with the site at all)
                        console.warn("Sound play failed:", e);
                    }),
            );
        </script>
    @endif
</body>
</html>
