import { defineStore } from 'pinia'
import { getMockData } from '../services/mockApi'

export const useVendorStore = defineStore('vendors', {
  state: () => ({
    vendors: [],
    menuItems: [],
    promotions: []
  }),

  actions: {
    async loadVendors() {
      this.vendors = await getMockData('vendors.json')
    },

    async loadMenuItems() {
      this.menuItems = await getMockData('menuItems.json')
    },

    async loadPromotions() {
      this.promotions = await getMockData('promotions.json')
    },

    approveVendor(vendorId) {
      const vendor = this.vendors.find((item) => item.vendor_id === vendorId)
      if (vendor) vendor.status = 'approved'
    },

    rejectVendor(vendorId) {
      const vendor = this.vendors.find((item) => item.vendor_id === vendorId)
      if (vendor) vendor.status = 'rejected'
    }
  }
})
