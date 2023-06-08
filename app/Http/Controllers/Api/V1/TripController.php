<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripUser;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    const TRAVELER_ROLE = 1;
    const ORGANIZER_ROLE = 2;
    const ADMIN_ROLE = 3;

    public function index()
    {
        Log::info("Get list of all trips");

        try {
            $trips = Trip::query()->get();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Trips retrieved successfuly",
                    "data" => $trips
                ],
                201
            );
        } catch (\Throwable $th) {

            Log::error("Error getting trips:" . $th->getMessage());

            return response()->json(
                [
                    "success" => true,
                    "message" => "Couldnt retrieve trips",
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
    public function show(Trip $trip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trip $trip)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $idTrip)
    {
        try {

            $user = auth()->user();

            if ($user->role_id != self::TRAVELER_ROLE) {

                $trip = DB::table('trips')->where('id', '=', $idTrip);
                
                if(!$trip->exists()){
                    
                    throw new Error('This trip does not exist!');
                }

                $trip->delete();
                
                return response()->json(['message' => 'Trip deleted successfuly'], 201);
            } else {

                throw new Error('You have no permission!');
            }
        } catch (\Throwable $th) {

            Log::error("Error at erase trip");

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldn't delete trip!",
                    "error" => $th->getMessage()
                ],
                500
            );
        }
    }
}
