<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Info(
 *     title="WhatsApp API",
 *     version="1.0.0",
 *     description="This is an API documentation for the WhatsApp-like chat application",
 * )
 */
class AuthController extends Controller
{

   /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-07T10:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-07T10:00:00Z"),
     *             @OA\Property(property="access_token", type="string", example="your-jwt-token"),
     *             @OA\Property(property="token_type", type="string", example="bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */

    // Register a new user
    public function register(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true,'status' => 422,'message'=>$validator->errors()]);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate API token using Sanctum
        $token = $user->createToken('MyAppToken')->plainTextToken;

        return response()->json([
            'error' => false,
            'status' => 200,
            'user' => $user,
            'token' => $token,
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login a user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *         @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-07T10:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-07T10:00:00Z"),
     *             @OA\Property(property="access_token", type="string", example="your-jwt-token"),
     *             @OA\Property(property="token_type", type="string", example="bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */

    // Login a user
    public function login(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true,'status' => 422,'message'=>$validator->errors()]);
        }

        // Attempt to find the user
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => true,'status' => 401,'message' => 'Invalid credentials']);
        }

        // Generate API token
        $token = $user->createToken('MyAppToken')->plainTextToken;

        return response()->json([
            'error' => false,
            'message' =>'User logged in successfully,',
            'status' => 200,
            'user' => $user,
            'token' => $token,
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get authenticated user data",
     *     security={{"sanctum": {}}},
     *     tags={"Authenticated"},
     *     @OA\Response(
     *         response=200,
     *         description="Get authenticated user data",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="General Chat"),
     *                 @OA\Property(property="email", type="string", example="example@example.com"),
     *                 @OA\Property(property="email_verified_at", type="string",format="date-time", example="2024-11-07T10:00:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-07T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-07T10:00:00Z")
     *             )
     *         )
     *     )
     * )
     */

    // Get authenticated user data
    public function user(Request $request)
    {
        return response()->json(['error' => false,'status' => 200,'message'=>'Get authenticated user data','user'=>$request->user()]);
    }
}
