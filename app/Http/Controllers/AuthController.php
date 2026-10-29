<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Resource;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required|string|min:6|confirmed',
            'phone_number'  => 'required|string|max:20',
            'status'        => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $prefix = '';
        switch ($request->status) {
            case 'nasabah':
                $prefix = 'NAS';
                break;
            case 'admin':
                $prefix = 'ADM';
                break;
            case 'owner':
                $prefix = 'OWN';
                break;
        }

        $lastUser = User::where('id_user', 'like', "$prefix%")->orderBy('id_user', 'desc')->first();
        $lastIdNumber = $lastUser ? intval(substr($lastUser->id_user, 3)) : 0;
        $newIdNumber = $lastIdNumber + 1;
        
        // Buat ID baru
        $newId = $prefix . str_pad($newIdNumber, 3, '0', STR_PAD_LEFT);
       

        $user = User::create([
            'id_user'       => $newId,
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'nik'           => $request->nik,
            'phone_number'  => $request->phone_number,
            'address'       => $request->address,
            'status'        => $request->status
        ]);

        $token = auth()->login($user);

        return $this->responseWithToken($token);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('name', 'password');

        auth()->factory()->setTTL(60); // 1 jam
    
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        return $this->responseWithToken($token);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['success' => true, 'message' => 'Berhasil logout']);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? response()->json(['message' => 'Link reset password telah dikirim ke email Anda'])
                    : response()->json(['error' => 'Gagal mengirim link reset password'], 500);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? response()->json(['message' => 'Password berhasil direset'])
                    : response()->json(['error' => 'Gagal mereset password'], 500);
    }

    /**
     * Display a listing of users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $keyword = $request->query('keyword');

        $query = User::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('phone_number', 'like', "%{$keyword}%")
                ->orWhere('id_user', 'like', "%{$keyword}%");
            });
        }

        $users = $query
            ->orderByRaw('LOWER(name) ASC')
            ->paginate(10);

        return new Resource(
            true,
            'List Data Nasabah',
            $users
        );
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {


        // Find user by id
        $user = User::find($id);

        // Check if user found
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah Tidak Ditemukan!',
                'data'    => null
            ], 404);
        }

        // Return single user as a resource
        return new Resource(true, 'Detail Nasabah!', $user);
    }

   /**
     * Store a newly created user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6',
            'nik'         => 'required|string',
            'phone_number' => 'required|string',
            'address'     => 'required|string',
            'status'      => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create user
        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => bcrypt($request->password),
            'nik'         => $request->nik,
            'phone_number' => $request->phone_number,
            'address'     => $request->address,
            'status'      => $request->status,
        ]);

        // Return response
        return new Resource(true, 'Data Nasabah Berhasil Ditambahkan!', $user);
    }

    /**
     * Update the specified user.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string',
            'email'       => 'required|email|unique:users,email,' . $id,
            // 'password'    => 'nullable|string|min:6',
            'nik'         => 'required|string',
            'phone_number' => 'required|string',
            // 'address'     => 'required|string',
            // 'status'      => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find user by id
        $user = User::find($id);

        // Check if user found
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah Tidak Ditemukan!',
                'data'    => null
            ], 404);
        }

        // Update user
        $user->update([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => $request->password ? bcrypt($request->password) : $user->password,
            'nik'         => $request->nik,
            'phone_number' => $request->phone_number,
            'address'     => $request->address,
            'status'      => $request->status,
        ]);

        // Return response
        return new Resource(true, 'Data Nasabah Berhasil Diperbarui!', $user);
    }

    /**
     * Remove the specified user.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find user by id
        $user = User::find($id);

        // Check if user found
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah Tidak Ditemukan!',
                'data'    => null
            ], 404);
        }

        // Delete user
        $user->delete();

        // Return response
        return new Resource(true, 'Data Nasabah Berhasil Dihapus!', $user);
    }

    public function refresh()
    {
        try {
            return $this->responseWithToken(auth()->refresh());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah kedaluwarsa',
             ], 401);
        }
    }

    protected function responseWithToken($token)
    {
        $ttl = auth()->factory()->getTTL();
        return response()->json([
            'success' => true,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttl * 60,
            'expires_at' => now()->addMinutes($ttl)->timestamp,
            'user' => auth()->user()
        ]);
    }
}
