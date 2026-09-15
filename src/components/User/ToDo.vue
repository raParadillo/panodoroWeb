<script setup>
import { ref } from 'vue'

const currentView = ref('list') // 'list' or 'create'
const newListTitle = ref('')
const todoLists = ref([
    { id: 1, title: 'Study session tasks', checked: true },
    { id: 2, title: 'Buy groceries', checked: false },
    { id: 3, title: 'Read project documentation', checked: false },
    { id: 4, title: 'Fix CSS bugs', checked: false },
])

function toggleTask(id) {
    const task = todoLists.value.find(item => item.id === id)
    if (task) task.checked = !task.checked
}

function handleAddList() {
    if (!newListTitle.value.trim()) return
    todoLists.value.push({ id: Date.now(), title: newListTitle.value, checked: false })
    newListTitle.value = ''
    currentView.value = 'list'
}
</script>

<template>
    <!-- List View Content Card -->
    <div v-if="currentView === 'list'" class="modal-card">
        <div class="card-header">
            <button class="icon-btn" aria-label="Back">&lt;</button>
            <span class="header-title">To do List</span>
            <button class="icon-btn" @click="currentView = 'create'" aria-label="Add">+</button>
        </div>
        <div class="card-content scrollable">
            <div v-for="item in todoLists" :key="item.id" class="todo-item" @click="toggleTask(item.id)">
                <div class="custom-checkbox" :class="{ 'is-checked': item.checked }">
                    <span v-if="item.checked" class="check-mark">✓</span>
                </div>
                <div class="todo-textbox">{{ item.title }}</div>
            </div>
        </div>
    </div>

    <!-- Create Task View Content Card -->
    <div v-else-if="currentView === 'create'" class="modal-card">
        <div class="card-header">
            <button class="icon-btn" @click="currentView = 'list'" aria-label="Back">&lt;</button>
            <span class="header-title">New List</span>
        </div>
        <div class="card-content create-form">
            <textarea v-model="newListTitle" class="text-input-field" placeholder="Enter list item text..."
                rows="3"></textarea>
            <button class="submit-btn" @click="handleAddList">Add List</button>
        </div>
    </div>
</template>

<style scoped>
/* Core layout of the standalone capsule block */
.modal-card {
    width: 350px;
    background-color: #cca885;
    border-radius: 20px; /* Enhanced corners for a larger box scale */
    padding: 24px; /* Increased padding inside the container */
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    box-sizing: border-box;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px; /* Increased margin for scaled headers */
    color: #fff;
}

.header-title {
    font-weight: bold;
    font-size: 1.3rem; /* Scaled text size up from 0.95rem */
}

.icon-btn {
    background: #a8825e;
    border: none;
    color: white;
    width: 32px; /* Increased button dimensions from 24px */
    height: 32px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem; /* Scaled internal icons (+ and <) */
}

.card-content.scrollable {
    max-height: 280px; /* Increased maximum list viewport display height */
    overflow-y: auto;
}

.todo-item {
    display: flex;
    align-items: center;
    gap: 12px; /* Increased item gap spacing */
    margin-bottom: 12px; /* Added line padding spacing */
    cursor: pointer;
}

.todo-textbox {
    flex-grow: 1;
    background-color: #ffffff;
    border-radius: 24px; /* Rounded pill shapes proportional to layout size */
    height: 44px; /* Scaled vertical capsule height up from 32px */
    display: flex;
    align-items: center;
    padding: 0 18px; /* Increased internal pill text boundary padding */
    color: #555;
    font-size: 1.05rem; /* Scaled down list row text size up from 0.85rem */
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.custom-checkbox {
    width: 22px; /* Scaled checkbox size up from 18px */
    height: 22px;
    border: 2px solid #fff;
    border-radius: 20%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.custom-checkbox.is-checked {
    background-color: #a8825e;
    border-color: #a8825e;
}

.check-mark {
    color: white;
    font-size: 0.85rem; /* Scaled verification checkmark symbol */
}

.create-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.text-input-field {
    width: 100%;
    border: none;
    border-radius: 14px;
    padding: 14px;
    box-sizing: border-box;
    resize: none;
    font-size: 1.05rem; /* Scaled entry fields font text area */
    outline: none;
}

.submit-btn {
    background-color: #7d5c3f;
    color: white;
    border: none;
    padding: 10px 20px; /* Scaled dimensions button bounds wrapper spacing */
    border-radius: 24px;
    font-size: 0.95rem; /* Scaled submission typography fonts up from 0.8rem */
    align-self: flex-end;
    cursor: pointer;
    font-weight: bold;
}
</style>
