<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { UserCog, Plus, Pencil, X, Check, CheckCircle, XCircle } from 'lucide-vue-next';
import { formatDate, formatTime } from '@/helpers/format';

const props = defineProps<{
    cashiers: Array<{
        id: number;
        name: string;
        email: string;
        rut: string;
        phone: string;
        address: string;
        cash_status: string;
        last_session: string;
        discrepancy: number;
    }>;
}>();

const showForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    rut: '',
    phone: '',
    address: '',
});

function openNew() {
    form.reset();
    editingId.value = null;
    showForm.value = true;
}
function openEdit(c: any) {
    form.name = c.name;
    form.email = c.email;
    form.password = '';
    form.password_confirmation = '';
    form.rut = c.rut;
    form.phone = c.phone;
    form.address = c.address;
    editingId.value = c.id;
    showForm.value = true;
}
function closeForm() {
    showForm.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
}
function submitForm() {
    if (editingId.value) {
        form.put(route('admin.cajeros.update', editingId.value), { onSuccess: closeForm });
    } else {
        form.post(route('admin.cajeros.store'), { onSuccess: closeForm });
    }
}

const fmt = (v: number) => '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Cajeros" />
    <AdminLayout>
        <template #title>
            <h1 class="text-xl font-display font-bold text-content-primary dark:text-white">Cajeros</h1>
        </template>

        <div class="bg-white dark:bg-surface-dark rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                <UserCog class="w-5 h-5 text-primary-500" />
                <h2 class="font-bold text-content-primary dark:text-white flex-1">Usuarios Cajeros</h2>
                <button @click="openNew"
                    class="flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold px-4 py-2 rounded-2xl transition-colors shadow-sm">
                    <Plus class="w-4 h-4" /> Nuevo Cajero
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase tracking-wider text-content-muted dark:text-gray-500">
                        <tr>
                            <th class="px-6 py-3 font-bold">Nombre</th>
                            <th class="px-6 py-3 font-bold">Email</th>
                            <th class="px-6 py-3 font-bold">Teléfono</th>
                            <th class="px-6 py-3 font-bold">Dirección</th>
                            <th class="px-6 py-3 font-bold text-center">Estado Caja</th>
                            <th class="px-6 py-3 font-bold">Última Sesión</th>
                            <th class="px-6 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-if="!cashiers?.length">
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500">No hay cajeros registrados.</td>
                        </tr>
                        <tr v-for="c in cashiers" :key="c.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 text-sm font-bold text-content-primary dark:text-white">{{ c.name }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ c.email }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary">{{ c.phone || '—' }}</td>
                            <td class="px-6 py-4 text-sm text-content-secondary max-w-[200px] truncate">{{ c.address || '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span :class="c.cash_status === 'Activa' ? 'bg-success/10 text-success' : 'bg-gray-100 text-gray-500'"
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase flex items-center justify-center gap-1">
                                    <CheckCircle v-if="c.cash_status === 'Activa'" class="w-3 h-3" />
                                    <XCircle v-else class="w-3 h-3" />
                                    {{ c.cash_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-content-muted whitespace-nowrap">
                                {{ c.last_session !== '-' ? formatDate(c.last_session) + ' ' + formatTime(c.last_session) : '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openEdit(c)" class="p-2 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-500 transition-colors">
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showForm" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-surface-dark rounded-3xl shadow-xl w-full max-w-md p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display font-bold text-lg text-content-primary dark:text-white">
                            {{ editingId ? 'Editar Cajero' : 'Nuevo Cajero' }}
                        </h3>
                        <button @click="closeForm" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <X class="w-5 h-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Nombre</label>
                            <input v-model="form.name" type="text" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm"
                                :class="{ 'border-danger': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-xs text-danger mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Email</label>
                            <input v-model="form.email" type="email" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm"
                                :class="{ 'border-danger': form.errors.email }" />
                            <p v-if="form.errors.email" class="text-xs text-danger mt-1">{{ form.errors.email }}</p>
                        </div>
                        <template v-if="!editingId">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Contraseña</label>
                                    <input v-model="form.password" type="password" required
                                        class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm"
                                        :class="{ 'border-danger': form.errors.password }" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Confirmar</label>
                                    <input v-model="form.password_confirmation" type="password" required
                                        class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm"
                                        :class="{ 'border-danger': form.errors.password }" />
                                </div>
                            </div>
                            <p v-if="form.errors.password" class="text-xs text-danger -mt-2">{{ form.errors.password }}</p>
                        </template>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">RUT</label>
                                <input v-model="form.rut" type="text"
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm"
                                    :class="{ 'border-danger': form.errors.rut }" />
                                <p v-if="form.errors.rut" class="text-xs text-danger mt-1">{{ form.errors.rut }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Teléfono *</label>
                                <input v-model="form.phone" type="text" required
                                    class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm"
                                    :class="{ 'border-danger': form.errors.phone }" />
                                <p v-if="form.errors.phone" class="text-xs text-danger mt-1">{{ form.errors.phone }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-content-muted dark:text-gray-400 uppercase tracking-wider mb-1">Dirección *</label>
                            <input v-model="form.address" type="text" required
                                class="w-full border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-2.5 bg-gray-50 dark:bg-gray-900 text-content-primary dark:text-white text-sm"
                                :class="{ 'border-danger': form.errors.address }" />
                            <p v-if="form.errors.address" class="text-xs text-danger mt-1">{{ form.errors.address }}</p>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="closeForm"
                                class="flex-1 py-2.5 rounded-2xl border border-gray-200 dark:border-gray-700 text-sm font-bold text-content-secondary hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 py-2.5 rounded-2xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2">
                                <Check class="w-4 h-4" />
                                {{ editingId ? 'Actualizar' : 'Crear Cajero' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
