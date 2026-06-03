<template>
  <div class="h-screen flex flex-col">
    <!-- Navbar con botón de notificaciones -->
    <nav class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between flex-shrink-0">
      <div class="flex items-center gap-4">
        <h1 class="text-lg font-bold text-gray-800">CelePadi</h1>
        <a href="/chat" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Chat</a>
      </div>

      <div class="flex items-center gap-4">
        <NotificationBell />
        <span class="text-sm text-gray-500">{{ userName }}</span>
      </div>
    </nav>

    <!-- Contenido principal -->
    <div class="flex-1 overflow-hidden">
      <DepartmentChat />
    </div>
  </div>
</template>

<script setup>
import NotificationBell from './NotificationBell.vue';
import DepartmentChat from './DepartmentChat.vue';

const userName = window.currentUserName || 'Usuario';
</script>
