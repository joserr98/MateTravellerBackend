<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Dotenv\Validator;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            if (!$user) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "User or password invalid",
                    ],
                    404
                );
            }

            if (!Hash::check($password, $user->password)) {

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
            Log::error("Error logging user: " . $th->getMessage());

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

            Log::error("Error getting users:" . $th->getMessage());

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

    public function store(Request $request)
    {
        try {

            $validator = FacadesValidator::make($request->all(), [
                'name' => 'required',
                'email' => 'required | unique:users,email',
                'password' => 'required | min:6 | max:12',
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

            $name = $request->input('name');
            $email = $request->input('email');
            $password = $request->input('password');

            $encryptedPassword = bcrypt($password);

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $encryptedPassword,
            ]);

            $token = $user->createToken('apiToken')->plainTextToken;

            DB::table('users')
                ->where('id', $user->id)
                ->update(['remember_token' => $token]);

            return response()->json(
                [
                    "success" => true,
                    "message" => "User registered",
                    "data" => $user,
                    "token" => $token
                ],
                200
            );
        } catch (\Throwable $th) {

            Log::error("Error registering user: " . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "User cannot be registered",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }

    public function profile()
    {
        Log::info("User profile");

        try {
            $user = auth()->user();
            $user = User::query()->where('id', '=', $user->id)->get();

            return response()->json(
                [
                    "success" => true,
                    "message" => "User retrieved successfuly",
                    "data" => $user
                ],
                201
            );
        } catch (\Throwable $th) {

            Log::error("Error getting user:" . $th->getMessage());

            return response()->json(
                [
                    "success" => true,
                    "message" => "Couldnt retrieve user profile",
                    "data" => $th->getMessage()
                ],
                404
            );
        }
    }

    public function update(Request $request, string $userId)
    {
        Log::info("User update");

        try {

            $user = auth()->user();

            if($user->id == $userId){

                $updatedUser = User::query()
                ->where('id', '=', $user->id)
                ->update($request->all());
            } else {

                throw new Error ('You have no permissions to update this user');
            }

            return response()->json(
                [
                    "success" => true,
                    "message" => "User updated successfuly",
                    "data" => $updatedUser
                ],
                201
            );
        } catch (\Throwable $th) {

            Log::error("Error updating user");

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldn't update user!",
                    "error" => $th->getMessage()
                ],
                500
            );
        }
    }

    public function destroy(User $user)
    {
        //
    }
}
