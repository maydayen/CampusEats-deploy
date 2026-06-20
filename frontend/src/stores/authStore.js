import { defineStore } from 'pinia'
import { getMockData } from '../services/mockApi'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    currentUser: JSON.parse(localStorage.getItem('currentUser')) || null
  }),

  actions: {
    async login(email, password) {
      const users = await getMockData('users.json')
      const user = users.find((item) => item.email === email && item.password === password)

      if (!user) {
        throw new Error('Invalid email or password')
      }

      this.currentUser = user
      localStorage.setItem('currentUser', JSON.stringify(user))

      return user
    },

    logout() {
      this.currentUser = null
      localStorage.removeItem('currentUser')
    }
  }
})
