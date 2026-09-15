<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Toggle state between Login and Sign Up
const isSignUp = ref(false)
const isForgotPassword = ref(false)
const codeSent = ref(false)

// Form fields
const loginEmail = ref('')
const loginPassword = ref('')

const signUpName = ref('')
const signUpEmail = ref('')
const signUpPassword = ref('')
const confirmPassword = ref('')
const resetEmail = ref('')
const resetCode = ref('')
const newPassword = ref('')
const confirmNewPassword = ref('')

const setActiveUser = (email) => {
  localStorage.setItem('panodoro.activeUser', email.trim().toLowerCase())
}

const handleLogin = () => {
  setActiveUser(loginEmail.value)
  router.push('/timer')
}

const showForgotPassword = () => {
  isForgotPassword.value = true
  isSignUp.value = false
  codeSent.value = false
}

const backToLogin = () => {
  isForgotPassword.value = false
  codeSent.value = false
}

const handleSendCode = () => {
  codeSent.value = true
}

const handleResetPassword = () => {
  if (newPassword.value !== confirmNewPassword.value) {
    alert('Passwords do not match!')
    return
  }

  backToLogin()
}

const handleSignUp = () => {
  if (signUpPassword.value !== confirmPassword.value) {
    alert('Passwords do not match!')
    return
  }
  setActiveUser(signUpEmail.value)
  router.push('/timer')
}
</script>

<template>
  <div class="auth-card">
    <div class="brand">
      <div class="logo">🥐</div>
      <h2>Panodoro</h2>
      <p class="subtitle">
        {{ isForgotPassword ? 'Reset your password' : isSignUp ? 'Create your account' : 'Welcome back!' }}
      </p>
    </div>

    <div v-if="isForgotPassword" class="form-wrapper">
      <form v-if="!codeSent" @submit.prevent="handleSendCode" class="form">
        <div class="form-group">
          <label for="reset-email">Email</label>
          <input
            id="reset-email"
            v-model="resetEmail"
            type="email"
            placeholder="Enter your email"
            required
          />
        </div>

        <button type="submit" class="submit-btn">Send Code</button>
      </form>

      <form v-else @submit.prevent="handleResetPassword" class="form">
        <div class="form-group">
          <label for="reset-code">Verification Code</label>
          <input
            id="reset-code"
            v-model="resetCode"
            type="text"
            placeholder="Enter the code"
            required
          />
        </div>

        <div class="form-group">
          <label for="new-password">New Password</label>
          <input
            id="new-password"
            v-model="newPassword"
            type="password"
            placeholder="Create a new password"
            required
          />
        </div>

        <div class="form-group">
          <label for="confirm-new-password">Confirm Password</label>
          <input
            id="confirm-new-password"
            v-model="confirmNewPassword"
            type="password"
            placeholder="Confirm your new password"
            required
          />
        </div>

        <button type="submit" class="submit-btn">Reset Password</button>
      </form>

      <div class="switch-format">
        <span>Remembered your password?</span>
        <button type="button" class="switch-btn" @click="backToLogin">Log In</button>
      </div>
    </div>

    <!-- Log In Form (Shown when isSignUp is false) -->
    <div v-else v-show="!isSignUp" class="form-wrapper">
      <form @submit.prevent="handleLogin" class="form">
        <div class="form-group">
          <label for="login-email">Email</label>
          <input 
            id="login-email" 
            v-model="loginEmail" 
            type="email" 
            placeholder="Enter your email" 
            required 
          />
        </div>

        <div class="form-group">
          <div class="label-row">
            <label for="login-password">Password</label>
            <button type="button" class="forgot-link" @click="showForgotPassword">Forgot password?</button>
          </div>
          <input 
            id="login-password" 
            v-model="loginPassword" 
            type="password" 
            placeholder="Enter your password" 
            required 
          />
        </div>

        <button type="submit" class="submit-btn">Log In</button>
      </form>

      <!-- Classic Switch format at the bottom -->
      <div class="switch-format">
        <span>Don't have an account?</span>
        <button type="button" class="switch-btn" @click="isSignUp = true">Sign Up</button>
      </div>
    </div>

    <!-- Sign Up Form (Shown when isSignUp is true) -->
    <div v-show="isSignUp" class="form-wrapper">
      <form @submit.prevent="handleSignUp" class="form">
        <div class="form-group">
          <label for="signup-name">Full Name</label>
          <input 
            id="signup-name" 
            v-model="signUpName" 
            type="text" 
            placeholder="Enter your full name" 
            required 
          />
        </div>

        <div class="form-group">
          <label for="signup-email">Email</label>
          <input 
            id="signup-email" 
            v-model="signUpEmail" 
            type="email" 
            placeholder="Enter your email" 
            required 
          />
        </div>

        <div class="form-group">
          <label for="signup-password">Password</label>
          <input 
            id="signup-password" 
            v-model="signUpPassword" 
            type="password" 
            placeholder="Create a password" 
            required 
          />
        </div>

        <div class="form-group">
          <label for="confirm-password">Confirm Password</label>
          <input 
            id="confirm-password" 
            v-model="confirmPassword" 
            type="password" 
            placeholder="Confirm your password" 
            required 
          />
        </div>

        <button type="submit" class="submit-btn">Create Account</button>
      </form>

      <!-- Classic Switch format at the bottom -->
      <div class="switch-format">
        <span>Already have an account?</span>
        <button type="button" class="switch-btn" @click="isSignUp = false">Log In</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-card {
  width: 100%;
  max-width: 420px;
  background: rgba(184, 151, 120, 0.45);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 28px;
  padding: 2.5rem 2rem;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
  color: #ffffff;
  font-family: 'Inter', system-ui, sans-serif;
  margin: 0 auto;
}

.brand {
  text-align: center;
  margin-bottom: 2rem;
}

.logo {
  font-size: 2.5rem;
  margin-bottom: 0.25rem;
}

.brand h2 {
  font-size: 1.6rem;
  font-weight: 700;
  margin: 0;
}

.subtitle {
  font-size: 0.875rem;
  color: #f5f7f9;
  margin-top: 0.25rem;
}

.form-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #cbd5e1;
}

.forgot-link {
  font-size: 0.75rem;
  color: #e48a2f;
  text-decoration: none;
}

.forgot-link:hover {
  text-decoration: underline;
}

input {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(212, 185, 155, 0.7);
  color: #4a3828;
  font-size: 0.9rem;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s ease;
}

input:focus {
  border-color: #6366f1;
}

.submit-btn {
  width: 100%;
  padding: 0.85rem;
  margin-top: 0.5rem;
  border: none;
  border-radius: 10px;
  background: rgba(226, 215, 200, 0.85);
  color: #ffffff;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.submit-btn:hover {
  background: #e48a2f;
}

/* Classic Switch bottom section */
.switch-format {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #94a3b8;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.switch-btn {
  background: none;
  border: none;
  color: #e48a2f;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  padding: 0;
}

.switch-btn:hover {
  color: #b66d25;
  text-decoration: underline;
}
</style>
