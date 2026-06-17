<script setup lang="ts">
import {
    ArrowDownLeft,
    ArrowUpRight,
    Check,
    DollarSign,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    show: boolean;
    type: 'ingreso' | 'retiro';
}>();

const emit = defineEmits<{
    close: [];
    saved: [];
}>();

const amount = ref<number | null>(null);
const description = ref('');
const loading = ref(false);
const error = ref('');

const isIngreso = () => props.type === 'ingreso';

function resetForm() {
    amount.value = null;
    description.value = '';
    error.value = '';
}

function handleClose() {
    resetForm();
    emit('close');
}

function submit() {
    if (!amount.value || amount.value < 100) {
        error.value = 'El monto debe ser al menos $100.';
        return;
    }

    loading.value = true;
    error.value = '';

    fetch(route('admin.pos.cash-movement'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN':
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '',
        },
        body: JSON.stringify({
            type: props.type,
            amount: amount.value,
            description: description.value || null,
        }),
    })
        .then((res) => {
            if (!res.ok) return res.json().then((err) => { throw new Error(err.message || 'Error al registrar movimiento'); });
            return res.json();
        })
        .then((data) => {
            resetForm();
            emit('saved');
        })
        .catch((err) => {
            error.value = err.message;
        })
        .finally(() => {
            loading.value = false;
        });
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-sm"
            @click.self="handleClose"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white shadow-xl dark:bg-surface-dark"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-800"
                >
                    <div class="flex items-center gap-2">
                        <component
                            :is="isIngreso() ? ArrowDownLeft : ArrowUpRight"
                            :class="[
                                'h-5 w-5',
                                isIngreso()
                                    ? 'text-emerald-500'
                                    : 'text-orange-500',
                            ]"
                        />
                        <h3
                            class="font-display text-sm font-bold text-content-primary dark:text-white"
                        >
                            {{ isIngreso() ? 'Ingresar Dinero' : 'Retirar Dinero' }}
                        </h3>
                    </div>
                    <button
                        @click="handleClose"
                        class="rounded-lg p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <X class="h-4 w-4 text-content-muted" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 p-5">
                    <!-- Type badge -->
                    <div
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold"
                        :class="
                            isIngreso()
                                ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20'
                                : 'bg-orange-50 text-orange-600 dark:bg-orange-900/20'
                        "
                    >
                        <component
                            :is="isIngreso() ? ArrowDownLeft : ArrowUpRight"
                            class="h-4 w-4"
                        />
                        {{
                            isIngreso()
                                ? 'Estás agregando dinero a la caja'
                                : 'Estás retirando dinero de la caja'
                        }}
                    </div>

                    <!-- Amount -->
                    <div>
                        <label
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                        >
                            Monto
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-content-muted"
                            >
                                $
                            </span>
                            <input
                                v-model.number="amount"
                                type="number"
                                min="100"
                                step="100"
                                autofocus
                                placeholder="0"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-8 pr-4 text-right text-lg font-bold text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                        >
                            Descripción (opcional)
                        </label>
                        <input
                            v-model="description"
                            type="text"
                            placeholder="Ej: Cambio para vueltos, retiro de efectivo..."
                            maxlength="255"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-content-primary transition-shadow focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        />
                    </div>

                    <!-- Error -->
                    <p
                        v-if="error"
                        class="rounded-xl bg-danger/10 px-3 py-2 text-xs font-bold text-danger"
                    >
                        {{ error }}
                    </p>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="handleClose"
                            class="flex-1 rounded-xl border border-gray-200 py-2.5 text-xs font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="loading || !amount"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-xl py-2.5 text-xs font-bold text-white shadow-sm transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                            :class="
                                isIngreso()
                                    ? 'bg-emerald-600 hover:bg-emerald-700'
                                    : 'bg-orange-600 hover:bg-orange-700'
                            "
                        >
                            <template v-if="loading">
                                <span
                                    class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>
                                Registrando...
                            </template>
                            <template v-else>
                                <Check class="h-4 w-4" />
                                {{ isIngreso() ? 'Ingresar Dinero' : 'Retirar Dinero' }}
                            </template>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>
