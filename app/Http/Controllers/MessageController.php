<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function index(string $userId)
    {
        Log::info("Get list of all messages from user");

        try {
            
            $user = auth()->user();

            if(!$user){
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Auth required",
                    ],
                    201
                );
            }

            $message = Message::query()->where('sender_id', $userId )->orWhere('recipient_id', $userId)->get();

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
}
