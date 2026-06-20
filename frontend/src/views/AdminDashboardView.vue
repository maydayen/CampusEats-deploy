<template>
  <main class="page">
    <Navbar />

    <h1 class="page-title">Admin Dashboard</h1>

    <DashboardCard label="Pending Vendors" :value="pendingVendors.length" />

    <div class="card" v-for="vendor in pendingVendors" :key="vendor.vendor_id">
      <h3>{{ vendor.name }}</h3>
      <p>{{ vendor.location }}</p>
      <div class="row">
        <button class="btn" @click="vendorStore.approveVendor(vendor.vendor_id)">Approve</button>
        <button class="btn secondary" @click="vendorStore.rejectVendor(vendor.vendor_id)">Reject</button>
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import Navbar from '../components/Navbar.vue'
import DashboardCard from '../components/DashboardCard.vue'
import { useVendorStore } from '../stores/vendorStore.js'

const vendorStore = useVendorStore()

onMounted(async () => {
  await vendorStore.loadVendors()
})

const pendingVendors = computed(() => {
  return vendorStore.vendors.filter((vendor) => vendor.status === 'pending')
})
</script>
