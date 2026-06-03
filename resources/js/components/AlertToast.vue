<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-x-8 scale-95"
        enter-to-class="opacity-100 translate-x-0 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-x-0 scale-100"
        leave-to-class="opacity-0 translate-x-8 scale-95"
      >
        <div
          v-if="show"
          class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg max-w-sm"
          :class="alertClasses"
          role="alert"
        >
          <!-- Icono -->
          <div class="flex-shrink-0">
            <svg v-if="type === 'success'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <!-- Mensaje -->
          <p class="text-sm font-medium flex-1">{{ message }}</p>

          <!-- Cerrar -->
          <button @click="$emit('dismiss')" class="flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  type: { type: String, default: 'success' },
  message: { type: String, default: '' },
});

defineEmits(['dismiss']);

const alertClasses = computed(() => {
  return props.type === 'success'
    ? 'bg-gray-900 border border-green-500/50 text-green-400 shadow-[0_0_15px_rgba(74,222,128,0.2)]'
    : 'bg-gray-900 border border-red-500/50 text-red-400 shadow-[0_0_15px_rgba(239,68,68,0.2)]';
});
</script>
