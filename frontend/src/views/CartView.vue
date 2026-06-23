<template>
  <main class="page bottom-space">
    <Navbar />

    <div class="page-header">
      <BackButton />
      <h1 class="page-title">Cart</h1>
    </div>

    <div v-if="cartStore.items.length === 0" class="empty-state">
      <span class="emoji">🛒</span>
      <h3>Your cart is empty</h3>
      <p>Add food from a vendor menu before checkout.</p>
      <button class="btn" @click="$router.push('/home')">
        Browse Vendors
      </button>
    </div>

    <template v-else>
      <CartItem
        v-for="item in cartStore.items"
        :key="item.menu_item_id"
        :item="item"
        @increase="cartStore.increaseQuantity"
        @decrease="cartStore.decreaseQuantity"
        @remove="cartStore.removeItem"
      />

      <div class="card">
        <h3>Order Summary</h3>

        <div class="space-between summary-row">
          <span>Subtotal</span>
          <span>RM {{ cartStore.subtotal.toFixed(2) }}</span>
        </div>

        <div class="space-between summary-row">
          <span>Service Fee</span>
          <span>RM {{ cartStore.serviceFee.toFixed(2) }}</span>
        </div>

        <div class="space-between summary-row">
          <span>Tax 6%</span>
          <span>RM {{ cartStore.taxAmount.toFixed(2) }}</span>
        </div>

        <hr />

        <div class="space-between">
          <strong>Total</strong>
          <strong>RM {{ cartStore.total.toFixed(2) }}</strong>
        </div>
      </div>

      <button class="btn" @click="$router.push('/checkout')">
        Proceed to Checkout
      </button>
    </template>

    <BottomNav />
  </main>
</template>

<script setup>
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import CartItem from '../components/CartItem.vue'
import { useCartStore } from '../stores/cartStore.js'
import BackButton from '../components/BackButton.vue'

const cartStore = useCartStore()
</script>