<template>
  <main class="page bottom-space">
    <Navbar />

    <h1 class="page-title">Notifications</h1>

    <NotificationItem
      v-for="notification in userNotifications"
      :key="notification.notification_id"
      :notification="notification"
      @read="notificationStore.markAsRead"
    />

    <BottomNav />
  </main>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import NotificationItem from '../components/NotificationItem.vue'
import { useAuthStore } from '../stores/authStore.js'
import { useNotificationStore } from '../stores/notificationStore.js'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

onMounted(async () => {
  await notificationStore.loadNotifications()
})

const userNotifications = computed(() => {
  return notificationStore.notifications.filter((item) => item.user_id === authStore.currentUser.user_id)
})
</script>
