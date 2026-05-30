<section class="relative overflow-hidden bg-white pt-28 pb-16 sm:pt-32 sm:pb-20 lg:pt-34 lg:pb-32">
    <!-- Decorative background glow -->
    <div
        class="absolute top-0 right-0 -translate-y-12 translate-x-12 w-[600px] h-[600px] bg-blue-50/50 rounded-full blur-3xl -z-10">
    </div>

    <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">

            <!-- Left Side: Content -->
            <div class="relative z-20 space-y-8 sm:space-y-10 lg:space-y-12">
                <div class="space-y-5 sm:space-y-6">
                    <h1
                        class="text-[42px] sm:text-5xl md:text-6xl lg:text-[68px] font-bold tracking-tighter text-[#1B1B18] leading-[1.12] sm:leading-[1.18] lg:leading-[1.25] max-w-full break-words">
                        Pusat Aktivitas <br class="hidden sm:block">
                        & Kolaborasi <br class="hidden sm:block">
                        <span class="block lg:inline">Mahasiswa Kampus</span>
                    </h1>

                    <p class="text-base sm:text-lg text-gray-600 max-w-md leading-relaxed font-medium">
                        Younifirst membantu mahasiswa menemukan event kampus, kompetisi, berkolaborasi dengan sesama
                        mahasiswa, dan melaporkan barang hilang semua dalam satu platform.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 pt-0 sm:-mt-3 lg:-mt-5">

                    <a href="#"
                        class="w-full sm:w-48 justify-center flex items-center py-2.5 text-[14px] font-bold text-white bg-[#0A3EBA] rounded-full hover:bg-[#344ed4] shadow-lg shadow-blue-500/20 transition-all transform hover:-translate-y-1">
                        Download Aplikasi
                    </a>

                    <a href="{{ route('login') }}"
                        class="w-full sm:w-48 justify-center flex items-center space-x-2 py-2.5 text-[14px] font-bold text-gray-700 border border-gray-200 rounded-full hover:bg-gray-50 transition-all group">
                        <span>Login Admin</span>
                        <svg class="w-3.5 h-3.5 opacity-70 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="7" y1="17" x2="17" y2="7"></line>
                            <polyline points="7 7 17 7 17 17"></polyline>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Right Side: Tilted Hero Image -->
            <div class="hidden lg:flex relative lg:static items-center justify-center h-full overflow-hidden lg:overflow-visible">
                <div
                    class="w-full max-w-[420px] sm:max-w-[520px] lg:max-w-none lg:absolute lg:right-[-19%] lg:top-1/2 lg:-translate-y-1/2 lg:w-[60%] xl:w-[65%] pointer-events-none z-10">
                    <img src="{{ asset('images/landingPage/hero.png') }}" alt="Hero Decoration"
                        class="w-full h-auto object-contain">
                </div>
            </div>
        </div>
    </div>
</section>
