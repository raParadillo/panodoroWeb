<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'
import {
  Maximize2,
  Minimize2,
  X,
  Radio,
  Play,
  Pause,
  Volume2,
  VolumeX,
  Square
} from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'open'])

// ==========================================
// 1. DIRECT LO-FI & AMBIENT RADIO STATIONS
// 100% reliable HTML5 audio, zero ads/blocks
// ==========================================
const radioStations = [
  {
    id: 'lofi-live',
    name: '☕ 24/7 Lofi Chill Radio',
    desc: 'Smooth chillhop beats to relax, study, and focus',
    streamUrl: 'https://play.streamafrica.net/lofiradio'
  },
  {
    id: 'chillhop-live',
    name: '🥐 Cafe Chillhop Beats',
    desc: 'Warm coffee shop beats & mellow rhythms',
    streamUrl: 'https://streams.ilovemusic.de/iloveradio17.mp3'
  },
  {
    id: 'groove-salad',
    name: '🌿 Groove Salad Chill',
    desc: 'Downtempo ambient grooves for deep concentration',
    streamUrl: 'https://ice1.somafm.com/groovesalad-128-mp3'
  },
  {
    id: 'drone-zone',
    name: '🌌 Drone Zone Ambient',
    desc: 'Atmospheric soundscapes for high focus & calm',
    streamUrl: 'https://ice1.somafm.com/dronezone-128-mp3'
  }
]

const currentRadio = ref(radioStations[0])
const isRadioPlaying = ref(false)
const radioVolume = ref(0.7)
const isMuted = ref(false)
let audioElement = null

function getAudio() {
  if (!audioElement) {
    audioElement = new Audio(currentRadio.value.streamUrl)
    audioElement.volume = radioVolume.value
    audioElement.addEventListener('play', () => {
      isRadioPlaying.value = true
    })
    audioElement.addEventListener('pause', () => {
      isRadioPlaying.value = false
    })
    audioElement.addEventListener('error', () => {
      isRadioPlaying.value = false
    })
  }
  return audioElement
}

function toggleRadio() {
  const audio = getAudio()
  if (isRadioPlaying.value) {
    audio.pause()
  } else {
    audio.play().catch(() => {
      isRadioPlaying.value = false
    })
  }
}

function selectRadio(station) {
  currentRadio.value = station
  const audio = getAudio()
  audio.src = station.streamUrl
  audio.play().catch(() => {
    isRadioPlaying.value = false
  })
}

function updateVolume(e) {
  const val = parseFloat(e.target.value)
  radioVolume.value = val
  if (audioElement) {
    audioElement.volume = isMuted.value ? 0 : val
  }
}

function toggleMute() {
  isMuted.value = !isMuted.value
  if (audioElement) {
    audioElement.volume = isMuted.value ? 0 : radioVolume.value
  }
}

const isAnyPlaying = computed(() => {
  return isRadioPlaying.value
})

const shouldShowMini = computed(() => {
  return !props.isOpen && isAnyPlaying.value
})

const shouldRender = computed(() => {
  return props.isOpen || shouldShowMini.value
})

function stopAllMusic() {
  if (audioElement) {
    audioElement.pause()
    isRadioPlaying.value = false
  }
}

onBeforeUnmount(() => {
  if (audioElement) {
    audioElement.pause()
    audioElement = null
  }
})
</script>

<template>
  <div v-show="shouldRender" class="music-root">
    <!-- Modal Backdrop View -->
    <div
      v-if="isOpen"
      class="music-backdrop"
      @click.self="emit('close')"
      aria-label="Close music overlay"
    >
      <div class="music-panel modal-view" aria-label="Music player modal">
        <!-- Header -->
        <div class="music-header">
          <div class="header-left">
            <button
              class="back-btn"
              title="Minimize to background"
              aria-label="Minimize music player"
              @click="emit('close')"
            >
              &lt;
            </button>
            <div class="title-wrap">
              <h2>Study Music</h2>
              <span class="music-badge">24/7 Lo-Fi & Ambient</span>
            </div>
          </div>

          <div class="header-actions">
            <button
              v-if="isAnyPlaying"
              class="stop-btn"
              title="Stop playback"
              @click="stopAllMusic"
            >
              <Square :size="14" />
              <span>Stop</span>
            </button>
            <button
              class="minimize-btn"
              title="Minimize to background mini-player"
              @click="emit('close')"
            >
              <Minimize2 :size="16" />
            </button>
          </div>
        </div>

        <div class="tab-content radio-tab">
          <div class="radio-card">
            <div class="radio-visual">
              <div class="radio-equalizer" :class="{ playing: isRadioPlaying }">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
              </div>
              <div class="radio-details">
                <h3>{{ currentRadio.name }}</h3>
                <p>{{ currentRadio.desc }}</p>
                <span class="status-tag" :class="{ live: isRadioPlaying }">
                  {{ isRadioPlaying ? '● LIVE STREAMING' : 'READY TO PLAY' }}
                </span>
              </div>
            </div>

            <div class="radio-controls">
              <button
                class="play-main-btn"
                :class="{ playing: isRadioPlaying }"
                @click="toggleRadio"
                :aria-label="isRadioPlaying ? 'Pause radio' : 'Play radio'"
              >
                <Pause v-if="isRadioPlaying" :size="24" />
                <Play v-else :size="24" />
                <span>{{ isRadioPlaying ? 'Pause Radio' : 'Play Radio' }}</span>
              </button>

              <div class="volume-slider-box">
                <button class="mute-btn" @click="toggleMute" title="Toggle mute">
                  <VolumeX v-if="isMuted || radioVolume === 0" :size="18" />
                  <Volume2 v-else :size="18" />
                </button>
                <input
                  type="range"
                  min="0"
                  max="1"
                  step="0.05"
                  :value="isMuted ? 0 : radioVolume"
                  @input="updateVolume"
                  class="volume-range"
                  aria-label="Volume slider"
                />
              </div>
            </div>
          </div>

          <!-- Station Selector -->
          <div class="stations-list">
            <span class="section-subtitle">Select Station:</span>
            <div class="station-grid">
              <button
                v-for="st in radioStations"
                :key="st.id"
                class="station-card-btn"
                :class="{ active: currentRadio.id === st.id }"
                @click="selectRadio(st)"
              >
                <div class="st-name">{{ st.name }}</div>
                <div class="st-desc">{{ st.desc }}</div>
              </button>
            </div>
          </div>

          <p class="radio-hint">
            ✨ Instant direct audio with zero ads, no video buffering, and no embedding restrictions.
          </p>
        </div>


        <div class="modal-footer-hint">
          <span>💡 Tip: Minimizing this window keeps your study music playing while you work!</span>
        </div>
      </div>
    </div>

    <!-- Floating Mini Player (when panel is minimized but audio is playing) -->
    <div
      v-else-if="shouldShowMini"
      class="music-panel mini-view"
      aria-label="Study music mini player"
    >
      <div class="mini-header" @click="emit('open')">
        <div class="mini-left">
          <div class="mini-equalizer" :class="{ playing: isAnyPlaying }" aria-hidden="true">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
          </div>
          <div class="mini-info">
            <span class="mini-title">Study Music</span>
            <span class="mini-subtitle">
              {{ currentRadio.name }}
            </span>
          </div>
        </div>

        <div class="mini-actions" @click.stop>
          <button
            class="mini-action-btn play-action"
            :title="isRadioPlaying ? 'Pause radio' : 'Play radio'"
            @click="toggleRadio"
          >
            <Pause v-if="isRadioPlaying" :size="15" />
            <Play v-else :size="15" />
          </button>
          <button
            class="mini-action-btn"
            title="Expand player"
            @click="emit('open')"
          >
            <Maximize2 :size="15" />
          </button>
          <button
            class="mini-action-btn close-action"
            title="Stop music"
            @click="stopAllMusic"
          >
            <X :size="15" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.music-root {
  position: relative;
}

/* Modal Backdrop */
.music-backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  background: rgba(35, 24, 20, 0.45);
  backdrop-filter: blur(5px);
  -webkit-backdrop-filter: blur(5px);
}

.music-panel.modal-view {
  width: min(720px, 94vw);
  max-height: 92vh;
  overflow-y: auto;
  padding: 24px;
  border: 1px solid rgba(120, 80, 48, 0.35);
  border-radius: 22px;
  background: #cbb298;
  box-shadow: 0 18px 36px rgba(58, 35, 20, 0.28);
  color: #4a3025;
}

/* Header */
.music-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.title-wrap {
  display: flex;
  align-items: baseline;
  gap: 10px;
  flex-wrap: wrap;
}

.music-header h2 {
  margin: 0;
  font-size: 1.6rem;
  font-weight: 700;
  color: #4a3025;
}

.music-badge {
  font-size: 0.72rem;
  padding: 3px 9px;
  border-radius: 999px;
  background: rgba(109, 69, 47, 0.16);
  color: #5e320f;
  font-weight: 600;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.back-btn,
.minimize-btn {
  width: 36px;
  height: 36px;
  border: 0;
  border-radius: 50%;
  background: #6d452f;
  color: #fff;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.back-btn:hover,
.minimize-btn:hover {
  background: #7a3d12;
  transform: scale(1.05);
}

.stop-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border: 1px solid rgba(138, 47, 37, 0.35);
  border-radius: 8px;
  background: #8a2f25;
  color: #fff;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.stop-btn:hover {
  background: #a1382c;
  transform: translateY(-1px);
}

/* Tabs */
.mode-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
  border-bottom: 2px solid rgba(109, 69, 47, 0.18);
  padding-bottom: 10px;
  flex-wrap: wrap;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border: 1px solid rgba(109, 69, 47, 0.25);
  border-radius: 12px;
  background: #f3e6d5;
  color: #4a3025;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn:hover {
  background: #e9d5bf;
}

.tab-btn.active {
  background: #6d452f;
  color: #fff;
  border-color: #6d452f;
  box-shadow: 0 2px 8px rgba(109, 69, 47, 0.3);
}

/* Radio Tab */
.radio-tab {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.radio-card {
  padding: 20px;
  border-radius: 16px;
  background: #ebd8c3;
  border: 1px solid rgba(109, 69, 47, 0.2);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.radio-visual {
  display: flex;
  align-items: center;
  gap: 18px;
}

.radio-equalizer {
  display: flex;
  align-items: flex-end;
  gap: 4px;
  height: 38px;
  padding: 6px 8px;
  background: #6d452f;
  border-radius: 10px;
}

.radio-equalizer .bar {
  width: 5px;
  background: #f3e6d5;
  border-radius: 3px;
  height: 25%;
}

.radio-equalizer.playing .bar {
  animation: barBounce 1s ease-in-out infinite alternate;
}

.radio-equalizer.playing .bar:nth-child(1) { animation-delay: 0.1s; height: 60%; }
.radio-equalizer.playing .bar:nth-child(2) { animation-delay: 0.3s; height: 100%; }
.radio-equalizer.playing .bar:nth-child(3) { animation-delay: 0.2s; height: 40%; }
.radio-equalizer.playing .bar:nth-child(4) { animation-delay: 0.4s; height: 80%; }
.radio-equalizer.playing .bar:nth-child(5) { animation-delay: 0.15s; height: 50%; }

@keyframes barBounce {
  0% { height: 20%; }
  100% { height: 100%; }
}

.radio-details h3 {
  margin: 0 0 4px;
  font-size: 1.18rem;
  color: #4a3025;
  font-weight: 700;
}

.radio-details p {
  margin: 0 0 6px;
  font-size: 0.85rem;
  color: #6d452f;
}

.status-tag {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  padding: 3px 8px;
  border-radius: 6px;
  background: rgba(109, 69, 47, 0.15);
  color: #5e320f;
}

.status-tag.live {
  background: #2e7d32;
  color: #fff;
  animation: pulseTag 2s infinite;
}

@keyframes pulseTag {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.75; }
}

.radio-controls {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}

.play-main-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 22px;
  border: 0;
  border-radius: 12px;
  background: #6d452f;
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.play-main-btn:hover {
  background: #7a3d12;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(109, 69, 47, 0.35);
}

.play-main-btn.playing {
  background: #8a2f25;
}

.volume-slider-box {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f3e6d5;
  padding: 6px 14px;
  border-radius: 10px;
  border: 1px solid rgba(109, 69, 47, 0.2);
}

.mute-btn {
  border: 0;
  background: transparent;
  color: #6d452f;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 0;
}

.volume-range {
  accent-color: #6d452f;
  cursor: pointer;
  width: 100px;
}

.stations-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.section-subtitle {
  font-size: 0.82rem;
  font-weight: 700;
  color: #5e320f;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.station-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 10px;
}

.station-card-btn {
  padding: 12px 14px;
  border: 1px solid rgba(109, 69, 47, 0.25);
  border-radius: 12px;
  background: #f3e6d5;
  color: #4a3025;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
}

.station-card-btn:hover {
  background: #e9d5bf;
  transform: translateY(-1px);
}

.station-card-btn.active {
  background: #6d452f;
  color: #fff;
  border-color: #6d452f;
  box-shadow: 0 3px 10px rgba(109, 69, 47, 0.25);
}

.station-card-btn .st-name {
  font-weight: 700;
  font-size: 0.92rem;
  margin-bottom: 2px;
}

.station-card-btn .st-desc {
  font-size: 0.78rem;
  opacity: 0.85;
}

.radio-hint {
  margin: 0;
  font-size: 0.84rem;
  color: #5e320f;
}

.modal-footer-hint {
  margin-top: 14px;
  font-size: 0.82rem;
  color: #5e320f;
  opacity: 0.9;
  text-align: center;
}

/* Mini Floating Player */
.music-panel.mini-view {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 45;
  width: min(320px, calc(100vw - 36px));
  padding: 12px 14px;
  border-radius: 16px;
  background: #cbb298;
  border: 1px solid rgba(120, 80, 48, 0.45);
  box-shadow: 0 12px 30px rgba(45, 25, 15, 0.38);
  color: #4a3025;
  animation: slideUp 0.25s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.mini-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}

.mini-left {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  flex: 1;
}

.mini-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.mini-title {
  font-size: 0.88rem;
  font-weight: 700;
  color: #4a3025;
  line-height: 1.2;
}

.mini-subtitle {
  font-size: 0.72rem;
  color: #6d452f;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 170px;
  font-weight: 500;
}

.mini-actions {
  display: flex;
  align-items: center;
  gap: 6px;
}

.mini-action-btn {
  width: 28px;
  height: 28px;
  border: 0;
  border-radius: 8px;
  background: #6d452f;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.mini-action-btn:hover {
  background: #7a3d12;
  transform: scale(1.08);
}

.mini-action-btn.play-action {
  background: #5e320f;
}

.mini-action-btn.close-action {
  background: rgba(138, 47, 37, 0.85);
}

.mini-action-btn.close-action:hover {
  background: #a1382c;
}

.mini-equalizer {
  display: flex;
  align-items: flex-end;
  gap: 2px;
  height: 14px;
  width: 14px;
  flex-shrink: 0;
}

.mini-equalizer .bar {
  width: 2.5px;
  background: #6d452f;
  border-radius: 2px;
  height: 30%;
}

.mini-equalizer.playing .bar {
  animation: barBounce 1s ease-in-out infinite alternate;
}

.mini-equalizer.playing .bar:nth-child(1) { animation-delay: 0.1s; height: 50%; }
.mini-equalizer.playing .bar:nth-child(2) { animation-delay: 0.3s; height: 100%; }
.mini-equalizer.playing .bar:nth-child(3) { animation-delay: 0.2s; height: 40%; }
.mini-equalizer.playing .bar:nth-child(4) { animation-delay: 0.4s; height: 80%; }

@media (max-width: 640px) {
  .music-panel.modal-view {
    padding: 18px 14px;
  }

  .music-panel.mini-view {
    bottom: 14px;
    right: 14px;
    width: calc(100vw - 28px);
  }
}
</style>
