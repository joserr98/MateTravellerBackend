<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripUser;
use Error;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripUserController extends Controller
{
    const TRAVELER_ROLE = 1;
    const ADMIN_ROLE = 3;

    // ADD USER TO A TRIP
    public function join(string $tripId)
    {
        Log::info("Add to trip {$tripId}");

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

            $existingTrip = Trip::find($tripId);

            if (!$existingTrip) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "There's no trip available",
                    ],
                    404
                );
            }

            $existingUserTrip = DB::table('trip_users')->where([['trip_id', $tripId], ['user_id', $user->id]]);

            if ($existingUserTrip->exists()) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "You are already joined to this trip",
                    ],
                    401
                );
            }

            $newTrip = TripUser::create([
                'trip_id' => $tripId,
                'user_id' => $user->id,
                'role_id' => self::TRAVELER_ROLE
            ]);

            if (!$newTrip) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Couldn't create trip!",
                    ],
                    404
                );
            }

            return response()->json(
                [
                    "success" => true,
                    "message" => "Joined to the trip",
                    "data" => $newTrip
                ],
                201
            );
        } catch (\Throwable $th) {

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldnt join trip",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }

    public function findTripsFromUser(string $userId)
    {
        Log::error("Trips from users");

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

            if ($user->role_id != self::ADMIN_ROLE && $user->id != $userId) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Unauthorized",
                    ],
                    403
                );
            }

            $tripsFromUser = DB::table('users AS u')
                ->select('tu.user_id', 't.id', 'u.name', 'u.lastname', 'u.country', 't.city', 't.description', 't.start_date', 't.end_date')
                ->join('trip_users AS tu', 'u.id', '=', 'tu.user_id')
                ->join('trips AS t', 't.id', '=', 'tu.trip_id')
                ->where('u.id', $userId)
                ->get();

            if (!$tripsFromUser) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Couldn't get trips from user!",
                    ],
                    404
                );
            }

            $total = $tripsFromUser->count();

            return response()->json(['userTrips' => $tripsFromUser, 'count' => $total]);
        } catch (\Throwable $th) {

            Log::error("Error at getting users' trip");

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldn't get user's trips",
                    "error" => $th->getMessage()
                ],
                500
            );
        }
    }

    public function findTravelersFromTrip(string $tripId)
    {
        Log::error("Users from {$tripId} trip");

        try {

            $usersFromTrip = DB::table('users AS u')
                ->select('tu.user_id', 'tu.trip_id', 'u.name', 'u.lastname', 'u.country', 't.city', 't.description', 'u.birthday')
                ->join('trip_users AS tu', 'u.id', '=', 'tu.user_id')
                ->join('trips AS t', 't.id', '=', 'tu.trip_id')
                ->where([['t.id', $tripId], ['tu.role_id', '1']])
                ->get();

            if (!$usersFromTrip) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Couldn't get users trom trip!",
                    ],
                    404
                );
            }

            $total = $usersFromTrip->count();

            return response()->json(['usersFromTrip' => $usersFromTrip, 'count' => $total]);
        } catch (\Throwable $th) {

            Log::error("Error at getting users' trip");

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldn't get user's trips",
                    "error" => $th->getMessage()
                ],
                500
            );
        }
    }

    public function findOrganizerFromTrip(string $tripId)
    {
        Log::error("Users from {$tripId} trip");

        try {

            $usersFromTrip = DB::table('users AS u')
                ->select('tu.user_id', 'tu.trip_id', 'u.name', 'u.lastname', 'u.country', 't.city', 't.description', 'u.birthday')
                ->join('trip_users AS tu', 'u.id', '=', 'tu.user_id')
                ->join('trips AS t', 't.id', '=', 'tu.trip_id')
                ->where([['t.id', $tripId], ['tu.role_id', '<>', '1']])
                ->get();

            $total = $usersFromTrip->count();

            if (!$usersFromTrip) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Couldn't get users trom trip!",
                    ],
                    404
                );
            }

            return response()->json(['usersFromTrip' => $usersFromTrip, 'count' => $total]);
        } catch (\Throwable $th) {

            Log::error("Error at getting users' trip");

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldn't get user's trips",
                    "error" => $th->getMessage()
                ],
                500
            );
        }
    }
}
