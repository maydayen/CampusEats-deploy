<template>
  <main class="page">
    <h1 class="page-title">Checkout</h1>

    <h2>Pickup Slot</h2>
    <select v-model="pickupSlot" class="input">
      <option>ASAP - 15 mins</option>
      <option>12:30 PM</option>
      <option>12:45 PM</option>
      <option>01:00 PM</option>
    </select>

    <textarea class="input" placeholder="Add note"></textarea>

    <div class="card">
      <div class="space-between">
        <span>Subtotal</span>
        <span>RM {{ subtotal.toFixed(2) }}</span>
      </div>
      <div class="space-between">
        <span>Service Fee</span>
        <span>RM {{ serviceFee.toFixed(2) }}</span>
      </div>
      <hr />
      <div class="space-between">
        <strong>Total</strong>
        <strong>RM {{ total.toFixed(2) }}</strong>
      </div>
    </div>

    <button class="btn" @click="placeOrder">Place Order</button>
  </main>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore'
import { useCartStore } from '../stores/cartStore'
import { useOrderStore } from '../stores/orderStore'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()
const orderStore = useOrderStore()

const pickupSlot = ref('ASAP - 15 mins')
const serviceFee = ref(0.5)

const subtotal = computed(() => cartStore.subtotal)
const total = computed(() => subtotal.value + serviceFee.value)

function placeOrder() {
  orderStore.createOrder({
    user_id: authStore.currentUser.user_id,
    vendor_id: cartStore.items[0]?.vendor_id || 1,
    subtotal: subtotal.value,
    service_fee: serviceFee.value,
    tax_amount: 0,
    total: total.value,
    pickup_at: pickupSlot.value
  })

  cartStore.clearCart()
  router.push('/order-confirmation')
}
</script>
