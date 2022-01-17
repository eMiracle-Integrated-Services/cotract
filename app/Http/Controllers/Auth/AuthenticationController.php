<?php


namespace App\Http\Controllers;


use App\Actions\EmailVerification;
use App\Actions\EmailVerificationAction;
use App\Actions\Password;
use App\Actions\PasswordAction;
use App\Mail\UserRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    {
        $v = Validator::make( $request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'false',
                'message' => 'Login Failed',
                'data' => $v->errors()
            ], 422);

        }

        $user = User::where('email', $request->email)->first();
        if (isset($user->id)) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client');

                return response()->json([
                    'status' => true,
                    'message' => 'logged in successfully',
                    'data' => $user,
                    'token' => $token->plainTextToken
                ]);
            }
        }
        return response()->json([
            'status'=>false,
            'message'=>'Invalid Email and Password Combination',
            'data'=>[]
        ], 422);
    }

    public function register(Request $request)
    {
        $v = Validator::make( $request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'name' => 'required|string|min:3',
            'country' => 'required|string'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'data' => $v->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country' => $request->country
        ]);

        /**
         * add the referral system
         */
        if(isset($request->ref) && $request->ref != ''){
            //ref
        }

        $token = $user->createToken(Str::random(5));

        $data = ([
            "name"=>$request->name,
            "email"=>$request->email,
        ]);

        //send mail
        try {
            //code...
            //notification
        } catch (\Throwable $th) {
            //throw $th;
            report($th);
        }

        return response()->json([
            'status' => true,
            'message' => 'Account Creation was successful',
            'data' => $user,
            'token' => $token->plainTextToken
        ]);
    }

    public function passwordResetGetToken(Request $request)
    {
        $v = Validator::make( $request->all(), [
            'email' => 'required|email|unique:users',
        ]);

        if($v->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Login Failed',
                'data' => $v->errors()
            ], 422);

        }
        /**
         * verify user email address, generate token and send
         */
        if (PasswordAction::sendPasswordResetToken($request))
        {
            return response([
                'status' => true,
                'message' => 'Please check your email for token',
                'data' => '',
            ]);
        }

        return response([
            'status' => false,
            'message' => 'Invalid Credentials',
            'data' => '',
        ]);
    }

    public function submitPasswordResetGetToken(Request $request): \Illuminate\Http\JsonResponse
    {
        $v = Validator::make( $request->all(), [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if($v->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'data' => $v->errors()
            ], 422);

        }

        if (PasswordAction::validatePasswordResetToken($request)){
            return response()->json([]);
        }
        return response()->json([], 401);
    }

    public function validateEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $v = Validator::make( $request->all(), [
            'email' => 'required|email|exists:users,email',
            'token'=>'required|string'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Login Failed',
                'data' => $v->errors()
            ], 422);

        }
        //check if it matches
        $user = User::where('email', $request->email)->first();
        if(EmailVerificationAction::verifyEmail($user, $request->token)){
            try {
                Notification::send($user, new emailVerifiedNotification());
            }catch (\Throwable $th)
            {
                report($th);
            }
            return response()->json([
                'status' => true,
                'message'=> 'Email Verified'
            ]);
        }

        return response()->json([
            'status'=>false,
            'message'=>'An error occurred',
            'data'=>[]
        ], 422);
    }

    public function sendEmailValidationCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $v = Validator::make( $request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if($v->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'data' => $v->errors()
            ], 422);

        }

        if (EmailVerificationAction::send(User::byEmail($request->email)))
        {
            return response()->json([
                'status'=>true,
                'message'=>'Please check your mail for a token',
                'data'=>[]
            ]);
        }
        return response()->json([
            'status'=>false,
            'message'=>'An error occurred',
            'data'=>[]
        ], 422);
    }
}
