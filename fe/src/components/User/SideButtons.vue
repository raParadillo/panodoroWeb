<script setup>
import { ref } from 'vue'
import { 
  ListChecks, 
  FileText, 
  History, 
  BarChart3, 
  LogOut 
} from 'lucide-vue-next'

const emit = defineEmits(['select', 'logout'])

const activeItem = ref('')

const navItems = [
  { id: 'checklist', name: 'Checklist', icon: ListChecks },
  { id: 'notes', name: 'Notes', icon: FileText },
  { id: 'history', name: 'History', icon: History },
  { id: 'analytics', name: 'Analytics', icon: BarChart3 }
]

function handleSelect(id) {
  if (activeItem.value === id) {
    activeItem.value = ''
    emit('select', null)
  } else {
    activeItem.value = id
    emit('select', id)
  }
}

function resetSelection() {
  activeItem.value = ''
}

function handleLogout() {
  emit('logout')
}

defineExpose({ resetSelection })
</script>

<template>
  <aside class="sidebar-container" aria-label="Sidebar Navigation">
    <!-- Top Brand Icon for layout balance -->
    <div class="sidebar-top">
      <div class="brand-dot" title="Panodoro">🥐</div>
    </div>

    <!-- Centered 4 Navigation Buttons -->
    <nav class="sidebar-center">
      <button
        v-for="item in navItems"
        :key="item.id"
        class="nav-btn"
        :class="{ active: activeItem === item.id }"
        :aria-label="item.name"
        @click="handleSelect(item.id)"
      >
        <component :is="item.icon" class="btn-icon" :size="22" />
        <span class="tooltip">{{ item.name }}</span>
      </button>
    </nav>

    <!-- Bottom Logout Button -->
    <div class="sidebar-bottom">
      <button
        class="nav-btn logout-btn"
        aria-label="Logout"
        title="Logout"
        @click="handleLogout"
      >
        <LogOut class="btn-icon" :size="22" />
        <span class="tooltip">Logout</span>
      </button>
    </div>
  </aside>
</template>

<style scoped>
.sidebar-container {
  position: fixed;
  left: 20px;
  top: 20px;
  bottom: 20px;
  width: 64px;

  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  padding: 18px 0;
  z-index: 100;

}

.sidebar-top {
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-dot {
  font-size: 1.4rem;
  user-select: none;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.brand-dot:hover {
  transform: scale(1.15) rotate(10deg);
}

.sidebar-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  width: 100%;
}

.sidebar-bottom {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}

.nav-btn {
  position: relative;
  width: 44px;
  height: 44px;
  border-radius: 14px;
  border: 1px solid transparent;
  background: #5e320f;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.25s ease;
  outline: none;
}

.btn-icon {
  width: 22px;
  height: 22px;
  transition: transform 0.2s ease, stroke 0.2s ease;
}

.nav-btn:hover {
  background: #7a3d12;
  
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.nav-btn:hover .btn-icon {
  stroke: #ffddaa;
  transform: scale(1.08);
}

.nav-btn.active {
  background: #962b08;
  border-color: rgba(255, 255, 255, 0.4);
}


.logout-btn {
  background: #4a3025;
}

.logout-btn:hover {
  background: #8a2f25;
  border-color: rgba(239, 68, 68, 0.5);
  color: #fca5a5;
}

.logout-btn:hover .btn-icon {
  stroke: #fca5a5;
}

/* Tooltip on Hover */
.tooltip {
  position: absolute;
  left: calc(100% + 14px);
  background: rgba(40, 26, 20, 0.88);
  color: #ffffff;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 500;
  white-space: nowrap;
  pointer-events: none;
  opacity: 0;
  transform: translateX(-6px);
  transition: opacity 0.2s ease, transform 0.2s ease;
  border: 1px solid rgba(255, 255, 255, 0.15);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
  z-index: 1000;
  font-family: 'Inter', system-ui, sans-serif;
}
.nav-btn:hover .tooltip {
  opacity: 1;
  transform: translateX(0);
}

</style>
