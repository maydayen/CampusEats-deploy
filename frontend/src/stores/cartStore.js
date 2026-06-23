import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
    vendorId: null
  }),

  getters: {
    subtotal(state) {
      return state.items.reduce((sum, item) => {
        return sum + Number(item.price) * item.quantity
      }, 0)
    },

    serviceFee() {
      return this.items.length > 0 ? 0.5 : 0
    },

    taxAmount() {
      return this.subtotal * 0.06
    },

    total() {
      return this.subtotal + this.serviceFee + this.taxAmount
    },

    totalItems(state) {
      return state.items.reduce((sum, item) => sum + item.quantity, 0)
    }
  },

  actions: {
    addItem(menuItem) {
      if (this.vendorId && this.vendorId !== menuItem.vendor_id) {
        const confirmed = window.confirm(
          'Your cart has items from another vendor. Clear cart and add this item?'
        )

        if (!confirmed) return

        this.clearCart()
      }

      this.vendorId = menuItem.vendor_id

      const existing = this.items.find((item) => {
        return item.menu_item_id === menuItem.menu_item_id
      })

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

      if (item) {
        item.quantity += 1
      }
    },

    decreaseQuantity(menuItemId) {
      const item = this.items.find((item) => item.menu_item_id === menuItemId)

      if (!item) return

      if (item.quantity > 1) {
        item.quantity -= 1
      } else {
        this.removeItem(menuItemId)
      }
    },

    removeItem(menuItemId) {
      this.items = this.items.filter((item) => item.menu_item_id !== menuItemId)

      if (this.items.length === 0) {
        this.vendorId = null
      }
    },

    clearCart() {
      this.items = []
      this.vendorId = null
    }
  }
})