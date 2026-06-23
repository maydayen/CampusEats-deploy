<template>
  <button class="back-btn" @click="goBack" aria-label="Go back">
    <svg
      class="back-icon"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2.5"
      stroke-linecap="round"
      stroke-linejoin="round"
    >
      <path d="M19 12H5"></path>
      <path d="M12 19l-7-7 7-7"></path>
    </svg>
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

<style scoped>
.back-btn {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  border: 2px solid #f97316;
  background: white;
  color: #f97316;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.back-btn:hover {
  background: #f97316;
  color: white;
}

.back-icon {
  width: 24px;
  height: 24px;
}
</style>
