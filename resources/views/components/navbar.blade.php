<div class="fixed top-4 sm:top-6 left-0 right-0 z-50 px-3 sm:px-4">
    <nav class="w-full max-w-6xl mx-auto bg-white/95 backdrop-blur-md rounded-full shadow-md border border-gray-100 px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between gap-3 overflow-hidden">
        <!-- Logo Section -->
        <div class="flex items-center space-x-2 sm:space-x-3 shrink-0 min-w-0">
            <img src="{{ asset('images/logo.png') }}" alt="Younifirst Logo" class="w-7 h-7 sm:w-8 sm:h-8 object-contain shrink-0">
            <span class="text-[14px] sm:text-[18px] font-extrabold tracking-tight text-[#1B1B18] truncate">Younifirst</span>
        </div>

        <!-- Navigation Links (Centered & Spaced) -->
        <div class="hidden lg:flex items-center justify-center space-x-10 px-4">
            <a href="#" class="text-[14px] font-semibold text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap">Tentang Kami</a>
            <a href="#" class="text-[14px] font-semibold text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap">Fitur</a>
            <a href="#" class="text-[14px] font-semibold text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap">FAQ</a>
            <a href="#" class="text-[14px] font-semibold text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap">Tutorial</a>
            <a href="#" class="text-[14px] font-semibold text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap">Kontak Kami</a>
        </div>

        <!-- Buttons Section -->
        <div class="flex items-center space-x-2 sm:space-x-3 shrink-0">
            <a href="{{ route('login') }}" class="flex items-center px-3 sm:px-5 py-2 text-[12px] sm:text-[14px] font-bold text-gray-700 border border-gray-300 rounded-full hover:bg-gray-50 transition-all whitespace-nowrap">
                <span>Login</span>
                <svg class="w-3.5 h-3.5 ml-1.5 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="7" y1="17" x2="17" y2="7"></line>
                    <polyline points="7 7 17 7 17 17"></polyline>
                </svg>
            </a>
            <a href="#" class="hidden sm:flex items-center px-5 lg:px-6 py-2.5 text-[13px] lg:text-[14px] font-bold text-white bg-[#0A3EBA] rounded-full hover:bg-[#344ed4] transition-all shadow-xl shadow-blue-500/10 whitespace-nowrap">
                <span>Download Aplikasi</span>
                <svg class="w-4 h-4 ml-2.5 fill-current" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"></path>
                </svg>
            </a>
        </div>
    </nav>
</div>
