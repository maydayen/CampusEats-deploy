<template>
  <main class="page bottom-space">
    <Navbar />

    <div class="page-header">
      <BackButton />
      <h1 class="page-title">Profile</h1>
    </div>

    <div class="profile-card">
      <img
        src="/images/campuseats-logo.png"
        alt="CampusEats Logo"
        class="profile-avatar"
      />

      <h2>{{ authStore.currentUser?.name }}</h2>
      <p class="muted">{{ authStore.currentUser?.email }}</p>
      <span class="profile-role">{{ authStore.currentUser?.role }}</span>
    </div>

    <div class="card">
      <h3>Account Information</h3>

      <div class="space-between summary-row">
        <span>Name</span>
        <strong>{{ authStore.currentUser?.name }}</strong>
      </div>

      <div class="space-between summary-row">
        <span>Email</span>
        <strong>{{ authStore.currentUser?.email }}</strong>
      </div>

      <div class="space-between summary-row">
        <span>Role</span>
        <strong>{{ authStore.currentUser?.role }}</strong>
      </div>
    </div>

    <button class="btn secondary" @click="handleLogout">
      Logout
    </button>

    <BottomNav />
  </main>
</template>

<script setup>
import { useRouter } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import BackButton from '../components/BackButton.vue'
import { useAuthStore } from '../stores/authStore.js'

const router = useRouter()
const authStore = useAuthStore()

function handleLogout() {
  authStore.logout()
  router.push('/login')
}
</script>