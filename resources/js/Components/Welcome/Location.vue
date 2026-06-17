<script setup lang="ts"></script>

<template>
    <section
        id="sucursales"
        class="relative overflow-hidden border-y border-zinc-100 bg-[#FFF5F8] py-20 transition-colors dark:border-zinc-800 dark:bg-zinc-900"
    >
        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-start gap-12 lg:flex-row lg:gap-16">
                <!-- Info Column -->
                <div class="pt-4 lg:w-1/3">
                    <h2
                        class="text-primario mb-6 font-display text-5xl font-bold md:text-6xl"
                    >
                        Encuéntranos
                    </h2>
                    <div class="bg-secundario mb-8 h-1 w-16 rounded-full"></div>

                    <p
                        class="mb-10 text-balance font-sans text-lg font-medium leading-relaxed text-zinc-700 dark:text-zinc-300"
                    >
                        Ven a visitarnos a nuestra casa matriz o coordina tu
                        retiro en tienda. Estamos ubicados en el corazón de
                        Santiago para tu comodidad.
                    </p>

                    <div class="space-y-6">
                        <!-- Sede Card -->
                        <div
                            class="flex items-start gap-5 rounded-2xl border border-zinc-50 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-800"
                        >
                            <div
                                class="dark:bg-primario/10 text-primario flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#FFF0F4] text-xl"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h4
                                    class="mb-1 font-display text-lg font-bold tracking-wide text-zinc-900 dark:text-white"
                                >
                                    Sede Principal
                                </h4>
                                <p
                                    class="font-medium text-zinc-600 dark:text-zinc-400"
                                >
                                    AV Matta 833, Santiago, Chile
                                </p>
                            </div>
                        </div>

                        <!-- Horario Card -->
                        <div
                            class="flex items-start gap-5 rounded-2xl border border-zinc-50 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-800"
                        >
                            <div
                                class="dark:bg-primario/10 text-primario flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#FFF0F4] text-xl"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h4
                                    class="mb-1 font-display text-lg font-bold tracking-wide text-zinc-900 dark:text-white"
                                >
                                    Horario de Atención
                                </h4>
                                <p
                                    class="font-medium text-zinc-600 dark:text-zinc-400"
                                >
                                    Lunes a Domingo: 7:00 – 20:00 hrs
                                </p>
                            </div>
                        </div>

                        <!-- Telefono Card -->
                        <div
                            class="flex items-start gap-5 rounded-2xl border border-zinc-50 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-800"
                        >
                            <div
                                class="dark:bg-primario/10 text-primario flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#FFF0F4] text-xl"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h4
                                    class="mb-1 font-display text-lg font-bold tracking-wide text-zinc-900 dark:text-white"
                                >
                                    Teléfono
                                </h4>
                                <p
                                    class="font-medium text-zinc-600 dark:text-zinc-400"
                                >
                                    +56 9 7462 4872
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Column -->
                <div class="w-full lg:w-2/3">
                    <div
                        class="relative h-[500px] w-full overflow-hidden rounded-[3rem] bg-white p-3 shadow-2xl shadow-zinc-200/50 dark:shadow-black/50"
                    >
                        <!-- Mock Map using iframe to Google Maps for authenticity -->
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3329.131758509931!2d-70.6508821!3d-33.4589279!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9662c50e05eeaba5%3A0x6bba847bbcb3f5cb!2sAv.%20Manuel%20Antonio%20Matta%20833%2C%208360216%20Santiago%2C%20Regi%C3%B3n%20Metropolitana!5e0!3m2!1ses!2scl!4v1700000000000!5m2!1ses!2scl"
                            width="100%"
                            height="100%"
                            style="border: 0; border-radius: 2.5rem"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        >
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
