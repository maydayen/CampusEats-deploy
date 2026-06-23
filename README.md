# CampusEats Frontend Framework

This is the basic PR2 frontend framework for CampusEats.

## Tech Stack

- Vue 3
- Vue Router
- Pinia
- Vite
- Mock JSON data

## Project Setup

To run the frontend:

```bash
cd frontend
npm install
npm run dev
```

## Folder Structure

```text
CampusEats/
├── README.md
├── .gitignore
└── frontend/
    ├── index.html
    ├── package.json
    ├── package-lock.json
    ├── vite.config.js
    ├── public/
    │   ├── data/
    │   │   ├── users.json
    │   │   ├── vendors.json
    │   │   ├── menuItems.json
    │   │   ├── orders.json
    │   │   ├── orderItems.json
    │   │   ├── reviews.json
    │   │   ├── notifications.json
    │   │   └── promotions.json
    │   └── images/
    │       ├── campuseats-logo.png
    │       └── menu/
    │           ├── Chill Cafe/
    │           ├── Lemon Tree/
    │           └── Nasi Kandar Osman/
    └── src/
        ├── assets/
        │   ├── main.css
        │   └── images/
        ├── components/
        │   ├── BackButton.vue
        │   ├── BottomNav.vue
        │   ├── Navbar.vue
        │   ├── VendorCard.vue
        │   ├── MenuItemCard.vue
        │   ├── CartItem.vue
        │   ├── OrderCard.vue
        │   ├── PromotionCard.vue
        │   ├── DashboardCard.vue
        │   └── NotificationItem.vue
        ├── router/
        │   └── index.js
        ├── services/
        │   └── mockApi.js
        ├── stores/
        │   ├── authStore.js
        │   ├── cartStore.js
        │   ├── orderStore.js
        │   ├── vendorStore.js
        │   └── notificationStore.js
        ├── views/
        │   ├── LoginView.vue
        │   ├── RegisterView.vue
        │   ├── CustomerHomeView.vue
        │   ├── VendorMenuView.vue
        │   ├── CartView.vue
        │   ├── CheckoutView.vue
        │   ├── OrderConfirmationView.vue
        │   ├── OrderHistoryView.vue
        │   ├── ProfileView.vue
        │   ├── NotificationView.vue
        │   ├── VendorDashboardView.vue
        │   ├── VendorMenuManagementView.vue
        │   └── AdminDashboardView.vue
        ├── App.vue
        └── main.js

```

## Current Demo Accounts

```text
Customer: ali@student.utm.my / 123456
Vendor: dailygrind@utm.my / 123456
Admin: admin@campuseats.my / 123456
```
