<template>
  <div class="relative">
    <!-- Botón campana -->
    <button
      @click="togglePanel"
      class="relative p-2 rounded-full transition-all duration-300"
      :class="[
        hasNew
          ? 'bg-green-950 text-green-400 animate-bounce-subtle hover:bg-green-900 shadow-[0_0_10px_rgba(74,222,128,0.4)]'
          : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-green-400'
      ]"
      aria-label="Notificaciones"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <!-- Badge contador -->
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 scale-0"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-0"
      >
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold text-gray-950 bg-green-400 rounded-full ring-2 ring-gray-900 shadow-[0_0_8px_rgba(74,222,128,0.6)]"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
      </Transition>
    </button>

    <!-- Panel de notificaciones -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-1 scale-95"
    >
      <div
        v-if="showPanel"
        class="absolute right-0 top-12 w-96 max-h-[480px] bg-gray-900 rounded-xl shadow-2xl border border-gray-700 z-50 flex flex-col overflow-hidden"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
          <h3 class="font-semibold text-green-400">Notificaciones</h3>
          <LoadingButton
            v-if="unreadCount > 0"
            @click="markAllRead"
            :loading="markAllAction.isLoading.value"
            loading-text=""
            class="text-xs text-green-400 hover:text-green-300 font-medium px-2 py-1 rounded hover:bg-gray-800"
          >
            Marcar todas como leídas
          </LoadingButton>
        </div>

        <!-- Lista -->
        <div class="flex-1 overflow-y-auto">
          <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
          >
            <div v-if="notifications.length === 0" class="p-8 text-center text-gray-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              <p>No tienes notificaciones</p>
            </div>
          </Transition>

          <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-x-4"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 translate-x-4"
            tag="div"
          >
            <a
              v-for="notif in notifications"
              :key="notif.id"
              :href="notif.link || '#'"
              @click="handleClick(notif)"
              class="flex items-start gap-3 px-4 py-3 border-b border-gray-800 transition-colors hover:bg-gray-800"
              :class="{ 'bg-green-950/30': !notif.read_at }"
            >
              <!-- Icono por tipo -->
              <div
                class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                :class="typeStyles[notif.type]?.bg || 'bg-gray-800'"
              >
                <span v-html="typeStyles[notif.type]?.icon || ''" class="w-5 h-5"></span>
              </div>

              <!-- Contenido -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <p class="text-sm font-medium text-gray-200 truncate">{{ notif.title }}</p>
                  <Transition
                    enter-active-class="transition duration-200"
                    enter-from-class="scale-0"
                    enter-to-class="scale-100"
                    leave-active-class="transition duration-150"
                    leave-from-class="scale-100"
                    leave-to-class="scale-0"
                  >
                    <span
                      v-if="!notif.read_at"
                      class="w-2 h-2 bg-green-400 rounded-full flex-shrink-0 shadow-[0_0_6px_rgba(74,222,128,0.6)]"
                    ></span>
                  </Transition>
                </div>
                <p class="text-sm text-gray-400 line-clamp-2">{{ notif.body }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ timeAgo(notif.created_at) }}</p>
              </div>

              <!-- Badge tipo -->
              <span
                class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full font-medium"
                :class="typeStyles[notif.type]?.badge || 'bg-gray-800 text-gray-400'"
              >
                {{ typeLabels[notif.type] }}
              </span>
            </a>
          </TransitionGroup>
        </div>
      </div>
    </Transition>

    <!-- Overlay -->
    <div v-if="showPanel" @click="showPanel = false" class="fixed inset-0 z-40"></div>

    <!-- Alert toast -->
    <AlertToast
      :show="markAllAction.showAlert.value"
      :type="markAllAction.alert.value?.type"
      :message="markAllAction.alert.value?.message"
      @dismiss="markAllAction.dismissAlert"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import '../echo';
import LoadingButton from './LoadingButton.vue';
import AlertToast from './AlertToast.vue';
import { useAsyncAction } from '../composables/useAsyncAction';

const notifications = ref([]);
const unreadCount = ref(0);
const showPanel = ref(false);
const hasNew = ref(false);

const markAllAction = useAsyncAction();

const typeLabels = {
  mensaje: 'Mensaje',
  multa: 'Multa',
  asamblea: 'Asamblea',
  pago_atrasado: 'Pago',
};

const typeStyles = {
  mensaje: {
    bg: 'bg-green-950',
    badge: 'bg-green-950 text-green-400',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-green-400"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>',
  },
  multa: {
    bg: 'bg-red-950',
    badge: 'bg-red-950 text-red-400',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-red-400"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>',
  },
  asamblea: {
    bg: 'bg-purple-950',
    badge: 'bg-purple-950 text-purple-400',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-purple-400"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>',
  },
  pago_atrasado: {
    bg: 'bg-amber-950',
    badge: 'bg-amber-950 text-amber-400',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-400"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
  },
};

let newTimeout = null;

async function loadNotifications() {
  try {
    const { data } = await axios.get('/api/notifications');
    notifications.value = data.notifications;
    unreadCount.value = data.unread_count;
  } catch (e) {
    console.error('Error cargando notificaciones:', e);
  }
}

function togglePanel() {
  showPanel.value = !showPanel.value;
  hasNew.value = false;
}

async function handleClick(notif) {
  if (!notif.read_at) {
    await axios.patch(`/api/notifications/${notif.id}/read`);
    notif.read_at = new Date().toISOString();
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  }
}

async function markAllRead() {
  await markAllAction.execute(
    async () => {
      await axios.post('/api/notifications/read-all');
      notifications.value.forEach(n => { n.read_at = n.read_at || new Date().toISOString(); });
      unreadCount.value = 0;
    },
    {
      successMessage: 'Todas las notificaciones marcadas como leídas',
      errorMessage: 'Error al marcar notificaciones',
      duration: 2500,
    }
  );
}

function timeAgo(dateStr) {
  const seconds = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
  if (seconds < 60) return 'Ahora';
  if (seconds < 3600) return `Hace ${Math.floor(seconds / 60)} min`;
  if (seconds < 86400) return `Hace ${Math.floor(seconds / 3600)}h`;
  return `Hace ${Math.floor(seconds / 86400)}d`;
}

onMounted(() => {
  loadNotifications();

  window.Echo.private(`user.${window.currentUserId}.notifications`)
    .listen('NewNotification', (data) => {
      notifications.value.unshift(data);
      unreadCount.value++;
      hasNew.value = true;

      clearTimeout(newTimeout);
      newTimeout = setTimeout(() => { hasNew.value = false; }, 3000);
    });
});

onUnmounted(() => {
  window.Echo.leave(`user.${window.currentUserId}.notifications`);
  clearTimeout(newTimeout);
});
</script>

<style scoped>
@keyframes bounce-subtle {
  0%, 100% { transform: translateY(0); }
  25% { transform: translateY(-3px); }
  50% { transform: translateY(0); }
  75% { transform: translateY(-2px); }
}
.animate-bounce-subtle {
  animation: bounce-subtle 0.6s ease-in-out 3;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
