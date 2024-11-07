<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Chatroom;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{

/**
     * @OA\Post(
     *     path="/api/chatrooms/{chatroomId}/messages",
     *     summary="Send a message to a chatroom",
     *     tags={"Messages"},
     *     @OA\Parameter(
     *         name="chatroomId",
     *         in="path",
     *         required=true,
     *         description="The ID of the chatroom where the message will be sent",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content", "type"},
     *             @OA\Property(property="content", type="string", example="Hello, this is a message!"),
     *             @OA\Property(property="type", type="string", enum={"text", "attachment"}, example="text"),
     *             @OA\Property(property="attachment", type="string", format="binary", description="File attachment (optional)", example="path/to/file.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="chatroom_id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="content", type="string", example="Hello, this is a message!"),
     *             @OA\Property(property="type", type="string", example="text"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-07T10:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */

    // Send a message
    public function send(Request $request)
    {
        $request->validate([
            'message_text' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|max:10000', // No limit size
            'chatroom_id' => 'required|exists:chatrooms,id',
        ]);

        $chatroom = Chatroom::findOrFail($request->chatroom_id);
        $user = Auth::user();

        // Save the attachment if it exists
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $attachmentType = $attachment->getMimeType();
            // $attachmentPath = $attachment->store('attachments', 'local');
           // $attachmentPath = $attachment->storeAs('pictures', $attachment->getClientOriginalName(), 'local');
             $extension = $attachment->getClientOriginalExtension();
             $attachmentPath = $attachment->storeAs('/' . ($attachment->getMimeType() === 'video/mp4' ? 'video' : 'picture'), uniqid() . '.' . $extension);
        }

        $message = Message::create([
            'chatroom_id' => $request->chatroom_id,
            'sender_user_id' => $user->id ?? 1,
            'message_text' => $request->input('message_text'),
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType ?? null,
        ]);

        // Trigger WebSocket message broadcast
        broadcast(new MessageSent($message,$user));

        // return response()->json($message, 200);

        return response()->json(['error' => false,'status' => 200,'message' => $message,'user' => $user]);
    }


    /**
     * @OA\Get(
     *     path="/api/chatrooms/{chatroomId}/messages",
     *     summary="Get messages from a chatroom",
     *     tags={"Messages"},
     *     @OA\Parameter(
     *         name="chatroomId",
     *         in="path",
     *         required=true,
     *         description="The ID of the chatroom",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of messages in the chatroom",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 properties={
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="content", type="string", example="Hello, this is a message!"),
     *                     @OA\Property(property="type", type="string", example="text"),
     *                     @OA\Property(property="attachment", type="string", example="path/to/attachment.jpg"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-07T10:00:00Z")
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chatroom not found"
     *     )
     * )
     */

    // List messages in a chatroom
    public function list($chatroomId)
    {
        $messages = Message::where('chatroom_id', $chatroomId)->orderBy('created_at', 'desc')
        ->paginate(20);
        return response()->json(['error' => false,'status' => 200,'messages' => $messages]);
    }


    public function show()
    {
        return view('chat');
    }
}
