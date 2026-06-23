<template>
  <main class="page bottom-space">
    <Navbar />

    <div class="page-header">
      <BackButton />
      <h1 class="page-title">Order Details</h1>
    </div>

    <section v-if="loading">
      <div class="loading-card">
        <div class="loading-line short"></div>
        <div class="loading-line long"></div>
        <div class="loading-line medium"></div>
      </div>
    </section>

    <section v-else-if="!order" class="empty-state">
      <span class="emoji">📦</span>
      <h3>Order not found</h3>
      <p>The selected order cannot be found.</p>
      <button class="btn" @click="$router.push('/orders')">
        Back to Orders
      </button>
    </section>

    <template v-else>
        <div class="card">
            <div class="space-between">
                <div>
                    <h3 style="margin: 0;">Order #{{ order.order_id }}</h3>
                    <p class="muted" style="margin: 4px 0 0;">{{ formattedDate }}</p>
                </div>

                <span class="status-badge" :class="`status-${order.status}`">
                    {{ order.status }}
                </span>
            </div>
        </div>

        <div class="card">
            <h3>Order Status</h3>
            <p class="muted">Track your pickup order progress.</p>

            <div class="status-timeline">
                <div
                    v-for="step in statusSteps"
                    :key="step.value"
                    class="timeline-step"
                    :class="{
                    active: isStatusActive(step.value),
                    current: order.status === step.value
                }"
            >
                <div class="timeline-icon">
                    {{ step.icon }}
                </div>

                <div class="timeline-content">
                    <strong>{{ step.label }}</strong>
                    <small>{{ step.description }}</small>
                </div>
            </div>
        </div>
    </div>

      <div class="card">
        <h3>Pickup Information</h3>

        <div class="space-between summary-row">
          <span>Pickup Time</span>
          <strong>{{ order.pickup_at }}</strong>
        </div>

        <div class="space-between summary-row">
          <span>Vendor ID</span>
          <strong>#{{ order.vendor_id }}</strong>
        </div>

        <div v-if="order.note" class="summary-row">
          <span class="muted">Note for Vendor</span>
          <p style="margin: 6px 0 0;">{{ order.note }}</p>
        </div>
      </div>

      <div class="card">
        <h3>Payment Information</h3>

        <div class="space-between summary-row">
          <span>Payment Method</span>
          <strong>{{ order.payment_label || '-' }}</strong>
        </div>

        <div class="space-between summary-row">
          <span>Payment Status</span>
          <strong>{{ order.payment_status || '-' }}</strong>
        </div>
      </div>

      <div class="card">
        <h3>Ordered Items</h3>

        <div
          v-for="item in orderItems"
          :key="item.menu_item_id"
          class="order-detail-item"
        >
          <div>
            <strong>{{ item.name || getMenuItemName(item.menu_item_id) }}</strong>
            <p class="muted">
              {{ item.quantity }} × RM {{ Number(item.unit_price || item.price || 0).toFixed(2) }}
            </p>
          </div>

          <strong>
            RM {{ itemTotal(item).toFixed(2) }}
          </strong>
        </div>

        <p v-if="orderItems.length === 0" class="muted">
          No item details available for this order.
        </p>
      </div>

      <div class="card">
        <h3>Payment Summary</h3>

        <div class="space-between summary-row">
          <span>Subtotal</span>
          <span>RM {{ Number(order.subtotal || 0).toFixed(2) }}</span>
        </div>

        <div class="space-between summary-row">
          <span>Service Fee</span>
          <span>RM {{ Number(order.service_fee || 0).toFixed(2) }}</span>
        </div>

        <div class="space-between summary-row">
          <span>Tax</span>
          <span>RM {{ Number(order.tax_amount || 0).toFixed(2) }}</span>
        </div>

        <hr />

        <div class="space-between">
          <strong>Total</strong>
          <strong>RM {{ Number(order.total || 0).toFixed(2) }}</strong>
        </div>
      </div>
    </template>

    <BottomNav />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import BackButton from '../components/BackButton.vue'
import { useOrderStore } from '../stores/orderStore.js'
import { useVendorStore } from '../stores/vendorStore.js'
import { getMockData } from '../services/mockApi.js'

const route = useRoute()
const orderStore = useOrderStore()
const vendorStore = useVendorStore()

const loading = ref(false)
const orderItemsData = ref([])

const orderId = Number(route.params.id)

const statusSteps = [
  {
    value: 'placed',
    label: 'Placed',
    description: 'Your order has been received by the vendor.',
    icon: '🧾'
  },
  {
    value: 'preparing',
    label: 'Preparing',
    description: 'The vendor is preparing your food.',
    icon: '👨‍🍳'
  },
  {
    value: 'ready',
    label: 'Ready',
    description: 'Your food is ready for pickup.',
    icon: '✅'
  },
  {
    value: 'collected',
    label: 'Collected',
    description: 'The order has been collected.',
    icon: '📦'
  }
]

onMounted(async () => {
  loading.value = true

  await orderStore.loadOrders()
  await vendorStore.loadMenuItems()
  orderItemsData.value = await getMockData('orderItems.json')

  loading.value = false
})

const order = computed(() => {
  return orderStore.orders.find((item) => Number(item.order_id) === orderId)
})

const orderItems = computed(() => {
  if (!order.value) return []

  if (order.value.items?.length) {
    return order.value.items
  }

  return orderItemsData.value.filter((item) => {
    return Number(item.order_id) === orderId
  })
})

const formattedDate = computed(() => {
  if (!order.value?.created_at) return 'Recently placed'

  const date = new Date(order.value.created_at)

  if (Number.isNaN(date.getTime())) {
    return order.value.created_at
  }

  return date.toLocaleString()
})

function getMenuItemName(menuItemId) {
  const menuItem = vendorStore.menuItems.find((item) => {
    return Number(item.menu_item_id) === Number(menuItemId)
  })

  return menuItem ? menuItem.name : `Menu Item #${menuItemId}`
}

function itemTotal(item) {
  const price = Number(item.unit_price || item.price || 0)
  const quantity = Number(item.quantity || 0)

  return price * quantity
}

function isStatusActive(stepValue) {
  const currentStatus = order.value?.status || 'placed'
  const currentIndex = statusSteps.findIndex((step) => step.value === currentStatus)
  const stepIndex = statusSteps.findIndex((step) => step.value === stepValue)

  return stepIndex <= currentIndex
}
</script>