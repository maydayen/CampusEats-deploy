<template>
  <main class="page">
    <Navbar />

    <div class="page-header">
      <BackButton />
      <h1 class="page-title">Vendor Order Details</h1>
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
      <button class="btn" @click="$router.push('/vendor/dashboard')">
        Back to Dashboard
      </button>
    </section>

    <template v-else>
      <div class="card">
        <div class="space-between">
          <div>
            <h3 style="margin: 0;">Order #{{ order.order_id }}</h3>
            <p class="muted" style="margin: 4px 0 0;">
              {{ formattedDate }}
            </p>
          </div>

          <span class="status-badge" :class="`status-${order.status}`">
            {{ order.status }}
          </span>
        </div>
      </div>

      <div class="card">
        <h3>Customer and Pickup</h3>

        <div class="space-between summary-row">
          <span>Customer</span>
          <strong>{{ order.customer_name || `Customer #${order.user_id}` }}</strong>
        </div>

        <div class="space-between summary-row">
          <span>Customer ID</span>
          <strong>#{{ order.user_id }}</strong>
        </div>

        <div class="space-between summary-row">
          <span>Pickup Time</span>
          <strong>{{ order.pickup_at }}</strong>
        </div>

        <div class="space-between summary-row">
          <span>Payment</span>
          <strong>{{ order.payment_label || '-' }}</strong>
        </div>

        <div class="space-between summary-row">
          <span>Payment Status</span>
          <strong>{{ formatPaymentStatus(order.payment_status) }}</strong>
        </div>

        <div v-if="order.note" class="vendor-note-box">
          <span class="muted">Customer Note</span>
          <p>{{ order.note }}</p>
        </div>
      </div>

      <div class="card">
        <h3>Items to Prepare</h3>

        <div
          v-for="item in orderItems"
          :key="item.menu_item_id"
          class="vendor-order-item"
        >
          <div>
            <strong>{{ item.name || getMenuItemName(item.menu_item_id) }}</strong>
            <p class="muted">
              Qty: {{ item.quantity }} × RM {{ Number(item.unit_price || item.price || 0).toFixed(2) }}
            </p>
          </div>

          <strong>RM {{ itemTotal(item).toFixed(2) }}</strong>
        </div>

        <p v-if="orderItems.length === 0" class="muted">
          No item details available for this order.
        </p>
      </div>

      <div class="card">
        <h3>Update Order Status</h3>
        <p class="muted">
          Change the status based on the food preparation progress.
        </p>

        <select v-model="selectedStatus" class="input">
          <option value="placed">Placed</option>
          <option value="preparing">Preparing</option>
          <option value="ready">Ready</option>
          <option value="collected">Collected</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <button class="btn" @click="saveStatus">
          Save Status
        </button>
      </div>

      <div class="receipt-card">
        <div class="receipt-header">
          <img
            src="/images/campuseats-logo.png"
            alt="CampusEats Logo"
            class="receipt-logo"
          />
          <h3>Vendor Order Receipt</h3>
          <p class="muted">Preparation copy for vendor</p>
        </div>

        <div class="receipt-section">
          <div class="receipt-row">
            <span>Order ID</span>
            <strong>#{{ order.order_id }}</strong>
          </div>

          <div class="receipt-row">
            <span>Pickup Time</span>
            <strong>{{ order.pickup_at }}</strong>
          </div>

          <div class="receipt-row">
            <span>Status</span>
            <strong class="receipt-status">{{ order.status }}</strong>
          </div>
        </div>

        <div class="receipt-divider"></div>

        <div class="receipt-section">
          <div class="receipt-row">
            <span>Subtotal</span>
            <span>RM {{ Number(order.subtotal || 0).toFixed(2) }}</span>
          </div>

          <div class="receipt-row">
            <span>Service Fee</span>
            <span>RM {{ Number(order.service_fee || 0).toFixed(2) }}</span>
          </div>

          <div class="receipt-row">
            <span>Tax</span>
            <span>RM {{ Number(order.tax_amount || 0).toFixed(2) }}</span>
          </div>

          <div class="receipt-total">
            <span>Total</span>
            <strong>RM {{ Number(order.total || 0).toFixed(2) }}</strong>
          </div>
        </div>
      </div>
    </template>
  </main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import BackButton from '../components/BackButton.vue'
import { useOrderStore } from '../stores/orderStore.js'
import { useVendorStore } from '../stores/vendorStore.js'
import { useAuthStore } from '../stores/authStore.js'
import { getMockData } from '../services/mockApi.js'

const route = useRoute()
const orderStore = useOrderStore()
const vendorStore = useVendorStore()
const authStore = useAuthStore()

const loading = ref(false)
const orderItemsData = ref([])
const selectedStatus = ref('placed')

const orderId = Number(route.params.id)

const vendorId = computed(() => {
  return Number(authStore.currentUser?.vendor_id)
})

onMounted(async () => {
  loading.value = true

  await orderStore.loadOrders()
  await vendorStore.loadMenuItems()
  orderItemsData.value = await getMockData('orderItems.json')

  if (order.value) {
    selectedStatus.value = order.value.status
  }

  loading.value = false
})

const order = computed(() => {
  return orderStore.orders.find((item) => {
    return (
      Number(item.order_id) === orderId &&
      Number(item.vendor_id) === vendorId.value
    )
  })
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

watch(order, (newOrder) => {
  if (newOrder) {
    selectedStatus.value = newOrder.status
  }
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

function formatPaymentStatus(status) {
  if (status === 'paid_mock') return 'Paid'
  if (status === 'pending') return 'Pending'
  return status || '-'
}

async function saveStatus() {
  if (!order.value) return

  await orderStore.updateOrderStatus(order.value.order_id, selectedStatus.value)
}
</script>