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
        <div class="card rating-summary-card">
          <div class="space-between">
            <div>
              <h3>Rating Summary</h3>
              <p class="muted">Based on customer feedback.</p>
            </div>

            <div class="rating-score">
              ⭐ {{ averageRating ? averageRating.toFixed(1) : vendor.rating }}
            </div>
          </div>

          <p class="muted">
            {{ vendorReviews.length }} customer review(s)
          </p>
        </div>

        <div class="menu-search-box">
          <input
            v-model="searchText"
            class="input"
            placeholder="Search food, e.g. pasta, rice, coffee..."
          />
        </div>

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
          <p>Try another keyword or category.</p>

          <button
            v-if="searchText || selectedCategory !== 'All'"
            class="btn secondary"
            @click="clearMenuFilters"
          >
            Clear Filters
          </button>
        </div>

        <MenuItemCard
          v-for="item in filteredMenuItems"
          :key="item.menu_item_id"
          :item="item"
          @add="cartStore.addItem"
        />

        <div class="card">
          <h3>Customer Feedback</h3>

          <div v-if="vendorReviews.length === 0" class="empty-review">
            <p class="muted">No customer reviews yet.</p>
          </div>

          <div
            v-for="review in vendorReviews"
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
import { useReviewStore } from '../stores/reviewStore.js'

const route = useRoute()
const vendorStore = useVendorStore()
const cartStore = useCartStore()

const vendorId = Number(route.params.id)
const selectedCategory = ref('All')
const searchText = ref('')
const loading = ref(false)
const errorMessage = ref('')
const reviewStore = useReviewStore()

onMounted(() => {
  loadMenuData()
})

async function loadMenuData() {
  loading.value = true
  errorMessage.value = ''

  try {
    await vendorStore.loadVendors()
    await vendorStore.loadMenuItems()
    await reviewStore.loadReviews()
  } catch (error) {
    errorMessage.value = error.message || 'Something went wrong while loading menu data.'
  } finally {
    loading.value = false
  }
}

const vendor = computed(() => {
  return vendorStore.vendors.find((item) => Number(item.vendor_id) === vendorId)
})

const vendorMenuItems = computed(() => {
  return vendorStore.menuItems.filter((item) => Number(item.vendor_id) === vendorId)
})

const categories = computed(() => {
  const uniqueCategories = vendorMenuItems.value.map((item) => item.category)
  return ['All', ...new Set(uniqueCategories)]
})

const filteredMenuItems = computed(() => {
  const keyword = searchText.value.toLowerCase().trim()

  return vendorMenuItems.value.filter((item) => {
    const matchesCategory =
      selectedCategory.value === 'All' ||
      item.category === selectedCategory.value

    const name = item.name?.toLowerCase() || ''
    const description = item.description?.toLowerCase() || ''
    const category = item.category?.toLowerCase() || ''
    const tags = item.tags?.join(' ').toLowerCase() || ''

    const matchesSearch =
      !keyword ||
      name.includes(keyword) ||
      description.includes(keyword) ||
      category.includes(keyword) ||
      tags.includes(keyword)

    return matchesCategory && matchesSearch
  })
})

const vendorReviews = computed(() => {
  return reviewStore.getVendorReviews(vendorId)
})

const averageRating = computed(() => {
  return reviewStore.getAverageRating(vendorId)
})

function clearMenuFilters() {
  searchText.value = ''
  selectedCategory.value = 'All'
}
</script>
