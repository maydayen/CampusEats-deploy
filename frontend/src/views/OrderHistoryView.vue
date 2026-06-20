<template>
  <main class="page bottom-space">
    <Navbar />

    <h1 class="page-title">Order History</h1>

    <OrderCard
      v-for="order in customerOrders"
      :key="order.order_id"
      :order="order"
    />

    <BottomNav />
  </main>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import OrderCard from '../components/OrderCard.vue'
import { useAuthStore } from '../stores/authStore.js'
import { useOrderStore } from '../stores/orderStore.js'

const authStore = useAuthStore()
const orderStore = useOrderStore()

onMounted(async () => {
  await orderStore.loadOrders()
})

const customerOrders = computed(() => {
  return orderStore.orders.filter((order) => order.user_id === authStore.currentUser.user_id)
})
</script>
