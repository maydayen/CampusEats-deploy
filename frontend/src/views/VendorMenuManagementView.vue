<template>
  <main class="page">
    <Navbar />

    <div class="page-header">
      <BackButton />
      <div>
        <h1 class="page-title">Menu Management</h1>
        <p class="muted">Manage menu availability and item pricing.</p>
      </div>
    </div>

    <section v-if="loading">
      <div class="loading-card" v-for="n in 3" :key="n">
        <div class="loading-line short"></div>
        <div class="loading-line long"></div>
        <div class="loading-line medium"></div>
      </div>
    </section>

    <section v-else>
      <div class="dashboard-grid">
        <DashboardCard label="Total Items" :value="vendorItems.length" />
        <DashboardCard label="In Stock" :value="inStockItems.length" />
        <DashboardCard label="Out of Stock" :value="outOfStockItems.length" />
        <DashboardCard label="Categories" :value="categories.length - 1" />
      </div>

      <div class="card">
        <h3 style="margin-top: 0;">Search and Filter</h3>

        <input
          v-model="searchText"
          class="input"
          placeholder="Search menu item..."
        />

        <select v-model="selectedCategory" class="input">
          <option
            v-for="category in categories"
            :key="category"
            :value="category"
          >
            {{ category }}
          </option>
        </select>

        <select v-model="stockFilter" class="input">
          <option value="all">All Stock Status</option>
          <option value="in_stock">In Stock</option>
          <option value="out_of_stock">Out of Stock</option>
        </select>

        <button
          v-if="searchText || selectedCategory !== 'All' || stockFilter !== 'all'"
          class="btn secondary"
          @click="clearFilters"
        >
          Clear Filters
        </button>
      </div>

      <div v-if="filteredItems.length === 0" class="empty-state">
        <span class="emoji">🍽️</span>
        <h3>No menu items found</h3>
        <p>Try another keyword or filter.</p>
      </div>

      <div
        v-for="item in filteredItems"
        :key="item.menu_item_id"
        class="card vendor-menu-card"
      >
        <div class="vendor-menu-row">
          <img
            v-if="item.image_url"
            :src="item.image_url"
            :alt="item.name"
            class="vendor-menu-image"
          />

          <div v-else class="vendor-menu-placeholder">
            🍴
          </div>

          <div class="vendor-menu-info">
            <div class="space-between">
              <div>
                <h3>{{ item.name }}</h3>
                <p class="muted">{{ item.category }}</p>
              </div>

              <span
                class="stock-badge"
                :class="item.in_stock ? 'stock-in' : 'stock-out'"
              >
                {{ item.in_stock ? 'In Stock' : 'Out of Stock' }}
              </span>
            </div>

            <p class="menu-description">
              {{ item.description }}
            </p>

            <div class="vendor-price-row">
              <div>
                <span class="muted">Price</span>
                <strong>RM {{ Number(item.price).toFixed(2) }}</strong>
              </div>

              <div class="row vendor-menu-actions">
                <button
                  class="btn secondary small-btn"
                  @click="openEditPrice(item)"
                >
                  Edit Price
                </button>

                <button
                  class="btn small-btn"
                  @click="item.in_stock = !item.in_stock"
                >
                  {{ item.in_stock ? 'Mark Out' : 'Mark In' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div v-if="editingItem" class="modal-overlay">
      <div class="modal-card">
        <h3>Edit Price</h3>
        <p class="muted">{{ editingItem.name }}</p>

        <label>New Price (RM)</label>
        <input
          v-model.number="editedPrice"
          class="input"
          type="number"
          min="0"
          step="0.10"
        />

        <div class="row modal-actions">
          <button class="btn secondary" @click="closeEditPrice">
            Cancel
          </button>

          <button class="btn" @click="savePrice">
            Save
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import Navbar from '../components/Navbar.vue'
import BackButton from '../components/BackButton.vue'
import DashboardCard from '../components/DashboardCard.vue'
import { useVendorStore } from '../stores/vendorStore'

const vendorStore = useVendorStore()

const loading = ref(false)
const searchText = ref('')
const selectedCategory = ref('All')
const stockFilter = ref('all')
const editingItem = ref(null)
const editedPrice = ref(0)

onMounted(async () => {
  loading.value = true
  await vendorStore.loadMenuItems()
  loading.value = false
})

const vendorItems = computed(() => {
  return vendorStore.menuItems.filter((item) => item.vendor_id === 1)
})

const inStockItems = computed(() => {
  return vendorItems.value.filter((item) => item.in_stock)
})

const outOfStockItems = computed(() => {
  return vendorItems.value.filter((item) => !item.in_stock)
})

const categories = computed(() => {
  const categoryList = vendorItems.value.map((item) => item.category)
  return ['All', ...new Set(categoryList)]
})

const filteredItems = computed(() => {
  const keyword = searchText.value.toLowerCase().trim()

  return vendorItems.value.filter((item) => {
    const matchesSearch =
      !keyword ||
      item.name.toLowerCase().includes(keyword) ||
      item.description.toLowerCase().includes(keyword) ||
      item.category.toLowerCase().includes(keyword)

    const matchesCategory =
      selectedCategory.value === 'All' ||
      item.category === selectedCategory.value

    const matchesStock =
      stockFilter.value === 'all' ||
      (stockFilter.value === 'in_stock' && item.in_stock) ||
      (stockFilter.value === 'out_of_stock' && !item.in_stock)

    return matchesSearch && matchesCategory && matchesStock
  })
})

function clearFilters() {
  searchText.value = ''
  selectedCategory.value = 'All'
  stockFilter.value = 'all'
}

function openEditPrice(item) {
  editingItem.value = item
  editedPrice.value = Number(item.price)
}

function closeEditPrice() {
  editingItem.value = null
  editedPrice.value = 0
}

function savePrice() {
  if (!editingItem.value) return

  if (editedPrice.value <= 0) {
    alert('Please enter a valid price.')
    return
  }

  editingItem.value.price = Number(editedPrice.value)
  closeEditPrice()
}
</script>