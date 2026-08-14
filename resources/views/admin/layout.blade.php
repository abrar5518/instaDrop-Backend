<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InstaDrop Admin Panel — Executive Dispatch Desk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 min-h-screen flex antialiased">

    <!-- SLEEK EXECUTIVE SIDEBAR -->
    <aside class="w-64 bg-[#0a192f] border-r border-slate-800 flex flex-col justify-between p-6 shrink-0 shadow-xl z-20">
        <div class="space-y-8">
            <!-- Brand Logo -->
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-[#0a192f] text-[#c6ff00] flex items-center justify-center font-black border border-[#c6ff00]/40 shadow-md group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-[#c6ff00]" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <div>
                    <span class="font-extrabold text-xl tracking-tight text-white font-display">Insta<span class="text-[#c6ff00]">Drop</span></span>
                    <span class="block text-[9px] font-extrabold uppercase tracking-widest text-[#c6ff00]">Admin Control</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="space-y-1.5 text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#c6ff00] text-[#0a192f] font-extrabold shadow-md' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-[#0a192f]' : 'text-[#c6ff00]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard Overview</span>
                </a>

                <a href="{{ route('admin.quotes.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.quotes*') ? 'bg-[#c6ff00] text-[#0a192f] font-extrabold shadow-md' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.quotes*') ? 'text-[#0a192f]' : 'text-[#c6ff00]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>Quote Requests</span>
                </a>

                <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.orders*') ? 'bg-[#c6ff00] text-[#0a192f] font-extrabold shadow-md' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.orders*') ? 'text-[#0a192f]' : 'text-[#c6ff00]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <span>Orders & Tracking</span>
                </a>

                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.settings*') ? 'bg-[#c6ff00] text-[#0a192f] font-extrabold shadow-md' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.settings*') ? 'text-[#0a192f]' : 'text-[#c6ff00]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>System Settings</span>
                </a>
            </nav>
        </div>

        <div class="text-[10px] text-slate-400 border-t border-slate-800 pt-4 space-y-1">
            <p class="font-bold text-slate-300">InstaDrop Dispatch v2.0</p>
            <p>24/7 UK Courier Brokerage</p>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA WITH TOP CONTROL HEADER -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- TOP CONTROL BAR HEADER -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between gap-4 shrink-0 shadow-xs z-10">
            <!-- Left Quick Search Bar -->
            <div class="relative max-w-md w-full">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="Quick search quote #, customer name, or postcode..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0a192f] focus:bg-white transition-all">
            </div>

            <!-- Right Status & Date Badges -->
            <div class="flex items-center gap-4 text-xs font-semibold">
                <div class="flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[11px] font-bold">DISPATCH SYSTEM ONLINE</span>
                </div>
                <div class="hidden sm:block text-slate-500 font-medium">
                    {{ date('l, d M Y') }}
                </div>
                <div class="w-8 h-8 rounded-full bg-[#0a192f] text-[#c6ff00] flex items-center justify-center font-bold text-xs shadow-xs">
                    AD
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT CONTAINER -->
        <main class="flex-1 overflow-y-auto p-8 space-y-8">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-xs font-bold flex items-center justify-between animate-in fade-in">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-extrabold text-sm">✕</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
