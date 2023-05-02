<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Mail\RegisterUserMail;
use App\Mail\ResetPasswordOtpMail;
use App\Models\Token;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    public function RegisterUser(RegisterUserRequest $request)
    {
        if ($request->referral_code) {
            $referrer = User::where(['referral_code' => $request->referral_code])->first();

            if (!$referrer) return ErrorResponse('Referral Code Not Bound To Any User');
        }

        $referralCode = substr($request->first_name, 0, 3) . substr($request->last_name, 0, 3) . generateRandom(3);

        $signToken = rand(1111, 9999);

        $user = new User([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'referral_code' => $referralCode,
            'referred_by' => $request->referred_by,
            'email_verified_at' => $request->email_verified_at,
            'password' => Hash::make($request->password),
        ]);

        $user->save();

        $token = new Token([
            'token_type' => 'signup_token',
            'token_code' => $signToken,
            'token_user' => $user->id,
            'token_exipry' => Carbon::now()->addHours(2),
        ]);

        $token->save();

        Mail::to($request->email)->send(new RegisterUserMail($user, $token));

        return SuccessResponse('User Created Successfully', $user, 201);
    }

    public function VerifySignUpToken(Request $request)
    {

        $request->validate([
            'token' => 'required|numeric',
            'user_id' => 'required|numeric'
        ]);

        $token = Token::where(['token_type' => 'signup_token', 'token_code' => $request->token, 'token_user' => $request->user_id, ['token_exipry', '>=', Carbon::now()], 'token_status' => 'Valid'])->first();

        $user = User::where(['id' => $request->user_id, 'is_frozen' => false, 'is_deleted' => false])->first();

        if (!$token) return ErrorResponse('Invalid Or Expired Token');
        if (!$user) return ErrorResponse('Invalid User Id');

        $user->email_verified_at = Carbon::now();
        $token->token_status = 'Invalid';

        $user->save();
        $token->save();

        return SuccessResponse('User Verified Successfully', $user);
    }

    public function LoginUser(LoginUserRequest $request)
    {
        $user = User::where(['email' => $request->email, 'is_frozen' => false, 'is_deleted' => false])->first();

        if (!$user) return ErrorResponse('Invalid Login Details');

        $passwordVerify = Hash::check($request->password, $user->password);

        if (!$passwordVerify) return ErrorResponse('Invalid Login Details');

        if (!$user->email_verified_at) return ErrorResponse('User Has Not Been Verified', $user);

        $UserToken = $user->createToken('User Access Token', ['User']);

        $accessToken = $UserToken->accessToken;
        $accessToken->expires_at = Carbon::now()->addWeeks(6);

        $accessToken->save();

        $responseData = [
            'access_token' => explode('|', $UserToken->plainTextToken)[1],
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $UserToken->accessToken->expires_at
            )->toDateTimeString(),
            'UserDetails' => $user
        ];

        return SuccessResponse('User Logged In Successfully', $responseData);
    }

    public function requestResetOtp(Request $request)
    {

        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where(['email' => $request->email, 'is_frozen' => false, 'is_deleted' => false])->first();

        if (!$user) return ErrorResponse('Invalid User Details');

        $resetToken = rand(1111, 9999);

        $token = new Token([
            'token_type' => 'reset_password',
            'token_code' => $resetToken,
            'token_user' => $user->id,
            'token_exipry' => Carbon::now()->addHours(2),
        ]);

        $token->save();

        Mail::to($request->email)->send(new ResetPasswordOtpMail($user, $token));

        return SuccessResponse('Reset Otp Sent Successfully', $user);
    }

    public function resetPassword(Request $request)
    {

        $request->validate([
            'password' => 'required|string',
            'token' => 'required|numeric',
            'user_id' => 'required|numeric'
        ]);

        $token = Token::where(['token_type' => 'reset_password', 'token_code' => $request->token, 'token_user' => $request->user_id, ['token_exipry', '>=', Carbon::now()], 'token_status' => 'Valid'])->first();

        $user = User::where(['id' => $request->user_id, 'is_frozen' => false, 'is_deleted' => false])->first();

        if (!$token) return ErrorResponse('Invalid Or Expired Token');
        if (!$user) return ErrorResponse('Invalid User Id');

        $user->password = Hash::make($request->password);
        $token->token_status = 'Invalid';

        $user->save();
        $token->save();

        return SuccessResponse('User Password Reset Successfully', $user);
    }
}
