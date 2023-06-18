<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripUser;
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

    // LIST ALL TRIPS
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
                    "success" => false,
                    "message" => "Couldnt retrieve trips",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }

    // STORE TRIPS
    public function store(Request $request)
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

            if ($user->role_id == self::TRAVELER_ROLE) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "Unauthorized",
                    ],
                    403
                );
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
                        "message" => "Data missing",
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
                'user_id' => $user->id,
                'role_id' => self::ORGANIZER_ROLE
            ]);

            return response()->json(
                [
                    "success" => true,
                    "message" => "New trip created",
                    "data" => $trip,
                ],
                201
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

    // GET INFORMATION FROM SINGLE TRIP 
    public function show(Trip $trip)
    {
        Log::info("Get Trip {$trip->id}");

        try {
            $trip = Trip::query()->where('id', '=', $trip->id)->get();

            if (!$trip) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "There is no trip",
                    ],
                    404
                );
            }

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
                500
            );
        }
    }

    // UPDATE TRIP INFORMATION
    public function update(Request $request, Trip $trip)
    {
        Log::info("Trip update");

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

            if ($request->input('start_date') || $request->input('end_date')) {
                $start_date = $request->input('start_date');
                $end_date = $request->input('end_date');

                if ($end_date < $start_date) {

                    return response()->json(
                        [
                            "success" => true,
                            "message" => "End date can't be previous to start date.",
                        ],
                        400
                    );
                }

                if ($start_date < date('Y-m-d')) {

                    return response()->json(
                        [
                            "success" => true,
                            "message" => "Start date can't be previous to today.",
                        ],
                        400
                    );
                }
            }

            if ($user->role_id == self::TRAVELER_ROLE) {
                
                return response()->json(
                    [
                        "success" => true,
                        "message" => "Unauthorized",
                    ],
                    403
                );
            }

            DB::table('trips')
                ->where('id', $trip->id)
                ->update($request->all());

            $trip = DB::table('trips')->where('id', $trip->id)->first();

            if (!$trip) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "No trip found",
                    ],
                    404
                );
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

    // DELETE TRIP
    public function destroy(string $idTrip)
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

            if ($user->role_id == self::TRAVELER_ROLE) {

                return response()->json(
                    [
                        "success" => true,
                        "message" => "Unauthorized",
                    ],
                    403
                );
            }

            $trip = DB::table('trips')->where('id', '=', $idTrip);

            if (!$trip->exists()) {

                return response()->json(
                    [
                        "success" => false,
                        "message" => "This trip does not exist!",
                    ],
                    404
                );
            }

            $trip->delete();

            return response()->json(['message' => 'Trip deleted successfuly'], 201);
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

    // GET TRIPS BY PAGINATION
    public function tripPagination(Request $request)
    {
        Log::error("Get trips paginated");

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

            Log::error("Error getting trips paginated:" . $th->getMessage());

            return response()->json(
                [
                    "success" => false,
                    "message" => "Couldnt retrieve trips",
                    "data" => $th->getMessage()
                ],
                500
            );
        }
    }
}
