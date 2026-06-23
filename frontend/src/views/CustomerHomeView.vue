<template>
  <main class="page bottom-space">
    <Navbar />

    <h1 class="page-title">Customer Home</h1>
    <input v-model="searchText" class="input" placeholder="Search vendor, category or location..." />

    <section v-if="loading">
      <div class="loading-card" v-for="n in 3" :key="n">
        <div class="loading-line short"></div>
        <div class="loading-line long"></div>
        <div class="loading-line medium"></div>
      </div>
    </section>

    <section v-else-if="errorMessage" class="error-state">
      <span class="emoji">⚠️</span>
      <h3>Unable to load vendors</h3>
      <p>{{ errorMessage }}</p>
      <button class="btn" @click="loadHomeData">Try Again</button>
    </section>

    <template v-else>
      <h2>Promotions</h2>

      <div v-if="activePromotions.length === 0" class="card">
        <p class="muted">No active promotions available now.</p>
      </div>

      <PromotionCard
        v-for="promotion in activePromotions"
        :key="promotion.promotion_id"
        :promotion="promotion"
      />

      <h2>Nearby Dining</h2>

      <div v-if="filteredVendors.length === 0" class="empty-state">
        <span class="emoji">🏪</span>
        <h3>No vendors found</h3>
        <p>Try another keyword or category.</p>
      </div>

      <VendorCard
        v-for="vendor in filteredVendors"
        :key="vendor.vendor_id"
        :vendor="vendor"
        @select="goToVendor"
      />
    </template>

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
import { useVendorStore } from '../stores/vendorStore'

const router = useRouter()
const vendorStore = useVendorStore()

const searchText = ref('')
const loading = ref(false)
const errorMessage = ref('')

onMounted(() => {
  loadHomeData()
})

async function loadHomeData() {
  loading.value = true
  errorMessage.value = ''

  try {
    await vendorStore.loadVendors()
    await vendorStore.loadPromotions()
  } catch (error) {
    errorMessage.value = error.message || 'Something went wrong while loading home data.'
  } finally {
    loading.value = false
  }
}

const filteredVendors = computed(() => {
  const keyword = searchText.value.toLowerCase()

  return vendorStore.vendors.filter((vendor) => {
    const name = vendor.name.toLowerCase()
    const location = vendor.location.toLowerCase()
    const category = vendor.category?.toLowerCase() || ''

    return vendor.status === 'approved' &&
      (
        name.includes(keyword) ||
        location.includes(keyword) ||
        category.includes(keyword)
      )
  })
})

const activePromotions = computed(() => {
  return vendorStore.promotions.filter((promotion) => promotion.is_active)
})

function goToVendor(vendor) {
  router.push(`/vendors/${vendor.vendor_id}/menu`)
}
</script>