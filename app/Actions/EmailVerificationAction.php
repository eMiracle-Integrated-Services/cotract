<?php


namespace App\Actions;


use App\Models\User;
use App\Notifications\Auth\EmailVerifyNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class EmailVerificationAction
{
    public static function send(User $user): bool
    {
        $token = (rand(1000,9999));
        $user->email_verification_token()->create([
            'token' => Hash::make($token)
        ]);
        $user->notify(new EmailVerifyNotification($token));
        return true;
    }

    public static function verifyEmail(User $user, $token): bool
    {
        $dbToken = $user->email_verification_token()->latest()->first();
        if(isset($dbToken->token) and Hash::check($token, $dbToken->token)){
            $user->email_verified_at = Carbon::now();
            return $user->save();
        }
        return false;
    }
}
