<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{

    // GET ALL MESSAGES
    public function index(string $userId)
    {
        Log::info("Get list of all messages from user");

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

            $message = Message::query()
                ->select('messages.*', 'senders.name as sender_name', 'recipients.name as recipient_name')
                ->join('users as senders', 'messages.sender_id', '=', 'senders.id')
                ->join('users as recipients', 'messages.recipient_id', '=', 'recipients.id')
                ->where('messages.sender_id', $userId)
                ->orWhere('messages.recipient_id', $userId)
                ->orderBy('id', 'DESC')
                ->get();

            if (!$message) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "Couldn't retrieve messages!",
                    ],
                    404
                );
            }

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
                    "success" => false,
                    "message" => "Couldnt retrieve messages",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }

    // CREATE NEW MESSAGE
    public function store(Request $request)
    {
        Log::info("Send message");

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

            $validator = Validator::make($request->all(), [
                'description' => 'required',
                'recipient_id' => 'required',
            ]);

            if ($validator->fails()) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "Description missing!",
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

            if (!$message) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "Couldn't retrieve messages!",
                    ],
                    404
                );
            }

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
