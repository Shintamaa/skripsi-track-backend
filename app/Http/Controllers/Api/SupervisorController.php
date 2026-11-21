<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserMiniResource;
use App\Models\User;

class SupervisorController extends Controller
{
    public function index()
    {
        $dosen = User::where('role', 'dosen')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar dosen berhasil diambil.',
            'data' => UserMiniResource::collection($dosen),
        ]);
    }
}