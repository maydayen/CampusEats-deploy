import { defineStore } from 'pinia'
import { getMockData } from '../services/mockApi.js'

export const useOrderStore = defineStore('orders', {
  state: () => ({
    orders: [],
    latestOrder: JSON.parse(sessionStorage.getItem('latestOrder')) || null,
    sessionOrders: JSON.parse(sessionStorage.getItem('sessionOrders')) || []
  }),

  actions: {
    async loadOrders() {
      const mockOrders = await getMockData('orders.json')
      this.orders = [...this.sessionOrders, ...mockOrders]
    },

    createOrder(orderData) {
      const newOrder = {
        ...orderData,
        order_id: Date.now(),
        status: 'placed',
        created_at: new Date().toISOString()
      }

      this.sessionOrders.unshift(newOrder)
      this.orders.unshift(newOrder)
      this.latestOrder = newOrder

      sessionStorage.setItem('latestOrder', JSON.stringify(newOrder))
      sessionStorage.setItem('sessionOrders', JSON.stringify(this.sessionOrders))

      return newOrder
    },

    updateOrderStatus(orderId, status) {
      const order = this.orders.find((item) => item.order_id === orderId)

      if (order) {
        order.status = status
      }

      const sessionOrder = this.sessionOrders.find((item) => item.order_id === orderId)

      if (sessionOrder) {
        sessionOrder.status = status
        sessionStorage.setItem('sessionOrders', JSON.stringify(this.sessionOrders))
      }
    },

    cancelOrder(orderId) {
      this.updateOrderStatus(orderId, 'cancelled')

      if (this.latestOrder && Number(this.latestOrder.order_id) === Number(orderId)) {
        this.latestOrder.status = 'cancelled'
        sessionStorage.setItem('latestOrder', JSON.stringify(this.latestOrder))
      }
    }
  }
})