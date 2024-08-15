<?php

namespace App\Services;

use App\Events\ChatSent;
use App\Models\Message;
use App\Models\User;

class UserService
{
    public function getUser($user_id)
    {
        return User::where('id', $user_id)->first();
    }
    public function sendMessage($user_id, $message)
    {
        $data['sender_id'] = auth()->user()->id;
        $data['receiver_id'] = $user_id;
        $data['message'] = $message;
        Message::create($data);
        broadcast(new ChatSent($message, $this->getUser($user_id)));
    }
}