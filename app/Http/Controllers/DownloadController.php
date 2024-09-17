<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Download;

class DownloadController extends Controller
{
    public function store(Request $request)
    {
        // Validate request data
        $request->validate([
            'ip_address' => 'required',
            'location' => 'required|string',
        ]);

        // Check if IP address already exists in the database
        $download = Download::where('ip_address', $request->ip_address)->first();

        if ($download) {
            // Update existing record
            $download->clicks += 1;
            $download->save();

            if($download->save()){
                $response = [
                    'status' => 'success',
                    'message' => 'download info updated successfully',
                    'data' => $download
                ];

                return response()->json($response);
            }
        } else {
            // Create new record
           $download =  Download::create([
                            'ip_address' => $request->ip_address,
                            'location' => $request->location,
                            'clicks' => 1
                        ]);
            
            if ($download) {

                $response = [
                    'status' => 'success',
                    'message' => 'download info stored successfully',
                    'data' => $download
                ];

                return response()->json($response);
            } else {

                $response = [
                    'status' => 'failed',
                    'message' => 'unable to store download info'
                ];

                return response()->json($response, 404);
            }
        }
        
    }

    public function getTotalClicks()
    {
        $totalClicks = Download::sum('clicks');

        if($totalClicks){


            $response = [
                'status' => 'success',
                'message' => 'total clicks fetched successfully',
                'data' => $totalClicks ?? 0
            ];

            return response()->json($response, 200);
        }

        
    }
}
