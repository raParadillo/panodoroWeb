<script setup>
import AuthForm from './AuthForm.vue';

</script>

<template>
  <div class="auth-container">
    <!-- Static Fixed Background Image -->
    <div class="static-bg"></div>

    <!-- Auth Form Component -->
    <main class="auth-wrapper">
      <AuthForm />
    </main>
  </div>
</template>

<style scoped>
.auth-container {
  position: relative;
  min-height: 100vh;
  width: 100%;
}

.static-bg {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-image: url('@/assets/images/panodoroBgQ.png');
  filter:brightness(50%);
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  z-index: -1;
  pointer-events: none;
}

.auth-wrapper {
  position: relative;
  z-index: 1;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  box-sizing: border-box;
}
</style>
