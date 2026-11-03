<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JemputAman</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">JemputAman</h1>
            <p class="text-sm text-gray-600">Sistem Serah Terima Anak Antar-Jemput</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                Masuk
            </button>
        </form>

        <div class="mt-6 pt-4 border-t text-xs text-gray-500">
            <p class="font-semibold mb-1">Akun Percobaan (Password: password):</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li>Admin: <code>admin@jemputaman.test</code></li>
                <li>Orang Tua: <code>ortu1@jemputaman.test</code></li>
                <li>Pengemudi: <code>driver1@jemputaman.test</code></li>
                <li>Penerima: <code>penerima1@jemputaman.test</code></li>
            </ul>
        </div>
    </div>
</body>
</html>
