<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify($id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return redirect('https://good.ihor.fun/register')->with('error', 'Invalid activation code.');
        }

        if ($user->email_verified_at == null) {
            $user->email_verified_at = now();
            $user->save();
        }

        return redirect('https://good.ihor.fun/confirm-email')->with('success', 'Email verified successfully. You can now login.');
    }
}
