<template>
  <main class="page bottom-space">
    <Navbar />

    <h1 class="page-title">{{ vendor?.name || 'Vendor Menu' }}</h1>
    <p>{{ vendor?.location }}</p>

    <MenuItemCard
      v-for="item in vendorMenuItems"
      :key="item.menu_item_id"
      :item="item"
      @add="cartStore.addItem"
    />

    <button class="btn" @click="$router.push('/cart')">
      Go to Cart ({{ cartStore.totalItems }})
    </button>

    <BottomNav />
  </main>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import MenuItemCard from '../components/MenuItemCard.vue'
import { useVendorStore } from '../stores/vendorStore.js'
import { useCartStore } from '../stores/cartStore.js'

const route = useRoute()
const vendorStore = useVendorStore()
const cartStore = useCartStore()

const vendorId = Number(route.params.id)

onMounted(async () => {
  await vendorStore.loadVendors()
  await vendorStore.loadMenuItems()
})

const vendor = computed(() => {
  return vendorStore.vendors.find((item) => item.vendor_id === vendorId)
})

const vendorMenuItems = computed(() => {
  return vendorStore.menuItems.filter((item) => item.vendor_id === vendorId)
})
</script>
