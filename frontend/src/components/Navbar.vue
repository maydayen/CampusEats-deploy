<template>
  <div class="navbar">
    <div class="navbar-brand">
      <img
        src="/images/campuseats-logo.png"
        alt="CampusEats Logo"
        class="navbar-logo"
      />
      <strong>CampusEats</strong>
    </div>

    <div class="navbar-actions">
      <RouterLink to="/notifications" class="navbar-icon">
        🔔
        <span class="notification-badge">1</span>
      </RouterLink>

      <button class="navbar-icon logout-icon" @click="handleLogout">
        🚪
      </button>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

function handleLogout() {
  authStore.logout()
  router.push('/login')
}
</script>