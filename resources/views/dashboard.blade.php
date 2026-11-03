<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - JemputAman</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">
            Halo, {{ auth()->user()->name }} ({{ auth()->user()->role }})
        </h1>
        <p class="text-gray-600 mb-6">
            Selamat datang di sistem serah terima anak antar-jemput JemputAman.
        </p>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                Logout
            </button>
        </form>
    </div>
</body>
</html>
