<template>
  <main class="page">
    <Navbar />

    <h1 class="page-title">Vendor Dashboard</h1>

    <DashboardCard label="Active Orders" :value="activeOrders.length" />
    <DashboardCard label="Today's Sales" :value="`RM ${todaySales.toFixed(2)}`" />

    <h2>Incoming Orders</h2>
    <div class="card" v-for="order in vendorOrders" :key="order.order_id">
      <div class="space-between">
        <div>
          <h3>Order #{{ order.order_id }}</h3>
          <p>Status: {{ order.status }}</p>
        </div>
        <select v-model="order.status">
          <option>placed</option>
          <option>preparing</option>
          <option>ready</option>
          <option>collected</option>
        </select>
      </div>
    </div>

    <button class="btn secondary" @click="$router.push('/vendor/menu-management')">
      Manage Menu
    </button>
  </main>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import Navbar from '../components/Navbar.vue'
import DashboardCard from '../components/DashboardCard.vue'
import { useOrderStore } from '../stores/orderStore.js'

const orderStore = useOrderStore()

onMounted(async () => {
  await orderStore.loadOrders()
})

const vendorOrders = computed(() => {
  return orderStore.orders.filter((order) => order.vendor_id === 1)
})

const activeOrders = computed(() => {
  return vendorOrders.value.filter((order) => order.status !== 'collected')
})

const todaySales = computed(() => {
  return vendorOrders.value.reduce((sum, order) => sum + Number(order.total), 0)
})
</script>
