import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore.js'

import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import CustomerHomeView from '../views/CustomerHomeView.vue'
import VendorMenuView from '../views/VendorMenuView.vue'
import CartView from '../views/CartView.vue'
import CheckoutView from '../views/CheckoutView.vue'
import OrderConfirmationView from '../views/OrderConfirmationView.vue'
import OrderHistoryView from '../views/OrderHistoryView.vue'
import VendorDashboardView from '../views/VendorDashboardView.vue'
import VendorMenuManagementView from '../views/VendorMenuManagementView.vue'
import AdminDashboardView from '../views/AdminDashboardView.vue'
import NotificationView from '../views/NotificationView.vue'
import ProfileView from '../views/ProfileView.vue'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },

  { path: '/home', name: 'customer-home', component: CustomerHomeView, meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/vendors/:id/menu', name: 'vendor-menu', component: VendorMenuView, meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/cart', name: 'cart', component: CartView, meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/checkout', name: 'checkout', component: CheckoutView, meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/order-confirmation', name: 'order-confirmation', component: OrderConfirmationView, meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/orders', name: 'orders', component: OrderHistoryView, meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/profile', name: 'profile', component: ProfileView, meta: { requiresAuth: true, roles: ['customer'] } },

  { path: '/vendor/dashboard', name: 'vendor-dashboard', component: VendorDashboardView, meta: { requiresAuth: true, roles: ['vendor'] } },
  { path: '/vendor/menu-management', name: 'vendor-menu-management', component: VendorMenuManagementView, meta: { requiresAuth: true, roles: ['vendor'] } },

  { path: '/admin/dashboard', name: 'admin-dashboard', component: AdminDashboardView, meta: { requiresAuth: true, roles: ['admin'] } },

  { path: '/notifications', name: 'notifications', component: NotificationView, meta: { requiresAuth: true } }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.currentUser) {
    return '/login'
  }

  if (to.meta.roles && !to.meta.roles.includes(authStore.currentUser?.role)) {
    return '/login'
  }

  return true
})

export default router
