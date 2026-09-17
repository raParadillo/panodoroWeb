<template>
    <SideButtons ref="sideButtons" @select="handleSidebarSelect" @logout="handleLogout" />

    <div v-if="activePanel" class="utility-overlay">
        <HistoryPanel v-if="activePanel === 'history'" @close="closePanel" />
        <NotesCard v-else-if="activePanel === 'notes'" @close="closePanel" />
        <ToDo v-else-if="activePanel === 'checklist'" @close="closePanel" />
        <Analytics v-else-if="activePanel === 'analytics'" @close="closePanel" />
    </div>

    <div class="panodoro-container">
    <main class="timer-card">
        <h1 class="brand-title">{{username}}</h1>

        <Transition name="settings">
        <div v-show="!isRunning" class="settings" aria-label="Timer settings">
            <div class="setting">
                <span class="setting-label">Loops</span>
                <div class="stepper">
                    <button type="button" @click="changeSetting('loops', -1)" aria-label="Decrease loops">-</button>
                    <strong>{{ totalLoops }}</strong>
                    <button type="button" @click="changeSetting('loops', 1)" aria-label="Increase loops">+</button>
                </div>
            </div>
            <div class="setting">
                <span class="setting-label">Study</span>
                <div class="stepper">
                    <button type="button" @click="changeSetting('study', -1)" aria-label="Decrease study minutes">-</button>
                    <strong>{{ studyMinutes }}<small>m</small></strong>
                    <button type="button" @click="changeSetting('study', 1)" aria-label="Increase study minutes">+</button>
                </div>
            </div>
            <div class="setting">
                <span class="setting-label">Break</span>
                <div class="stepper">
                    <button type="button" @click="changeSetting('break', -1)" aria-label="Decrease break minutes">-</button>
                    <strong>{{ breakMinutes }}<small>m</small></strong>
                    <button type="button" @click="changeSetting('break', 1)" aria-label="Increase break minutes">+</button>
                </div>
            </div>
        </div>
        </Transition>

        <div class="timer-orbit" :class="{ 'is-break': currentMode === 'break' }">
            <div class="loop-label">Loop <strong>{{ loopCount }} / {{ totalLoops }}</strong></div>

            <div class="cycle-values">
                <div class="cycle-value">
                    <span>Study</span>
                    <strong>{{ studyMinutes }}</strong>
                </div>
                <div class="cycle-value">
                    <span>Break</span>
                    <strong>{{ breakMinutes }}</strong>
                </div>
            </div>

            <div class="phase-label">
                {{ currentMode === 'study' ? 'Time to study' : 'Break time' }}
            </div>
            <div class="time-display">
                {{ formatTime }}
            </div>

            <div
                class="pandesal-idle"
                :class="{ 'is-spinning': isRunning }"
                :style="{ backgroundImage: `url(${pandesalImage})` }"
                role="img"
                aria-label="Cute pandesal spinning like a disk"
            ></div>

            <div class="record-tonearm" :class="{ 'is-playing': isRunning }" aria-hidden="true">
                <span class="tonearm-head"></span>
                <span class="tonearm-needle"></span>
            </div>

            <button class="icon-btn play-pause" @click="toggleTimer" :title="isRunning ? 'Pause timer' : 'Start timer'" :aria-label="isRunning ? 'Pause timer' : 'Start timer'">
                <span v-if="!isRunning">
                <svg viewBox="0 0 24 24" fill="currentColor" width="30" height="30"><path d="M8 5v14l11-7z"/></svg>
                </span>
                <span v-else>
                <svg viewBox="0 0 24 24" fill="currentColor" width="30" height="30"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                </span>
            </button>
        </div>

        <div class="controls">
            <button class="icon-btn reset-btn" @click="resetTimer" title="Reset timer" aria-label="Reset timer">
                <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm-6 8c0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3c-3.31 0-6-2.69-6-6z"/></svg>
                <span>Reset loop</span>
            </button>
        </div>
    </main>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import pandesalImage from '../../assets/pandesal.png';
import { addStudyTime, getCurrentUser, getTimerSettings, logoutUser, updateTimerSettings } from '../../services/api';
import SideButtons from './SideButtons.vue';
import HistoryPanel from './HistoryPanel.vue';
import NotesCard from './NotesCard.vue';
import ToDo from './ToDo.vue';
import Analytics from './Analytics.vue';

const studyMinutes = ref(25);
const breakMinutes = ref(5);
const totalLoops = ref(4);
const username = ref(localStorage.getItem('panodoro.activeUser') || 'Panodoro');
const currentMode = ref('study');
const timeLeft = ref(studyMinutes.value * 60);
const isRunning = ref(false);
const loopCount = ref(1);
const activePanel = ref(null);
const sideButtons = ref(null);
let timerInterval = null;
let pendingStudySeconds = 0;

async function loadTimerSettings() {
    try {
        const settings = await getTimerSettings();
        studyMinutes.value = settings.study_minutes;
        breakMinutes.value = settings.break_minutes;
        totalLoops.value = settings.total_loops;
        timeLeft.value = studyMinutes.value * 60;
    } catch {
        // Keep the defaults if settings cannot be loaded.
    }
}

async function loadUsername() {
    try {
        const user = await getCurrentUser();
        username.value = user.full_name || user.email || username.value;
    } catch {
        // Keep the locally saved email fallback when the profile request fails.
    }
}

function saveTimerSettings() {
    return updateTimerSettings({
        study_minutes: studyMinutes.value,
        break_minutes: breakMinutes.value,
        total_loops: totalLoops.value,
    }).catch(() => {})
}

function recordStudySecond() {
    pendingStudySeconds++;
}

async function flushStudyTime() {
    if (!pendingStudySeconds) return;
    const seconds = pendingStudySeconds;
    pendingStudySeconds = 0;
    await addStudyTime({ seconds_studied: seconds }).catch(() => {
        pendingStudySeconds += seconds;
    });
}

const formatTime = computed(() => {
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

function toggleTimer() {
    if (isRunning.value) {
    stopTimer();
    } else {
    isRunning.value = true;
    timerInterval = setInterval(() => {
                if (timeLeft.value > 0) {
                    timeLeft.value--;
                    if (currentMode.value === 'study') recordStudySecond();
                    if (pendingStudySeconds >= 10) void flushStudyTime();
                    return;
                }

                                if (currentMode.value === 'study') {
                                    currentMode.value = 'break';
                                    timeLeft.value = breakMinutes.value * 60;
                                } else if (loopCount.value < totalLoops.value) {
                                    loopCount.value++;
                                    currentMode.value = 'study';
                                    timeLeft.value = studyMinutes.value * 60;
                                } else {
                                    stopTimer();
                                    resetTimer();
                                }
    }, 1000);
    }
}

function stopTimer() {
    isRunning.value = false;
    clearInterval(timerInterval);
    void flushStudyTime();
}

function resetTimer() {
    stopTimer();
    currentMode.value = 'study';
    loopCount.value = 1;
    timeLeft.value = studyMinutes.value * 60;
}

function changeSetting(setting, amount) {
    stopTimer();

    if (setting === 'loops') totalLoops.value = Math.max(1, Math.min(12, totalLoops.value + amount));
    if (setting === 'study') studyMinutes.value = Math.max(1, Math.min(120, studyMinutes.value + amount));
    if (setting === 'break') breakMinutes.value = Math.max(1, Math.min(60, breakMinutes.value + amount));

    currentMode.value = 'study';
    loopCount.value = 1;
    timeLeft.value = studyMinutes.value * 60;
    void saveTimerSettings();
}

function handleSidebarSelect(id) {
    activePanel.value = id;
}

function closePanel() {
    activePanel.value = null;
  sideButtons.value?.resetSelection();
}

function handleLogout() {
    stopTimer();

    logoutUser().catch(() => {});
    localStorage.removeItem('panodoro.activeUser');
    localStorage.removeItem('panodoro.authToken');
    window.location.replace('/authdefault');
}

onMounted(() => {
    loadTimerSettings();
    loadUsername();
});
onUnmounted(() => {
    clearInterval(timerInterval);
    void flushStudyTime();
});
</script>

<style scoped>
.panodoro-container {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 1.25rem;
    background:
        linear-gradient(rgba(53, 37, 28, 0.32), rgba(53, 37, 28, 0.32)),
        url('../../assets/images/panodoroBg.png') no-repeat center center / cover;
}

.utility-overlay {
    position: fixed;
    inset: 0;
    z-index: 40;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: rgba(35, 24, 20, 0.18);
}

.floating-overlay-wrapper {
    position: absolute;
    top: 15%;
    left: 10%;
    z-index: 100;
}

.timer-card {
    width: min(100%, 500px);
    min-height: 520px;
    text-align: center;
    background: rgba(112, 80, 64, 0.43);
    border: 1px solid rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(13px);
    -webkit-backdrop-filter: blur(13px);
    padding: 2.5rem 2.5rem 2.2rem;
    border-radius: 24px;
    color: #fff;
    box-shadow: 0 24px 55px rgba(38, 24, 17, 0.24);
}

.brand-title {
    font-family: Georgia, 'Times New Roman', serif;
    font-style: italic;
    font-size: clamp(1.85rem, 5vw, 2.15rem);
    font-weight: 600;
    letter-spacing: -0.02em;
    margin-bottom: 1.75rem;
}

.settings {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.settings-enter-active,
.settings-leave-active {
    overflow: hidden;
    transition: opacity 0.3s ease, transform 0.3s ease, max-height 0.3s ease, margin 0.3s ease;
}

.settings-enter-from,
.settings-leave-to {
    max-height: 0;
    margin-bottom: 0;
    opacity: 0;
    transform: translateY(-8px);
}

.settings-enter-to,
.settings-leave-from {
    max-height: 100px;
    opacity: 1;
    transform: translateY(0);
}

.setting {
    padding: 0.55rem 0.35rem 0.45rem;
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 12px;
    background: rgba(36, 29, 34, 0.18);
}

.setting-label {
    display: block;
    margin-bottom: 0.28rem;
    color: rgba(255, 255, 255, 0.72);
    font-size: 0.62rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.stepper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
}

.stepper button {
    width: 1.35rem;
    height: 1.35rem;
    padding: 0;
    border: 0;
    border-radius: 50%;
    color: #fff;
    background: rgba(255, 255, 255, 0.14);
    cursor: pointer;
    font-size: 1rem;
    line-height: 1;
}

.stepper button:hover {
    background: rgba(255, 255, 255, 0.3);
}

.stepper button:focus-visible {
    outline: 2px solid #fff;
    outline-offset: 2px;
}

.stepper strong {
    min-width: 1.8rem;
    color: #fff;
    font-size: 0.95rem;
}

.stepper small {
    margin-left: 0.08rem;
    font-size: 0.62rem;
    font-weight: 400;
}

.timer-orbit {
    position: relative;
    width: min(100%, 310px);
    aspect-ratio: 1;
    margin: 0 auto 1.5rem;
    border: 2px solid rgba(255, 255, 255, 0.85);
    border-radius: 50%;
    background: radial-gradient(circle, rgba(83, 57, 54, 0.64) 0 58%, rgba(45, 33, 38, 0.36) 59% 100%);
    box-shadow: 0 0 0 7px rgba(16, 28, 44, 0.18), 0 0 22px rgba(17, 211, 226, 0.55), inset 0 0 22px rgba(230, 25, 162, 0.2);
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.pandesal-idle {
    position: absolute;
    inset: 0;
    z-index: 0;
    border-radius: 50%;
    pointer-events: none;
    background-color: rgba(24, 18, 24, 0.62);
    background-blend-mode: multiply;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    opacity: 0.72;
    filter: brightness(0.62) saturate(0.82);
    box-shadow: inset 0 0 34px rgba(0, 0, 0, 0.42), inset 0 0 0 2px rgba(255, 255, 255, 0.14);
}

.pandesal-idle.is-spinning {
    animation: pandesal-spin 10s linear infinite;
}

.pandesal-idle::before {
    content: '';
    position: absolute;
    inset: 43%;
    border: 4px solid rgba(255, 255, 255, 0.52);
    border-radius: 50%;
    background: rgba(41, 29, 34, 0.78);
    box-shadow: 0 0 0 6px rgba(41, 29, 34, 0.18), inset 0 2px 5px rgba(255, 255, 255, 0.3);
}

.pandesal-idle::after {
    content: '';
    position: absolute;
    inset: 8%;
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 50%;
    transform: rotate(28deg);
    background: linear-gradient(125deg, transparent 35%, rgba(255, 255, 255, 0.14) 50%, transparent 64%);
}

.record-tonearm {
    position: absolute;
    right: -15%;
    top: 0%;
    width: 70%;
    height: 0.62rem;
    border-radius: 999px;
    background: linear-gradient(180deg, #f4d39a 0%, #b77946 46%, #70402f 100%);
    box-shadow: 0 3px 5px rgba(54, 30, 22, 0.38);
    transform: rotate(-100deg);
    transform-origin: right center;
    transition: top 0.55s ease, transform 0.55s ease;
}

.record-tonearm.is-playing {
    top: 0%;
    transform: rotate(-70deg);
}

.record-tonearm::before {
    content: '';
    position: absolute;
    right: -0.48rem;
    top: 50%;
    width: 1.15rem;
    height: 1.15rem;
    border: 0.22rem solid #b77946;
    border-radius: 50%;
    background: #f5e5c9;
    box-shadow: inset 0 0 0 0.2rem #70402f, 0 2px 4px rgba(54, 30, 22, 0.32);
    transform: translateY(-50%);
}

.tonearm-head {
    position: absolute;
    left: -0.25rem;
    top: 50%;
    width: 1.2rem;
    height: 0.9rem;
    border-radius: 0.25rem;
    background: #8d4f32;
    box-shadow: 0 2px 3px rgba(54, 30, 22, 0.35);
    transform: translateY(-50%);
}

.tonearm-needle {
    position: absolute;
    left: -0.1rem;
    top: 50%;
    width: 0.12rem;
    height: 0.42rem;
    background: #e7edf2;
    transform: rotate(160deg);
    transform-origin: top center;
}

@keyframes pandesal-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.timer-orbit::before {
    content: '';
    position: absolute;
    inset: -2px;
    border: 2px solid transparent;
    border-right-color: #ec25a9;
    border-bottom-color: #ec25a9;
    border-radius: 50%;
    transform: rotate(32deg);
}

.timer-orbit.is-break {
    border-color: #e9b45a;
    box-shadow: 0 0 0 7px rgba(16, 28, 44, 0.18), 0 0 22px rgba(233, 180, 90, 0.58), inset 0 0 22px rgba(233, 180, 90, 0.18);
}

.loop-label,
.phase-label,
.cycle-values,
.timer-orbit .time-display,
.timer-orbit .play-pause {
    position: absolute;
    z-index: 1;
}

.loop-label {
    top: 1.1rem;
    left: 0;
    right: 0;
    font-size: 0.7rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.loop-label strong {
    display: inline-block;
    margin-left: 0.3rem;
    font-size: 0.95rem;
}

.cycle-values {
    top: 3.4rem;
    left: 2.5rem;
    right: 2.5rem;
    display: flex;
    justify-content: space-between;
}

.cycle-value {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.12rem;
    color: rgba(255, 255, 255, 0.74);
    font-size: 0.62rem;
    text-transform: uppercase;
}

.cycle-value strong {
    color: #fff;
    font-size: 1.1rem;
    line-height: 1;
}

.phase-label {
    top: 6rem;
    left: 0;
    right: 0;
    color: rgba(255, 255, 255, 0.74);
    font-size: 0.72rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.time-display {
    font-family: 'Trebuchet MS', sans-serif;
    font-size: clamp(3.4rem, 16vw, 4.8rem);
    font-weight: 700;
    letter-spacing: 0.02em;
    line-height: 1;
    top: 7.4rem;
    left: 0;
    right: 0;
    text-shadow: 0 3px 12px rgba(54, 32, 24, 0.12);
}

.controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1.25rem;
}

.icon-btn {
    background: transparent;
    border: none;
    color: white;
    cursor: pointer;
    opacity: 0.82;
    display: grid;
    place-items: center;
    transition: opacity 0.2s ease, transform 0.2s ease, background 0.2s ease;
}

.icon-btn:hover {
    opacity: 1;
    transform: scale(1.1);
}

.icon-btn:focus-visible {
    outline: 2px solid rgba(255, 255, 255, 0.9);
    outline-offset: 3px;
}

.play-pause {
    bottom: 1.5rem;
    left: 50%;
    transform: translateX(-50%);
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
}

.play-pause:hover {
    transform: translateX(-50%) scale(1.1);
}

.reset-btn {
    gap: 0.45rem;
    padding: 0.35rem 0.65rem;
    border-radius: 999px;
    font: 600 0.75rem/1 inherit;
}

.reset-btn:hover {
    background: rgba(255, 255, 255, 0.12);
}

@media (max-width: 520px) {
    .panodoro-container {
        padding: 0.75rem;
    }

    .timer-card {
        min-height: 480px;
        padding: 2rem 1rem 1.8rem;
    }

    .timer-orbit {
        width: min(100%, 285px);
    }
}
</style>