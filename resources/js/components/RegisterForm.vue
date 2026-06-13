<template>
  <div class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
    <!-- Alerta de resultado -->
    <AlertToast
      :show="action.showAlert.value"
      :type="action.alert.value?.type"
      :message="action.alert.value?.message"
      @dismiss="action.dismissAlert"
    />

    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-green-400">CelePadi</h1>
        <p class="text-gray-500 mt-2">Crea tu cuenta</p>
      </div>

      <!-- Card -->
      <div class="bg-gray-900 border border-gray-800 rounded-xl p-8 shadow-xl">
        <!-- Estado de éxito: verificar email -->
        <Transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
        >
          <div v-if="registered" class="text-center py-4">
            <div class="w-16 h-16 bg-green-950 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h2 class="text-xl font-semibold text-green-400 mb-2">Revisa tu correo</h2>
            <p class="text-gray-400 text-sm mb-6">
              Enviamos un enlace de verificación a <span class="text-green-400">{{ form.email }}</span>
            </p>
            <LoadingButton
              @click="resendEmail"
              :loading="resending"
              loading-text="Enviando..."
              class="w-full px-4 py-2 bg-gray-800 border border-gray-700 text-green-400 rounded-lg hover:bg-gray-700 transition-colors"
            >
              Reenviar correo de verificación
            </LoadingButton>
            <a href="/login" class="block mt-4 text-sm text-gray-500 hover:text-green-400 transition-colors">
              Ir al login
            </a>
          </div>
        </Transition>

        <!-- Formulario de registro -->
        <Transition
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0 scale-95"
        >
          <form v-if="!registered" @submit.prevent="register" class="space-y-5">
            <!-- Nombre -->
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Nombre</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-gray-200 placeholder-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                placeholder="Tu nombre completo"
              />
              <p v-if="errors.name" class="mt-1 text-xs text-red-400">{{ errors.name[0] }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Correo electrónico</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-gray-200 placeholder-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                placeholder="correo@ejemplo.com"
              />
              <p v-if="errors.email" class="mt-1 text-xs text-red-400">{{ errors.email[0] }}</p>
            </div>

            <!-- Departamento -->
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Departamento</label>
              <select
                v-model="form.department_id"
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
              >
                <option value="" class="text-gray-600">Selecciona un departamento</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Contraseña</label>
              <input
                v-model="form.password"
                type="password"
                required
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-gray-200 placeholder-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                placeholder="Mínimo 8 caracteres"
              />
              <p v-if="errors.password" class="mt-1 text-xs text-red-400">{{ errors.password[0] }}</p>
            </div>

            <!-- Confirmar Password -->
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Confirmar contraseña</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                required
                class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-gray-200 placeholder-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                placeholder="Repite tu contraseña"
              />
            </div>

            <!-- Botón submit -->
            <LoadingButton
              type="submit"
              :loading="action.isLoading.value"
              loading-text="Registrando..."
              class="w-full px-4 py-2.5 bg-green-500 text-gray-950 font-semibold rounded-lg hover:bg-green-400 hover:shadow-[0_0_12px_rgba(74,222,128,0.5)] disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
            >
              Crear cuenta
            </LoadingButton>

            <p class="text-center text-sm text-gray-500">
              ¿Ya tienes cuenta?
              <a href="/login" class="text-green-400 hover:text-green-300 transition-colors">Inicia sesión</a>
            </p>
          </form>
        </Transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import LoadingButton from './LoadingButton.vue';
import AlertToast from './AlertToast.vue';
import { useAsyncAction } from '../composables/useAsyncAction';

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  department_id: '',
});

const departments = ref([]);
const errors = ref({});
const registered = ref(false);
const resending = ref(false);

const action = useAsyncAction();

async function loadDepartments() {
  try {
    const { data } = await axios.get('/api/departments');
    departments.value = data;
  } catch {
    // Los departamentos se cargan desde la ruta web pública
  }
}

async function register() {
  errors.value = {};

  try {
    await action.execute(
      async () => {
        const payload = { ...form.value };
        if (!payload.department_id) delete payload.department_id;

        const { data } = await axios.post('/api/register', payload);

        // Guardar token para futuras peticiones
        localStorage.setItem('auth_token', data.token);
        registered.value = true;
        return data;
      },
      {
        successMessage: 'Cuenta creada. Revisa tu correo para verificar.',
        errorMessage: 'Error al crear la cuenta',
        duration: 4000,
      }
    );
  } catch (error) {
    if (error?.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
  }
}

async function resendEmail() {
  resending.value = true;
  try {
    await axios.post('/api/email/resend-verification', { email: form.value.email });
    action.alert.value = { type: 'success', message: 'Email reenviado correctamente.' };
    action.showAlert.value = true;
  } catch {
    action.alert.value = { type: 'error', message: 'Error al reenviar email.' };
    action.showAlert.value = true;
  } finally {
    resending.value = false;
  }
}

onMounted(() => {
  loadDepartments();
});
</script>
