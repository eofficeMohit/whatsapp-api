<?php
namespace App\Events;

use App\Models\Chatroom;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserJoinedChatroom implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatroom;
    public $user;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Chatroom $chatroom
     * @param \App\Models\User $user
     * @return void
     */
    public function __construct(Chatroom $chatroom, User $user)
    {
        $this->chatroom = $chatroom;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('chatroom.' . $this->chatroom->id);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'user' => $this->user->name,
            'user_id' => $this->user->id,
            'joined_at' => now()->toDateTimeString(),
        ];
    }
}
