<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\PasswordChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class PasswordAction
{
    public static function sendPasswordResetToken(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user){

            $digits_to_hash = rand(100000, 999999);
            $token = Hash::make($digits_to_hash);
            $user->password_reset_token = $token;
            $user->save();

            try {
                Notification::send($user, new PasswordResetTokenNotification($digits_to_hash));
                return true;
            }catch (\Throwable $throwable){
                report($throwable);
            }
        }
        return false;
    }

    public static function validatePasswordResetToken(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        if ($user)
        {
            if (Hash::check($request->input('token'), $user->password_reset_token)){
                $user->password = Hash::make($request->input('password'));
                $user->save();

                try {
                    Notification::send($user, new PasswordChanged());
                    return true;
                }catch (\Throwable $throwable) {
                    report($throwable);
                }

            }
        }
        return false;
    }

    public static function updatePassword(User $user, Request $request)
    {
        if (password_verify($request->old_password, $user->password)){
            $user->password = Hash::make($request->new_password);
            return $user->save();
        }
        return false;
    }
}
