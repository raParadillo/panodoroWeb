<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'

const emit = defineEmits(['close'])
const totalStudySeconds = ref(0)
const todayStudySeconds = ref(0)
let refreshInterval = null

const statsStorageKey = `panodoro.studyStats:${localStorage.getItem('panodoro.activeUser') || 'guest'}`
const todayKey = new Date().toISOString().slice(0, 10)

function loadStats() {
  try {
    const savedStats = JSON.parse(localStorage.getItem(statsStorageKey) || 'null')
    totalStudySeconds.value = Math.max(0, Number(savedStats?.totalStudySeconds) || Number(savedStats?.studySeconds) || 0)
    todayStudySeconds.value = Math.max(0, Number(savedStats?.dailyStudySeconds?.[todayKey]) || 0)
  } catch {
    totalStudySeconds.value = 0
    todayStudySeconds.value = 0
  }
}

function formatDuration(seconds) {
  const totalMinutes = Math.floor(seconds / 60)
  const hours = Math.floor(totalMinutes / 60)
  const minutes = totalMinutes % 60
  if (hours === 0) return `${minutes} min`
  return `${hours} hr ${minutes} min`
}

const formattedToday = computed(() => formatDuration(todayStudySeconds.value))
const formattedTotal = computed(() => formatDuration(totalStudySeconds.value))

onMounted(() => {
  loadStats()
  refreshInterval = setInterval(loadStats, 1000)
})

onUnmounted(() => clearInterval(refreshInterval))
</script>

<template>
  <section class="analytics-card" aria-label="Study analytics">
    <header class="analytics-header">
      <button class="close-btn" type="button" aria-label="Close analytics" @click="emit('close')">&lt;</button>
      <div>
        <p class="eyebrow">Your progress</p>
        <h2>Study Analytics</h2>
      </div>
    </header>

    <div class="total-card">
      <span class="metric-label">Today's study time</span>
      <strong>{{ formattedToday }}</strong>
      <span class="metric-detail">{{ todayStudySeconds }} seconds tracked today</span>
    </div>

    <div class="total-card secondary-card">
      <span class="metric-label">All-time study time</span>
      <strong>{{ formattedTotal }}</strong>
    </div>

    <div class="analytics-note">
      <span class="status-dot"></span>
      <span>Time is counted while a study session is running.</span>
    </div>
  </section>
</template>

<style scoped>
.analytics-card {
  width: min(440px, 100%);
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 22px;
  background: rgba(203, 178, 152, 0.78);
  box-shadow: 0 18px 36px rgba(58, 35, 20, 0.24);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  color: #4a3025;
}

.analytics-header {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  margin-bottom: 1.25rem;
}

.close-btn {
  width: 2.25rem;
  height: 2.25rem;
  border: 0;
  border-radius: 50%;
  background: rgba(112, 76, 56, 0.16);
  color: #4a3025;
  cursor: pointer;
  font-size: 1.4rem;
  line-height: 1;
}

.close-btn:hover {
  background: rgba(112, 76, 56, 0.28);
}

.eyebrow {
  margin: 0 0 0.1rem;
  color: #8a614c;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

h2 {
  margin: 0;
  font-size: 1.55rem;
}

.total-card {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding: 1.25rem;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.68);
}

.metric-label,
.metric-detail {
  color: #8a6958;
  font-size: 0.8rem;
}

.total-card strong {
  color: #684333;
  font-size: 2.5rem;
  line-height: 1.1;
}

.secondary-card {
  margin-top: 0.75rem;
}

.analytics-note {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  color: #745746;
  font-size: 0.78rem;
}

.status-dot {
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 50%;
  background: #7d9b62;
  box-shadow: 0 0 0 4px rgba(125, 155, 98, 0.16);
}
</style>
