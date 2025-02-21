<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use Illuminate\Http\Request;
use App\Jobs\SendReminderEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ReminderController extends Controller
{
    public function store(Request $request)
    {
        // $request->validate([
        //     'planning_id' => 'required|exists:plannings,id',
        //     'reminder_time' => 'required|date_format:Y-m-d H:i',
        // ]);

        $planning = Planning::findOrFail($request->planning_id);

        $planning->update(['reminder_time' => $request->reminder_time]);

        $reminderTime = Carbon::parse($request->reminder_time);
        $currentTime = Carbon::now();

        if ($reminderTime->lessThanOrEqualTo($currentTime)) {
            return response()->json(['message' => 'Reminder time must be in the future'], 400);
        }

        $delayInSeconds = $reminderTime->diffInSeconds($currentTime);

        \Log::info('Reminder scheduled', [
            'current_time' => $currentTime->toDateTimeString(),
            'reminder_time' => $reminderTime->toDateTimeString(),
            'delay_in_seconds' => $delayInSeconds,
        ]);
        
        SendReminderEmail::dispatch($planning)
            ->onQueue('reminder')
            ->delay($reminderTime);

        return response()->json(['message' => 'Reminder set successfully'], 201);
    }

}
