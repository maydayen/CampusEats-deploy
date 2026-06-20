<template>
  <main class="page bottom-space">
    <Navbar />

    <h1 class="page-title">Cart</h1>

    <p v-if="cartStore.items.length === 0">Your cart is empty.</p>

    <CartItem
      v-for="item in cartStore.items"
      :key="item.menu_item_id"
      :item="item"
      @increase="cartStore.increaseQuantity"
      @decrease="cartStore.decreaseQuantity"
      @remove="cartStore.removeItem"
    />

    <div class="card">
      <div class="space-between">
        <strong>Subtotal</strong>
        <strong>RM {{ cartStore.subtotal.toFixed(2) }}</strong>
      </div>
    </div>

    <button class="btn" :disabled="cartStore.items.length === 0" @click="$router.push('/checkout')">
      Checkout
    </button>

    <BottomNav />
  </main>
</template>

<script setup>
import Navbar from '../components/Navbar.vue'
import BottomNav from '../components/BottomNav.vue'
import CartItem from '../components/CartItem.vue'
import { useCartStore } from '../stores/cartStore.js'

const cartStore = useCartStore()
</script>
