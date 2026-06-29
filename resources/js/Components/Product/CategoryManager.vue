<script setup lang="ts">
import { X } from 'lucide-vue-next';

interface CategoryItem {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
}

interface CategoryTreeItem {
    id: number;
    name: string;
    slug: string;
    children: CategoryTreeItem[];
}

const props = defineProps<{
    showCategoryModal: boolean;
    activeTab: string;
    newCategoryName: string;
    parentId: number | null;
    savingCategory: boolean;
    localCategories: CategoryItem[];
    localCategoryTree: CategoryTreeItem[];
    editingCategoryId: number | null;
    editingCategoryName: string;
    expandedCategories: Set<number>;
    deletingCategory: boolean;
}>();

const emit = defineEmits<{
    close: [];
    'update:activeTab': [value: 'crear' | 'gestionar'];
    'update:newCategoryName': [value: string];
    'update:parentId': [value: number | null];
    'update:editingCategoryName': [value: string];
    save: [];
    startEdit: [cat: { id: number; name: string }];
    cancelEdit: [];
    confirmEdit: [id: number];
    deleteCategory: [id: number];
    toggleExpand: [id: number];
}>();
</script>

<template>
    <Transition name="fade">
        <div
            v-if="props.showCategoryModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            @click.self="emit('close')"
        >
            <div
                class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-surface-dark"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h3
                        class="font-display text-lg font-bold text-content-primary dark:text-white"
                    >
                        Administrador de Categorías
                    </h3>
                    <button
                        @click="emit('close')"
                        class="rounded-xl p-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <X class="h-5 w-5 text-content-muted" />
                    </button>
                </div>

                <div
                    class="mb-4 flex gap-2 border-b border-gray-200 dark:border-gray-700"
                >
                    <button
                        @click="emit('update:activeTab', 'crear')"
                        :class="
                            props.activeTab === 'crear'
                                ? 'border-primary-500 text-primary-600'
                                : 'border-transparent text-content-muted hover:text-content-primary'
                        "
                        class="border-b-2 px-4 py-2 text-sm font-bold transition-colors"
                    >
                        Crear
                    </button>
                    <button
                        @click="emit('update:activeTab', 'gestionar')"
                        :class="
                            props.activeTab === 'gestionar'
                                ? 'border-primary-500 text-primary-600'
                                : 'border-transparent text-content-muted hover:text-content-primary'
                        "
                        class="border-b-2 px-4 py-2 text-sm font-bold transition-colors"
                    >
                        Gestionar / Editar
                    </button>
                </div>

                <!-- Tab: Crear -->
                <div v-if="props.activeTab === 'crear'">
                    <div class="mb-3">
                        <label
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
                        >
                            Asociar como subcategoría de (opcional)
                        </label>
                        <select
                            :value="props.parentId"
                            @change="
                                emit(
                                    'update:parentId',
                                    ($event.target as HTMLSelectElement).value
                                        ? Number(
                                              (
                                                  $event.target as HTMLSelectElement
                                              ).value,
                                          )
                                        : null,
                                )
                            "
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option :value="null">
                                Ninguna (categoría principal)
                            </option>
                            <option
                                v-for="cat in props.localCategories.filter(
                                    (c) => c.parent_id === null,
                                )"
                                :key="cat.id"
                                :value="cat.id"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>
                    <input
                        :value="props.newCategoryName"
                        @input="
                            emit(
                                'update:newCategoryName',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                        @keydown.enter="emit('save')"
                        type="text"
                        placeholder="Nombre de la categoría o subcategoría"
                        class="mb-4 w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-content-primary focus:border-primary-400 focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    />
                    <div class="flex gap-3">
                        <button
                            @click="emit('close')"
                            class="flex-1 rounded-2xl border border-gray-200 py-2.5 text-sm font-bold text-content-secondary transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="emit('save')"
                            :disabled="
                                props.savingCategory ||
                                !props.newCategoryName.trim()
                            "
                            class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-primary-500 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600 disabled:opacity-50"
                        >
                            {{
                                props.savingCategory
                                    ? 'Guardando...'
                                    : 'Guardar'
                            }}
                        </button>
                    </div>
                </div>

                <!-- Tab: Gestionar / Editar -->
                <div
                    v-if="props.activeTab === 'gestionar'"
                    class="max-h-80 space-y-1 overflow-y-auto"
                >
                    <div
                        v-for="cat in props.localCategoryTree"
                        :key="cat.id"
                        class="rounded-xl border border-gray-100 dark:border-gray-700"
                    >
                        <div class="flex items-center gap-2 px-3 py-2">
                            <button
                                v-if="cat.children.length > 0"
                                @click="emit('toggleExpand', cat.id)"
                                class="rounded-lg p-1 text-content-muted transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <svg
                                    :class="
                                        props.expandedCategories.has(cat.id)
                                            ? 'rotate-90'
                                            : ''
                                    "
                                    class="h-4 w-4 transition-transform"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </button>
                            <span v-else class="w-6" />

                            <template v-if="props.editingCategoryId === cat.id">
                                <input
                                    :value="props.editingCategoryName"
                                    @input="
                                        emit(
                                            'update:editingCategoryName',
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        )
                                    "
                                    @keydown.enter="emit('confirmEdit', cat.id)"
                                    @keydown.escape="emit('cancelEdit')"
                                    type="text"
                                    class="flex-1 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                />
                                <button
                                    @click="emit('confirmEdit', cat.id)"
                                    class="rounded-lg p-1 text-emerald-600 transition-colors hover:bg-emerald-50 dark:hover:bg-emerald-900/30"
                                    title="Guardar"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click="emit('cancelEdit')"
                                    class="rounded-lg p-1 text-content-muted transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                    title="Cancelar"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </template>
                            <template v-else>
                                <span
                                    class="flex-1 text-sm font-medium text-content-primary dark:text-white"
                                >
                                    {{ cat.name }}
                                    <span
                                        v-if="cat.children.length > 0"
                                        class="ml-1 rounded-full bg-gray-100 px-1.5 py-0.5 text-xs text-content-muted dark:bg-gray-700"
                                    >
                                        {{ cat.children.length }}
                                    </span>
                                </span>
                                <button
                                    @click="emit('startEdit', cat)"
                                    class="rounded-lg p-1 text-content-muted transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                    title="Editar"
                                >
                                    ✏️
                                </button>
                                <button
                                    @click="emit('deleteCategory', cat.id)"
                                    :disabled="props.deletingCategory"
                                    class="rounded-lg p-1 text-red-500 transition-colors hover:bg-red-50 dark:hover:bg-red-900/30"
                                    title="Eliminar"
                                >
                                    🗑️
                                </button>
                            </template>
                        </div>

                        <!-- Subcategorías hijas expandidas -->
                        <div
                            v-if="
                                props.expandedCategories.has(cat.id) &&
                                cat.children.length > 0
                            "
                            class="ml-4 border-l-2 border-gray-100 pl-2 dark:border-gray-700"
                        >
                            <div
                                v-for="child in cat.children"
                                :key="child.id"
                                class="flex items-center gap-2 rounded-lg px-3 py-1.5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                            >
                                <span class="text-content-muted">└</span>

                                <template
                                    v-if="props.editingCategoryId === child.id"
                                >
                                    <input
                                        :value="props.editingCategoryName"
                                        @input="
                                            emit(
                                                'update:editingCategoryName',
                                                (
                                                    $event.target as HTMLInputElement
                                                ).value,
                                            )
                                        "
                                        @keydown.enter="
                                            emit('confirmEdit', child.id)
                                        "
                                        @keydown.escape="emit('cancelEdit')"
                                        type="text"
                                        class="flex-1 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                    />
                                    <button
                                        @click="emit('confirmEdit', child.id)"
                                        class="rounded-lg p-1 text-emerald-600 transition-colors hover:bg-emerald-50 dark:hover:bg-emerald-900/30"
                                        title="Guardar"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="emit('cancelEdit')"
                                        class="rounded-lg p-1 text-content-muted transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                        title="Cancelar"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </template>
                                <template v-else>
                                    <span
                                        class="flex-1 text-sm text-content-primary dark:text-white"
                                    >
                                        {{ child.name }}
                                    </span>
                                    <button
                                        @click="emit('startEdit', child)"
                                        class="rounded-lg p-1 text-content-muted transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                                        title="Editar"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        @click="
                                            emit('deleteCategory', child.id)
                                        "
                                        :disabled="props.deletingCategory"
                                        class="rounded-lg p-1 text-red-500 transition-colors hover:bg-red-50 dark:hover:bg-red-900/30"
                                        title="Eliminar"
                                    >
                                        🗑️
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div
                            v-if="
                                props.expandedCategories.has(cat.id) &&
                                cat.children.length === 0
                            "
                            class="ml-6 py-2 text-xs italic text-content-muted"
                        >
                            Sin subcategorías — ve a "Crear" y elige "{{
                                cat.name
                            }}" como padre.
                        </div>
                    </div>

                    <div
                        v-if="props.localCategoryTree.length === 0"
                        class="py-6 text-center text-sm text-content-muted"
                    >
                        No hay categorías. Crea una en la pestaña "Crear".
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
