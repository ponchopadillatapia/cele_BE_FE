import { ref } from 'vue';

/**
 * Composable para manejar acciones async con estado de carga y alerta de resultado.
 * Proporciona transiciones reactivas: idle → loading → success/error
 */
export function useAsyncAction() {
  const isLoading = ref(false);
  const alert = ref(null); // { type: 'success' | 'error', message: string }
  const showAlert = ref(false);

  let alertTimeout = null;

  /**
   * Ejecuta una acción async con manejo de estado automático.
   * @param {Function} action - función que retorna una promesa
   * @param {object} options - { successMessage, errorMessage, duration }
   */
  async function execute(action, options = {}) {
    const {
      successMessage = 'Operación exitosa',
      errorMessage = 'Ocurrió un error',
      duration = 3000,
    } = options;

    isLoading.value = true;
    alert.value = null;
    showAlert.value = false;

    try {
      const result = await action();
      alert.value = { type: 'success', message: successMessage };
      showAlert.value = true;
      scheduleHide(duration);
      return result;
    } catch (error) {
      const msg = error?.response?.data?.message || errorMessage;
      alert.value = { type: 'error', message: msg };
      showAlert.value = true;
      scheduleHide(duration + 1000);
      throw error;
    } finally {
      isLoading.value = false;
    }
  }

  function scheduleHide(duration) {
    clearTimeout(alertTimeout);
    alertTimeout = setTimeout(() => {
      showAlert.value = false;
    }, duration);
  }

  function dismissAlert() {
    showAlert.value = false;
  }

  return { isLoading, alert, showAlert, execute, dismissAlert };
}
