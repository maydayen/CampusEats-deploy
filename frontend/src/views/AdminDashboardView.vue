<template>
  <main class="page">
    <Navbar />

    <div class="page-header">
      <BackButton />
      <div>
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="muted">Review vendors and monitor platform activity.</p>
      </div>
    </div>

    <div class="dashboard-grid">
      <DashboardCard label="Total Orders" :value="orderStore.orders.length" />
      <DashboardCard label="Active Orders" :value="activeOrders.length" />
      <DashboardCard label="Completed Orders" :value="completedOrders.length" />
      <DashboardCard label="Platform Sales" :value="`RM ${platformSales.toFixed(2)}`" />
      <DashboardCard label="Average Order" :value="`RM ${averageOrderValue.toFixed(2)}`" />
      <DashboardCard label="Total Vendors" :value="vendorStore.vendors.length" />
      <DashboardCard label="Pending Vendors" :value="pendingVendors.length" />
      <DashboardCard label="Approved Vendors" :value="approvedVendors.length" />
    </div>

    <div class="card admin-analytics-card">
      <h3>Platform Overview</h3>
      <p class="muted">
        Summary of CampusEats ordering activity and vendor registration status.
      </p>

      <div class="analytics-row">
        <span>Order Completion Rate</span>
        <strong>{{ completionRate }}%</strong>
      </div>

      <div class="analytics-bar">
        <div
          class="analytics-bar-fill"
          :style="{ width: `${completionRate}%` }"
        ></div>
      </div>

      <div class="analytics-row">
        <span>Vendor Approval Rate</span>
        <strong>{{ approvalRate }}%</strong>
      </div>

      <div class="analytics-bar">
        <div
          class="analytics-bar-fill"
          :style="{ width: `${approvalRate}%` }"
        ></div>
      </div>
    </div>

    <div class="card">
      <h3 style="margin-top: 0;">Vendor Applications</h3>

      <input
        v-model="searchText"
        class="input"
        placeholder="Search vendor name or location..."
      />

      <select v-model="statusFilter" class="input">
        <option value="all">All Vendors</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
        <option value="deactivated">Deactivated</option>
      </select>
    </div>

    <div v-if="filteredVendors.length === 0" class="empty-state">
      <span class="emoji">🏪</span>
      <h3>No vendors found</h3>
      <p>No vendor matches the current filter.</p>
    </div>

    <div
      v-for="vendor in filteredVendors"
      :key="vendor.vendor_id"
      class="card admin-vendor-card"
    >
      <div class="admin-vendor-header">
        <div class="admin-vendor-main">
          <img
            :src="vendor.image_url || vendor.image || '/images/campuseats-logo.png'"
            :alt="vendor.name"
            class="admin-vendor-image"
          />

          <div>
            <h3>{{ vendor.name }}</h3>
            <p class="muted">{{ vendor.category || 'Food Vendor' }}</p>
            <p class="muted">{{ vendor.location }}</p>
          </div>
        </div>

        <span class="status-badge" :class="`vendor-status-${vendor.status}`">
          {{ vendor.status }}
        </span>
      </div>

      <div class="admin-detail-grid">
        <div>
          <span>Vendor ID</span>
          <strong>#{{ vendor.vendor_id }}</strong>
        </div>

        <div>
          <span>Owner ID</span>
          <strong>#{{ vendor.owner_id || 'N/A' }}</strong>
        </div>

        <div>
          <span>Opening Hours</span>
          <strong>{{ vendor.opening_hours || 'N/A' }}</strong>
        </div>

        <div>
          <span>Average Prep Time</span>
          <strong>{{ vendor.prep_time || 'N/A' }}</strong>
        </div>

        <div>
          <span>Rating</span>
          <strong>⭐ {{ vendor.rating || 0 }}</strong>
        </div>

        <div>
          <span>Registration Status</span>
          <strong class="capitalize">{{ vendor.status }}</strong>
        </div>
      </div>

      <div class="admin-verification-box">
        <div>
          <strong>Verification Checklist</strong>
          <p class="muted">
            Mock review details for PR2 vendor approval flow.
          </p>
        </div>

        <ul>
          <li>Business information provided</li>
          <li>Campus location available</li>
          <li>Opening hours submitted</li>
          <li>Menu can be reviewed after approval</li>
        </ul>
      </div>

      <div class="row action-row" v-if="vendor.status === 'pending'">
        <button class="btn" @click="vendorStore.approveVendor(vendor.vendor_id)">
          Approve Vendor
        </button>

        <button
          class="btn secondary"
          @click="vendorStore.rejectVendor(vendor.vendor_id)"
        >
          Reject Vendor
        </button>
      </div>

      <button
        v-else-if="vendor.status === 'approved'"
        class="btn secondary"
        @click="deactivateVendor(vendor.vendor_id)"
      >
        Deactivate Vendor
      </button>

      <button
        v-else-if="vendor.status === 'deactivated'"
        class="btn"
        @click="reactivateVendor(vendor.vendor_id)"
      >
        Reactivate Vendor
      </button>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import Navbar from '../components/Navbar.vue'
import BackButton from '../components/BackButton.vue'
import DashboardCard from '../components/DashboardCard.vue'
import { useVendorStore } from '../stores/vendorStore'
import { useOrderStore } from '../stores/orderStore'

const vendorStore = useVendorStore()
const orderStore = useOrderStore()

const searchText = ref('')
const statusFilter = ref('all')

onMounted(async () => {
  await vendorStore.loadVendors()
  await orderStore.loadOrders()
})

const pendingVendors = computed(() => {
  return vendorStore.vendors.filter((vendor) => vendor.status === 'pending')
})

const approvedVendors = computed(() => {
  return vendorStore.vendors.filter((vendor) => vendor.status === 'approved')
})

const platformSales = computed(() => {
  return orderStore.orders.reduce((sum, order) => {
    return sum + Number(order.total || 0)
  }, 0)
})

const activeOrders = computed(() => {
  return orderStore.orders.filter((order) => {
    return order.status !== 'collected' && order.status !== 'cancelled'
  })
})

const completedOrders = computed(() => {
  return orderStore.orders.filter((order) => {
    return order.status === 'collected'
  })
})

const averageOrderValue = computed(() => {
  if (orderStore.orders.length === 0) {
    return 0
  }

  return platformSales.value / orderStore.orders.length
})

const completionRate = computed(() => {
  if (orderStore.orders.length === 0) {
    return 0
  }

  return Math.round((completedOrders.value.length / orderStore.orders.length) * 100)
})

const approvalRate = computed(() => {
  if (vendorStore.vendors.length === 0) {
    return 0
  }

  return Math.round((approvedVendors.value.length / vendorStore.vendors.length) * 100)
})

const filteredVendors = computed(() => {
  const keyword = searchText.value.toLowerCase()

  return vendorStore.vendors.filter((vendor) => {
    const matchesSearch =
      vendor.name.toLowerCase().includes(keyword) ||
      vendor.location.toLowerCase().includes(keyword)

    const matchesStatus =
      statusFilter.value === 'all' || vendor.status === statusFilter.value

    return matchesSearch && matchesStatus
  })
})

function deactivateVendor(vendorId) {
  const vendor = vendorStore.vendors.find((item) => item.vendor_id === vendorId)

  if (vendor) {
    vendor.status = 'deactivated'
  }
}

function reactivateVendor(vendorId) {
  const vendor = vendorStore.vendors.find((item) => item.vendor_id === vendorId)

  if (vendor) {
    vendor.status = 'approved'
  }
}
</script>