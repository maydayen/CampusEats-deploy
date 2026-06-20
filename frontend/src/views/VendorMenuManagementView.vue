<template>
  <main class="page">
    <Navbar />

    <h1 class="page-title">Menu Management</h1>

    <div class="card" v-for="item in vendorItems" :key="item.menu_item_id">
      <div class="space-between">
        <div>
          <h3>{{ item.name }}</h3>
          <p>RM {{ item.price.toFixed(2) }}</p>
          <small>{{ item.in_stock ? 'In Stock' : 'Out of Stock' }}</small>
        </div>
        <button class="btn secondary" style="width: auto;" @click="item.in_stock = !item.in_stock">
          Toggle
        </button>
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import Navbar from '../components/Navbar.vue'
import { useVendorStore } from '../stores/vendorStore.js'

const vendorStore = useVendorStore()

onMounted(async () => {
  await vendorStore.loadMenuItems()
})

const vendorItems = computed(() => {
  return vendorStore.menuItems.filter((item) => item.vendor_id === 1)
})
</script>
