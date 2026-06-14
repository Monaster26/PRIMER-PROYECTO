<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Wallet, Plus, Trash2, X, Check } from 'lucide-vue-next';
import { formatDate, formatTime } from '@/helpers/format';

const props = defineProps<{
    sessions: { data: any[]; links: any[]; current_page: number; last_page: number; prev_page_url: string | null; next_page_url: string | null };
    cashiers: { id: number; name: string }[];
}>();

const showForm = ref(false);
const showCloseForm = ref(false);
const closingSessionId = ref<number | null>(null);

const form = useForm({
    user_id: null as number | null,
    opened_at: new Date().toISOString().slice(0, 16),
    opening_balance: 0,
    real_balance: 0,
});

const closeForm = useForm({
    closed_at: new Date().toISOString().slice(0, 16),
    real_balance: 0,
    total_z: 0,
    transfers: 0,
    pos_card_total: 0,
    observations: '',
});

function openNew() { form.reset(); form.opened_at = new Date().toISOString().slice(0, 16); showForm.value = true; }
function closeNewForm() { showForm.value = false; form.reset(); }

function openClose(session: any) {
    closingSessionId.value = session.id;
    closeForm.reset();
    closeForm.closed_at = new Date().toISOString().slice(0, 16);
    closeForm.real_balance = session.real_balance || 0;
    showCloseForm.value = true;
}
function closeCloseForm() { showCloseForm.value = false; closingSessionId.value = null; closeForm.reset(); }

function submitForm() {
    form.post(route('admin.arqueo-caja.store'), { onSuccess: closeNewForm });
}

function submitClose() {
    if (!closingSessionId.value) return;
    closeForm.post(route('admin.arqueo-caja.close', closingSessionId.value), { onSuccess: closeCloseForm });
}

function deleteSession(id: number) {
    if (!confirm('¿Eliminar esta sesión de caja?')) return;
    router.delete(route('admin.arqueo-caja.destroy', id), { preserveScroll: true });
}

const fmt = (v: number) => '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Arqueo de Caja" />
    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Arqueo de Caja</h1>
        </template>

        <div class="bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                <Wallet class="w-5 h-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white flex-1">Sesiones de Caja</h2>
                <button @click="openNew"
                    class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-4 py-2 rounded-2xl transition-colors shadow-sm">
                    <Plus class="w-4 h-4" /> Nueva Sesión
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-content-muted dark:text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-bold">Cajero</th>
                            <th class="px-6 py-3 font-bold">Fecha Apertura</th>
                            <th class="px-6 py-3 font-bold">Hora Apertura</th>
                            <th class="px-6 py-3 font-bold">Fecha Cierre</th>
                            <th class="px-6 py-3 font-bold">Hora Cierre</th>
                            <th class="px-6 py-3 font-bold text-right">Fondo Inicial</th>
                            <th class="px-6 py-3 font-bold text-right">Saldo Real</th>
                            <th class="px-6 py-3 font-bold text-center">Estado</th>
                            <th class="px-6 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!sessions.data?.length">
                            <td colspan="9" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">No hay sesiones de caja registradas.</td>
                        </tr>
                        <tr v-for="s in sessions.data" :key="s.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm font-medium text-content-primary dark:text-white">{{ s.user?.name || '—' }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ formatDate(s.opened_at) }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ formatTime(s.opened_at) }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ s.closed_at ? formatDate(s.closed_at) : '—' }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ s.closed_at ? formatTime(s.closed_at) : '—' }}</td>
                            <td class="px-6 py-4 text-sm text-right font-medium">{{ fmt(s.opening_balance) }}</td>
                            <td class="px-6 py-4 text-sm text-right font-bold" :class="s.closed_at ? 'text-success' : 'text-content-primary'">
                                {{ fmt(s.real_balance || 0) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="s.closed_at ? 'bg-gray-100 text-gray-500' : 'bg-success/10 text-success'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase">
                                    {{ s.closed_at ? 'Cerrada' : 'Activa' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button v-if="!s.closed_at" @click="openClose(s)"
                                        class="p-2 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-900/20 text-primary-500 transition-colors text-xs font-bold">
                                        Cerrar
                                    </button>
                                    <button @click="deleteSession(s.id)"
                                        class="p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-danger transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="sessions.last_page > 1"
                class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center text-sm text-content-muted dark:text-gray-500">
                <span>Página {{ sessions.current_page }} de {{ sessions.last_page }}</span>
                <div class="flex gap-2">
                    <a v-if="sessions.prev_page_url" :href="sessions.prev_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">←</a>
                    <a v-if="sessions.next_page_url" :href="sessions.next_page_url"
                        class="px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 font-bold transition-colors">→</a>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showForm" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-xl w-full max-w-md p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">Nueva Sesión de Caja</h3>
                        <button @click="closeNewForm" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-5 h-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Cajero</label>
                            <select v-model="form.user_id" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm">
                                <option :value="null" disabled>Seleccionar cajero...</option>
                                <option v-for="c in cashiers" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Apertura</label>
                            <input v-model="form.opened_at" type="datetime-local" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Fondo Inicial</label>
                                <input v-model.number="form.opening_balance" type="number" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Saldo Real</label>
                                <input v-model.number="form.real_balance" type="number" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="closeNewForm"
                                class="flex-1 py-2.5 rounded-2xl border border-gray-200 dark:border-gray-700 text-sm font-bold text-content-secondary hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 py-2.5 rounded-2xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2">
                                <Check class="w-4 h-4" />
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showCloseForm" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-xl w-full max-w-lg p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">Cerrar Sesión de Caja</h3>
                        <button @click="closeCloseForm" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-5 h-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitClose" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Cierre</label>
                            <input v-model="closeForm.closed_at" type="datetime-local" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Saldo Real</label>
                                <input v-model.number="closeForm.real_balance" type="number" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Total Z</label>
                                <input v-model.number="closeForm.total_z" type="number" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Transferencias</label>
                                <input v-model.number="closeForm.transfers" type="number" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Ventas Tarjeta</label>
                                <input v-model.number="closeForm.pos_card_total" type="number" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Observaciones</label>
                            <textarea v-model="closeForm.observations" rows="2"
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm"></textarea>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="closeCloseForm"
                                class="flex-1 py-2.5 rounded-2xl border border-gray-200 dark:border-gray-700 text-sm font-bold text-content-secondary hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="closeForm.processing"
                                class="flex-1 py-2.5 rounded-2xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2">
                                <Check class="w-4 h-4" />
                                Cerrar Sesión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
