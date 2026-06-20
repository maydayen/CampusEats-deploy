<template>
  <div class="topbar">
    <h1>CampusEats</h1>
    <div class="row" style="gap: 4px;">
      <RouterLink to="/notifications" class="icon-btn">
        🔔
        <span v-if="unreadCount > 0" class="nav-badge">{{ unreadCount }}</span>
      </RouterLink>
      <button class="icon-btn" @click="handleLogout">🚪</button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import { useNotificationStore } from '../stores/notificationStore'

const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

onMounted(async () => {
  if (notificationStore.notifications.length === 0) {
    await notificationStore.loadNotifications()
  }
})

const unreadCount = computed(() => {
  return notificationStore.notifications.filter(
    (item) => !item.is_read && item.user_id === authStore.currentUser?.user_id
  ).length
})

function handleLogout() {
  authStore.logout()
  router.push('/login')
}
</script>
