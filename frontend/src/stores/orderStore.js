import { defineStore } from 'pinia'
import { getMockData } from '../services/mockApi'

export const useOrderStore = defineStore('orders', {
  state: () => ({
    orders: [],
    latestOrder: null
  }),

  actions: {
    async loadOrders() {
      this.orders = await getMockData('orders.json')
    },

    createOrder(orderData) {
      const newOrder = {
        ...orderData,
        order_id: Date.now(),
        status: 'placed',
        created_at: new Date().toISOString()
      }

      this.orders.unshift(newOrder)
      this.latestOrder = newOrder

      return newOrder
    },

    updateOrderStatus(orderId, status) {
      const order = this.orders.find((item) => item.order_id === orderId)
      if (order) order.status = status
    }
  }
})
