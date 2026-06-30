import { defineStore } from 'pinia'
import { getMockData, createReviewApi } from '../services/mockApi.js'

export const useReviewStore = defineStore('review', {
  state: () => ({
    reviews: []
  }),

  getters: {
    allReviews(state) {
      return state.reviews
    }
  },

  actions: {
    async loadReviews() {
      this.reviews = await getMockData('reviews.json')
    },

    async addReview(reviewData) {
      const newReview = await createReviewApi(reviewData)

      this.reviews.unshift(newReview)

      return newReview
    },

    hasReviewedOrder(orderId) {
      return this.allReviews.some((review) => {
        return Number(review.order_id) === Number(orderId)
      })
    },

    getVendorReviews(vendorId) {
      return this.allReviews.filter((review) => {
        return Number(review.vendor_id) === Number(vendorId)
      })
    },

    getAverageRating(vendorId) {
      const vendorReviews = this.getVendorReviews(vendorId)

      if (vendorReviews.length === 0) {
        return 0
      }

      const total = vendorReviews.reduce((sum, review) => {
        return sum + Number(review.rating || 0)
      }, 0)

      return total / vendorReviews.length
    }
  }
})