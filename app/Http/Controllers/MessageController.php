<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Events\ChatSent;
use App\Models\User;

class MessageController extends Controller
{
    public function index()
    {
        $users = User::all()->except(auth()->user()->id);
        return view('index', compact('users'));
    }
    public function chatForm($id, UserService $userService)
    {
        $receiver = $userService->getUser($id);
        $messages = Message::where(function ($query) use ($id) {
            $query->where('sender_id', auth()->user()->id)
                ->where('receiver_id', $id);
        })->orWhere(function ($query) use ($id) {
            $query->where('sender_id', $id)
                ->where('receiver_id', auth()->user()->id);
        })->with('sender')->get();
        $users = User::all()->except(auth()->user()->id);
        return view('chat', compact('id', 'receiver', 'messages', 'users'));
    }

    public function sendMessage(Request $request, $id, UserService $userService)
    {
        $receiver = $userService->getUser($id);

        $message = new Message();
        $message->sender_id = auth()->user()->id;
        $message->receiver_id = $id;
        $message->message = $request->message;
        $message->save();

        broadcast(new ChatSent($message))->toOthers();

        return response()->json(['status' => 'Message sent']);
    }
    public function fetchMessages($id)
    {
        $messages = Message::where(function ($query) use ($id) {
            $query->where('sender_id', auth()->user()->id)
                ->where('receiver_id', $id);
        })->orWhere(function ($query) use ($id) {
            $query->where('sender_id', $id)
                ->where('receiver_id', auth()->user()->id);
        })->with('sender')->get();

        return response()->json([
            'messages' => $messages
        ]);
    }
}
