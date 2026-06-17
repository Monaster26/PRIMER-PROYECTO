<template>
    <footer class="relative overflow-hidden bg-zinc-900 pb-10 pt-20">
        <!-- Decorator Background -->
        <div
            class="bg-primario/5 pointer-events-none absolute right-0 top-0 h-[500px] w-[500px] rounded-full blur-[100px]"
        ></div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="mb-16 grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-4"
            >
                <!-- Info -->
                <div class="space-y-6 lg:col-span-1">
                    <div class="flex items-center gap-2">
                        <div
                            class="from-primario to-acento flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br text-xl font-black text-white"
                        >
                            M
                        </div>
                        <span
                            class="text-xl font-black tracking-tight text-white"
                            >MONASTERIOS<br /><span
                                class="text-primario text-sm"
                                >MARKET</span
                            ></span
                        >
                    </div>
                    <p class="text-sm leading-relaxed text-zinc-400">
                        Tu minimarket de confianza, ahora en la palma de tu
                        mano. Llevando calidad, frescura y rapidez a tu hogar.
                    </p>
                    <div class="flex gap-4">
                        <a
                            href="#"
                            class="hover:bg-primario flex h-10 w-10 transform items-center justify-center rounded-full bg-zinc-800 text-zinc-400 transition-all hover:-translate-y-1 hover:text-white"
                        >
                            📱
                            <!-- Logo Inst, FB, etc placeholder -->
                        </a>
                        <a
                            href="#"
                            class="hover:bg-secundario flex h-10 w-10 transform items-center justify-center rounded-full bg-zinc-800 text-zinc-400 transition-all hover:-translate-y-1 hover:text-zinc-900"
                        >
                            💬
                        </a>
                    </div>
                </div>

                <!-- Links 1 -->
                <div>
                    <h4
                        class="mb-6 font-black uppercase tracking-wider text-white"
                    >
                        Navegación
                    </h4>
                    <ul class="space-y-3">
                        <li>
                            <a
                                href="#"
                                class="hover:text-primario text-sm font-medium text-zinc-400 transition-colors"
                                >Inicio</a
                            >
                        </li>
                        <li>
                            <a
                                href="#categorias"
                                class="hover:text-primario text-sm font-medium text-zinc-400 transition-colors"
                                >Catálogo</a
                            >
                        </li>
                        <li>
                            <a
                                href="#ofertas"
                                class="hover:text-primario text-sm font-medium text-zinc-400 transition-colors"
                                >Ofertas</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="hover:text-primario text-sm font-medium text-zinc-400 transition-colors"
                                >Nosotros</a
                            >
                        </li>
                    </ul>
                </div>

                <!-- Links 2 -->
                <div>
                    <h4
                        class="mb-6 font-black uppercase tracking-wider text-white"
                    >
                        Legal
                    </h4>
                    <ul class="space-y-3">
                        <li>
                            <a
                                href="#"
                                class="hover:text-primario text-sm font-medium text-zinc-400 transition-colors"
                                >Términos y Condiciones</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="hover:text-primario text-sm font-medium text-zinc-400 transition-colors"
                                >Políticas de Privacidad</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="hover:text-primario text-sm font-medium text-zinc-400 transition-colors"
                                >Despachos y Retiros</a
                            >
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4
                        class="mb-6 font-black uppercase tracking-wider text-white"
                    >
                        Contacto
                    </h4>
                    <ul class="space-y-4">
                        <li
                            class="flex items-start gap-3 text-sm text-zinc-400"
                        >
                            <span class="text-xl">📍</span>
                            <span
                                >Avenida Siempre Viva 123,<br />Santiago,
                                Chile</span
                            >
                        </li>
                        <li
                            class="flex items-center gap-3 text-sm text-zinc-400"
                        >
                            <span class="text-xl">📞</span>
                            <span>+56 9 1234 5678</span>
                        </li>
                        <li
                            class="flex items-center gap-3 text-sm text-zinc-400"
                        >
                            <span class="text-xl">✉️</span>
                            <span>contacto@monasteriosmarket.cl</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom -->
            <div
                class="flex flex-col items-center justify-between gap-4 border-t border-zinc-800 pt-8 md:flex-row"
            >
                <p class="text-sm text-zinc-500">
                    &copy; 2024 Monasterios Market. Todos los derechos
                    reservados.
                </p>
                <div
                    class="flex cursor-crosshair items-center gap-4 text-2xl opacity-50 grayscale transition-all hover:opacity-100 hover:grayscale-0"
                >
                    💳
                    <!-- Placeholder payment methods -->
                    <span
                        class="ml-2 rounded bg-zinc-800 px-2 py-1 text-sm font-black text-white"
                        >WEBPAY</span
                    >
                </div>
            </div>
        </div>
    </footer>
</template>
