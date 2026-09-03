<template>
  <main class="page">
    <h1>About</h1>
    <p>
      This route is retained from the starter application while the product features are defined.
    </p>
    <RouterLink to="/">Back home</RouterLink>
  </main>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.page {
  max-width: 42rem;
  margin: 0 auto;
  padding: 4rem 1.5rem;
}
h1 {
  margin-bottom: 1rem;
}
p {
  margin-bottom: 1.5rem;
  color: #475569;
}
a {
  color: #2563eb;
}
</style>
