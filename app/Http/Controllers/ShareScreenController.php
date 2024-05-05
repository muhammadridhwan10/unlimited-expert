<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

class ShareScreenController extends Controller
{
    public function index(Request $request)
    {
        return view('share-screen.index');
    }

    public function createRoom(Request $request)
    {
        $request->validate([
            'room_id' => 'required',
        ]);

        $room = new Room();
        $room->room_id      = $request->room_id;
        $room->created_by   = \Auth::user()->id;
        $room->save();

        return response()->json(['success' => true, 'message' => 'Room created successfully!']);
    }

    public function joinRoom(Request $request)
    {
        $request->validate([
            'room_id' => 'required',
        ]);

        $room = Room::where('room_id', $request->room_id)->first();

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room ID not found.']);
        }

        $room->user_join = \Auth::user()->id;
        $room->save();

        return response()->json(['success' => true, 'message' => 'Successfully joined the room!']);
    }
}
