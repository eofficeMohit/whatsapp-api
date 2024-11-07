<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chatroom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'max_members_count'];

    // A chatroom has many members (users).
    public function members()
    {
        return $this->belongsToMany(User::class, 'chatroom_members')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }

    // A chatroom has many messages.
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
