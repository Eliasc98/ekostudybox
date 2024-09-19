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

        $data = ReferralSale::create([
            'referral_code_id' => $referralCode->id,
            'amount' => $request->amount,
        ]);

        if ($data) {
            $response = [
                'status' => 'success',
                'message' => 'Sale record created successfully',
                'data' =>  $data
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to create sales record'
            ];
            return response()->json($response, 404);
        }

    }

    public function listSales()
    {
        $sales = ReferralSale::with('referralCode')->get();

        if ($sales -> isNotEmpty()) {
            $response = [
                'status' => 'success',
                'message' => 'referral sales fetched successfully',
                'data' =>  $sales
            ];

            return response()->json($response);
        } else {

            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch sales'
            ];
            
            return response()->json($response, 404);
        }

    }
}
