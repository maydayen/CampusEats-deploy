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
      <DashboardCard label="Pending Vendors" :value="pendingVendors.length" />
      <DashboardCard label="Approved Vendors" :value="approvedVendors.length" />
      <DashboardCard label="Total Vendors" :value="vendorStore.vendors.length" />
      <DashboardCard label="Platform Sales" :value="`RM ${platformSales.toFixed(2)}`" />
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
      <div class="space-between">
        <div>
          <h3>{{ vendor.name }}</h3>
          <p class="muted">{{ vendor.location }}</p>
          <p class="muted">{{ vendor.opening_hours }}</p>
        </div>

        <span class="status-badge" :class="`vendor-status-${vendor.status}`">
          {{ vendor.status }}
        </span>
      </div>

      <div class="admin-vendor-meta">
        <span>⭐ {{ vendor.rating || 0 }}</span>
        <span>⏱️ {{ vendor.prep_time || 'N/A' }}</span>
      </div>

      <div class="row action-row" v-if="vendor.status === 'pending'">
        <button class="btn" @click="vendorStore.approveVendor(vendor.vendor_id)">
          Approve
        </button>

        <button
          class="btn secondary"
          @click="vendorStore.rejectVendor(vendor.vendor_id)"
        >
          Reject
        </button>
      </div>

      <button
        v-else-if="vendor.status === 'approved'"
        class="btn secondary"
        @click="deactivateVendor(vendor.vendor_id)"
      >
        Deactivate Vendor
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
</script>