<script setup lang="ts">
import { formatDate, formatTime } from '@/helpers/format';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Check,
    CheckCircle,
    Pencil,
    Plus,
    Trash2,
    UserCog,
    X,
    XCircle,
} from 'lucide-vue-next';
import { ref } from 'vue';

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
        form.put(route('admin.cajeros.update', editingId.value), {
            onSuccess: closeForm,
        });
    } else {
        form.post(route('admin.cajeros.store'), { onSuccess: closeForm });
    }
}

function deleteCashier(c: any) {
    if (
        !confirm(
            `¿Eliminar al cajero "${c.name}"? Esta acción no se puede deshacer.`,
        )
    )
        return;
    router.delete(route('admin.cajeros.destroy', c.id));
}

const fmt = (v: number) =>
    '$' + v.toLocaleString('es-CO', { minimumFractionDigits: 0 });
</script>

<template>
    <Head title="Cajeros" />
    <AdminLayout>
        <template #title>
            <h1
                class="font-display text-xl font-bold text-content-primary dark:text-white"
            >
                Cajeros
            </h1>
        </template>

        <div
            class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-surface-dark"
        >
            <div
                class="flex flex-wrap items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
            >
                <UserCog class="h-5 w-5 text-primary-500" />
                <h2
                    class="flex-1 font-bold text-content-primary dark:text-white"
                >
                    Usuarios Cajeros
                </h2>
                <button
                    @click="openNew"
                    class="flex items-center gap-2 rounded-2xl bg-primary-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <Plus class="h-4 w-4" /> Nuevo Cajero
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wider text-content-muted dark:bg-gray-900/50 dark:text-gray-500"
                    >
                        <tr>
                            <th class="px-6 py-3 font-bold">Nombre</th>
                            <th class="px-6 py-3 font-bold">Email</th>
                            <th class="px-6 py-3 font-bold">Teléfono</th>
                            <th class="px-6 py-3 font-bold">Dirección</th>
                            <th class="px-6 py-3 text-center font-bold">
                                Estado Caja
                            </th>
                            <th class="px-6 py-3 font-bold">Última Sesión</th>
                            <th class="px-6 py-3 text-right font-bold">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-100 dark:divide-gray-800"
                    >
                        <tr v-if="!cashiers?.length">
                            <td
                                colspan="7"
                                class="px-6 py-12 text-center text-sm text-content-muted dark:text-gray-500"
                            >
                                No hay cajeros registrados.
                            </td>
                        </tr>
                        <tr
                            v-for="c in cashiers"
                            :key="c.id"
                            class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td
                                class="px-6 py-4 text-sm font-bold text-content-primary dark:text-white"
                            >
                                {{ c.name }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ c.email }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ c.phone || '—' }}
                            </td>
                            <td
                                class="max-w-[200px] truncate px-6 py-4 text-sm text-content-secondary"
                            >
                                {{ c.address || '—' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    :class="
                                        c.cash_status === 'Activa'
                                            ? 'bg-success/10 text-success'
                                            : 'bg-gray-100 text-gray-500'
                                    "
                                    class="flex items-center justify-center gap-1 rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase"
                                >
                                    <CheckCircle
                                        v-if="c.cash_status === 'Activa'"
                                        class="h-3 w-3"
                                    />
                                    <XCircle v-else class="h-3 w-3" />
                                    {{ c.cash_status }}
                                </span>
                            </td>
                            <td
                                class="whitespace-nowrap px-6 py-4 text-sm text-content-muted"
                            >
                                {{
                                    c.last_session !== '-'
                                        ? formatDate(c.last_session) +
                                          ' ' +
                                          formatTime(c.last_session)
                                        : '—'
                                }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="openEdit(c)"
                                        class="rounded-xl p-2 text-blue-500 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteCashier(c)"
                                        class="rounded-xl p-2 text-danger transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showForm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            >
                <div
                    class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-xl dark:bg-surface-dark"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="font-display text-lg font-bold text-content-primary dark:text-white"
                        >
                            {{ editingId ? 'Editar Cajero' : 'Nuevo Cajero' }}
                        </h3>
                        <button
                            @click="closeForm"
                            class="rounded-xl p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <X class="h-5 w-5 text-content-muted" />
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Nombre</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                :class="{ 'border-danger': form.errors.name }"
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-xs text-danger"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Email</label
                            >
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                :class="{ 'border-danger': form.errors.email }"
                            />
                            <p
                                v-if="form.errors.email"
                                class="mt-1 text-xs text-danger"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>
                        <template v-if="!editingId">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Contraseña</label
                                    >
                                    <input
                                        v-model="form.password"
                                        type="password"
                                        required
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        :class="{
                                            'border-danger':
                                                form.errors.password,
                                        }"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                        >Confirmar</label
                                    >
                                    <input
                                        v-model="form.password_confirmation"
                                        type="password"
                                        required
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        :class="{
                                            'border-danger':
                                                form.errors.password,
                                        }"
                                    />
                                </div>
                            </div>
                            <p
                                v-if="form.errors.password"
                                class="-mt-2 text-xs text-danger"
                            >
                                {{ form.errors.password }}
                            </p>
                        </template>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >RUT</label
                                >
                                <input
                                    v-model="form.rut"
                                    type="text"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    :class="{
                                        'border-danger': form.errors.rut,
                                    }"
                                />
                                <p
                                    v-if="form.errors.rut"
                                    class="mt-1 text-xs text-danger"
                                >
                                    {{ form.errors.rut }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                    >Teléfono *</label
                                >
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    :class="{
                                        'border-danger': form.errors.phone,
                                    }"
                                />
                                <p
                                    v-if="form.errors.phone"
                                    class="mt-1 text-xs text-danger"
                                >
                                    {{ form.errors.phone }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                                >Dirección *</label
                            >
                            <input
                                v-model="form.address"
                                type="text"
                                required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                :class="{
                                    'border-danger': form.errors.address,
                                }"
                            />
                            <p
                                v-if="form.errors.address"
                                class="mt-1 text-xs text-danger"
                            >
                                {{ form.errors.address }}
                            </p>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button
                                type="button"
                                @click="closeForm"
                                class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                            >
                                <Check class="h-4 w-4" />
                                {{ editingId ? 'Actualizar' : 'Crear Cajero' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>
