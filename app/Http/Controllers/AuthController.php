<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Contracts\Providers\Auth;

class AuthController extends Controller
{
    use HttpResponses;
 

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
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
       } catch (\Throwable $th) {
            Log::error([
                "message" => $exception->getMessage(),
                "controller_action" => "AuthController@register",
                "line" => 45
            ]);
            return $this->error('User registration failed', 500);
       }
    }

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
                return $this->error('Unauthorized', 401);
            }

            Log::info(["message" => "Login Successful"]);
            return $this->respondWithToken($token);
        } catch (\Throwable $th) {
            Log::error([
                "message" => $exception->getMessage(),
                "controller_action" => "AuthController@login",
                "line" => 74
            ]);
            return $this->error('Server Error', 503);
        }
  
    }

    protected function respondWithToken($token)
    {
        $response = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ];
        return $this->success($response);
    }
}
