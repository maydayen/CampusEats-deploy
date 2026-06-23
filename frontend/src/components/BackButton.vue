<template>
  <button class="back-btn" @click="goBack" aria-label="Go back">
    ←
  </button>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

function goBack() {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/home')
  }
}
</script>