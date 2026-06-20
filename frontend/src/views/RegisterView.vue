<template>
  <main class="page">
    <h1 class="page-title">Register</h1>

    <input class="input" placeholder="Full name" />
    <input class="input" placeholder="Email address" />
    <input class="input" type="password" placeholder="Password" />

    <select class="input">
      <option value="customer">Customer</option>
      <option value="vendor">Vendor</option>
    </select>

    <button class="btn" @click="$router.push('/login')">Create Account</button>
  </main>
</template>
