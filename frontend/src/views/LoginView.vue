<template>
  <main class="page">
    <h1 class="page-title">CampusEats</h1>
    <p>Welcome back. Please login to continue.</p>

    <input v-model="email" class="input" placeholder="Email address" />
    <input v-model="password" class="input" type="password" placeholder="Password" />

    <p v-if="errorMessage" style="color: red;">{{ errorMessage }}</p>

    <button class="btn" @click="handleLogin">Login</button>

    <p style="text-align: center;">
      New to CampusEats?
      <RouterLink to="/register"><strong>Register Now</strong></RouterLink>
    </p>

    <div class="card">
      <strong>Demo Accounts</strong>
      <p>Customer: ali@student.utm.my / 123456</p>
      <p>Vendor: dailygrind@utm.my / 123456</p>
      <p>Admin: admin@campuseats.my / 123456</p>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('ali@student.utm.my')
const password = ref('123456')
const errorMessage = ref('')

async function handleLogin() {
  try {
    const user = await authStore.login(email.value, password.value)

    if (user.role === 'customer') router.push('/home')
    if (user.role === 'vendor') router.push('/vendor/dashboard')
    if (user.role === 'admin') router.push('/admin/dashboard')
  } catch (error) {
    errorMessage.value = error.message
  }
}
</script>
