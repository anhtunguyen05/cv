<template>
  <main class="welcome">
    <p class="eyebrow">Frontend foundation</p>
    <h1>CareerFitCV</h1>
    <p>
      The Vue application foundation is ready for feature work. Product capabilities will be added
      under <code>src/features</code> when they are implemented.
    </p>
    <nav aria-label="Primary navigation">
      <RouterLink to="/">Home</RouterLink>
      <RouterLink to="/about">About</RouterLink>
    </nav>
  </main>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.welcome {
  max-width: 42rem;
  margin: 0 auto;
  padding: 4rem 1.5rem;
}
.eyebrow {
  color: #41b883;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
h1 {
  margin: 0.5rem 0 1rem;
  font-size: 3rem;
}
p {
  color: #475569;
}
nav {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}
a {
  color: #2563eb;
}
code {
  border-radius: 0.25rem;
  background: #e2e8f0;
  padding: 0.15rem 0.35rem;
}
</style>
