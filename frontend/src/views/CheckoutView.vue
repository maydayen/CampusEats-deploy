<template>
  <main class="page">
    <Navbar />

    <div class="page-header">
      <BackButton />
      <h1 class="page-title">Checkout</h1>
    </div>

    <div v-if="cartStore.items.length === 0" class="empty-state">
      <span class="emoji">🛒</span>
      <h3>No items to checkout</h3>
      <p>Please add food items before placing an order.</p>
      <button class="btn" @click="$router.push('/home')">
        Browse Vendors
      </button>
    </div>

    <template v-else>
      <div class="card">
        <h3>Pickup Slot</h3>
        <p class="muted">Choose a 15-minute pickup time.</p>

        <select v-model="pickupSlot" class="input">
          <option disabled value="">Select pickup slot</option>
          <option>ASAP - 15 mins</option>
          <option>12:30 PM</option>
          <option>12:45 PM</option>
          <option>01:00 PM</option>
          <option>01:15 PM</option>
        </select>

        <textarea
          v-model="note"
          class="input"
          placeholder="Add note for vendor, e.g. less spicy"
        ></textarea>
      </div>

      <div class="card">
        <h3>Payment Method</h3>
        <p class="muted">This is a mock payment selection for PR2.</p>

        <div class="payment-options">
          <label
            v-for="method in paymentMethods"
            :key="method.value"
            class="payment-option"
            :class="{ active: paymentMethod === method.value }"
          >
            <input
              v-model="paymentMethod"
              type="radio"
              :value="method.value"
            />

            <span class="payment-icon">{{ method.icon }}</span>

            <span>
              <strong>{{ method.label }}</strong>
              <small>{{ method.description }}</small>
            </span>
          </label>
        </div>
      </div>

      <div class="card">
        <h3>Items</h3>

        <div
          v-for="item in cartStore.items"
          :key="item.menu_item_id"
          class="space-between summary-row"
        >
          <span>{{ item.quantity }} × {{ item.name }}</span>
          <span>RM {{ (item.price * item.quantity).toFixed(2) }}</span>
        </div>
      </div>

      <div class="card">
        <h3>Payment Summary</h3>

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

        <div class="space-between summary-row">
          <span>Payment Method</span>
          <strong>{{ selectedPaymentLabel }}</strong>
        </div>

        <hr />

        <div class="space-between">
          <strong>Total</strong>
          <strong>RM {{ cartStore.total.toFixed(2) }}</strong>
        </div>
      </div>

      <button
        class="btn"
        :disabled="!pickupSlot || !paymentMethod || placingOrder"
        @click="placeOrder"
      >
        {{ placingOrder ? 'Placing Order...' : 'Place Order' }}
      </button>
    </template>
  </main>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import BackButton from '../components/BackButton.vue'
import { useAuthStore } from '../stores/authStore.js'
import { useCartStore } from '../stores/cartStore.js'
import { useOrderStore } from '../stores/orderStore.js'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()
const orderStore = useOrderStore()

const pickupSlot = ref('ASAP - 15 mins')
const note = ref('')
const paymentMethod = ref('cash')
const placingOrder = ref(false)

const paymentMethods = [
  {
    value: 'cash',
    label: 'Cash at Pickup',
    description: 'Pay directly to vendor when collecting food.',
    icon: '💵'
  },
  {
    value: 'ewallet',
    label: 'E-Wallet',
    description: 'Mock Touch n Go / GrabPay style payment.',
    icon: '📱'
  },
  {
    value: 'card',
    label: 'Debit / Credit Card',
    description: 'Mock card payment for PR2 demo.',
    icon: '💳'
  }
]

const selectedPaymentLabel = computed(() => {
  const selected = paymentMethods.find((method) => method.value === paymentMethod.value)
  return selected ? selected.label : '-'
})

function placeOrder() {
  if (cartStore.items.length === 0 || !pickupSlot.value || !paymentMethod.value) return

  placingOrder.value = true

  setTimeout(() => {
    orderStore.createOrder({
      user_id: authStore.currentUser.user_id,
      vendor_id: cartStore.vendorId || cartStore.items[0]?.vendor_id,
      subtotal: cartStore.subtotal,
      service_fee: cartStore.serviceFee,
      tax_amount: cartStore.taxAmount,
      total: cartStore.total,
      pickup_at: pickupSlot.value,
      payment_method: paymentMethod.value,
      payment_label: selectedPaymentLabel.value,
      payment_status: paymentMethod.value === 'cash' ? 'pending' : 'paid_mock',
      note: note.value,
      items: cartStore.items.map((item) => ({
        menu_item_id: item.menu_item_id,
        name: item.name,
        quantity: item.quantity,
        unit_price: item.price
      }))
    })

    cartStore.clearCart()
    placingOrder.value = false
    router.push('/order-confirmation')
  }, 600)
}
</script>