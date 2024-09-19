<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminReferralController extends Controller
{
    public function index()
    {
        $referrals = ReferralCode::withCount('referralSales')->get();

        if ($referrals->isNotEmpty()) {

            $response = [
                'status' => 'success',
                'message' => 'data fetched successfully',
                'data' => $referrals
            ];

            return response()->json($response);
        } else {

            $response = [
                'status' => 'failed',
                'message' => 'unable to fetch data'
            ];

            return response()->json($response, 404);
        }

    }

    public function deleteReferral($id)
    {
       $deleteData = ReferralCode::findOrFail($id)->delete();

        if ($deleteData) {
            $response = [
                'status' => 'success',
                'message' => 'referral deleted successfully'                
            ];

            return response()->json($response);
        } else {
            $response = [
                'status' => 'failed',
                'message' => 'unable to delete referral'
            ];

            return response()->json($response, 404);
        }
        
    }
}
