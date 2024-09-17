<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\GenerateCode;
use App\Models\Activation;
use Carbon\Carbon;

class GenerateCodeController extends Controller
{
    //
    public function generateAndStoreCode(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'number_of_months' => 'required|integer',
            'number_of_users' => 'required|integer',
            'platform' => 'required|string',
            'client_name' => 'required|string',
            'client_phone_number' => 'required|string',
            'client_email_address' => 'required|email',
            'client_remarks' => 'nullable|string',
        ]);

        // Generate a random 12-digit hexadecimal code
        $generatedCode = strtoupper(Str::random(12)); // 6 characters generates a 12-digit hexadecimal code

        // Store the generated code and client information in the database
        $storedCode = GenerateCode::create([
            'code_generated' => $generatedCode,
            'number_of_months' => $validatedData['number_of_months'],
            'number_of_users' => $validatedData['number_of_users'],
            'platform' => $validatedData['platform'],
            'client_name' => $validatedData['client_name'],
            'client_phone_number' => $validatedData['client_phone_number'],
            'client_email_address' => $validatedData['client_email_address'],
            'client_remarks' => $validatedData['client_remarks'],
        ]);

        if($storedCode){
            $response = [
                "status" => "success",
                "message" => "code generated successfully",
                "data" => $storedCode
            ];

            return response()->json($response, 200);
        } else{
            $response = [
                "status" => "failed",
                "message" => "unable to generate code",
                
            ];
            return response()->json($response);
        }

    }

    public function activateCode(Request $request){

        $validatedData = $request->validate([
            'activate_code' => 'required|string',
        ]);

        // Check if the code exists in the generated_codes table
        $generatedCode = GenerateCode::where('code_generated', $validatedData['activate_code'])->first();

        if (!$generatedCode) {
            return response()->json(['status' => 'failed', 'message' => 'Invalid code']);
        }

        $codeCheck = Activation::where('generated_code_id', $generatedCode->id)->first();

        if($codeCheck){
            // Check if the code has been fully used
            if ($generatedCode->number_of_users === $codeCheck->number_of_used) {

                return response()->json(['status' => 'failed','message' => 'Code has been fully used'], 400);

            }else{
                
                $codeCheck->number_of_used += 1;
                $codeCheck->save();

                return response()->json(['status' => 'success','message' => 'Code activated for another user successfully', 'data'=>$codeCheck], 200);
            }
        }

        

        // Calculate expiry date
        $expiryDate = Carbon::now()->addMonths($generatedCode->number_of_months)->toDateString();

        // Create activation record
        $activation = Activation::create([
            'generated_code_id' => $generatedCode->id,
            'number_of_used' => 1, // Set to 1 as this is the first use
            'number_of_users' => $generatedCode->number_of_users,
            'name_of_client'=> $generatedCode->client_name,
            'current_date' => Carbon::now()->toDateString(),
            'expiry_date' => $expiryDate,
        ]);

        

        if($activation){
            $response = [
                "status" => "success",
                "message" => "code activated successfully",
                "data" => $activation
            ];

            return response()->json($response, 200);
        } else{
            $response = [
                "status" => "failed",
                "message" => "unable to activate code",
                
            ];
            return response()->json($response);
        }
    }
    
}
