import { defineStore } from 'pinia'
import { getMockData, createOrderApi, updateOrderStatusApi } from '../services/mockApi.js'

export const useOrderStore = defineStore('orders', {
  state: () => ({
    orders: [],
    latestOrder: JSON.parse(sessionStorage.getItem('latestOrder')) || null,
    sessionOrders: JSON.parse(sessionStorage.getItem('sessionOrders')) || [],
    showStatusModal: false,
    statusModalTitle: '',
    statusModalMessage: '',
    showCancelModal: false,
    cancelOrderId: null
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
      try {
        const updatedOrder = await updateOrderStatusApi(orderId, status)

        const order = this.orders.find((item) => {
          return Number(item.order_id) === Number(orderId)
        })

        if (order) {
          order.status = updatedOrder.status || status
        }

        if (this.latestOrder && Number(this.latestOrder.order_id) === Number(orderId)) {
          this.latestOrder.status = updatedOrder.status || status
          sessionStorage.setItem('latestOrder', JSON.stringify(this.latestOrder))
        }

        this.showStatusModal = true
        this.statusModalTitle = 'Order Status Updated'
        this.statusModalMessage = `Order #${orderId} has been updated to ${status}.`

        return updatedOrder
      } catch (error) {
        this.showStatusModal = true
        this.statusModalTitle = 'Update Failed'
        this.statusModalMessage = error.message || 'Unable to update order status.'

        throw error
      }
    },

    async cancelOrder(orderId) {
      await this.updateOrderStatus(orderId, 'cancelled')
    },

    closeStatusModal() {
      this.showStatusModal = false
      this.statusModalTitle = ''
      this.statusModalMessage = ''
    },

    openCancelModal(orderId) {
      this.cancelOrderId = orderId
      this.showCancelModal = true
    },

    closeCancelModal() {
      this.cancelOrderId = null
      this.showCancelModal = false
    },

    async confirmCancelOrder() {
      if (!this.cancelOrderId) return

      const orderId = this.cancelOrderId

      try {
        await this.updateOrderStatus(orderId, 'cancelled')

        this.showCancelModal = false
        this.cancelOrderId = null

        this.showStatusModal = true
        this.statusModalTitle = 'Order Cancelled'
        this.statusModalMessage = `Order #${orderId} has been cancelled successfully.`
      } catch (error) {
        this.showCancelModal = false
        this.cancelOrderId = null

        this.showStatusModal = true
        this.statusModalTitle = 'Cancel Failed'
        this.statusModalMessage = error.message || 'Unable to cancel order.'
      }
    }
  }
})