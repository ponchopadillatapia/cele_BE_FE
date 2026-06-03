<template>
  <div class="h-screen flex flex-col bg-gray-950">
    <!-- Navbar -->
    <nav class="bg-gray-900 border-b border-gray-800 px-6 py-3 flex items-center justify-between flex-shrink-0">
      <div class="flex items-center gap-4">
        <h1 class="text-lg font-bold text-green-400">CelePadi</h1>
        <a href="/chat" class="text-sm text-gray-400 hover:text-green-400 transition-colors">Chat</a>
      </div>

      <div class="flex items-center gap-4">
        <NotificationBell />
        <span class="text-sm text-gray-400">{{ userName }}</span>
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
