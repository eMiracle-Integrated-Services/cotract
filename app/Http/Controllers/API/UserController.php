<?php


namespace App\Http\Controllers\API;


use App\Actions\PasswordAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    private $user;

    public function __construct()
    {
        $this->middleware('auth:api');
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

    public function updatePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $v = Validator::make( $request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8',
        ]);

        if($v->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'data' => $v->errors()
            ], 422);
        }
        if (PasswordAction::updatePassword($this->user, $request))
        {
            return response()->json([
                'status' => true,
                'message' => 'Password changed successfully',
                'data' => []
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Invalid credentials supplied',
            'data' => []
        ], 422);
    }
}
