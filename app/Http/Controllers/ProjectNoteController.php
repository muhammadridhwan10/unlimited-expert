<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProjectNote;
use Illuminate\Support\Facades\Auth;

class ProjectNoteController extends Controller
{

    public function index($projectId)
    {
        $users = User::whereHas('projects', function ($query) use ($projectId) {
            $query->where('project_id', $projectId); 
        })->with(['notes' => function ($query) use ($projectId) {
            $query->where('project_id', $projectId)
                ->orderBy('created_at', 'desc'); 
        }])->get();

        return response()->json($users);
    }

    public function store(Request $request, $projectId)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $note = new ProjectNote();
        $note->project_id = $projectId;
        $note->user_id = Auth::id();
        $note->content = $validated['content'];
        $note->save();

        return response()->json($note, 201);
    }

    public function updateStatus($id)
    {
        $note = ProjectNote::findOrFail($id);
        $note->is_completed = !$note->is_completed;
        $note->save();

        return response()->json($note);
    }

    public function destroy($id)
    {
        $note = ProjectNote::findOrFail($id);
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }
}
