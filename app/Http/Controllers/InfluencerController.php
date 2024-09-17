<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfluencerController extends Controller
{
    //

    public function generateCode(Request $request)
    {
        $request->validate([
            'influencer_id' => 'required|exists:users,id',
        ]);

        $code = strtoupper(str_random(10));

        ReferralCode::create([
            'influencer_id' => $request->influencer_id,
            'code' => $code,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Referral code generated', 
            'code' => $code
        ]);
    }

    public function listCodes()
    {
        $codes = ReferralCode::with('influencer')->get();
        return response()->json($codes);
    }
}
