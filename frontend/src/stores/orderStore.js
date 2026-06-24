import { defineStore } from 'pinia'
import { getMockData } from '../services/mockApi.js'

export const useOrderStore = defineStore('orders', {
  state: () => ({
    orders: [],
    latestOrder: JSON.parse(sessionStorage.getItem('latestOrder')) || null,
    sessionOrders: JSON.parse(sessionStorage.getItem('sessionOrders')) || [],
    updatedStatuses: JSON.parse(sessionStorage.getItem('updatedStatuses')) || {}
  }),

  actions: {
    async loadOrders() {
      const mockOrders = await getMockData('orders.json')

      const mergedOrders = [...this.sessionOrders, ...mockOrders]

      this.orders = mergedOrders.map((order) => {
        const savedStatus = this.updatedStatuses[order.order_id]

        if (savedStatus) {
          return {
            ...order,
            status: savedStatus
          }
        }

        return order
      })
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
      const order = this.orders.find((item) => {
        return Number(item.order_id) === Number(orderId)
      })

      if (order) {
        order.status = status
      }

      const sessionOrder = this.sessionOrders.find((item) => {
        return Number(item.order_id) === Number(orderId)
      })
      
      if (sessionOrder) {
        sessionOrder.status = status
        sessionStorage.setItem('sessionOrders', JSON.stringify(this.sessionOrders))
      }

      this.updatedStatuses[orderId] = status
      sessionStorage.setItem('updatedStatuses', JSON.stringify(this.updatedStatuses))

      if (this.latestOrder && Number(this.latestOrder.order_id) === Number(orderId)) {
        this.latestOrder.status = status
        sessionStorage.setItem('latestOrder', JSON.stringify(this.latestOrder))
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