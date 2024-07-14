<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Contracts\Providers\Auth;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="API Endpoints for managing user state"
 * )
 */
class AuthController extends Controller
{
    use HttpResponses;
 
     /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         ),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *              type="object",
     *              required={"name","email","password","password_confirmation"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *            )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Registration successful"
     *                 ),
     *                 @OA\Property(
     *                     property="user",
     *                     ref="#/components/schemas/User"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="User registration failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registration failed")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:255',
            'email' => 'bail|required|email|unique:users,email',
            'password' => 'bail|required|string|confirmed|min:6',
        ]);

       if ($validator->fails()) {
            return $this->error($validator->errors()->toJson(), 400);
       }
       try {
        //code...
            $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
            ));
    
            $response = [
            "message" => "Registration successful",
            "user" => $user
            ];
    
            return $this->success($response);
       } catch (\Throwable $exception) {
            Log::error([
                "message" => $exception->getMessage(),
                "controller_action" => "AuthController@register",
                "line" => 45
            ]);
            return $this->error('User registration failed', 500);
       }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login a user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600),
     *                 @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized user attempt")
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        Log::info(["message" => "Validate login credentials"]);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        
        if ($validator->fails()) {
            Log::warning(["message" => "Validation Failed"]);
            return $this->error($validator->errors()->toJson(), 400);
        }

        try {
            if (! $token = auth()->attempt($validator->validated())) {
                Log::warning(["message" => "Unauthorized user attempt"]);
                return $this->error('Unauthorized user attempt', 401);
            }

            Log::info(["message" => "Login Successful"]);
            return $this->respondWithToken($token);
        } catch (\Throwable $exception) {
            Log::error([
                "message" => $exception->getMessage(),
                "controller_action" => "AuthController@login",
                "line" => 74
            ]);
            return $this->error('Server Error', 503);
        }
  
    }

    /**
     * @OA\Get(
     *     path="/api/auth",
     *     summary="Check if user is logged in",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User is logged in",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600),
     *                 @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token is invalid or expired",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token is invalid or expired")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function isLoggedIn(Request $request)
    {
        try {
            if (!$user = auth()->user()) {
                return $this->error('User not found', 404);
            }

            $token = $request->bearerToken();
            Log::info($token);

        } catch (JWTException $e) {
            return $this->error('Token is invalid or expired', 401);
        }

        return $this->respondWithToken($token);
    }

     /**
     * @OA\Get(
     *     path="/api/signout",
     *     summary="Logout a user",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Signout Successful",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Signout Successful")
     *             ) 
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to logout")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::parseToken());

            return $this->success(["message" => "Signout Successfull"]);
        } catch (JWTException $e) {
            Log::error([
                "message" => $e->getMessage(),
                "controller_action" => "AuthController@logout",
                "line" => $e->getLine()
            ]);
            return $this->error('Failed to logout', 500);
        }

    }

    protected function respondWithToken($token)
    {
        $response = [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ];
        return $this->success($response);
    }
}
