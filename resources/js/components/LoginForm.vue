<template>
  <div class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
    <AlertToast
      :show="action.showAlert.value"
      :type="action.alert.value?.type"
      :message="action.alert.value?.message"
      @dismiss="action.dismissAlert"
    />

    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-green-400">CelePadi</h1>
        <p class="text-gray-500 mt-2">Inicia sesión</p>
      </div>

      <div class="bg-gray-900 border border-gray-800 rounded-xl p-8 shadow-xl">
        <form @submit.prevent="login" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">Correo electrónico</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-gray-200 placeholder-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
              placeholder="correo@ejemplo.com"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">Contraseña</label>
            <input
              v-model="form.password"
              type="password"
              required
              class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-gray-200 placeholder-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
              placeholder="Tu contraseña"
            />
          </div>

          <!-- Mensaje si email no verificado -->
          <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
          >
            <div v-if="needsVerification" class="p-3 bg-amber-950 border border-amber-800 rounded-lg">
              <p class="text-sm text-amber-400 mb-2">Debes verificar tu correo primero.</p>
              <button
                type="button"
                @click="resendVerification"
                class="text-xs text-green-400 hover:text-green-300 underline"
              >
                Reenviar email de verificación
              </button>
            </div>
          </Transition>

          <LoadingButton
            type="submit"
            :loading="action.isLoading.value"
            loading-text="Ingresando..."
            class="w-full px-4 py-2.5 bg-green-500 text-gray-950 font-semibold rounded-lg hover:bg-green-400 hover:shadow-[0_0_12px_rgba(74,222,128,0.5)] disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
          >
            Iniciar sesión
          </LoadingButton>

          <p class="text-center text-sm text-gray-500">
            ¿No tienes cuenta?
            <a href="/register" class="text-green-400 hover:text-green-300 transition-colors">Regístrate</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import LoadingButton from './LoadingButton.vue';
import AlertToast from './AlertToast.vue';
import { useAsyncAction } from '../composables/useAsyncAction';

const form = ref({ email: '', password: '' });
const needsVerification = ref(false);
const action = useAsyncAction();

async function login() {
  needsVerification.value = false;

  try {
    await action.execute(
      async () => {
        const { data } = await axios.post('/api/login', form.value);
        localStorage.setItem('auth_token', data.token);
        window.location.href = '/chat';
        return data;
      },
      {
        successMessage: 'Sesión iniciada. Redirigiendo...',
        errorMessage: 'Credenciales incorrectas',
        duration: 2000,
      }
    );
  } catch (error) {
    if (error?.response?.status === 403) {
      needsVerification.value = true;
    }
  }
}

async function resendVerification() {
  try {
    await axios.post('/api/email/resend-verification', { email: form.value.email });
    action.alert.value = { type: 'success', message: 'Email de verificación reenviado.' };
    action.showAlert.value = true;
  } catch {
    action.alert.value = { type: 'error', message: 'Error al reenviar.' };
    action.showAlert.value = true;
  }
}
</script>
