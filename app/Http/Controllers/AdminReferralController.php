<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminReferralController extends Controller
{
    public function index()
    {
        $referrals = ReferralCode::withCount('referralSales')->get();
        return response()->json($referrals);
    }

    public function deleteReferral($id)
    {
        ReferralCode::findOrFail($id)->delete();
        return response()->json(['message' => 'Referral deleted']);
    }
}
