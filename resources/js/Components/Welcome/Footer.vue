<template>
    <footer class="bg-zinc-900 pt-20 pb-10 overflow-hidden relative">
        <!-- Decorator Background -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primario/5 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                <!-- Info -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-gradient-to-br from-primario to-acento text-white font-black text-xl">
                            M
                        </div>
                        <span class="text-xl font-black tracking-tight text-white">MONASTERIOS<br/><span class="text-primario text-sm">MARKET</span></span>
                    </div>
                    <p class="text-zinc-400 text-sm leading-relaxed">
                        Tu minimarket de confianza, ahora en la palma de tu mano. Llevando calidad, frescura y rapidez a tu hogar.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-400 hover:bg-primario hover:text-white transition-all transform hover:-translate-y-1">
                            📱 <!-- Logo Inst, FB, etc placeholder -->
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-400 hover:bg-secundario hover:text-zinc-900 transition-all transform hover:-translate-y-1">
                            💬
                        </a>
                    </div>
                </div>

                <!-- Links 1 -->
                <div>
                    <h4 class="text-white font-black uppercase tracking-wider mb-6">Navegación</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-zinc-400 hover:text-primario transition-colors text-sm font-medium">Inicio</a></li>
                        <li><a href="#categorias" class="text-zinc-400 hover:text-primario transition-colors text-sm font-medium">Catálogo</a></li>
                        <li><a href="#ofertas" class="text-zinc-400 hover:text-primario transition-colors text-sm font-medium">Ofertas</a></li>
                        <li><a href="#" class="text-zinc-400 hover:text-primario transition-colors text-sm font-medium">Nosotros</a></li>
                    </ul>
                </div>

                <!-- Links 2 -->
                <div>
                    <h4 class="text-white font-black uppercase tracking-wider mb-6">Legal</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-zinc-400 hover:text-primario transition-colors text-sm font-medium">Términos y Condiciones</a></li>
                        <li><a href="#" class="text-zinc-400 hover:text-primario transition-colors text-sm font-medium">Políticas de Privacidad</a></li>
                        <li><a href="#" class="text-zinc-400 hover:text-primario transition-colors text-sm font-medium">Despachos y Retiros</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-white font-black uppercase tracking-wider mb-6">Contacto</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3 text-zinc-400 text-sm">
                            <span class="text-xl">📍</span>
                            <span>Avenida Siempre Viva 123,<br/>Santiago, Chile</span>
                        </li>
                        <li class="flex items-center gap-3 text-zinc-400 text-sm">
                            <span class="text-xl">📞</span>
                            <span>+56 9 1234 5678</span>
                        </li>
                        <li class="flex items-center gap-3 text-zinc-400 text-sm">
                            <span class="text-xl">✉️</span>
                            <span>contacto@monasteriosmarket.cl</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom -->
            <div class="pt-8 border-t border-zinc-800 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-zinc-500 text-sm">
                    &copy; 2024 Monasterios Market. Todos los derechos reservados.
                </p>
                <div class="flex items-center gap-4 text-2xl grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all cursor-crosshair">
                    💳 <!-- Placeholder payment methods -->
                    <span class="text-sm font-black text-white ml-2 bg-zinc-800 px-2 py-1 rounded">WEBPAY</span>
                </div>
            </div>
        </div>
    </footer>
</template>
