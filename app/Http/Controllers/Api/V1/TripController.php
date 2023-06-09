<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripUser;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    // USER ROLES CONST
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

    public function store(Request $request)
    {
        try {

            $user = auth()->user();

            if ($user->role_id == self::TRAVELER_ROLE) {

                throw new Error('You cannot create trips');
            }

            $validator = Validator::make($request->all(), [
                'city' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
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

            $city = $request->input('city');
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $description = $request->input('description');

            $trip = Trip::create([
                'city' => $city,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'description' => $description
            ]);

            TripUser::create([
                'trip_id' => $trip->id,
                'user_id' => $user->id
            ]);

            return response()->json(
                [
                    "success" => true,
                    "message" => "New trip created",
                    "data" => $trip,
                ],
                200
            );
        } catch (\Throwable $th) {

            Log::error("Error creating trip: " . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Trip cannot be created",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }

    public function join(string $tripId)
    {
        Log::info("Add to trip {$tripId}");

        try {

            $user = auth()->user();

            $existingTrip = Trip::find($tripId);

            if (!$existingTrip) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "There's no trip available",
                    ],
                    201
                );
            }

            $existingUserTrip = DB::table('trips_users')->where([['trip_id', $tripId], ['user_id', $user->id]]);

            if ($existingUserTrip->exists()) {
                return response()->json(
                    [
                        "success" => true,
                        "message" => "You are already joined to this trip",
                    ],
                    201
                );
            }

            $newTrip = TripUser::create([
                'trip_id' => $tripId,
                'user_id' => $user->id
            ]);

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

    public function show(Trip $trip)
    {
        Log::info("Get Trip {$trip->id}");

        try {

            $trip = Trip::query()->where('id', '=', $trip->id)->get();

            return response()->json(
                [
                    "success" => true,
                    "message" => "Trip retrieved successfuly",
                    "data" => $trip
                ],
                201
            );
        } catch (\Throwable $th) {

            Log::error("Error getting trip:" . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldnt retrieve trip",
                    "data" => $th->getMessage()
                ],
                404
            );
        }
    }

    public function update(Request $request, Trip $trip)
    {
        Log::info("Trip update");

        try {

            $user = auth()->user();

            if ($request->input('start_date') || $request->input('end_date')) {
                $start_date = $request->input('start_date');
                $end_date = $request->input('end_date');

                if ($end_date < $start_date) {

                    throw new Error("End date can't be previous to start date.");
                }

                if ($start_date < date('Y-m-d')) {

                    throw new Error("Start date can't be previous to today.");
                }
            }

            if ($user->role_id != self::TRAVELER_ROLE) {

                DB::table('trips')
                    ->where('id', $trip->id)
                    ->update($request->all());

                $trip = DB::table('trips')->where('id', $trip->id)->first();
            } else {

                throw new Error('You have no permissions to update this trip');
            }

            return response()->json(
                [
                    "success" => true,
                    "message" => "Trip updated successfuly",
                    "data" => $trip
                ],
                201
            );
        } catch (\Throwable $th) {

            Log::error("Error updating trip");

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldn't update trip!",
                    "error" => $th->getMessage()
                ],
                500
            );
        }
    }

    public function destroy(string $idTrip)
    {
        try {

            $user = auth()->user();

            if ($user->role_id != self::TRAVELER_ROLE) {

                $trip = DB::table('trips')->where('id', '=', $idTrip);

                if (!$trip->exists()) {

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

    public function tripPagination(Request $request)
    {

        try {
            $pageSize = $request->input('page_size', 9);
            $trips = Trip::paginate($pageSize);

            $currentPage = $trips->currentPage();
            $totalPages = $trips->lastPage();

            $nextPage = null;
            if ($trips->hasMorePages()) {
                $nextPage = $trips->nextPageUrl();
            }

            $previousPage = null;
            if ($trips->currentPage() > 1) {
                $previousPage = $trips->previousPageUrl();
            }

            $responseData = [
                'data' => $trips->items(),
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'next_page' => $nextPage,
                'previous_page' => $previousPage,
            ];

            return response()->json(
                [
                    "success" => true,
                    "message" => "Trips retrieved successfuly",
                    "data" => $responseData
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
}
