<template>
  <div class="flex h-full bg-gray-950">
    <!-- Sidebar: Departamentos -->
    <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col">
      <div class="p-4 border-b border-gray-800">
        <h2 class="text-lg font-semibold text-green-400">Departamentos</h2>
      </div>
      <ul class="flex-1 overflow-y-auto">
        <li
          v-for="dept in departments"
          :key="dept.id"
          @click="selectDepartment(dept)"
          :class="[
            'px-4 py-3 cursor-pointer transition-colors',
            activeDepartment?.id === dept.id
              ? 'bg-green-950 border-r-2 border-green-400 text-green-400'
              : 'hover:bg-gray-800 text-gray-300'
          ]"
        >
          {{ dept.name }}
        </li>
      </ul>
      <div class="p-4 border-t border-gray-800 text-sm text-gray-500">
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 bg-green-400 rounded-full shadow-[0_0_6px_rgba(74,222,128,0.6)]"></span>
          {{ onlineUsers.length }} en línea
        </div>
      </div>
    </aside>

    <!-- Área principal del chat -->
    <main class="flex-1 flex flex-col">
      <!-- Header -->
      <header class="px-6 py-4 bg-gray-900 border-b border-gray-800 flex items-center justify-between">
        <div v-if="activeDepartment">
          <h1 class="text-xl font-semibold text-green-400">{{ activeDepartment.name }}</h1>
          <p class="text-sm text-gray-500">Chat del departamento</p>
        </div>
        <div v-else>
          <h1 class="text-xl font-semibold text-green-400">Chat entre Departamentos</h1>
          <p class="text-sm text-gray-500">Selecciona un departamento para comenzar</p>
        </div>
      </header>

      <!-- Mensajes -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
        <div v-if="!activeDepartment" class="flex items-center justify-center h-full text-gray-600">
          <p>Selecciona un departamento del panel izquierdo</p>
        </div>

        <template v-else>
          <!-- Transición de carga -->
          <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div v-if="loadingMessages" class="flex items-center justify-center h-full">
              <div class="flex flex-col items-center gap-3">
                <svg class="w-8 h-8 animate-spin text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-500 text-sm">Cargando mensajes...</p>
              </div>
            </div>
          </Transition>

          <!-- Mensajes con transición -->
          <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 -translate-y-2"
            tag="div"
            class="space-y-4"
          >
            <div
              v-for="msg in messages"
              :key="msg.id"
              :class="[
                'flex',
                msg.user.id === currentUserId ? 'justify-end' : 'justify-start'
              ]"
            >
              <div
                :class="[
                  'max-w-md px-4 py-2 rounded-lg',
                  msg.user.id === currentUserId
                    ? 'bg-green-600 text-white shadow-[0_0_10px_rgba(74,222,128,0.3)]'
                    : 'bg-gray-800 text-gray-200 border border-gray-700'
                ]"
              >
                <p v-if="msg.user.id !== currentUserId" class="text-xs font-semibold mb-1 text-green-400">
                  {{ msg.user.name }}
                </p>
                <p>{{ msg.body }}</p>
                <p :class="[
                  'text-xs mt-1',
                  msg.user.id === currentUserId ? 'text-green-200' : 'text-gray-500'
                ]">
                  {{ formatTime(msg.created_at) }}
                </p>
              </div>
            </div>
          </TransitionGroup>
        </template>
      </div>

      <!-- Input de mensaje -->
      <div v-if="activeDepartment" class="px-6 py-4 bg-gray-900 border-t border-gray-800">
        <form @submit.prevent="sendMessage" class="flex gap-3">
          <input
            v-model="newMessage"
            type="text"
            placeholder="Escribe un mensaje..."
            class="flex-1 px-4 py-2 bg-gray-800 border border-gray-700 text-gray-200 placeholder-gray-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            :disabled="sendAction.isLoading.value"
          />
          <LoadingButton
            type="submit"
            :loading="sendAction.isLoading.value"
            loading-text="Enviando..."
            :disabled="!newMessage.trim()"
            class="px-6 py-2 bg-green-500 text-gray-950 font-semibold rounded-lg hover:bg-green-400 hover:shadow-[0_0_12px_rgba(74,222,128,0.5)] disabled:opacity-50 disabled:cursor-not-allowed min-w-[120px] transition-all duration-200"
          >
            Enviar
          </LoadingButton>
        </form>
      </div>
    </main>

    <!-- Alerta toast -->
    <AlertToast
      :show="sendAction.showAlert.value"
      :type="sendAction.alert.value?.type"
      :message="sendAction.alert.value?.message"
      @dismiss="sendAction.dismissAlert"
    />
  </div>
</template>

<script setup>
import { ref, nextTick, onUnmounted } from 'vue';
import axios from 'axios';
import '../echo';
import LoadingButton from './LoadingButton.vue';
import AlertToast from './AlertToast.vue';
import { useAsyncAction } from '../composables/useAsyncAction';

const departments = ref([]);
const activeDepartment = ref(null);
const messages = ref([]);
const newMessage = ref('');
const loadingMessages = ref(false);
const onlineUsers = ref([]);
const messagesContainer = ref(null);
const currentUserId = window.currentUserId;

const sendAction = useAsyncAction();

let echoChannel = null;

async function loadDepartments() {
  const { data } = await axios.get('/api/departments');
  departments.value = data;
}

async function selectDepartment(dept) {
  if (activeDepartment.value?.id === dept.id) return;

  if (echoChannel) {
    window.Echo.leave(`department.${activeDepartment.value.id}`);
  }

  activeDepartment.value = dept;
  loadingMessages.value = true;

  const { data } = await axios.get(`/api/departments/${dept.id}/messages`);
  messages.value = data;
  loadingMessages.value = false;

  await nextTick();
  scrollToBottom();

  echoChannel = window.Echo.join(`department.${dept.id}`)
    .here((users) => { onlineUsers.value = users; })
    .joining((user) => { onlineUsers.value.push(user); })
    .leaving((user) => { onlineUsers.value = onlineUsers.value.filter(u => u.id !== user.id); })
    .listen('MessageSent', (e) => {
      messages.value.push(e);
      nextTick(() => scrollToBottom());
    });
}

async function sendMessage() {
  if (!newMessage.value.trim()) return;

  const body = newMessage.value;
  newMessage.value = '';

  try {
    await sendAction.execute(
      async () => {
        const { data } = await axios.post(
          `/api/departments/${activeDepartment.value.id}/messages`,
          { body }
        );
        messages.value.push(data);
        await nextTick();
        scrollToBottom();
        return data;
      },
      {
        successMessage: 'Mensaje enviado',
        errorMessage: 'Error al enviar el mensaje',
        duration: 2000,
      }
    );
  } catch {
    newMessage.value = body;
  }
}

function scrollToBottom() {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
}

function formatTime(dateStr) {
  return new Date(dateStr).toLocaleTimeString('es', { hour: '2-digit', minute: '2-digit' });
}

loadDepartments();

onUnmounted(() => {
  if (echoChannel && activeDepartment.value) {
    window.Echo.leave(`department.${activeDepartment.value.id}`);
  }
});
</script>
