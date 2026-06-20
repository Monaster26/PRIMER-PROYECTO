<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(defineProps<{
    modelValue?: string;
    label?: string;
    placeholder?: string;
}>(), {
    modelValue: '',
    label: 'Fecha',
    placeholder: 'Seleccionar fecha',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
    select: [payload: { dia: number; mes: number; anio: number }];
}>();

const today = new Date();

const isOpen = ref(false);
const calendarMonth = ref(today.getMonth());
const calendarYear = ref(today.getFullYear());

const wrapperRef = ref<HTMLElement | null>(null);
const triggerRef = ref<HTMLElement | null>(null);
const panelRef = ref<HTMLElement | null>(null);

const panelStyle = computed(() => {
    if (!isOpen.value || !triggerRef.value) return { display: 'none' } as const;
    const rect = triggerRef.value.getBoundingClientRect();
    return {
        position: 'fixed' as const,
        top: `${rect.bottom + 4}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`,
    };
});

watch(() => props.modelValue, (val) => {
    if (val) {
        const d = new Date(val + 'T12:00:00');
        if (!isNaN(d.getTime())) {
            calendarMonth.value = d.getMonth();
            calendarYear.value = d.getFullYear();
        }
    }
}, { immediate: true });

const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

const displayDate = computed(() => {
    if (!props.modelValue) return props.placeholder;
    const d = new Date(props.modelValue + 'T12:00:00');
    if (isNaN(d.getTime())) return props.placeholder;
    return d.toLocaleDateString('es-CL', { day: 'numeric', month: 'short', year: 'numeric' });
});

const daysInMonth = computed(() => new Date(calendarYear.value, calendarMonth.value + 1, 0).getDate());
const firstDayOfMonth = computed(() => {
    const day = new Date(calendarYear.value, calendarMonth.value, 1).getDay();
    return day === 0 ? 6 : day - 1;
});

const calendarDays = computed(() => {
    const days: (number | null)[] = [];
    for (let i = 0; i < firstDayOfMonth.value; i++) days.push(null);
    for (let d = 1; d <= daysInMonth.value; d++) days.push(d);
    return days;
});

const monthYearLabel = computed(() => `${meses[calendarMonth.value]} ${calendarYear.value}`);

function isToday(day: number) {
    const d = new Date();
    return d.getFullYear() === calendarYear.value && d.getMonth() === calendarMonth.value && d.getDate() === day;
}

function isSelected(day: number) {
    if (!props.modelValue) return false;
    const d = new Date(props.modelValue + 'T12:00:00');
    return d.getFullYear() === calendarYear.value && d.getMonth() === calendarMonth.value && d.getDate() === day;
}

function selectDay(day: number) {
    const m = String(calendarMonth.value + 1).padStart(2, '0');
    const d = String(day).padStart(2, '0');
    const dateStr = `${calendarYear.value}-${m}-${d}`;
    emit('update:modelValue', dateStr);
    emit('select', { dia: day, mes: calendarMonth.value + 1, anio: calendarYear.value });
    isOpen.value = false;
}

function goToToday() {
    const d = new Date();
    calendarMonth.value = d.getMonth();
    calendarYear.value = d.getFullYear();
    selectDay(d.getDate());
}

function prevMonth() {
    if (calendarMonth.value === 0) { calendarMonth.value = 11; calendarYear.value--; }
    else { calendarMonth.value--; }
}

function nextMonth() {
    if (calendarMonth.value === 11) { calendarMonth.value = 0; calendarYear.value++; }
    else { calendarMonth.value++; }
}

function toggleCalendar() {
    if (!isOpen.value && props.modelValue) {
        const d = new Date(props.modelValue + 'T12:00:00');
        if (!isNaN(d.getTime())) {
            calendarMonth.value = d.getMonth();
            calendarYear.value = d.getFullYear();
        }
    }
    isOpen.value = !isOpen.value;
}

function handleClickOutside(e: MouseEvent) {
    if (!isOpen.value) return;
    const target = e.target as HTMLElement;
    if (wrapperRef.value?.contains(target)) return;
    if (panelRef.value?.contains(target)) return;
    isOpen.value = false;
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="wrapperRef" class="date-filter-wrapper relative">
        <label
            v-if="label"
            class="block text-[9px] font-bold uppercase tracking-wider text-content-muted dark:text-gray-400"
        >
            {{ label }}
        </label>
        <button
            ref="triggerRef"
            type="button"
            @click="toggleCalendar"
            class="mt-0.5 flex w-full items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-[11px] text-content-primary transition-shadow hover:border-gray-300 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:hover:border-gray-600"
            style="min-width: 180px"
        >
            <svg class="h-3.5 w-3.5 flex-shrink-0 text-content-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="flex-1 text-left">{{ displayDate }}</span>
            <svg class="h-3 w-3 flex-shrink-0 text-content-muted transition-transform" :class="isOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-if="isOpen"
                    ref="panelRef"
                    :style="panelStyle"
                    class="z-[9999] origin-top-left rounded-xl border border-gray-200 bg-white p-3 shadow-lg dark:border-gray-700 dark:bg-surface-dark"
                >
                <div class="mb-2 flex items-center justify-between">
                    <button
                        type="button"
                        @click.stop="prevMonth"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-content-muted transition-colors hover:bg-gray-100 hover:text-content-primary dark:hover:bg-gray-800"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <span class="select-none text-[11px] font-bold text-content-primary dark:text-white">
                        {{ monthYearLabel }}
                    </span>
                    <button
                        type="button"
                        @click.stop="nextMonth"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-content-muted transition-colors hover:bg-gray-100 hover:text-content-primary dark:hover:bg-gray-800"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <div class="mb-1 grid grid-cols-7 gap-0.5">
                    <div
                        v-for="d in ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa', 'Do']"
                        :key="d"
                        class="flex h-7 w-8 items-center justify-center text-[9px] font-bold uppercase tracking-wider text-content-muted"
                    >
                        {{ d }}
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-0.5">
                    <div
                        v-for="(day, idx) in calendarDays"
                        :key="idx"
                        class="flex h-8 w-8 items-center justify-center"
                    >
                        <button
                            v-if="day"
                            type="button"
                            @click.stop="selectDay(day)"
                            :class="[
                                'flex h-7 w-7 items-center justify-center rounded-full text-[11px] font-semibold transition-colors',
                                isSelected(day)
                                    ? 'bg-primary-500 text-white shadow-sm'
                                    : isToday(day)
                                      ? 'text-primary-500 ring-1 ring-primary-500/50 hover:bg-primary-50 dark:hover:bg-primary-900/20'
                                      : 'text-content-primary hover:bg-gray-100 dark:hover:bg-gray-800',
                            ]"
                        >
                            {{ day }}
                        </button>
                        <span v-else class="w-7" />
                    </div>
                </div>

                <div class="mt-2 border-t border-gray-100 pt-2 dark:border-gray-700">
                    <button
                        type="button"
                        @click.stop="goToToday"
                        class="flex w-full items-center justify-center gap-1 rounded-lg px-2 py-1 text-[10px] font-bold text-primary-500 transition-colors hover:bg-primary-50 dark:hover:bg-primary-900/20"
                    >
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Hoy
                    </button>
                </div>
            </div>
            </Transition>
        </Teleport>
    </div>
</template>
