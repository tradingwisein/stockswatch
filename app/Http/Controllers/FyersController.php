<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FyersController extends Controller
{
    public function login()
    {
        $appId = config('services.fyers.app_id');
        $redirectUri = config('services.fyers.redirect_uri');

        $state = bin2hex(random_bytes(16));

        session(['fyers_state' => $state]);


        $url = 'https://api-t1.fyers.in/api/v3/generate-authcode?' . http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect()->away($url);
    }
    
    public function callback(Request $request)
    {
        $authCode = $request->query('auth_code');
    
        if (!$authCode) {
            return response()->json([
                'error' => 'Authorization code missing'
            ], 400);
        }
    
        $appId = config('services.fyers.app_id');
        $secretId = config('services.fyers.secret_id');
    
        $appIdHash = hash(
            'sha256',
            $appId . ':' . $secretId
        );
    
        $response = \Illuminate\Support\Facades\Http::post(
            'https://api-t1.fyers.in/api/v3/validate-authcode',
            [
                'grant_type' => 'authorization_code',
                'appIdHash' => $appIdHash,
                'code' => $authCode,
            ]
        );
    
        if (!$response->successful()) {
            return response()->json([
                'error' => 'FYERS authentication failed',
                'response' => $response->json(),
            ], 400);
        }
    
        $data = $response->json();
    
        if (($data['s'] ?? null) !== 'ok') {
            return response()->json([
                'error' => 'FYERS returned an error',
                'response' => $data,
            ], 400);
        }
    
        session([
            'fyers_access_token' => $data['access_token'],
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'FYERS connected successfully',
            'token' => $data['access_token'],
        ]);
    }
    
    public function profile()
    {
        $token = session('fyers_access_token');
    
        if (!$token) {
            return response()->json([
                'error' => 'Not connected to FYERS'
            ], 401);
        }
    
        $appId = config('services.fyers.app_id');
    
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => $appId . ':' . $token,
        ])->get(
            'https://api-t1.fyers.in/api/v3/profile'
        );
    
        return response()->json($response->json());
    }
    
    public function quote()
    {
        $token = session('fyers_access_token');
    
        if (!$token) {
            return response()->json([
                'error' => 'Not connected to FYERS'
            ], 401);
        }
    
        $appId = config('services.fyers.app_id');
    
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => $appId . ':' . $token,
        ])->get(
            'https://api-t1.fyers.in/data/quotes',
            [
                'symbols' => 'NSE:RELIANCE-EQ',
            ]
        );
    
        return response()->json($response->json());
    }
    
    public function getQuote($symbol)
    {
        $token = session('fyers_access_token');
    
        if (!$token) {
            return response()->json([
                'error' => 'Not connected to FYERS'
            ], 401);
        }
    
        $appId = config('services.fyers.app_id');
    
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => $appId . ':' . $token,
        ])->get(
            'https://api-t1.fyers.in/data/quotes',
            [
                'symbols' => $symbol,
            ]
        );
    
        return response()->json($response->json());
    }

    public function setToken($token) {
        session([
            'fyers_access_token' => $token,
        ]);

        die('Access token set!');
    }
}