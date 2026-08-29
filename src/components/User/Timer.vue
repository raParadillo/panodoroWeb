<template>
    <!-- Main Wrapper with your warm background layout -->
    <div class="panodoro-container">

        <!-- 1. The floating overlay layout wrapper for your independent ToDo component -->
        <!-- It listens to the global layout and renders right on top using absolute positioning -->
        <div v-show="currentActivePanel === 'checklist'" class="floating-overlay-wrapper">
            <ToDoCard />
        </div>

        <div class="timer-card">
            <h1 class="brand-title">Panodoro</h1>

            <!-- Tab Selection Tabs -->
            <div class="tabs">
                <button :class="{ active: currentMode === 'pomodoro' }"
                    @click="setMode('pomodoro', 25)">Panodoro</button>
                <button :class="{ active: currentMode === 'longBreak' }" @click="setMode('longBreak', 15)">Long
                    break</button>
                <button :class="{ active: currentMode === 'shortBreak' }" @click="setMode('shortBreak', 5)">Short
                    break</button>
            </div>

            <!-- Time Display -->
            <div class="time-display">{{ formatTime }}</div>

            <!-- Timer Control Actions -->
            <div class="controls">
                <button class="icon-btn" @click="resetTimer" title="Reset">
                    <svg xmlns="http://w3.org" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                        <path
                            d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm-6 8c0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3c-3.31 0-6-2.69-6-6z" />
                    </svg>
                </button>

                <button class="icon-btn play-pause" @click="toggleTimer" title="Play/Pause">
                    <span v-if="!isRunning">
                        <svg xmlns="http://w3.org" viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </span>
                    <span v-else>
                        <svg xmlns="http://w3.org" viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onUnmounted, ref, inject } from 'vue';
// 2. Import your separate custom To-Do layout component
import ToDoCard from './ToDo.vue';

// 3. Capture your teammate's sidebar selection variable string state
// Adjust this key definition if your team passes down active states via provide/inject or props
const currentActivePanel = inject('activePanelId', ref('checklist')); // Defaults open to 'checklist' for your immediate view testing

const currentMode = ref('pomodoro');
const timeLeft = ref(25 * 60); // 25 minutes in seconds
const isRunning = ref(false);
let timerInterval = null;

// Format remaining seconds into MM:SS configuration
const formatTime = computed(() => {
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

function setMode(mode, minutes) {
    stopTimer();
    currentMode.value = mode;
    timeLeft.value = minutes * 60;
}

function toggleTimer() {
    if (isRunning.value) {
        stopTimer();
    } else {
        isRunning.value = true;
        timerInterval = setInterval(() => {
            if (timeLeft.value > 0) {
                timeLeft.value--;
            } else {
                stopTimer();
                alert('Time is up!');
            }
        }, 1000);
    }
}

function stopTimer() {
    isRunning.value = false;
    clearInterval(timerInterval);
}

function resetTimer() {
    stopTimer();
    if (currentMode.value === 'pomodoro') timeLeft.value = 25 * 60;
    if (currentMode.value === 'longBreak') timeLeft.value = 15 * 60;
    if (currentMode.value === 'shortBreak') timeLeft.value = 5 * 60;
}

onUnmounted(() => clearInterval(timerInterval));
</script>

<style scoped>
.panodoro-container {
    position: relative;
    /* CRUCIAL: Absolute layered cards stay nested within this specific viewport box */
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('../../assets/images/panodoroBg.png') no-repeat center center/cover;
}

/* 4. Overlay alignment rules mapping to your original design mockup cards */
.floating-overlay-wrapper {
    position: absolute;
    top: 15%;
    left: 10%;
    /* Floats nicely over the left side of the room illustration layout */
    z-index: 100;
    /* Forces your to-do template to stay crisp on top of all image graphics */
}

.timer-card {
    text-align: center;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(8px);
    padding: 2.5rem;
    border-radius: 24px;
    color: #fff;
    width: 100%;
    max-width: 500px;
}

.brand-title {
    font-family: 'Georgia', serif;
    font-style: italic;
    font-size: 2rem;
    margin-bottom: 1.5rem;
}

.tabs {
    display: flex;
    justify-content: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.2);
    padding: 6px;
    border-radius: 30px;
    margin-bottom: 2rem;
}

.tabs button {
    background: transparent;
    border: none;
    color: #fff;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.tabs button.active {
    background: #6d4c41;
    /* Brown tone matched to your active selection highlight */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.time-display {
    font-size: 6rem;
    font-weight: bold;
    margin: 1.5rem 0;
    font-family: monospace;
}

.controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
}

.icon-btn {
    background: transparent;
    border: none;
    color: white;
    cursor: pointer;
    opacity: 0.8;
    transition: transform 0.2s;
}

.icon-btn:hover {
    opacity: 1;
    transform: scale(1.1);
}

.play-pause {
    background: rgba(255, 255, 255, 0.2);
    padding: 12px;
    border-radius: 50%;
}
</style>
