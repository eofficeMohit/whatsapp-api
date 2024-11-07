<?php
namespace App\Http\Controllers;

use App\Models\Chatroom;
use App\Models\ChatroomMember;
use App\Events\UserJoinedChatroom;
use App\Events\UserLeftChatroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatroomController extends Controller
{

     /**
     * @OA\Post(
     *     path="/api/chatrooms",
     *     summary="Create a new chatroom",
     *     tags={"Chatroom"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "user_limit"},
     *             @OA\Property(property="name", type="string", example="General Chat"),
     *             @OA\Property(property="user_limit", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="General Chat"),
     *             @OA\Property(property="user_limit", type="integer", example=100)
     *         )
     *     )
     * )
     */

    // Create a new chatroom
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'max_members_count' => 'required|integer|min:10',
        ]);
        $chatroom = Chatroom::create($request->only(['name', 'max_members_count']));

        return response()->json(['error' => false,'status' => 200,'chatroom' => $chatroom]);
    }


    /**
     * @OA\Get(
     *     path="/api/chatrooms",
     *     summary="List all chatrooms",
     *     tags={"Chatroom"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of chatrooms",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="General Chat"),
     *                 @OA\Property(property="user_limit", type="integer", example=100)
     *             )
     *         )
     *     )
     * )
     */

    // List all chatrooms
    public function index()
    {
        $chatrooms = Chatroom::orderBy('created_at', 'desc')->paginate(20);
        return response()->json(['error' => false,'status' => 200,'chatrooms' => $chatrooms]);
    }



    /**
     * @OA\Get(
     *     path="/api/chatrooms/{chatroomId}/join",
     *     summary="User enters a chatroom",
     *     tags={"Chatroom"},
     *     @OA\Response(
     *         response=200,
     *         description="User enters a chatroom",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="General Chat"),
     *                 @OA\Property(property="user_limit", type="integer", example=100)
     *             )
     *         )
     *     )
     * )
     */

    // User enters a chatroom
    public function join($chatroomId)
    {
        if(Chatroom::where('id', $chatroomId)->exists()) {
            $chatroom = Chatroom::findOrFail($chatroomId);
            $user = Auth::user();

            if ($chatroom->members()->count() >= $chatroom->max_members_count) {
                return response()->json(['error' => false,'status' => 400,'message' => 'Chatroom is full']);
            }

            $chatroom->members()->attach($user->id);

            // Trigger websocket connection
            broadcast(new UserJoinedChatroom($chatroom,$user));

            return response()->json(['error' => false,'status' => 200,'message' => 'Joined chatroom successfully','user'=>$user]);
        }else{
            return response()->json(['error' => true,'status' => 401,'message' => 'Invalid chatroom.']);

        }
    }



    /**
     * @OA\Get(
     *     path="/api/chatrooms/{chatroomId}/leave",
     *     summary="User leaves a chatroom",
     *     tags={"Chatroom"},
     *     @OA\Response(
     *         response=200,
     *         description="Left chatroom successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="chatroomId", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="General Chat"),
     *                 @OA\Property(property="user_limit", type="integer", example=100)
     *             )
     *         )
     *     )
     * )
     */

    // User leaves a chatroom
    public function leave($chatroomId)
    {
        if(Chatroom::where('id', $chatroomId)->exists()) {

            $chatroom = Chatroom::findOrFail($chatroomId);
            $user = Auth::user();

            $chatroom->members()->detach($user->id);
            // Trigger websocket disconnection
            broadcast(new UserLeftChatroom($chatroom,$user));

            return response()->json(['error' => false,'status' => 200,'message' => 'Left chatroom successfully','user'=>$user]);
        }else{
            return response()->json(['error' => true,'status' => 401,'message' => 'Invalid chatroom.']);

        }
    }
}
