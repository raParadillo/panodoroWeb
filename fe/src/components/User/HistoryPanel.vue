<script setup>
const historyEntries = [
  { date: 'August 8, 2026', items: ['Added a New Task', 'Added a New Task'] },
  { date: 'August 7, 2026', items: ['Added a New Task', 'Added a New Task','Added a New Task','Added a New Task','Added a New Task', 'Added a New Task','Added a New Task'] },
  
]
</script>

<template>
  <div class="history-overlay" aria-label="History panel">
    <div class="history-panel">
      <div class="panel-header">
        <button class="back-btn" aria-label="Close history">‹</button>
        <h2>History</h2>
      </div>

      <div class="history-list">
        <div v-for="group in historyEntries" :key="group.date" class="date-group">
          <p class="date-label">{{ group.date }}</p>

          <div v-for="(entry, index) in group.items" :key="`${group.date}-${index}`" class="entry-card">
            {{ entry }}
          </div>
        </div>
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
  background: rgba(204, 171, 129, 0.58);
  border: 1px solid rgba(120, 80, 48, 0.35);
  border-radius: 28px;
  padding: 26px 28px 20px;
  box-shadow: 0 18px 36px rgba(58, 35, 20, 0.18);
  backdrop-filter: blur(6px);
  display: flex;
  flex-direction: column;
}

.history-list {
  overflow-y: auto;
  padding-right: 8px;
  scrollbar-width: thin;
  scrollbar-color: rgba(112, 76, 56, 0.7) rgba(255, 255, 255, 0.08);
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
