<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>{{ $title }} | Strichliste der FSIM</title>

	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-fsim-dark flex flex-col min-h-svh overflow-y-auto">
    {{ $slot }}
</body>
</html>
