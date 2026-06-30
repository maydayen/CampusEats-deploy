import { defineStore } from 'pinia'
import { getMockData, createOrderApi, updateOrderStatusApi } from '../services/mockApi.js'

export const useOrderStore = defineStore('orders', {
  state: () => ({
    orders: [],
    latestOrder: JSON.parse(sessionStorage.getItem('latestOrder')) || null
  }),

  actions: {
    async loadOrders() {
      this.orders = await getMockData('orders.json')
    },

    async createOrder(orderData) {
      const newOrder = await createOrderApi(orderData)

      this.latestOrder = newOrder
      this.orders.unshift(newOrder)

      sessionStorage.setItem('latestOrder', JSON.stringify(newOrder))

      return newOrder
    },

    async updateOrderStatus(orderId, status) {
      const result = await updateOrderStatusApi(orderId, status)

      const order = this.orders.find((item) => {
        return Number(item.order_id) === Number(orderId)
      })

      if (order) {
        order.status = status
      }

      if (this.latestOrder && Number(this.latestOrder.order_id) === Number(orderId)) {
        this.latestOrder.status = status
        sessionStorage.setItem('latestOrder', JSON.stringify(this.latestOrder))
      }

      return result
    },

    async cancelOrder(orderId) {
      await this.updateOrderStatus(orderId, 'cancelled')
    }
  }
})