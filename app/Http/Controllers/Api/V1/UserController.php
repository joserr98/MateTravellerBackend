<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class UserController extends Controller
{

    const ADMIN_ROLE = 3;

    // LOGIN FUNCTION
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
                        "message" => "Email and password are required!",
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
                    400
                );
            }

            if (!Hash::check($password, $user->password)) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "User or password invalid",
                    ],
                    400
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
                201
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

    // GET USERS LIST
    public function index()
    {
        Log::info("Get list of all users");

        try {
            $users = User::query()->get();

            if (!$users) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "Could not retrieve users",
                    ],
                    404
                );
            }

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
                    "success" => false,
                    "message" => "Couldn't retrieve users",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }

    // REGISTER USER
    public function store(Request $request)
    {
        try {

            $validator = FacadesValidator::make($request->all(), [
                'name' => 'required',
                'email' => 'required | unique:users,email',
                'password' => 'required | min:6',
            ]);

            if ($validator->fails()) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "All fields are required! Password must have at least 6 characters.",
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

            if (!$user) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Couldn't register user!",
                    ],
                    404
                );
            }

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
                201
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

    // GET USER PROFILE
    public function show()
    {
        Log::info("User profile");

        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "No user found",
                    ],
                    401
                );
            }

            $user = User::query()->where('id', '=', $user->id)->get();

            if (!$user) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "Couldn't retrieve user profile!",
                    ],
                    404
                );
            }

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
                    "success" => false,
                    "message" => "Couldnt retrieve user profile",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }

    // UPDATE USER DATA
    public function update(Request $request, string $userId)
    {
        Log::info("User update");

        try {

            $user = auth()->user();

            if (!$user) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "No user found",
                    ],
                    401
                );
            }

            $password = $request->input('password');

            if ($password) {
                $encryptedPassword = bcrypt($password);
                $request->merge(['password' => $encryptedPassword]);
            }

            if ($user->id != $userId && $user->role_id != self::ADMIN_ROLE) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Unauthorized",
                    ],
                    403
                );
            }

            $updatedUser = User::query()
                ->where('id', '=', $user->id)
                ->update($request->all());

            if (!$updatedUser) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Couldn't update user!",
                    ],
                    404
                );
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

    public function destroy(string $userId)
    {
        try {

            $user = auth()->user();

            if (!$user) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "No user found",
                    ],
                    401
                );
            }

            if ($user->role_id != self::ADMIN_ROLE && $userId != $user->id) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Unauthorized",
                    ],
                    403
                );
            }

            DB::table('users')->where('id', '=', $userId)->delete();

            return response()->json(['message' => 'User deleted successfuly'], 201);
        } catch (\Throwable $th) {

            Log::error("Error at erase user");

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldn't delete user!",
                    "error" => $th->getMessage()
                ],
                500
            );
        }
    }

    // GET USERS BY PAGINATION
    public function userPagination(Request $request)
    {

        try {
            $pageSize = $request->input('page_size', 10);
            $users = User::paginate($pageSize);

            $currentPage = $users->currentPage();
            $totalPages = $users->lastPage();

            $nextPage = null;
            if ($users->hasMorePages()) {
                $nextPage = $users->nextPageUrl();
            }

            $previousPage = null;
            if ($users->currentPage() > 1) {
                $previousPage = $users->previousPageUrl();
            }

            $responseData = [
                'data' => $users->items(),
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'next_page' => $nextPage,
                'previous_page' => $previousPage,
            ];

            if (!$responseData) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Couldnt retrieve users",
                    ],
                    404
                );
            }

            return response()->json(
                [
                    "success" => true,
                    "message" => "Users retrieved successfuly",
                    "data" => $responseData
                ],
                201
            );
        } catch (\Throwable $th) {

            Log::error("Error getting users:" . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldnt retrieve users",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }

    // FILTER USERS
    public function userByFilter(Request $request)
    {
        Log::info("Get users filtered");

        try {
            $filter = $request->query('filter');
            $usersQuery = User::query();

            if ($filter) {
                $usersQuery->where(function ($query) use ($filter) {
                    $query->where('name', 'like', '%' . $filter . '%')
                        ->orWhere('lastname', 'like', '%' . $filter . '%')
                        ->orWhere('country', 'like', '%' . $filter . '%')
                        ->orWhere('email', 'like', '%' . $filter . '%');
                });
            }

            $users = $usersQuery->get();

            if (!$users) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Couldnt retrieve users",
                    ],
                    404
                );
            }

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
                    "success" => false,
                    "message" => "Couldnt retrieve users",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }
}
