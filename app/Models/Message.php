<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['chatroom_id', 'sender_user_id', 'message_text', 'attachment_type', 'attachment_path'];

    // A message belongs to a chatroom.
    public function chatroom()
    {
        return $this->belongsTo(Chatroom::class);
    }

    // A message is sent by a user.
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
