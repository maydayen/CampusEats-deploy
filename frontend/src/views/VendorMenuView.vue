<template>
  <main class="page bottom-space">
    <Navbar />

    <div class="page-header">
      <BackButton />
      <div>
        <h1 class="page-title">{{ vendor?.name || 'Vendor Menu' }}</h1>
        <p v-if="vendor" class="muted vendor-subtitle">
          {{ vendor.location }} · ⭐ {{ vendor.rating }} · {{ vendor.prep_time }}
        </p>
      </div>
    </div>

    <section v-if="loading">
      <div class="loading-card" v-for="n in 4" :key="n">
        <div class="loading-line short"></div>
        <div class="loading-line long"></div>
        <div class="loading-line medium"></div>
      </div>
    </section>

    <section v-else-if="errorMessage" class="error-state">
      <span class="emoji">⚠️</span>
      <h3>Unable to load menu</h3>
      <p>{{ errorMessage }}</p>
      <button class="btn" @click="loadMenuData">Try Again</button>
    </section>

    <template v-else>
      <div v-if="!vendor" class="empty-state">
        <span class="emoji">🏪</span>
        <h3>Vendor not found</h3>
        <p>Please return to the home page and choose another vendor.</p>
        <button class="btn" @click="$router.push('/home')">Back to Home</button>
      </div>

      <template v-else>
        <div class="category-scroll">
          <button
            v-for="category in categories"
            :key="category"
            class="category-chip"
            :class="{ active: selectedCategory === category }"
            @click="selectedCategory = category"
          >
            {{ category }}
          </button>
        </div>

        <p class="muted menu-count">
          {{ filteredMenuItems.length }} item(s) available
        </p>

        <div v-if="filteredMenuItems.length === 0" class="empty-state">
          <span class="emoji">🍽️</span>
          <h3>No items found</h3>
          <p>Please choose another category.</p>
        </div>

        <MenuItemCard
          v-for="item in filteredMenuItems"
          :key="item.menu_item_id"
          :item="item"
          @add="cartStore.addItem"
        />

        <button
          v-if="cartStore.totalItems > 0"
          class="floating-cart-btn"
          @click="$router.push('/cart')"
        >
          View Cart · {{ cartStore.totalItems }} item(s) · RM {{ cartStore.total.toFixed(2) }}
        </button>
      </template>
    </template>

    <BottomNav />
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import BackButton from '../components/BackButton.vue'
import MenuItemCard from '../components/MenuItemCard.vue'
import { useVendorStore } from '../stores/vendorStore'
import { useCartStore } from '../stores/cartStore'

const route = useRoute()
const vendorStore = useVendorStore()
const cartStore = useCartStore()

const vendorId = Number(route.params.id)
const selectedCategory = ref('All')
const loading = ref(false)
const errorMessage = ref('')

onMounted(() => {
  loadMenuData()
})

async function loadMenuData() {
  loading.value = true
  errorMessage.value = ''

  try {
    await vendorStore.loadVendors()
    await vendorStore.loadMenuItems()
  } catch (error) {
    errorMessage.value = error.message || 'Something went wrong while loading menu data.'
  } finally {
    loading.value = false
  }
}

const vendor = computed(() => {
  return vendorStore.vendors.find((item) => item.vendor_id === vendorId)
})

const vendorMenuItems = computed(() => {
  return vendorStore.menuItems.filter((item) => item.vendor_id === vendorId)
})

const categories = computed(() => {
  const uniqueCategories = vendorMenuItems.value.map((item) => item.category)
  return ['All', ...new Set(uniqueCategories)]
})

const filteredMenuItems = computed(() => {
  if (selectedCategory.value === 'All') {
    return vendorMenuItems.value
  }

  return vendorMenuItems.value.filter((item) => {
    return item.category === selectedCategory.value
  })
})
</script>

<style scoped>
.vendor-menu-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: 22px;
  margin-bottom: 26px;
}

.vendor-info {
  min-width: 0;
}

.vendor-name {
  margin: 0 0 6px;
  font-size: 26px;
  line-height: 1.15;
  color: #111827;
}

.vendor-subtitle {
  margin: 0;
  color: var(--muted);
  font-size: 16px;
  line-height: 1.4;
}

@media (max-width: 420px) {
  .vendor-menu-header {
    gap: 14px;
    margin-top: 20px;
    margin-bottom: 24px;
  }

  .vendor-name {
    font-size: 24px;
  }
}
</style>