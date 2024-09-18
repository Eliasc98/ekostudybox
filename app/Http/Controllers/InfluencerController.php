<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InfluencerController extends Controller
{
    //

    public function generateCode(Request $request)
    {
        $request->validate([
            'influencer_id' => 'required|exists:users,id',
        ]);


        //generate influencer's id        
        $code = strtoupper(Str::random(6)) . rand(1000, 9999);

        // Ensure uniqueness by checking if it exists in your database
        while (DB::table('referral_codes')->where('code', $code)->exists()) {
            $code = strtoupper(Str::random(6)) . rand(1000, 9999);
        }

        //referral code

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
    
        $length = 6; 

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        ReferralCode::create([
            'influencer_id' => $code,
            'influencer_name' => $request->influencer_name,
            'code' => $randomString,
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
