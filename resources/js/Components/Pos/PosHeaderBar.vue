<script setup lang="ts">
import { Plus, X, Check } from 'lucide-vue-next';
import type { TabState } from '@/Stores/posTabsStore';

defineProps<{
    tabs: TabState[];
    activeIndex: number;
    showSuccess: boolean;
    lastSaleId: number | null;
    lastDiscount: number;
    lastAppliedPromotions: string[];
}>();

const emit = defineEmits<{
    'switch-tab': [index: number];
    'remove-tab': [index: number];
    'add-tab': [];
    'dismiss-success': [];
}>();
</script>

<template>
    <div class="mb-4 flex items-center gap-2 overflow-x-auto">
        <button
            v-for="(tab, i) in tabs"
            :key="tab.id"
            @click="emit('switch-tab', i)"
            class="flex items-center gap-2 whitespace-nowrap rounded-full px-4 py-1.5 text-sm font-bold transition-colors"
            :class="
                activeIndex === i
                    ? 'bg-primary-500 text-white shadow-sm'
                    : 'bg-gray-100 text-content-muted hover:bg-gray-200 hover:text-content-primary dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white'
            "
        >
            {{ tab.name }}
            <button
                v-if="tabs.length > 1"
                @click.stop="emit('remove-tab', i)"
                class="flex h-4 w-4 items-center justify-center rounded-full text-[10px] font-bold transition-colors hover:bg-white/20"
            >
                <X class="h-3 w-3" />
            </button>
        </button>
        <button
            @click="emit('add-tab')"
            class="flex items-center gap-1 rounded-full px-3 py-1.5 text-sm font-bold text-content-muted transition-colors hover:bg-gray-100 hover:text-content-primary dark:hover:bg-gray-800 dark:hover:text-white"
        >
            <Plus class="h-4 w-4" />
        </button>
    </div>

    <div
        v-if="showSuccess && lastSaleId"
        class="mb-4 flex items-center gap-3 rounded-3xl border border-success/30 bg-success/10 p-4 text-success"
    >
        <Check class="h-6 w-6 flex-shrink-0" />
        <div>
            <p class="font-bold">
                Venta #{{ lastSaleId }} registrada correctamente.
            </p>
            <p class="text-sm text-success/80">
                Puedes seguir agregando productos para una nueva venta.
            </p>
            <p
                v-if="lastDiscount > 0"
                class="text-sm text-success/80"
            >
                Descuento: -${{ (lastDiscount / 100).toLocaleString('es-CL') }}
                <span v-if="lastAppliedPromotions.length">
                    ({{ lastAppliedPromotions.join(', ') }})
                </span>
            </p>
        </div>
        <button
            @click="emit('dismiss-success')"
            class="ml-auto rounded-full p-1.5 transition-colors hover:bg-success/20"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
