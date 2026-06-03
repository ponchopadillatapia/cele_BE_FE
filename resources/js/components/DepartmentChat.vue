<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar: Departamentos -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
      <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Departamentos</h2>
      </div>
      <ul class="flex-1 overflow-y-auto">
        <li
          v-for="dept in departments"
          :key="dept.id"
          @click="selectDepartment(dept)"
          :class="[
            'px-4 py-3 cursor-pointer transition-colors',
            activeDepartment?.id === dept.id
              ? 'bg-blue-50 border-r-2 border-blue-500 text-blue-700'
              : 'hover:bg-gray-50 text-gray-700'
          ]"
        >
          {{ dept.name }}
        </li>
      </ul>
      <div class="p-4 border-t border-gray-200 text-sm text-gray-500">
        <div class="flex items-center gap-2">
          <span class="w-2 h-2 bg-green-500 rounded-full"></span>
          {{ onlineUsers.length }} en línea
        </div>
      </div>
    </aside>

    <!-- Área principal del chat -->
    <main class="flex-1 flex flex-col">
      <!-- Header -->
      <header class="px-6 py-4 bg-white border-b border-gray-200 flex items-center justify-between">
        <div v-if="activeDepartment">
          <h1 class="text-xl font-semibold text-gray-800">{{ activeDepartment.name }}</h1>
          <p class="text-sm text-gray-500">Chat del departamento</p>
        </div>
        <div v-else>
          <h1 class="text-xl font-semibold text-gray-800">Chat entre Departamentos</h1>
          <p class="text-sm text-gray-500">Selecciona un departamento para comenzar</p>
        </div>
      </header>

      <!-- Mensajes -->
      <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
        <div v-if="!activeDepartment" class="flex items-center justify-center h-full text-gray-400">
          <p>Selecciona un departamento del panel izquierdo</p>
        </div>

        <template v-else>
          <div v-if="loading" class="flex items-center justify-center h-full">
            <p class="text-gray-400">Cargando mensajes...</p>
          </div>
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
                  ? 'bg-blue-500 text-white'
                  : 'bg-white text-gray-800 border border-gray-200'
              ]"
            >
              <p v-if="msg.user.id !== currentUserId" class="text-xs font-semibold mb-1 text-blue-600">
                {{ msg.user.name }}
              </p>
              <p>{{ msg.body }}</p>
              <p :class="[
                'text-xs mt-1',
                msg.user.id === currentUserId ? 'text-blue-100' : 'text-gray-400'
              ]">
                {{ formatTime(msg.created_at) }}
              </p>
            </div>
          </div>
        </template>
      </div>

      <!-- Input de mensaje -->
      <div v-if="activeDepartment" class="px-6 py-4 bg-white border-t border-gray-200">
        <form @submit.prevent="sendMessage" class="flex gap-3">
          <input
            v-model="newMessage"
            type="text"
            placeholder="Escribe un mensaje..."
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :disabled="sending"
          />
          <button
            type="submit"
            :disabled="!newMessage.trim() || sending"
            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Enviar
          </button>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, nextTick, onUnmounted } from 'vue';
import axios from 'axios';
import '../echo';

const departments = ref([]);
const activeDepartment = ref(null);
const messages = ref([]);
const newMessage = ref('');
const sending = ref(false);
const loading = ref(false);
const onlineUsers = ref([]);
const messagesContainer = ref(null);
const currentUserId = window.currentUserId;

let echoChannel = null;

// Cargar departamentos
async function loadDepartments() {
  const { data } = await axios.get('/api/departments');
  departments.value = data;
}

// Seleccionar departamento
async function selectDepartment(dept) {
  if (activeDepartment.value?.id === dept.id) return;

  // Salir del canal anterior
  if (echoChannel) {
    window.Echo.leave(`department.${activeDepartment.value.id}`);
  }

  activeDepartment.value = dept;
  loading.value = true;

  // Cargar mensajes
  const { data } = await axios.get(`/api/departments/${dept.id}/messages`);
  messages.value = data;
  loading.value = false;

  await nextTick();
  scrollToBottom();

  // Unirse al canal de presencia
  echoChannel = window.Echo.join(`department.${dept.id}`)
    .here((users) => {
      onlineUsers.value = users;
    })
    .joining((user) => {
      onlineUsers.value.push(user);
    })
    .leaving((user) => {
      onlineUsers.value = onlineUsers.value.filter(u => u.id !== user.id);
    })
    .listen('MessageSent', (e) => {
      messages.value.push(e);
      nextTick(() => scrollToBottom());
    });
}

// Enviar mensaje
async function sendMessage() {
  if (!newMessage.value.trim() || sending.value) return;

  sending.value = true;
  try {
    const { data } = await axios.post(
      `/api/departments/${activeDepartment.value.id}/messages`,
      { body: newMessage.value }
    );
    messages.value.push(data);
    newMessage.value = '';
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error('Error al enviar mensaje:', error);
  } finally {
    sending.value = false;
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

// Inicializar
loadDepartments();

onUnmounted(() => {
  if (echoChannel && activeDepartment.value) {
    window.Echo.leave(`department.${activeDepartment.value.id}`);
  }
});
</script>
