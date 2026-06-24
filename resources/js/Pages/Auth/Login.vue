<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="flex min-h-screen">
        <!-- Left: Branding (60%) -->
        <div
            class="relative hidden w-[60%] overflow-hidden bg-[#FF2E7A] lg:flex lg:items-center lg:justify-center"
        >
            <div
                class="absolute -bottom-20 -left-20 h-96 w-96 animate-[pulse_4s_ease-in-out_infinite] rounded-full bg-[#FF92B7] opacity-60 blur-3xl"
            />
            <div
                class="absolute -right-20 -top-20 h-96 w-96 animate-[pulse_5s_ease-in-out_infinite] rounded-full bg-[#FFD232] opacity-40 blur-3xl"
            />

            <div class="relative z-10 flex flex-col items-center px-12">
                <img
                    src="/images/logo.png"
                    alt="Monasterios Market"
                    class="h-56 w-auto animate-[float_3s_ease-in-out_infinite,pop-in_0.6s_cubic-bezier(0.34,1.56,0.64,1)_both] object-contain drop-shadow-2xl"
                />
                <p
                    class="mt-6 max-w-sm animate-[slide-up_0.4s_ease-out_0.15s_both] text-center text-xl font-medium tracking-wide text-white/90"
                >
                    Punto de Venta y Gestión Integral.
                </p>
            </div>
        </div>

        <!-- Right: Form (40%) -->
        <div
            class="flex w-full items-center justify-center bg-[#FAFAFA] p-8 lg:w-[40%]"
        >
            <div
                v-if="status"
                class="mb-4 w-full max-w-sm text-sm font-medium text-green-600"
            >
                {{ status }}
            </div>

            <div class="w-full max-w-sm">
                <h2 class="text-2xl font-bold text-gray-900">
                    ¡Bienvenido de vuelta!
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Ingresa tus credenciales para acceder al panel
                </p>

                <form @submit.prevent="submit" class="mt-8 space-y-5">
                    <!-- Email -->
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-[#FF2E7A] focus:outline-none focus:ring-2 focus:ring-[#FF2E7A]/20"
                            placeholder="tu@correo.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Contraseña
                        </label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            class="mt-1 w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-[#FF2E7A] focus:outline-none focus:ring-2 focus:ring-[#FF2E7A]/20"
                            placeholder="••••••••"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="flex items-center justify-between">
                        <label class="flex cursor-pointer items-center gap-2">
                            <Checkbox
                                name="remember"
                                v-model:checked="form.remember"
                            />
                            <span class="text-sm text-gray-600"
                                >Recordarme</span
                            >
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-[#FF92B7] transition-colors hover:text-[#FF2E7A]"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-[#FF2E7A] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-[#FF2E7A]/25 transition-all duration-300 hover:bg-[#E0005F] hover:shadow-[#E0005F]/30 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span
                            v-if="form.processing"
                            class="inline-block animate-pulse"
                            >Iniciando...</span
                        >
                        <span v-else>Iniciar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}
</style>
