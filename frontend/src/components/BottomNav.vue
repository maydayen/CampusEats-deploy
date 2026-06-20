<template>
  <nav class="bottom-nav">
    <RouterLink to="/home">
      <span class="nav-icon">🏠</span>
      Home
    </RouterLink>
    <RouterLink to="/orders">
      <span class="nav-icon">🧾</span>
      Orders
    </RouterLink>
    <RouterLink to="/cart">
      <span class="nav-icon">🛒</span>
      <span v-if="cartStore.totalItems > 0" class="nav-badge">{{ cartStore.totalItems }}</span>
      Cart
    </RouterLink>
    <RouterLink to="/notifications">
      <span class="nav-icon">🔔</span>
      Alerts
    </RouterLink>
  </nav>
</template>

<script setup>
import { useCartStore } from '../stores/cartStore.js'

const cartStore = useCartStore()
</script>