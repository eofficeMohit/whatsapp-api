<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Chatroom;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{

/**
     * @OA\Post(
     *     path="/api/messages",
     *     summary="Send a message to a chatroom",
     *     security={{"sanctum": {}}},
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
     *             required={"message_text", "chatroom_id"},
     *             @OA\Property(property="message_text", type="string", example="Hello, this is a message!"),
     *             @OA\Property(property="chatroom_id", type="integer", example=1),
     *             @OA\Property(property="attachment", type="string", format="binary", description="File attachment (optional)", example="path/to/file.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message sent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="chatroom_id", type="integer", example=1),
     *             @OA\Property(property="sender_user_id", type="integer", example=1),
     *             @OA\Property(property="message_text", type="text", example="Hello, this is a message!"),
     *             @OA\Property(property="attachment_path", type="string", example="pictures/3.jpg"),
     *             @OA\Property(property="attachment_type", type="string", example="image/jpeg"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-07T10:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-07T10:00:00Z")
     *  )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */


    public function send(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'message_text' => 'nullable|string|max:50000',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,gif,bmp,svg,mp4,webm,mov,avi,mkv,mp3,wav,ogg,flac|max:102400', //100mb No limit size
            'chatroom_id' => 'required|exists:chatrooms,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true,'status' => 422,'message'=>$validator->errors()]);
        }

        $chatroom = Chatroom::findOrFail($request->chatroom_id);
        $user = Auth::user();

        // Save the attachment if it exists
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {


            $attachment = $request->file('attachment');
            $attachmentType = $attachment->getMimeType();
            $extension = $attachment->getClientOriginalExtension();

             if($attachment->getMimeType() === 'video/mp4' || $attachment->getMimeType() === 'video/webm'){
                $attachmentPath = $attachment->storeAs('videos', $attachment->getClientOriginalName());
             }else{
                $attachmentPath = $attachment->storeAs('pictures', $attachment->getClientOriginalName());
             }
        }

        $messageData = Message::create([
            'chatroom_id' => $request->chatroom_id,
            'sender_user_id' => $user->id,
            'message_text' => $request->input('message_text'),
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType ?? null,
        ]);

        // Trigger WebSocket message broadcast
        broadcast(new MessageSent($messageData,$user));

        return response()->json(['error' => false,'status' => 200,'message' =>"Message sent successfully.",'data' => $messageData]);
    }



    /**
     * @OA\Get(
     *     path="/api/messages/{chatroom_id}",
     *     summary="List all messages in a chatroom",
     *     security={{"sanctum": {}}},
     *     tags={"Messages"},
     *     @OA\Parameter(
     *         name="chatroom_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of messages in a chatroom",
     *         @OA\JsonContent(
     *             type="array",
     *            @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                   @OA\Property(property="chatroom_id", type="integer", example=1),
     *                   @OA\Property(property="sender_user_id", type="integer", example=1),
     *                   @OA\Property(property="message_text", type="text", example="Hello, this is a message!"),
     *                   @OA\Property(property="attachment_path", type="string", example="pictures/3.jpg"),
     *                   @OA\Property(property="attachment_type", type="string", example="image/jpeg"),
     *                   @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-07T10:00:00Z"),
     *                   @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-07T10:00:00Z")
     *              )
     *         )
     *     ),
     *       @OA\Response(
     *         response=401,
     *         description="Invalid chatroom."
     *     )
     * )
     */

    // List messages in a chatroom
    public function list($chatroomId)
    {
        if(Chatroom::where('id', $chatroomId)->exists()) {
            $messagesData = Message::where('chatroom_id', $chatroomId)->orderBy('created_at', 'desc')
            ->paginate(5);
            return response()->json(['error' => false,'status' => 200,'message'=>"Messages fetch successfully.",'data' => $messagesData]);
        }else{
            return response()->json(['error' => true,'status' => 401,'message' => 'Invalid chatroom.']);

        }
    }


    public function show()
    {
        return view('chat');
    }
}
