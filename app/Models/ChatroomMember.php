<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatroomMember extends Model
{
    use HasFactory;

    protected $table = 'chatroom_members';  // Specify table name

    protected $fillable = ['chatroom_id', 'user_id', 'joined_at'];

    // A chatroom member refers to a user and a chatroom.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatroom()
    {
        return $this->belongsTo(Chatroom::class);
    }
}
