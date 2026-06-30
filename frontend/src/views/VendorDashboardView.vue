<template>
  <main class="page">
    <Navbar />

    <div class="page-header">
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

    <div class="dashboard-grid">
      <DashboardCard label="Placed" :value="placedOrders.length" />
      <DashboardCard label="Preparing" :value="preparingOrders.length" />
      <DashboardCard label="Ready" :value="readyOrders.length" />
      <DashboardCard label="Collected" :value="collectedOrders.length" />
    </div>

    <div class="card">
      <h3 style="margin: 0;">Best-Selling Item</h3>
      <p class="muted" style="margin: 4px 0 0;">
        Most ordered item based on current loaded orders.
      </p>

      <div class="best-selling-box">
        <span>🏆</span>
        <strong>{{ bestSellingItem }}</strong>
      </div>
    </div>

    <div class="card">
      <div class="space-between">
        <div>
          <h3 style="margin: 0;">Review Monitoring</h3>
          <p class="muted" style="margin: 4px 0 0;">
            Monitor customer feedback for your vendor account.
          </p>
        </div>

        <div class="rating-score">
          ⭐ {{ vendorAverageRating ? vendorAverageRating.toFixed(1) : '0.0' }}
        </div>
      </div>

      <p class="muted">
        {{ vendorReviews.length }} review(s) received
      </p>

      <select v-model="reviewFilter" class="input">
        <option value="all">All Reviews</option>
        <option value="5">5 Stars</option>
        <option value="4">4 Stars</option>
        <option value="low">3 Stars and Below</option>
      </select>

      <div v-if="filteredVendorReviews.length === 0" class="empty-review">
        <p class="muted">No reviews match the selected filter.</p>
      </div>

      <div
        v-for="review in filteredVendorReviews.slice(0, 3)"
        :key="review.review_id"
        class="review-item"
      >
        <div class="space-between">
          <strong>Customer #{{ review.user_id }}</strong>
          <span class="review-stars">
            {{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}
          </span>
        </div>

        <p>{{ review.comment }}</p>
        <small class="muted">{{ new Date(review.created_at).toLocaleString() }}</small>
      </div>
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
        @change.stop="handleStatusChange(order)"
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
import { useRouter } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import DashboardCard from '../components/DashboardCard.vue'
import { useOrderStore } from '../stores/orderStore'
import { useReviewStore } from '../stores/reviewStore.js'
import { useAuthStore } from '../stores/authStore.js'

const orderStore = useOrderStore()
const reviewStore = useReviewStore()
const authStore = useAuthStore()
const router = useRouter()

const statusFilter = ref('all')
const reviewFilter = ref('all')

const vendorId = computed(() => {
  return Number(authStore.currentUser?.vendor_id)
})

onMounted(async () => {
  await orderStore.loadOrders()
  await reviewStore.loadReviews()
})

const vendorOrders = computed(() => {
  return orderStore.orders.filter((order) => {
    return Number(order.vendor_id) === vendorId.value
  })
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

const placedOrders = computed(() => {
  return vendorOrders.value.filter((order) => {
    return order.status === 'placed'
  })
})

const preparingOrders = computed(() => {
  return vendorOrders.value.filter((order) => {
    return order.status === 'preparing'
  })
})

const readyOrders = computed(() => {
  return vendorOrders.value.filter((order) => {
    return order.status === 'ready'
  })
})

const collectedOrders = computed(() => {
  return vendorOrders.value.filter((order) => {
    return order.status === 'collected'
  })
})

const todaySales = computed(() => {
  return vendorOrders.value.reduce((sum, order) => {
    return sum + Number(order.total || 0)
  }, 0)
})

const bestSellingItem = computed(() => {
  const itemCount = {}

  vendorOrders.value.forEach((order) => {
    if (!order.items) return

    order.items.forEach((item) => {
      const name = item.name || `Item #${item.menu_item_id}`

      if (!itemCount[name]) {
        itemCount[name] = 0
      }

      itemCount[name] += Number(item.quantity || 0)
    })
  })

  const sortedItems = Object.entries(itemCount).sort((a, b) => {
    return b[1] - a[1]
  })

  if (sortedItems.length === 0) {
    return 'No data'
  }

  return sortedItems[0][0]
})

const filteredOrders = computed(() => {
  if (statusFilter.value === 'all') {
    return vendorOrders.value
  }

  return vendorOrders.value.filter((order) => {
    return order.status === statusFilter.value
  })
})

const vendorReviews = computed(() => {
  return reviewStore.getVendorReviews(vendorId.value)
})

const filteredVendorReviews = computed(() => {
  if (reviewFilter.value === 'all') {
    return vendorReviews.value
  }

  if (reviewFilter.value === 'low') {
    return vendorReviews.value.filter((review) => {
      return Number(review.rating) <= 3
    })
  }

  return vendorReviews.value.filter((review) => {
    return Number(review.rating) === Number(reviewFilter.value)
  })
})

const vendorAverageRating = computed(() => {
  return reviewStore.getAverageRating(vendorId.value)
})

function goToOrderDetails(orderId) {
  router.push(`/vendor/orders/${orderId}`)
}

async function handleStatusChange(order) {
  await orderStore.updateOrderStatus(order.order_id, order.status)
}
</script>