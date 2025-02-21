<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanningController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        Planning::create([
            'project_id' => $request->project_id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json(['message' => 'Planning added successfully'], 201);
    }

    public function index($projectId)
    {
        $plannings = Planning::where('project_id', $projectId)
            ->with(['user'])
            ->get()
            ->map(function ($planning) {
                return [
                    'id' => $planning->id,
                    'title' => $planning->title,
                    'start' => $planning->start_date,
                    'end' => $planning->end_date,
                    'allDay' => true,
                    'user' => [
                        'id' => $planning->user->id,
                        'name' => $planning->user->name,
                        'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($planning->user->name) . '&background=random',
                    ],
                ];
            });

        return response()->json($plannings);
    }

    public function destroy($id)
    {
        $planning = Planning::findOrFail($id);
        $planning->delete();

        return response()->json(['message' => 'Planning deleted successfully'], 200);
    }
}
