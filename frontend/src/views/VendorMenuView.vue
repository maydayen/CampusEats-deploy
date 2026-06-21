<template>
  <main class="page bottom-space">
    <Navbar />

    <h1 class="page-title">{{ vendor?.name || 'Vendor Menu' }}</h1>
    <p style="margin: -8px 0 14px; color: var(--muted); font-size: 13px;" v-if="vendor">
      ★ {{ vendor.rating || 'New' }} · {{ vendor.prep_time }} · 📍 {{ vendor.location }}
    </p>

    <template v-if="loading">
      <div class="skeleton" v-for="n in 3" :key="n"></div>
    </template>

    <template v-else>
      <div class="chip-row" v-if="categories.length > 1">
        <button
          v-for="category in categories"
          :key="category"
          class="chip"
          :class="{ active: category === activeCategory }"
          @click="activeCategory = category"
        >
          {{ category }}
        </button>
      </div>

      <MenuItemCard
        v-for="item in visibleItems"
        :key="item.menu_item_id"
        :item="item"
        @add="cartStore.addItem"
      />

      <div v-if="visibleItems.length === 0" class="empty-state">
        <span class="emoji">🍽️</span>
        No items in this category yet.
      </div>
    </template>

    <div v-if="cartStore.totalItems > 0" style="position: sticky; bottom: 70px;">
      <button class="btn" @click="$router.push('/cart')">
        Go to Cart ({{ cartStore.totalItems }})
      </button>
    </div>

    <BottomNav />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
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
const loading = ref(true)
const activeCategory = ref('All')

onMounted(async () => {
  await Promise.all([vendorStore.loadVendors(), vendorStore.loadMenuItems()])
  loading.value = false
})

const vendor = computed(() => {
  return vendorStore.vendors.find((item) => item.vendor_id === vendorId)
})

const vendorMenuItems = computed(() => {
  return vendorStore.menuItems.filter((item) => item.vendor_id === vendorId)
})

const categories = computed(() => {
  return ['All', ...new Set(vendorMenuItems.value.map((item) => item.category))]
})

const visibleItems = computed(() => {
  if (activeCategory.value === 'All') return vendorMenuItems.value
  return vendorMenuItems.value.filter((item) => item.category === activeCategory.value)
})
</script>
