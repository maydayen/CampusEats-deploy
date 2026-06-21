<template>
  <main class="page">
    <h1 class="page-title">Create Account</h1>
    <p style="color: var(--muted);">Join CampusEats to start pre-ordering.</p>

    <input v-model="name" class="input" placeholder="Full name" />
    <input v-model="email" class="input" placeholder="Email address" />
    <input v-model="password" class="input" type="password" placeholder="Password" />

    <select v-model="role" class="input">
      <option value="customer">Customer</option>
      <option value="vendor">Vendor</option>
    </select>

    <p v-if="errorMessage" style="color: var(--danger); font-size: 13px;">{{ errorMessage }}</p>

    <button class="btn" :disabled="submitting" @click="handleRegister">
      {{ submitting ? 'Creating account…' : 'Create Account' }}
    </button>

    <p style="text-align: center; margin-top: 14px;">
      Already have an account?
      <RouterLink to="/login"><strong>Login</strong></RouterLink>
    </p>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const role = ref('customer')
const errorMessage = ref('')
const submitting = ref(false)

function validate() {
  if (!name.value.trim()) return 'Please enter your full name'
  if (!email.value.includes('@')) return 'Please enter a valid email address'
  if (password.value.length < 6) return 'Password must be at least 6 characters'
  return ''
}

async function handleRegister() {
  errorMessage.value = validate()
  if (errorMessage.value) return

  submitting.value = true
  try {
    const user = await authStore.register(name.value.trim(), email.value.trim(), password.value, role.value)

    if (user.role === 'vendor') router.push('/vendor/dashboard')
    else router.push('/home')
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    submitting.value = false
  }
}
</script>
