import { defineStore } from 'pinia'
import { getMockData } from '../services/mockApi'

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    notifications: []
  }),

  actions: {
    async loadNotifications() {
      this.notifications = await getMockData('notifications.json')
    },

    markAsRead(notificationId) {
      const notification = this.notifications.find((item) => item.notification_id === notificationId)
      if (notification) notification.is_read = true
    }
  }
})
