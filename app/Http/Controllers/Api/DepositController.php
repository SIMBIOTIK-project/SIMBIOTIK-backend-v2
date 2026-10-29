<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepositController extends Controller
{
    /**
     * index
     * 
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $user_id = $request->query('user_id');
        $waste_type_id = $request->query('waste_type_id');

        $date = $request->query('date');

        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $deposits = Deposit::with([
            'user',
            'wastetype'
        ])
            ->when($user_id, function ($query) use ($user_id) {
                $query->where('id_user', $user_id);
            })
            ->when($waste_type_id, function ($query) use ($waste_type_id) {
                $query->where('id_wastetype', $waste_type_id);
            })
            ->when($date, function ($query) use ($date) {
                $query->whereDate('created_at', $date);
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

        return new Resource(
            true,
            'List Data Deposit',
            $deposits
        );
    }

    /**
     * store
     * 
     * @param mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'id_user'       => 'required|exists:users,id_user',
            'id_wastetype'  => 'required|exists:wastetypes,id',
            'weight'        => 'required|numeric',
            'price'         => 'required|numeric',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userId = auth()->user()->id_user;

        //Create deposit
        $deposit = Deposit::create([
            'id_user'       => $request->id_user,
            'id_wastetype'  => $request->id_wastetype,
            'weight'        => $request->weight,
            'price'         => $request->price,
            'created_by'    => $userId,
        ]);

        // Return response
        return new Resource(true, 'Data Deposit Berhasil Ditambahkan!', $deposit);
    }

    /**
     * show
     * 
     * @param mixed $id_user
     * @return void
     */
    public function show($id)
    {
        //Find deposit by id_user with related user and wastetype
        $deposit = Deposit::with(['user', 'wastetype'])->where('id_user', $id)->first();

        // Check if deposit found
        if (!$deposit) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit tidak ditemukan!',
                'data'    => null
            ], 404);
        }

        // Return single deposit as a resource
        return new Resource(true, 'Deposit untuk pengguna!', $deposit);
    }

    /**
     * update
     * 
     * @param mixed $request
     * @param mixed $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id_user',
            'id_wastetype'  => 'required|exists:wastetype,id',
            'weight'        => 'required|numeric',
            'price'         => 'required|numeric',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find deposit by id
        $deposit = Deposit::where('user_id', $id)->first();

        // Check if deposit found
        if (!$deposit) {
            return response()->json([
                'success'   => false,
                'message'   => 'Deposit Tidak Ditemukan!',
                'data'      => null
            ], 404);
        }

        // Update deposit
        $deposit->update([
            'user_id'       => $request->user_id,
            'id_wastetype'  => $request->id_wastetype,
            'weight'        => $request->weight,
            'price'         => $request->price,
        ]);

        // Return response
        return new Resource(true, 'Data Deposit Berhasil Diperbarui', $deposit);
    }

    /**
     * destroy
     * 
     * @param mixed $id
     * @return void
     */
    public function destroy($id)
    {
        // Find deposit by ID
        $deposit = Deposit::where('id', $id)->first();

        // Check if deposit found
        if (!$deposit) {
            return response()->json([
                'success'  => false,
                'message'  => 'Deposit Tidak Ditemukan!',
                'data'     => null
            ], 404);
        }

        // Delete deposit
        $deposit->delete();

        // Return response
        return new Resource(true, 'Data deposit berhasil dihapus', $deposit);
    }
}
