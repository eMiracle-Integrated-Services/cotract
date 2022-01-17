<?php


namespace App\Http\Controllers;


class UserController extends Controller
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->middleware('api:auth');
        $this->user = auth()->guard('api')->user();
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        auth()->guard('api')->logout();
        return response()->json([
            'status' => true,
            'message' => 'logged out successfully'
        ]);
    }
}
