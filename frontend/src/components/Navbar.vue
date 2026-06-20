<template>
  <div class="space-between" style="margin-bottom: 20px;">
    <strong>CampusEats</strong>
    <button class="btn secondary" style="width: auto;" @click="handleLogout">Logout</button>
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
