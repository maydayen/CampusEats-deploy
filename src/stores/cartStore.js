import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: []
  }),

  getters: {
    subtotal(state) {
      return state.items.reduce((sum, item) => sum + item.price * item.quantity, 0)
    },

    totalItems(state) {
      return state.items.reduce((sum, item) => sum + item.quantity, 0)
    }
  },

  actions: {
    addItem(menuItem) {
      const existing = this.items.find((item) => item.menu_item_id === menuItem.menu_item_id)

      if (existing) {
        existing.quantity += 1
      } else {
        this.items.push({
          ...menuItem,
          quantity: 1
        })
      }
    },

    increaseQuantity(menuItemId) {
      const item = this.items.find((item) => item.menu_item_id === menuItemId)
      if (item) item.quantity += 1
    },

    decreaseQuantity(menuItemId) {
      const item = this.items.find((item) => item.menu_item_id === menuItemId)
      if (item && item.quantity > 1) item.quantity -= 1
    },

    removeItem(menuItemId) {
      this.items = this.items.filter((item) => item.menu_item_id !== menuItemId)
    },

    clearCart() {
      this.items = []
    }
  }
})
