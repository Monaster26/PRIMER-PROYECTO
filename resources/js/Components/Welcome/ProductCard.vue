<script setup lang="ts">
defineProps<{
    product: {
        id: number;
        name: string;
        category: string;
        price: number;
        oldPrice?: number;
        imageEmogi?: string;
        image?: string;
        isNew?: boolean;
        isDiscount?: boolean;
    };
}>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        maximumFractionDigits: 0,
    }).format(price);
};
</script>

<template>
    <div
        class="hover:border-primario/30 group relative flex h-full flex-col overflow-hidden rounded-[2.5rem] border border-zinc-100 bg-white transition-all duration-500 hover:shadow-[0_32px_64px_-12px_rgba(255,46,122,0.15)] dark:border-zinc-800 dark:bg-zinc-900"
    >
        <!-- Badges -->
        <div class="absolute left-6 top-6 z-10 flex flex-col gap-2">
            <span
                v-if="product.isNew"
                class="rounded-full bg-zinc-900 px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-white shadow-lg"
            >
                Nuevo
            </span>
            <span
                v-if="product.isDiscount"
                class="bg-primario rounded-full px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-white shadow-lg"
            >
                Elite Offer
            </span>
        </div>

        <!-- Image Area -->
        <div
            class="relative flex aspect-[4/5] items-center justify-center overflow-hidden bg-zinc-50 dark:bg-zinc-800/30"
        >
            <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
            />
            <span
                v-else
                class="select-none text-9xl drop-shadow-2xl transition-transform duration-700 group-hover:rotate-6 group-hover:scale-110"
            >
                {{ product.imageEmogi }}
            </span>

            <!-- Gradient Overlay -->
            <div
                class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/20 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"
            ></div>

            <!-- Quick Add Button (Hover) -->
            <button
                class="hover:bg-secundario absolute bottom-6 right-6 z-20 flex h-14 w-14 translate-y-16 transform items-center justify-center rounded-2xl bg-white text-zinc-900 opacity-0 shadow-2xl transition-all duration-500 hover:scale-110 active:scale-95 group-hover:translate-y-0 group-hover:opacity-100"
            >
                <svg
                    class="h-7 w-7"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
            </button>
        </div>

        <!-- Details Area -->
        <div class="flex flex-grow flex-col p-8">
            <div class="mb-3 flex items-center gap-2">
                <span class="bg-primario h-1.5 w-1.5 rounded-full"></span>
                <p
                    class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400"
                >
                    {{ product.category }}
                </p>
            </div>

            <h3
                class="group-hover:text-primario mb-6 line-clamp-2 text-xl font-black leading-snug text-zinc-900 transition-colors dark:text-white"
            >
                {{ product.name }}
            </h3>

            <div class="mt-auto flex items-center justify-between">
                <div class="flex flex-col">
                    <span
                        v-if="product.oldPrice"
                        class="mb-0.5 text-xs font-bold text-zinc-400 line-through"
                    >
                        {{ formatPrice(product.oldPrice) }}
                    </span>
                    <span
                        class="text-3xl font-black tracking-tighter text-zinc-900 dark:text-white"
                    >
                        {{ formatPrice(product.price) }}
                    </span>
                </div>

                <div class="text-secundario flex items-center gap-1">
                    <span class="text-xs font-black">4.9</span>
                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                        />
                    </svg>
                </div>
            </div>

            <!-- Premium Action Button -->
            <button
                class="dark:hover:bg-primario mt-8 w-full rounded-2xl border border-zinc-100 bg-zinc-50 py-4 text-sm font-black uppercase tracking-widest text-zinc-900 transition-all hover:bg-zinc-900 hover:text-white active:scale-95 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-white"
            >
                Añadir al Carrito
            </button>
        </div>
    </div>
</template>
