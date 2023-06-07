<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = FacadesValidator::make($request->all(), [
                'email' => 'required | email',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Body validation fails",
                        "errors" => $validator->errors()
                    ],
                    400
                );
            };

            $email = $request->input('email');
            $password = $request->input('password');

            $user = User::query()->where('email', $email)->first();

            if (!$user){
                return response()->json(
                    [
                        "success" => true,
                        "message" => "User or password invalid",
                    ],
                    404
                );
            }

            if (!Hash::check($password, $user->password)){
                return response()->json(
                    [
                        "success" => true,
                        "message" => "User or password invalid",
                    ],
                    404
                );
            }

            $token = $user->createToken('apiToken')->plainTextToken;

            return response()->json(
                [
                    "success" => true,
                    "message" => "User logged in correctly",
                    "data" => $user,
                    "token" => $token
                ],
                200
            );
        } catch (\Throwable $th) {
            Log::error("Error logging user: ". $th->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "User cannot be logged in",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info("Get list of all users");

        try {
            $users = User::query()->get();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Users retrieved successfuly",
                    "data" => $users
                ], 
                201
            );
        } catch (\Throwable $th) {
        
            Log::error("Error getting users:". $th->getMessage());
            return response()->json(
                [
                    "success" => true,
                    "message" => "Couldnt retrieve users",
                    "data" => $th->getMessage()
                ], 
                201
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}



