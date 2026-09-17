<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Note;
use App\Models\StudyLog;
use App\Models\Task;
use App\Models\TimerSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductivityController extends Controller
{
    public function settings(Request $request): JsonResponse
    {
        $settings = TimerSetting::firstOrCreate(
            ['user_id' => $request->user()->user_id],
            ['study_minutes' => 25, 'break_minutes' => 5, 'total_loops' => 4]
        );

        return response()->json($settings);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'study_minutes' => ['required', 'integer', 'between:1,120'],
            'break_minutes' => ['required', 'integer', 'between:1,60'],
            'total_loops' => ['required', 'integer', 'between:1,12'],
        ]);

        $settings = TimerSetting::updateOrCreate(
            ['user_id' => $request->user()->user_id],
            [...$validated, 'updated_at' => now()]
        );

        return response()->json($settings);
    }

    public function addStudyTime(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_date' => ['sometimes', 'date'],
            'seconds_studied' => ['required', 'integer', 'min:1'],
        ]);
        $date = $validated['session_date'] ?? now()->toDateString();

        $log = StudyLog::updateOrCreate(
            ['user_id' => $request->user()->user_id, 'session_date' => $date],
            []
        );
        $log->increment('seconds_studied', $validated['seconds_studied']);
        $log->refresh();
        $this->recordActivity(
            $request,
            'study_time_added',
            'Studied for '.$validated['seconds_studied'].' seconds'
        );

        return response()->json($log);
    }

    public function analytics(Request $request): JsonResponse
    {
        $userId = $request->user()->user_id;
        $today = now()->toDateString();
        $total = StudyLog::where('user_id', $userId)->sum('seconds_studied');
        $todayTotal = StudyLog::where('user_id', $userId)->whereDate('session_date', $today)->sum('seconds_studied');
        $daily = StudyLog::where('user_id', $userId)
            ->orderByDesc('session_date')
            ->limit(30)
            ->get(['session_date', 'seconds_studied']);

        return response()->json([
            'total_study_seconds' => (int) $total,
            'today_study_seconds' => (int) $todayTotal,
            'daily' => $daily,
        ]);
    }

    public function notes(Request $request): JsonResponse
    {
        return response()->json(Note::where('user_id', $request->user()->user_id)->latest('updated_at')->get());
    }

    public function createNote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string'],
        ]);
        $note = Note::create([...$validated, 'user_id' => $request->user()->user_id]);
        $this->recordActivity($request, 'note_created', 'Created a note', $note->note_id);

        return response()->json($note, 201);
    }

    public function updateNote(Request $request, Note $note): JsonResponse
    {
        $this->ensureOwner($request, $note->user_id);
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string'],
        ]);
        $note->update($validated);
        $this->recordActivity($request, 'note_updated', 'Updated a note', $note->note_id);

        return response()->json($note->fresh());
    }

    public function deleteNote(Request $request, Note $note): JsonResponse
    {
        $this->ensureOwner($request, $note->user_id);
        $noteId = $note->note_id;
        $note->delete();
        $this->recordActivity($request, 'note_deleted', 'Deleted a note', $noteId);

        return response()->json(['message' => 'Note deleted.']);
    }

    public function tasks(Request $request): JsonResponse
    {
        return response()->json(Task::where('user_id', $request->user()->user_id)->latest('created_at')->get());
    }

    public function createTask(Request $request): JsonResponse
    {
        $validated = $request->validate(['title' => ['required', 'string', 'max:255']]);
        $task = Task::create([...$validated, 'user_id' => $request->user()->user_id]);
        $this->recordActivity($request, 'task_created', 'Added a new task', $task->task_id);

        return response()->json($task, 201);
    }

    public function updateTask(Request $request, Task $task): JsonResponse
    {
        $this->ensureOwner($request, $task->user_id);
        $validated = $request->validate(['is_checked' => ['required', 'boolean']]);
        $task->update($validated);
        $this->recordActivity($request, 'task_updated', $validated['is_checked'] ? 'Completed a task' : 'Reopened a task', $task->task_id);

        return response()->json($task->fresh());
    }

    public function deleteTask(Request $request, Task $task): JsonResponse
    {
        $this->ensureOwner($request, $task->user_id);
        $taskId = $task->task_id;
        $task->delete();
        $this->recordActivity($request, 'task_deleted', 'Removed a task', $taskId);

        return response()->json(['message' => 'Task deleted.']);
    }

    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'between:10,50'],
        ]);

        return response()->json(
            ActivityLog::where('user_id', $request->user()->user_id)
                ->latest('created_at')
                ->paginate($validated['per_page'] ?? 20)
        );
    }

    private function recordActivity(Request $request, string $type, string $description, ?int $relatedId = null): void
    {
        ActivityLog::create([
            'user_id' => $request->user()->user_id,
            'action_type' => $type,
            'description' => $description,
            'related_id' => $relatedId,
            'created_at' => now(),
        ]);
    }

    private function ensureOwner(Request $request, int $ownerId): void
    {
        abort_unless($request->user()->user_id === $ownerId, 404);
    }
}
