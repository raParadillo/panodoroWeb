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
                    <div class="note-preview">{{ note.content }}</div>
                    <div class="paper-fold"></div>
                </div>
            </div>
        </div>

        <!-- VIEW 2: Edit/Create View -->
        <div v-else class="note-editor-container">
            <textarea v-model="currentContent" class="note-textarea" placeholder="Type your notes here..."></textarea>

            <div class="editor-actions">
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
const currentContent = ref('');

// Reactive store for notes
const notes = ref([
    { id: 1, content: 'Recipe ideas for next weekend...' },
    { id: 2, content: 'Pomodoro technique strategy guidelines' },
    { id: 3, content: 'Project layout checklist & notes' }
]);

function createNewNote() {
    selectedNoteId.value = null;
    currentContent.value = '';
    activeView.value = 'edit';
}

function openNote(note) {
    selectedNoteId.value = note.id;
    currentContent.value = note.content;
    activeView.value = 'edit';
}

function saveNote() {
    if (!currentContent.value.trim()) {
        activeView.value = 'grid';
        return;
    }

    if (selectedNoteId.value !== null) {
        // Update existing note
        const note = notes.value.find(n => n.id === selectedNoteId.value);
        if (note) note.content = currentContent.value;
    } else {
        // Save new note
        notes.value.push({
            id: Date.now(),
            content: currentContent.value
        });
    }

    activeView.value = 'grid';
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
    background-color: #cbb298;
    /* Warm beige tone matching design */
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    color: #5d4037;
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
    width: 6px;
}

.notes-grid-container::-webkit-scrollbar-thumb,
.note-textarea::-webkit-scrollbar-thumb {
    background-color: #a88d75;
    border-radius: 4px;
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
    justify-content: flex-end;
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