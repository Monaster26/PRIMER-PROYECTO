<script setup lang="ts">
</script>

<template>
    <section id="sucursales" class="py-20 bg-[#FFF5F8] dark:bg-zinc-900 border-y border-zinc-100 dark:border-zinc-800 transition-colors relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col lg:flex-row gap-12 lg:gap-16 items-start">
                
                <!-- Info Column -->
                <div class="lg:w-1/3 pt-4">
                    <h2 class="text-5xl md:text-6xl font-display font-bold text-primario mb-6">
                        Encuéntranos
                    </h2>
                    <div class="w-16 h-1 bg-secundario rounded-full mb-8"></div>
                    
                    <p class="text-zinc-700 dark:text-zinc-300 font-sans text-lg mb-10 leading-relaxed font-medium text-balance">
                        Ven a visitarnos a nuestra casa matriz o coordina tu retiro en tienda. Estamos ubicados en el corazón de Santiago para tu comodidad.
                    </p>

                    <div class="space-y-6">
                        <!-- Sede Card -->
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 flex items-start gap-5 shadow-sm border border-zinc-50 dark:border-zinc-800">
                            <div class="w-12 h-12 bg-[#FFF0F4] dark:bg-primario/10 rounded-full flex items-center justify-center text-primario text-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white text-lg font-display tracking-wide mb-1">Sede Principal</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 font-medium">AV Matta 833, Santiago, Chile</p>
                            </div>
                        </div>

                        <!-- Horario Card -->
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 flex items-start gap-5 shadow-sm border border-zinc-50 dark:border-zinc-800">
                            <div class="w-12 h-12 bg-[#FFF0F4] dark:bg-primario/10 rounded-full flex items-center justify-center text-primario text-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white text-lg font-display tracking-wide mb-1">Horario de Atención</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 font-medium">Lunes a Domingo: 7:00 – 20:00 hrs</p>
                            </div>
                        </div>

                        <!-- Telefono Card -->
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 flex items-start gap-5 shadow-sm border border-zinc-50 dark:border-zinc-800">
                            <div class="w-12 h-12 bg-[#FFF0F4] dark:bg-primario/10 rounded-full flex items-center justify-center text-primario text-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-zinc-900 dark:text-white text-lg font-display tracking-wide mb-1">Teléfono</h4>
                                <p class="text-zinc-600 dark:text-zinc-400 font-medium">+56 9 7462 4872</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Column -->
                <div class="lg:w-2/3 w-full">
                    <div class="bg-white p-3 rounded-[3rem] shadow-2xl shadow-zinc-200/50 dark:shadow-black/50 overflow-hidden h-[500px] w-full relative">
                        <!-- Mock Map using iframe to Google Maps for authenticity -->
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3329.131758509931!2d-70.6508821!3d-33.4589279!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9662c50e05eeaba5%3A0x6bba847bbcb3f5cb!2sAv.%20Manuel%20Antonio%20Matta%20833%2C%208360216%20Santiago%2C%20Regi%C3%B3n%20Metropolitana!5e0!3m2!1ses!2scl!4v1700000000000!5m2!1ses!2scl" 
                            width="100%" 
                            height="100%" 
                            style="border:0; border-radius: 2.5rem;" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>
