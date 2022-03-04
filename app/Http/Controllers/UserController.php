<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller {

    public function register(Request $request) {
        if (!$request->filled('android_identifier')) {
            return response()->json([
                'status' => 'error',
                'message' => 'android_identifier is required',
            ], 400);
        }

        $user = User::create([
            'device_id' => Str::uuid()->toString(),
            'android_identifier' => $request->input('android_identifier'),
            'register_ip' => $request->ip(),
        ]);

        return response()->json($user, 201);
    }
}
