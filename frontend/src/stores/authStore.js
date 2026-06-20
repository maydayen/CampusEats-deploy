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

    async register(name, email, password, role) {
      const users = await getMockData('users.json')
      const existing = users.find((item) => item.email === email)

      if (existing) {
        throw new Error('An account with this email already exists')
      }

      const newUser = {
        user_id: Date.now(),
        name,
        email,
        password,
        role,
        created_at: new Date().toISOString()
      }

      this.currentUser = newUser
      localStorage.setItem('currentUser', JSON.stringify(newUser))

      return newUser
    },

    logout() {
      this.currentUser = null
      localStorage.removeItem('currentUser')
    }
  }
})
