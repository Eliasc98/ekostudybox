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

        if ($user->save()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Trial activated for 30 days'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to activate trial, please try again later'
            ], 500);
        }
    }

    public function autoDeduct()
    {
        $users = User::where('trial_active', true)
                      ->where('trial_ends_at', '<', Carbon::now())
                      ->get();

        foreach ($users as $user) {
            try {
            
                $user->trial_active = false;
                $user->save();

                // Deduct payment logic here
                
            } catch (\Exception $e) {
               
                \Log::error('Error processing auto deduction for user ID: ' . $user->id . ' - ' . $e->getMessage());
            }
            
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Auto deduction process completed'
        ]);
    }
}
