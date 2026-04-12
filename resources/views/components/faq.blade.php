<section class="relative bg-white pb-32" x-data="{ activeAccordion: 1, activeCategory: 'Semua Topik' }">
    <!-- Blue Header Background (Connects to Features) -->
    <div class="bg-[#0A3EBA] pt-20 pb-48 relative">
        <!-- Decorative Assets - Pinned to the top edges -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <img src="{{ asset('images/landingPage/faq.png') }}" alt="FAQ Decoration"
                class="w-full h-full object-contain object-top opacity-100 scale-200 translate-y-[70%] translate-x-[1%]">
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 text-center pt-12">
            <!-- Section Header -->
            <div class="inline-block relative">
                <h2 class="text-3xl lg:text-4xl font-bold text-white tracking-tight mx-4">FAQ</h2>
            </div>
            <p class="text-blue-100 max-w-2xl mx-auto text-lg leading-relaxed font-medium mt-6">
                Temukan jawaban dari pertanyaan yang sering diajukan seputar penggunaan aplikasi Younifirst
            </p>
        </div>
    </div>

    <!-- Main FAQ Overlap Container -->
    <div class="relative z-20 max-w-7xl mx-auto px-6 lg:px-8 -mt-40">
        <div class="bg-white rounded-[40px] p-6 lg:p-10 shadow-2xl relative border border-gray-100">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

                <!-- Left Sidebar: Categories -->
                <div class="lg:col-span-3 space-y-3">
                    @php
                        $categories = [
                            ['name' => 'Semua Topik', 'count' => 10, 'color' => 'blue', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                            ['name' => 'Event Center', 'count' => 2, 'color' => 'blue', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                            ['name' => 'Team Builder', 'count' => 2, 'color' => 'teal', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197'],
                            ['name' => 'Lost & Found', 'count' => 1, 'color' => 'amber', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
                            ['name' => 'Notifications', 'count' => 1, 'color' => 'indigo', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                            ['name' => 'Akun & Lainnya', 'count' => 5, 'color' => 'emerald', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z'],
                        ];
                        
                        $colorMap = [
                            'blue' => 'text-blue-500 bg-blue-50',
                            'teal' => 'text-teal-500 bg-teal-50',
                            'amber' => 'text-amber-500 bg-amber-50',
                            'indigo' => 'text-indigo-500 bg-indigo-50',
                            'emerald' => 'text-emerald-500 bg-emerald-50',
                        ];
                    @endphp

                    @foreach($categories as $cat)
                        <button @click="activeCategory = '{{ $cat['name'] }}'"
                            :class="activeCategory === '{{ $cat['name'] }}' ? 'bg-[#0A3EBA] text-white shadow-[0_15px_30px_-5px_rgba(10,62,186,0.3)] scale-[1.02]' : 'bg-[#F8F9FE] text-gray-700 hover:bg-white border border-gray-100 shadow-sm'"
                            class="w-full flex items-center justify-between px-5 py-4 rounded-[22px] transition-all duration-300 group">
                            <div class="flex items-center space-x-3">
                                <span class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                                    :class="activeCategory === '{{ $cat['name'] }}' ? 'bg-white/20 text-white' : '{{ $colorMap[$cat['color']] }} shadow-inner'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="{{ $cat['icon'] }}"></path>
                                    </svg>
                                </span>
                                <span class="font-bold text-[14px] tracking-tight">{{ $cat['name'] }}</span>
                            </div>
                            <span
                                :class="activeCategory === '{{ $cat['name'] }}' ? 'bg-white text-[#0A3EBA]' : 'bg-blue-100/50 text-blue-600'"
                                class="w-8 h-8 rounded-full flex items-center justify-center text-[10px] font-black shadow-sm transition-colors">{{ $cat['count'] }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Center: Card-based Accordion Area -->
                <div class="lg:col-span-6 space-y-4">
                    @php
                        $faqs = [
                            ['id' => 1, 'q' => 'Apa itu Younifirst?', 'a' => 'Younifirst adalah platform kolaborasi mahasiswa yang dirancang untuk memudahkan interaksi, pencarian event, dan kolaborasi tim di lingkungan kampus.'],
                            ['id' => 2, 'q' => 'Bagaimana cara menggunakan fitur Event Center?', 'a' => 'Anda bisa menjelajahi berbagai event kampus di menu Event Center, memfilter berdasarkan kategori, dan mendaftar langsung melalui aplikasi.'],
                            ['id' => 3, 'q' => 'Apa itu Team Builder dan bagaimana cara kerjanya?', 'a' => 'Team Builder memungkinkan Anda mencari rekan tim untuk kompetisi atau proyek dengan melihat profil keahlian mahasiswa lain.'],
                            ['id' => 4, 'q' => 'Bagaimana cara melaporkan barang di Lost & Found?', 'a' => 'Cukup buka menu Lost & Found, klik tombol lapor, unggah foto barang, dan berikan deskripsi serta lokasi penemuan.'],
                            ['id' => 5, 'q' => 'Apa saja notifikasi yang akan saya terima?', 'a' => 'Anda akan menerima notifikasi pendaftaran event, permintaan bergabung tim, pesan masuk, dan pengumuman resmi kampus.'],
                            ['id' => 6, 'q' => 'Apakah saya harus login untuk menggunakan Younifirst?', 'a' => 'Ya, untuk keamanan dan validasi mahasiswa, Anda harus login menggunakan akun institusi atau email terdaftar.'],
                            ['id' => 7, 'q' => 'Apakah Younifirst gratis digunakan?', 'a' => 'Ya, semua fitur utama Younifirst dapat diakses secara gratis oleh seluruh mahasiswa universitas mitra.'],
                            ['id' => 8, 'q' => 'Bagaimana jika saya tidak menemukan event atau tim yang cocok?', 'a' => 'Anda bisa menyetel preferensi minat Anda agar sistem dapat memberikan rekomendasi yang lebih akurat di masa mendatang.'],
                            ['id' => 9, 'q' => 'Apakah data saya aman di Younifirst?', 'a' => 'Tentu. Privasi data Anda adalah prioritas kami. Data akademik hanya digunakan untuk validasi status kemahasiswaan.'],
                            ['id' => 10, 'q' => 'Siapa yang bisa saya hubungi jika mengalami masalah?', 'a' => 'Anda dapat menghubungi tim support kami melalui tombol "Hubungi Support" atau mengirim email ke support@younifirst.id.']
                        ];
                    @endphp

                    @foreach($faqs as $faq)
                        <div class="bg-white border border-gray-100 rounded-[22px] shadow-sm hover:shadow-md transition-all duration-300">
                            <button @click="activeAccordion = activeAccordion === {{ $faq['id'] }} ? 0 : {{ $faq['id'] }}"
                                class="w-full flex items-center justify-between px-6 py-5 text-left group transition-all">
                                <div class="flex items-center space-x-5">
                                    <!-- Circular Badge -->
                                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center transition-colors group-hover:bg-blue-100/80">
                                        <span class="text-[#0A3EBA] font-extrabold text-[14px]">0{{ $faq['id'] }}</span>
                                    </div>
                                    <span
                                        class="text-gray-900 font-bold tracking-tight text-[16px] group-hover:text-[#0A3EBA] transition-colors leading-tight">{{ $faq['q'] }}</span>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-500"
                                        :class="activeAccordion === {{ $faq['id'] }} ? 'rotate-180 text-[#0A3EBA]' : ''"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="activeAccordion === {{ $faq['id'] }}" 
                                 x-collapse.duration.500ms
                                 class="px-6 pb-6 pl-21">
                                <div class="bg-gray-50/80 rounded-2xl p-5 text-gray-600 text-[15px] leading-relaxed font-medium relative shadow-inner">
                                    <!-- Chat bubble tail effect -->
                                    <div class="absolute -top-2 left-6 w-4 h-4 bg-gray-50 rotate-45 border-l border-t border-gray-100/50"></div>
                                    {{ $faq['a'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Right Sidebar: Support Help -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="bg-[#F8FAFF] rounded-[32px] p-8 border border-blue-50 space-y-6 text-center shadow-inner">
                        <div class="flex justify-center">
                            <div class="relative">
                                <div class="absolute inset-0 bg-blue-500/10 blur-2xl rounded-full"></div>
                                <img src="{{ asset('images/landingPage/support.png') }}"
                                    alt="Support" class="w-32 h-32 object-contain relative z-10">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-lg font-black text-gray-900 tracking-tight leading-tight">Masih butuh bantuan?</h4>
                            <p class="text-gray-500 text-[13px] leading-relaxed font-medium">
                                Tim kami siap membantumu menjawab pertanyaanmu.
                            </p>
                        </div>
                        <button
                            class="w-full bg-[#0A3EBA] text-white py-4 rounded-2xl font-bold text-[14px] shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                            </svg>
                            <span>Hubungi Support</span>
                        </button>
                    </div>

                    <!-- Side Links -->
                    <div class="bg-white rounded-[24px] border border-gray-100 overflow-hidden divide-y divide-gray-100">
                        @php
                            $sideLinks = [
                                ['n' => 'Bantuan & Panduan', 'i' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['n' => 'Laporkan Masalah', 'i' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                                ['n' => 'Kirim Saran', 'i' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z']
                            ];
                        @endphp
                        @foreach($sideLinks as $link)
                            <a href="#"
                                class="flex items-center justify-between p-5 hover:bg-gray-50 transition-all group">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-[#0A3EBA]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['i'] }}"></path>
                                        </svg>
                                    </div>
                                    <span class="text-[13px] font-bold text-gray-700 group-hover:text-[#0A3EBA] transition-colors">{{ $link['n'] }}</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>