<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function index(string $userId)
    {
        Log::info("Get list of all messages from user");

        try {

            $user = auth()->user();

            if (!$user) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Auth required",
                    ],
                    201
                );
            }

            $message = Message::query()->where('sender_id', $userId)->orWhere('recipient_id', $userId)->get();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Messages retrieved successfuly",
                    "data" => $message
                ],
                201
            );
        } catch (\Throwable $th) {

            Log::error("Error getting messages:" . $th->getMessage());

            return response()->json(
                [
                    "success" => true,
                    "message" => "Couldnt retrieve messages",
                    "data" => $th->getMessage()
                ],
                201
            );
        }
    }

    public function store(Request $request)
    {
        Log::info("Send message");

        try {

            $user = auth()->user();

            $validator = Validator::make($request->all(), [
                'description' => 'required',
                'recipient_id' => 'required',
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

            $description = $request->input('description');
            $recipient = $request->input('recipient_id');

            $message = Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $recipient,
                'description' => $description
            ]);

            return response()->json(
                [
                    "success" => true,
                    "message" => "New message created",
                    "data" => $message,
                ],
                200
            );
        } catch (\Throwable $th) {

            Log::error("Error registering message: " . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Message cannot be registered",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }
}
