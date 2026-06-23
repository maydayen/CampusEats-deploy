<template>
  <main class="page">
    <Navbar />

    <div class="page-header">
      <BackButton />
      <div>
        <h1 class="page-title">Vendor Dashboard</h1>
        <p class="muted">Manage orders and monitor today’s performance.</p>
      </div>
    </div>

    <div class="dashboard-grid">
      <DashboardCard label="Active Orders" :value="activeOrders.length" />
      <DashboardCard label="Today's Sales" :value="`RM ${todaySales.toFixed(2)}`" />
      <DashboardCard label="Completed Orders" :value="completedOrders.length" />
      <DashboardCard label="Average Prep Time" value="15 min" />
    </div>

    <div class="card">
      <div class="space-between">
        <div>
          <h3 style="margin: 0;">Order Queue</h3>
          <p class="muted" style="margin: 4px 0 0;">
            Update order status based on preparation progress.
          </p>
        </div>

        <select v-model="statusFilter" class="small-select">
          <option value="all">All</option>
          <option value="placed">Placed</option>
          <option value="preparing">Preparing</option>
          <option value="ready">Ready</option>
          <option value="collected">Collected</option>
        </select>
      </div>
    </div>

    <div v-if="filteredOrders.length === 0" class="empty-state">
      <span class="emoji">📦</span>
      <h3>No orders found</h3>
      <p>No order matches the selected status.</p>
    </div>

    <div
      v-for="order in filteredOrders"
      :key="order.order_id"
      class="card vendor-order-card clickable-order-card"
      @click="goToOrderDetails(order.order_id)"
    >
      <div class="space-between">
        <div>
          <h3>Order #{{ order.order_id }}</h3>
          <p class="muted">Pickup: {{ order.pickup_at }}</p>
        </div>

        <span class="status-badge" :class="`status-${order.status}`">
          {{ order.status }}
        </span>
      </div>

      <div class="vendor-order-summary">
        <div>
          <span class="muted">Customer ID</span>
          <strong>#{{ order.user_id }}</strong>
        </div>

        <div>
          <span class="muted">Total</span>
          <strong>RM {{ Number(order.total).toFixed(2) }}</strong>
        </div>
      </div>

      <label class="muted">Update Status</label>
      <select
        v-model="order.status"
        class="input"
        @click.stop
        @change.stop="orderStore.updateOrderStatus(order.order_id, order.status)"
      >
        <option value="placed">Placed</option>
        <option value="preparing">Preparing</option>
        <option value="ready">Ready</option>
        <option value="collected">Collected</option>
      </select>
    </div>

    <button class="btn secondary" @click="$router.push('/vendor/menu-management')">
      Manage Menu Items
    </button>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import Navbar from '../components/Navbar.vue'
import BackButton from '../components/BackButton.vue'
import DashboardCard from '../components/DashboardCard.vue'
import { useOrderStore } from '../stores/orderStore'
import { useRouter } from 'vue-router'

const orderStore = useOrderStore()
const statusFilter = ref('all')

onMounted(async () => {
  await orderStore.loadOrders()
})

const vendorOrders = computed(() => {
  return orderStore.orders.filter((order) => order.vendor_id === 1)
})

const activeOrders = computed(() => {
  return vendorOrders.value.filter((order) => {
    return order.status !== 'collected'
  })
})

const completedOrders = computed(() => {
  return vendorOrders.value.filter((order) => {
    return order.status === 'collected'
  })
})

const todaySales = computed(() => {
  return vendorOrders.value.reduce((sum, order) => {
    return sum + Number(order.total || 0)
  }, 0)
})

const filteredOrders = computed(() => {
  if (statusFilter.value === 'all') {
    return vendorOrders.value
  }

  return vendorOrders.value.filter((order) => {
    return order.status === statusFilter.value
  })
})

const router = useRouter()

function goToOrderDetails(orderId) {
  router.push(`/vendor/orders/${orderId}`)
}
</script>