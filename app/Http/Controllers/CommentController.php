<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Notification;
use App\Models\ProjectUser;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'text' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx|max:2048',
        ]);

        $project = Project::findOrFail($request->project_id);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $path = $request->file('file')->storeAs(
                "uploads/project/{$project->project_name}/document",
                $fileNameToStore,
                's3'
            );

            $filePath = Storage::disk('s3')->url($path);
        }

        $comment = Comment::create([
            'project_id' => $request->project_id,
            'user_id' => Auth::id(),
            'text' => $request->text,
            'file_path' => $filePath,
        ]);

        $projectUsers = ProjectUser::where('project_id', $request->project_id)
                          ->where('user_id', '!=', Auth::id())
                          ->pluck('user_id');

        foreach($projectUsers as $userId) {
            Notification::createNotification(
                $userId,
                'project_comment',
                [
                    'project_id' => $project->id,
                    'project_name' => $project->project_name,
                    'comment_text' => Str::limit($request->text, 50),
                    'updated_by' => Auth::id()
                ],
                Notification::PRIORITY_LOW
            );
        }

        return response()->json($comment, 201);
    }

    public function index($projectId)
    {
        $comments = Comment::where('project_id', $projectId)
            ->with(['user']) 
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }
}
