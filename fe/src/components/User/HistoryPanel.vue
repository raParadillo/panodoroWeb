<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
import { getHistory } from '../../services/api'

const emit = defineEmits(['close'])
const activities = ref([])
const currentPage = ref(0)
const lastPage = ref(1)
const isLoading = ref(false)
const historyError = ref('')

async function loadMoreHistory() {
  if (isLoading.value || currentPage.value >= lastPage.value) return

  isLoading.value = true
  historyError.value = ''
  const response = await getHistory(currentPage.value + 1).catch((error) => {
    historyError.value = error.message || 'History could not be loaded.'
    return null
  })
  if (response) {
    const entries = Array.isArray(response.data) ? response.data : []
    activities.value.push(...entries)
    currentPage.value = response.current_page ?? currentPage.value + 1
    lastPage.value = response.last_page ?? currentPage.value
    await nextTick()

    const list = document.querySelector('.history-list')
    if (list && list.scrollHeight <= list.clientHeight && currentPage.value < lastPage.value) {
      await loadMoreHistory()
    }
  }
  isLoading.value = false
}

function handleHistoryScroll(event) {
  const element = event.currentTarget
  const nearBottom = element.scrollTop + element.clientHeight >= element.scrollHeight - 80
  if (nearBottom) loadMoreHistory()
}

const historyEntries = computed(() => {
  const groups = new Map()
  activities.value.forEach((activity) => {
    const date = new Date(activity.created_at).toLocaleDateString(undefined, {
      year: 'numeric', month: 'long', day: 'numeric'
    })
    if (!groups.has(date)) groups.set(date, [])
    groups.get(date).push(activity.description)
  })
  return [...groups].map(([date, items]) => ({ date, items }))
})

onMounted(loadMoreHistory)
</script>

<template>
  <div class="history-overlay" aria-label="History panel">
    <div class="history-panel">
      <div class="panel-header">
        <button class="back-btn" aria-label="Close history" @click="emit('close')">‹</button>
        <h2>History</h2>
      </div>

      <div class="history-list" @scroll="handleHistoryScroll">
        <div v-for="group in historyEntries" :key="group.date" class="date-group">
          <p class="date-label">{{ group.date }}</p>

          <div v-for="(entry, index) in group.items" :key="`${group.date}-${index}`" class="entry-card">
            {{ entry }}
          </div>
        </div>
        <p v-if="isLoading" class="history-status">Loading more history...</p>
        <p v-else-if="historyError" class="history-status error-status">{{ historyError }}</p>
        <p v-else-if="currentPage >= lastPage && activities.length" class="history-status">You have reached the beginning.</p>
        <p v-else-if="!isLoading && !activities.length" class="history-status">No history yet.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.history-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 40;
  padding: 32px;
}

.history-panel {
  width: min(780px, 80vw);
  max-height: 78vh;
  background: rgba(204, 171, 129, 0.25);
  border: 1px solid rgba(120, 80, 48, 0.35);
  border-radius: 28px;
  padding: 26px 28px 20px;
  box-shadow: 0 18px 36px rgba(58, 35, 20, 0.18);
  backdrop-filter: blur(22px) saturate(115%);
  -webkit-backdrop-filter: blur(22px) saturate(115%);
  display: flex;
  flex-direction: column;
}

.history-list {
  flex: 1;
  min-height: 140px;
  overflow-y: auto;
  padding-right: 8px;
  scrollbar-width: thin;
  scrollbar-color: rgba(112, 76, 56, 0.7) rgba(255, 255, 255, 0.08);
}

.history-status {
  margin: 24px 0 8px;
  color: rgba(49, 33, 27, 0.7);
  font-size: 0.9rem;
  text-align: center;
}

.error-status {
  color: #7f3f32;
}

.panel-header {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-bottom: 18px;
}

.back-btn {
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 50%;
  background: transparent;
  color: rgba(49, 33, 27, 0.9);
  font-size: 2rem;
  line-height: 1;
  cursor: pointer;
}

.panel-header h2 {
  margin: 0;
  font-size: clamp(1.6rem, 2vw, 2.2rem);
  color: rgba(49, 33, 27, 0.9);
  font-weight: 700;
}

.date-group {
  margin-top: 18px;
}

.date-label {
  margin: 0 0 12px;
  font-size: 0.9rem;
  color: rgba(49, 33, 27, 0.75);
}

.entry-card {
  width: 100%;
  min-height: 66px;
  display: flex;
  align-items: center;
  padding: 0 20px;
  margin-bottom: 14px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(107, 75, 49, 0.3);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
  color: rgba(42, 29, 24, 0.9);
  font-size: 1.05rem;
}
</style>
