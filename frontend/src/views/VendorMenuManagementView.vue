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
        <div class="space-between">
          <div>
            <h3 style="margin: 0;">Menu Item Tools</h3>
            <p class="muted" style="margin: 4px 0 0;">
              Add new menu items for the vendor menu.
            </p>
          </div>

          <button class="btn small-btn" @click="openAddItemModal">
            Add New Item
          </button>
        </div>
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
    
    <div v-if="showAddModal" class="modal-overlay">
      <div class="modal-card">
        <h3>Add New Menu Item</h3>
        <p class="muted">Create a mock menu item for PR2 demonstration.</p>

        <label>Item Name</label>
        <input
          v-model="newItem.name"
          class="input"
          placeholder="Example: Chicken Rice"
        />

        <label>Description</label>
        <textarea
          v-model="newItem.description"
          class="input review-textarea"
          placeholder="Enter menu item description..."
        ></textarea>

        <label>Category</label>
        <input
          v-model="newItem.category"
          class="input"
          placeholder="Example: Rice"
        />

        <label>Price (RM)</label>
        <input
          v-model.number="newItem.price"
          class="input"
          type="number"
          min="0"
          step="0.10"
        />

        <label>Image URL</label>
        <input
          v-model="newItem.image_url"
          class="input"
          placeholder="/images/menu/example.jpg"
        />

        <div class="row modal-actions">
          <button class="btn secondary" @click="closeAddItemModal">
            Cancel
          </button>

          <button class="btn" @click="saveNewItem">
            Save Item
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
import { useAuthStore } from '../stores/authStore.js'

const vendorStore = useVendorStore()
const authStore = useAuthStore()

const loading = ref(false)
const searchText = ref('')
const selectedCategory = ref('All')
const stockFilter = ref('all')
const editingItem = ref(null)
const editedPrice = ref(0)

const showAddModal = ref(false)

const newItem = ref({
  name: '',
  description: '',
  category: '',
  price: 0,
  image_url: ''
})

const vendorId = computed(() => {
  return Number(authStore.currentUser?.vendor_id)
})

onMounted(async () => {
  loading.value = true
  await vendorStore.loadMenuItems()
  loading.value = false
})

const vendorItems = computed(() => {
  return vendorStore.menuItems.filter((item) => {
    return Number(item.vendor_id) === vendorId.value
  })
})

const inStockItems = computed(() => {
  return vendorItems.value.filter((item) => Number(item.in_stock) === 1 || item.in_stock === true)
})

const outOfStockItems = computed(() => {
  return vendorItems.value.filter((item) => Number(item.in_stock) === 0 || item.in_stock === false)
})

const categories = computed(() => {
  const categoryList = vendorItems.value.map((item) => item.category)
  return ['All', ...new Set(categoryList)]
})

const filteredItems = computed(() => {
  const keyword = searchText.value.toLowerCase().trim()

  return vendorItems.value.filter((item) => {
    const name = item.name || ''
    const description = item.description || ''
    const category = item.category || ''

    const matchesSearch =
      !keyword ||
      name.toLowerCase().includes(keyword) ||
      description.toLowerCase().includes(keyword) ||
      category.toLowerCase().includes(keyword)

    const matchesCategory =
      selectedCategory.value === 'All' ||
      category === selectedCategory.value

    const isInStock = Number(item.in_stock) === 1 || item.in_stock === true

    const matchesStock =
      stockFilter.value === 'all' ||
      (stockFilter.value === 'in_stock' && isInStock) ||
      (stockFilter.value === 'out_of_stock' && !isInStock)

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

function openAddItemModal() {
  showAddModal.value = true
}

function closeAddItemModal() {
  showAddModal.value = false

  newItem.value = {
    name: '',
    description: '',
    category: '',
    price: 0,
    image_url: ''
  }
}

function saveNewItem() {
  if (!newItem.value.name.trim()) {
    alert('Please enter item name.')
    return
  }

  if (!newItem.value.category.trim()) {
    alert('Please enter item category.')
    return
  }

  if (Number(newItem.value.price) <= 0) {
    alert('Please enter a valid price.')
    return
  }

  const item = {
    menu_item_id: Date.now(),
    vendor_id: vendorId.value,
    name: newItem.value.name.trim(),
    description: newItem.value.description.trim() || 'No description provided.',
    category: newItem.value.category.trim(),
    price: Number(newItem.value.price),
    image_url: newItem.value.image_url.trim(),
    in_stock: true,
    tags: []
  }

  vendorStore.menuItems.unshift(item)

  closeAddItemModal()
}
</script>