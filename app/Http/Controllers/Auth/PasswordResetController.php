<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetEmail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset email to the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPasswordResetEmail(Request $request)
    {
        // Validate the email address
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if the user has any linked social accounts
        if ($user->linkedSocialAccounts()->count() > 0) {
            // If the user has linked social accounts, they cannot reset their password
            return response()->json([
                'error' => 'Users who signed up with social accounts cannot reset their password.',
            ], 400);
        }

        // Generate a reset token
        $token = Str::random(60);

        // Store the token in the password_reset_tokens table
        PasswordResetToken::updateOrCreate(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        // Send reset email (You need to configure your email system)
        Mail::to($user->email)->send(new PasswordResetEmail($token, $user));

        // For now, we'll simulate email sending
        return response()->json([
            'message' => 'Password reset email sent successfully.',
        ], 200);
    }

    /**
     * Reset the user's password using the reset token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        // Validate the required fields
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if the reset token exists and is valid (not expired)
        $resetToken = PasswordResetToken::where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetToken) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired password reset token.']
            ]);
        }

        // Check if the token is older than 1 hour (expires after 1 hour)
        if (Carbon::parse($resetToken->created_at)->addHour()->isPast()) {
            $resetToken->delete(); // Token is expired, delete it
            throw ValidationException::withMessages([
                'token' => ['The password reset token has expired.']
            ]);
        }

        // Find the user and update the password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password); // Hash the new password
        $user->save();

        // Delete the token after use
        $resetToken->delete();

        return response()->json([
            'message' => 'Password reset successfully.',
        ], 200);
    }
}
