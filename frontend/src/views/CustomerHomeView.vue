<template>
  <main class="page bottom-space">
    <Navbar />

    <h1 class="page-title">Customer Home</h1>
    <input v-model="searchText" class="input" placeholder="Search vendor..." />

    <h2>Promotions</h2>
    <PromotionCard
      v-for="promotion in activePromotions"
      :key="promotion.promotion_id"
      :promotion="promotion"
    />

    <h2>Nearby Dining</h2>
    <VendorCard
      v-for="vendor in filteredVendors"
      :key="vendor.vendor_id"
      :vendor="vendor"
      @select="goToVendor"
    />

    <BottomNav />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import VendorCard from '../components/VendorCard.vue'
import PromotionCard from '../components/PromotionCard.vue'
import { useVendorStore } from '../stores/vendorStore.js'

const router = useRouter()
const vendorStore = useVendorStore()
const searchText = ref('')

onMounted(async () => {
  await vendorStore.loadVendors()
  await vendorStore.loadPromotions()
})

const filteredVendors = computed(() => {
  return vendorStore.vendors.filter((vendor) => {
    return vendor.status === 'approved' &&
      vendor.name.toLowerCase().includes(searchText.value.toLowerCase())
  })
})

const activePromotions = computed(() => {
  return vendorStore.promotions.filter((promotion) => promotion.is_active)
})

function goToVendor(vendor) {
  router.push(`/vendors/${vendor.vendor_id}/menu`)
}
</script>
