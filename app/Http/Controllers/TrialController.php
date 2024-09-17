<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class TrialController extends Controller
{
    //

    public function activateTrial(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = User::find($request->user_id);
        $user->trial_ends_at = Carbon::now()->addDays(30);
        $user->trial_active = true;
        $user->save();

        return response()->json(['message' => 'Trial activated for 30 days']);
    }

    public function autoDeduct()
    {
        $users = User::where('trial_active', true)
                      ->where('trial_ends_at', '<', Carbon::now())
                      ->get();

        foreach ($users as $user) {
            // Deduct the fee (example logic)
            $user->trial_active = false;
            $user->save();

            // Deduct payment logic here
        }

        return response()->json(['message' => 'Auto deduction process completed']);
    }
}
