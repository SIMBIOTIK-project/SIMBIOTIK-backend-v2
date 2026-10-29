<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sodako;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\Validator;

class SodakoController extends Controller
{
    public function index(Request $request)
    {
        // Logic to retrieve and return sodako data
        $id_user = $request->query('id_user');

        $date = $request->query('date');

        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $sodako = Sodako::with(['user'])
            ->when($id_user, function($query, $id_user) {
                return $query->where('id_user', $id_user);
            })
            ->when($date, function($query, $date) {
                return $query->whereDate('created_at', $date);
            })
            ->when(
                $start_date && $end_date,
                function ($query) use ($start_date, $end_date) {
                    $query->whereBetween(
                        'created_at',
                        [
                            $start_date . ' 00:00:00',
                            $end_date . ' 23:59:59',
                        ]
                    );
                }
            )
            ->latest()
            ->paginate(
                $request->get('per_page', 10)
            );

        return new Resource(true, 'List Data Sodako', $sodako);
    }

    public function store(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'id_user'   => 'required|exists:users,id_user',
            'price'     => 'required|numeric',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userId = auth()->user()->id_user;

        // Create sodako
        $sodako = Sodako::create([
            'id_user'       => $request->id_user,
            'price'         => $request->price,
            'created_by'    => $userId,
        ]);

        // Return response
        return new Resource(true, 'Data Sodako Berhasil Ditambahkan!', $sodako);
    }

    public function show($id)
    {
        // Find sodako by id
        $sodako = Sodako::find($id);

        if (!$sodako) {
            return response()->json([
                'success' => false,
                'message' => 'Data Sodako Tidak Ditemukan!',
                'data'    => null
            ], 404);
        }

        // Return response
        return new Resource(true, 'Detail Data Sodako', $sodako);
    }

    public function update(Request $request, $id)
    {
        // Find sodako by id
        $sodako = Sodako::find($id);

        if (!$sodako) {
            return response()->json([
                'success' => false,
                'message' => 'Data Sodako Tidak Ditemukan!',
                'data'    => null
            ], 404);
        }

        // Define validation rules
        $validator = Validator::make($request->all(), [
            'id_user'   => 'required|exists:users,id_user',
            'price' => 'required|numeric',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update sodako
        $sodako->update([
            'id_user' => $request->id_user,
            'price' => $request->price,
        ]);

        // Return response
        return new Resource(true, 'Data Sodako Berhasil Diupdate!', $sodako);
    }

    public function destroy($id)
    {
        // Find sodako by id
        $sodako = Sodako::find($id);

        if (!$sodako) {
            return response()->json([
                'success' => false,
                'message' => 'Data Sodako Tidak Ditemukan!',
                'data'    => null
            ], 404);
        }

        // Delete sodako
        $sodako->delete();

        // Return response
        return new Resource(true, 'Data Sodako Berhasil Dihapus!', null);
    }
}
