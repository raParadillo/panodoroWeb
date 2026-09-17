<script setup>
import { onMounted, ref } from 'vue'
import { createTask, deleteTask, getTasks, updateTask } from '../../services/api'

const emit = defineEmits(['close'])

const currentView = ref('list') // 'list' or 'create'
const newListTitle = ref('')
const todoLists = ref([])

async function loadTasks() {
    try {
        todoLists.value = (await getTasks()).map(task => ({
            id: task.task_id,
            title: task.title,
            checked: task.is_checked,
        }))
    } catch {
        todoLists.value = []
    }
}

async function toggleTask(id) {
    const task = todoLists.value.find(item => item.id === id)
    if (task) {
        task.checked = !task.checked
        await updateTask(id, { is_checked: task.checked })
    }
}

async function removeTask(id) {
    await deleteTask(id)
    todoLists.value = todoLists.value.filter(item => item.id !== id)
}

async function handleAddList() {
    if (!newListTitle.value.trim()) return
    const task = await createTask({ title: newListTitle.value.trim() })
    todoLists.value.push({ id: task.task_id, title: task.title, checked: task.is_checked })
    newListTitle.value = ''
    currentView.value = 'list'
}

onMounted(loadTasks)
</script>

<template>
    <!-- List View Content Card -->
    <div v-if="currentView === 'list'" class="modal-card">
        <div class="card-header">
            <button class="icon-btn" @click="emit('close')" aria-label="Close to do list">&lt;</button>
            <span class="header-title">To do List</span>
            <button class="icon-btn" @click="currentView = 'create'" aria-label="Add">+</button>
        </div>
        <div class="card-content scrollable">
            <div v-for="item in todoLists" :key="item.id" class="todo-item" @click="toggleTask(item.id)">
                <div class="custom-checkbox" :class="{ 'is-checked': item.checked }">
                    <span v-if="item.checked" class="check-mark">✓</span>
                </div>
                <div class="todo-textbox">{{ item.title }}</div>
                <button
                    type="button"
                    class="remove-btn"
                    aria-label="Remove task"
                    title="Remove task"
                    @click.stop="removeTask(item.id)"
                >
                    ×
                </button>
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
    width: min(680px, calc(100vw - 2rem));
    background: #cca885;
    border-radius: 20px; /* Enhanced corners for a larger box scale */
    padding: 24px; /* Increased padding inside the container */
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    box-sizing: border-box;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
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
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: #80604d rgba(117, 79, 58, 0.14);
}

.card-content.scrollable::-webkit-scrollbar {
    width: 9px;
}

.card-content.scrollable::-webkit-scrollbar-thumb {
    background: linear-gradient(#9d765d, #72503f);
    border: 2px solid transparent;
    border-radius: 999px;
    background-clip: padding-box;
}

.card-content.scrollable::-webkit-scrollbar-track {
    background: rgba(117, 79, 58, 0.14);
    border-radius: 999px;
}

.todo-item {
    display: flex;
    align-items: center;
    gap: 12px; /* Increased item gap spacing */
    margin-bottom: 12px; /* Added line padding spacing */
    min-width: 0;
    cursor: pointer;
}

.todo-textbox {
    flex-grow: 1;
    min-width: 0;
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

.remove-btn {
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    border: 1px solid rgba(125, 92, 63, 0.24);
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.58);
    color: #7d5c3f;
    cursor: pointer;
    font-size: 1.35rem;
    line-height: 1;
    transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.remove-btn:hover {
    background: #8b4e3e;
    color: #fff;
    transform: scale(1.08);
}

.remove-btn:focus-visible {
    outline: 2px solid #7d5c3f;
    outline-offset: 2px;
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
