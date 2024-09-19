<?php

namespace App\Http\Controllers;

use App\Models\ReferralSale;
use App\Models\ReferralCode;

use Illuminate\Http\Request;

class ReferralSaleController extends Controller
{
    
    public function trackSale(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|exists:referral_codes,code',
            'amount' => 'required|numeric',
        ]);

        $referralCode = ReferralCode::where('code', $request->referral_code)->first();

        ReferralSale::create([
            'referral_code_id' => $referralCode->id,
            'amount' => $request->amount,
        ]);

        return response()->json(['message' => 'Sale tracked successfully']);
    }

    public function listSales()
    {
        $sales = ReferralSale::with('referralCode')->get();
        return response()->json($sales);
    }
}
