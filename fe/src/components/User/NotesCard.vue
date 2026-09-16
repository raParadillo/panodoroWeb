<template>
    <div class="notes-card">
        <!-- Header Controls -->
        <div class="notes-header">
            <button class="back-btn" @click="handleBack">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                <span v-if="activeView === 'grid'">Notes</span>
            </button>
        </div>

        <!-- VIEW 1: Grid View (Notes List + Add Button) -->
        <div v-if="activeView === 'grid'" class="notes-grid-container">
            <div class="notes-grid">
                <!-- Create New Note Card -->
                <button class="note-card add-card" @click="createNewNote">
                    <span class="plus-icon">+</span>
                </button>

                <!-- Existing Notes Cards -->
                <div v-for="note in notes" :key="note.id" class="note-card paper-card" @click="openNote(note)">
                    <div class="note-title">{{ note.title }}</div>
                    <div class="note-preview">{{ note.content }}</div>
                    <div class="paper-fold"></div>
                </div>
            </div>
        </div>

        <!-- VIEW 2: Edit/Create View -->
        <div v-else class="note-editor-container">
            <input v-model="currentTitle" class="note-title-input" type="text" placeholder="Note title" maxlength="80" />
            <textarea v-model="currentContent" class="note-textarea" placeholder="Type your notes here..."></textarea>

            <div class="editor-actions">
                <button
                    v-if="selectedNoteId !== null"
                    type="button"
                    class="remove-note-btn"
                    @click="removeNote(selectedNoteId)"
                >
                    Remove
                </button>
                <button class="save-btn" @click="saveNote">Save</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const emit = defineEmits(['close']);

// Views: 'grid' or 'edit'
const activeView = ref('grid');
const selectedNoteId = ref(null);
const currentTitle = ref('');
const currentContent = ref('');

// Reactive store for notes
const notes = ref([
    { id: 1, title: 'Weekend recipes', content: 'Recipe ideas for next weekend...' },
    { id: 2, title: 'Pomodoro strategy', content: 'Pomodoro technique strategy guidelines' },
    { id: 3, title: 'Project checklist', content: 'Project layout checklist & notes' }
]);

function createNewNote() {
    selectedNoteId.value = null;
    currentTitle.value = '';
    currentContent.value = '';
    activeView.value = 'edit';
}

function openNote(note) {
    selectedNoteId.value = note.id;
    currentTitle.value = note.title;
    currentContent.value = note.content;
    activeView.value = 'edit';
}

function saveNote() {
    if (!currentTitle.value.trim() && !currentContent.value.trim()) {
        activeView.value = 'grid';
        return;
    }

    const title = currentTitle.value.trim() || 'Untitled note';

    if (selectedNoteId.value !== null) {
        // Update existing note
        const note = notes.value.find(n => n.id === selectedNoteId.value);
        if (note) {
            note.title = title;
            note.content = currentContent.value;
        }
    } else {
        // Save new note
        notes.value.push({
            id: Date.now(),
            title,
            content: currentContent.value
        });
    }

    activeView.value = 'grid';
}

function removeNote(noteId) {
    notes.value = notes.value.filter(note => note.id !== noteId);
    if (selectedNoteId.value === noteId) {
        selectedNoteId.value = null;
        currentTitle.value = '';
        currentContent.value = '';
        activeView.value = 'grid';
    }
}

function handleBack() {
    if (activeView.value === 'edit') {
        activeView.value = 'grid';
    } else {
        emit('close');
    }
}
</script>

<style scoped>
.notes-card {
    width: 580px;
    height: 360px;
    background: rgba(203, 178, 152, 0.82);
    /* Warm beige tone matching design */
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    color: #5d4037;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

/* Header */
.notes-header {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
}

.back-btn {
    background: transparent;
    border: none;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
}

/* Grid View */
.notes-grid-container {
    flex: 1;
    overflow-y: auto;
    padding-right: 6px;
}

/* Custom Scrollbar for warm aesthetic */
.notes-grid-container::-webkit-scrollbar,
.note-textarea::-webkit-scrollbar {
    width: 9px;
}

.notes-grid-container::-webkit-scrollbar-thumb,
.note-textarea::-webkit-scrollbar-thumb {
    background: linear-gradient(#9d765d, #72503f);
    border: 2px solid transparent;
    border-radius: 999px;
    background-clip: padding-box;
}

.notes-grid-container::-webkit-scrollbar-track,
.note-textarea::-webkit-scrollbar-track {
    background: rgba(117, 79, 58, 0.14);
    border-radius: 999px;
}

.notes-grid-container,
.note-textarea {
    scrollbar-width: thin;
    scrollbar-color: #80604d rgba(117, 79, 58, 0.14);
}

.notes-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.note-card {
    height: 120px;
    border-radius: 12px;
    border: none;
    position: relative;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.note-card:hover {
    transform: translateY(-2px);
}

.add-card {
    background-color: #98785d;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.plus-icon {
    font-size: 2.5rem;
    font-weight: 300;
}

.paper-card {
    background-color: #ffffff;
    padding: 12px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.note-preview {
    font-size: 0.8rem;
    color: #4a3b32;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 5;
    line-clamp: 5;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.note-title {
    margin: 0 26px 6px 0;
    color: #3e2723;
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1.15;
    letter-spacing: 0.01em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.remove-note-btn {
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #8f3328;
    padding: 8px 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.15s ease, color 0.15s ease;
}

.remove-note-btn:hover {
    background: rgba(163, 58, 43, 0.12);
    color: #a33a2b;
}

.remove-note-btn:focus-visible {
    outline: 2px solid #5d4037;
    outline-offset: 2px;
}

/* Page Dog-ear Fold Accent */
.paper-fold {
    position: absolute;
    top: 0;
    right: 0;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 14px 14px 0;
    border-color: transparent #cbb298 transparent transparent;
}

/* Editor View */
.note-editor-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.note-title-input {
    width: 100%;
    background-color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 12px 16px;
    color: #3e2723;
    font-size: 1.05rem;
    font-weight: 700;
    outline: none;
    box-sizing: border-box;
    font-family: inherit;
}

.note-title-input::placeholder {
    color: #9b8175;
}

.note-textarea {
    width: 100%;
    flex: 1;
    background-color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 16px;
    font-size: 0.95rem;
    color: #3e2723;
    resize: none;
    outline: none;
    box-sizing: border-box;
    font-family: inherit;
}

.editor-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.save-btn {
    background-color: #8d6e63;
    color: #ffffff;
    border: none;
    padding: 8px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
}

.save-btn:hover {
    background-color: #6d4c41;
}
</style>