<?php


namespace App\Traits;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ValidationTrait
{
    public static function call(Request $request, array $rules): \Illuminate\Http\JsonResponse
    {
        $v = Validator::make($request->all(), $rules);
        if ($v->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'data' => $v->errors()
            ], 422);
        }
    }
}
