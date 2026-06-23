<template>
  <main class="page bottom-space">
    <Navbar />

    <h1 class="page-title">Order History</h1>

    <select v-model="statusFilter" class="input">
      <option value="all">All Orders</option>
      <option value="placed">Placed</option>
      <option value="preparing">Preparing</option>
      <option value="ready">Ready</option>
      <option value="collected">Collected</option>
    </select>

    <div v-if="filteredOrders.length === 0" class="empty-state">
      <span class="emoji">📦</span>
      <h3>No orders found</h3>
      <p>Your order history will appear here.</p>
    </div>

    <OrderCard
      v-for="order in filteredOrders"
      :key="order.order_id"
      :order="order"
    />

    <BottomNav />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import OrderCard from '../components/OrderCard.vue'
import { useAuthStore } from '../stores/authStore.js'
import { useOrderStore } from '../stores/orderStore.js'

const authStore = useAuthStore()
const orderStore = useOrderStore()
const statusFilter = ref('all')

onMounted(async () => {
  await orderStore.loadOrders()
})

const customerOrders = computed(() => {
  return orderStore.orders.filter((order) => {
    return order.user_id === authStore.currentUser.user_id
  })
})

const filteredOrders = computed(() => {
  if (statusFilter.value === 'all') {
    return customerOrders.value
  }

  return customerOrders.value.filter((order) => {
    return order.status === statusFilter.value
  })
})
</script>