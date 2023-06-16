<?php

namespace App\Http\Controllers;

use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripUserController extends Controller
{
    const ADMIN_ROLE = 3;

    public function findTripsFromUser(string $userId)
    {
        $user = auth()->user();
        Log::error("Trips from {$user->id}");

        try {

            if ($user->role_id != self::ADMIN_ROLE && $user->id != $userId) {

                throw new Error('You have no permission');
            }

            $tripsFromUser = DB::table('users AS u')
                ->select('tu.user_id', 't.id', 'u.name', 'u.lastname', 'u.country', 't.city', 't.description', 't.start_date', 't.end_date')
                ->join('trip_users AS tu', 'u.id', '=', 'tu.user_id')
                ->join('trips AS t', 't.id', '=', 'tu.trip_id')
                ->where('u.id', $userId)
                ->get();

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
                ->where([['t.id', $tripId], ['tu.role_id', '<>' ,'1']])
                ->get();

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
}
