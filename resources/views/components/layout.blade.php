<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'JemputAman — Sistem Antar-Jemput Sekolah' }}</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans antialiased">

    <!-- Top Navigation Bar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-brand-600 hover:text-brand-700 transition">
                        <div class="w-10 h-10 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-brand-500/20">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <div>
                            <span class="text-lg font-bold tracking-tight text-slate-900 block leading-tight">JemputAman</span>
                            <span class="text-xs text-slate-500 hidden sm:block">Armada Resmi SD JemputAman</span>
                        </div>
                    </a>
                </div>

                <!-- User Info & Action -->
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                            <span class="text-xs uppercase tracking-wider font-bold 
                                @if(auth()->user()->isAdmin()) text-purple-600 
                                @elseif(auth()->user()->isGuru()) text-emerald-600 
                                @elseif(auth()->user()->isPengemudi()) text-amber-600 
                                @else text-blue-600 @endif">
                                {{ str_replace('_', ' ', auth()->user()->role) }}
                            </span>
                        </div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" title="Keluar dari Aplikasi" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-700 transition">
                                <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                                <span class="hidden sm:inline">Logout</span>
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full flex-1">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            <!-- Sidebar Navigation -->
            <aside class="md:col-span-1 no-print">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 sticky top-24 space-y-2">
                    <div class="px-3 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Menu Utama
                    </div>

                    <!-- Dashboard Link -->
                    <a href="{{ route('dashboard') }}" 
                        class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>

                    @auth
                        @if(auth()->user()->isAdmin())
                            <div class="pt-4 px-3 py-1 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                Master Data (Fase 1)
                            </div>

                            <a href="{{ route('admin.anak.index') }}" 
                                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('admin.anak.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <i class="fa-solid fa-children w-5 text-center"></i>
                                <span>Data Siswa & QR</span>
                            </a>

                            <a href="{{ route('admin.pengemudi.index') }}" 
                                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('admin.pengemudi.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <i class="fa-solid fa-id-card w-5 text-center"></i>
                                <span>Driver & Armada</span>
                            </a>

                            <a href="{{ route('admin.jadwal.index') }}" 
                                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('admin.jadwal.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <i class="fa-solid fa-calendar-days w-5 text-center"></i>
                                <span>Jadwal Rutin</span>
                            </a>

                            <a href="{{ route('admin.sekolah.edit') }}" 
                                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('admin.sekolah.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <i class="fa-solid fa-school w-5 text-center"></i>
                                <span>Profil Sekolah</span>
                            </a>
                        @endif

                        @if(auth()->user()->isGuru())
                            <div class="pt-4 px-3 py-1 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                Piket Sekolah
                            </div>
                            <a href="{{ route('admin.anak.index') }}" 
                                class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('admin.anak.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                <i class="fa-solid fa-children w-5 text-center"></i>
                                <span>Daftar Siswa Jemputan</span>
                            </a>
                        @endif
                    @endauth
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="md:col-span-3 space-y-4">
                <!-- Flash Notification Messages -->
                @if (session('success'))
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start space-x-3 shadow-sm">
                        <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5"></i>
                        <div class="text-sm font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start space-x-3 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5"></i>
                        <div class="text-sm font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 shadow-sm">
                        <div class="flex items-center space-x-2 font-semibold text-sm mb-1">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                            <span>Terdapat beberapa kesalahan input:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-0.5 text-amber-700 ml-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Dynamic Content Slot -->
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto no-print">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            JemputAman &copy; 2026 · SD JemputAman Surakarta · Proyek Kelompok Praktikum RPL
        </div>
    </footer>

</body>
</html>
