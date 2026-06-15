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
    }
}>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 }).format(price);
};
</script>

<template>
    <div class="group relative bg-white dark:bg-zinc-900 rounded-[2.5rem] overflow-hidden border border-zinc-100 dark:border-zinc-800 hover:shadow-[0_32px_64px_-12px_rgba(255,46,122,0.15)] hover:border-primario/30 transition-all duration-500 flex flex-col h-full">
        <!-- Badges -->
        <div class="absolute top-6 left-6 z-10 flex flex-col gap-2">
            <span v-if="product.isNew" class="px-4 py-1.5 bg-zinc-900 text-white text-[10px] font-black uppercase rounded-full tracking-[0.2em] shadow-lg">
                Nuevo
            </span>
            <span v-if="product.isDiscount" class="px-4 py-1.5 bg-primario text-white text-[10px] font-black uppercase rounded-full tracking-[0.2em] shadow-lg">
                Elite Offer
            </span>
        </div>

        <!-- Image Area -->
        <div class="aspect-[4/5] bg-zinc-50 dark:bg-zinc-800/30 relative flex items-center justify-center overflow-hidden">
            <img 
                v-if="product.image" 
                :src="product.image" 
                :alt="product.name"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
            />
            <span v-else class="text-9xl group-hover:scale-110 group-hover:rotate-6 transition-transform duration-700 drop-shadow-2xl select-none">
                {{ product.imageEmogi }}
            </span>
            
            <!-- Gradient Overlay -->
            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

            <!-- Quick Add Button (Hover) -->
            <button class="absolute bottom-6 right-6 w-14 h-14 bg-white text-zinc-900 rounded-2xl flex items-center justify-center shadow-2xl transform translate-y-16 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500 hover:bg-secundario hover:scale-110 active:scale-95 z-20">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
            </button>
        </div>

        <!-- Details Area -->
        <div class="p-8 flex flex-col flex-grow">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-primario"></span>
                <p class="text-zinc-500 dark:text-zinc-400 text-[10px] font-black uppercase tracking-[0.2em]">{{ product.category }}</p>
            </div>
            
            <h3 class="text-xl font-black text-zinc-900 dark:text-white mb-6 line-clamp-2 leading-snug group-hover:text-primario transition-colors">
                {{ product.name }}
            </h3>
            
            <div class="mt-auto flex items-center justify-between">
                <div class="flex flex-col">
                    <span v-if="product.oldPrice" class="text-xs text-zinc-400 line-through font-bold mb-0.5">
                        {{ formatPrice(product.oldPrice) }}
                    </span>
                    <span class="text-3xl font-black text-zinc-900 dark:text-white tracking-tighter">
                        {{ formatPrice(product.price) }}
                    </span>
                </div>
                
                <div class="flex items-center gap-1 text-secundario">
                    <span class="text-xs font-black">4.9</span>
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                </div>
            </div>
            
            <!-- Premium Action Button -->
            <button class="mt-8 w-full py-4 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 text-zinc-900 dark:text-white font-black text-sm uppercase tracking-widest hover:bg-zinc-900 hover:text-white dark:hover:bg-primario transition-all active:scale-95 border border-zinc-100 dark:border-zinc-700">
                Añadir al Carrito
            </button>
        </div>
    </div>
</template>

