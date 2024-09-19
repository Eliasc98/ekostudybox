<?php

namespace App\Http\Controllers;

use App\Models\ReferralSale;
use App\Models\ReferralCode;

use Illuminate\Http\Request;

class ReferralSaleController extends Controller
{
    //

    public function trackSale(Request $request)
    {       
        $referralCode = ReferralCode::where('code', $request->referral_code)->first();

        ReferralSale::create([
            'referral_code_id' => $referralCode->code,
            'amount' => $request->amount,
        ]);

        return response()->json(['status' => 'success','message' => 'Sale tracked successfully']);
    }

    public function influencerSales(){
        
        $influencer = auth()->user();
        $sales = ReferralSale::with('referralCode')->where('referral_code_id',$influencer->influencer_id)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched Successfully', 
            'data' => $sales
        ]);
    }

    public function listSales()
    {
        $sales = ReferralSale::with('referralCode')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched Successfully', 
            'data' => $sales
        ]);
    }
}
