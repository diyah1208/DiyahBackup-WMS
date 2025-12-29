<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserModel;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // hanya user yang aktif
        $users = UserModel::where('status', 'active')->latest()->get()->map(function ($user) {
            return $this->formatUser($user);
        });

        return response()->json([
            'status' => true,
            'data'   => $users
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|string',
            'lokasi'   => 'nullable|string',
        ]);

        $user = UserModel::create([
            'nama'     => $data['nama'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'lokasi'   => $data['lokasi'] ?? null,
            'status'   => 'active', // default active
        ]);

        return response()->json([
            'status' => true,
            'data'   => $this->formatUser($user)
        ], 201);
    }

    public function show($id)
    {
        $user = UserModel::findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $this->formatUser($user)
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = UserModel::findOrFail($id);

        $data = $request->validate([
            'nama'   => 'sometimes|string',
            'email'  => 'sometimes|email|unique:users,email,' . $id,
            'role'   => 'sometimes|string',
            'lokasi' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive', // optional update status
        ]);

        $user->update($data);

        return response()->json([
            'status' => true,
            'data'   => $this->formatUser($user)
        ]);
    }
public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:active,inactive'
    ]);

    $user = UserModel::findOrFail($id);
    $user->status = $request->status;
    $user->save();

    return response()->json([
        'status' => true,
        'data' => [
            'id' => $user->id,
            'status' => $user->status,
        ]
    ]);
}

    public function destroy($id)
    {
        $user = UserModel::findOrFail($id);

        // soft delete manual
        $user->status = 'inactive';
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'User berhasil dinonaktifkan'
        ]);
    }

    protected function formatUser($user)
    {
        return [
            'id' => $user->id,
            'nama' => $user->nama,
            'email' => $user->email,
            'role' => $user->role,
            'lokasi' => $user->lokasi,
            'status' => $user->status, // tambahkan status

            'email_verified' => !is_null($user->email_verified_at),
            'email_verified_at' => $user->email_verified_at,

            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}

