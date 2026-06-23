<template>
  <main class="page bottom-space">
    <Navbar />

    <div class="page-header">
      <BackButton />
      <h1 class="page-title">Order History</h1>
    </div>

    <section v-if="loading">
      <div class="loading-card" v-for="n in 3" :key="n">
        <div class="loading-line short"></div>
        <div class="loading-line long"></div>
        <div class="loading-line medium"></div>
      </div>
    </section>

    <section v-else-if="errorMessage" class="error-state">
      <span class="emoji">⚠️</span>
      <h3>Unable to load orders</h3>
      <p>{{ errorMessage }}</p>
      <button class="btn" @click="loadOrderData">Try Again</button>
    </section>

    <template v-else>
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
    </template>

    <BottomNav />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import BackButton from '../components/BackButton.vue'
import OrderCard from '../components/OrderCard.vue'
import { useAuthStore } from '../stores/authStore.js'
import { useOrderStore } from '../stores/orderStore.js'

const authStore = useAuthStore()
const orderStore = useOrderStore()

const statusFilter = ref('all')
const loading = ref(false)
const errorMessage = ref('')

onMounted(() => {
  loadOrderData()
})

async function loadOrderData() {
  loading.value = true
  errorMessage.value = ''

  try {
    await orderStore.loadOrders()
  } catch (error) {
    errorMessage.value = error.message || 'Something went wrong while loading order history.'
  } finally {
    loading.value = false
  }
}

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