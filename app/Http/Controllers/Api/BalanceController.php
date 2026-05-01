<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Models\Sodako;
use App\Models\Withdrawal;
use App\Models\User;

class BalanceController extends Controller
{
    public function saldoByUser(Request $request, $id_user)
    {
        if (empty($id_user)) {
            return response()->json([
                'success' => false,
                'message' => 'ID user tidak boleh kosong',
            ], 400);
        }

        $user = User::where('id_user', $id_user)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        $totalDeposit = (float) Deposit::where('id_user', $id_user)->sum('price');
        $totalWithdrawal = (float) Withdrawal::where('id_user', $id_user)->sum('price');
        $totalSodako = (float) Sodako::where('id_user', $id_user)->sum('price');

        $saldo = $totalDeposit - ($totalWithdrawal + $totalSodako);

        return response()->json([
            'success' => true,
            'message' => 'Saldo berhasil dihitung',
            'data' => [
                'saldo' => $saldo,
                'total_deposit' => $totalDeposit,
                'total_withdrawal' => $totalWithdrawal,
                'total_sodako' => $totalSodako,
            ],
        ]);
    }

    public function saldoAll()
    {
        $totalDeposit = (float) Deposit::sum('price');
        $totalWithdrawal = (float) Withdrawal::sum('price');
        $totalSodako = (float) Sodako::sum('price');

        $saldo = $totalDeposit - ($totalWithdrawal + $totalSodako);

        return response()->json([
            'success' => true,
            'message' => 'Saldo berhasil dihitung',
            'data' => [
                'saldo' => $saldo,
                'total_deposit' => $totalDeposit,
                'total_withdrawal' => $totalWithdrawal,
                'total_sodako' => $totalSodako,
            ],
        ]);
    }
}
