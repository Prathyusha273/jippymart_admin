<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\Auth\Token\Verifier;
use Firebase\Auth\Token\Exception\InvalidToken;
use Illuminate\Support\Facades\Log;

class ImpersonationController extends Controller
{
    /**
     * Check if there's an active impersonation session
     */
    public function checkImpersonation(Request $request)
    {
        try {
            // Check for impersonation key in URL parameters
            $impersonationKey = $request->get('impersonation_key');
            
            if ($impersonationKey) {
                // Retrieve impersonation data from cache
                $impersonationData = \Illuminate\Support\Facades\Cache::get($impersonationKey);
                
                if ($impersonationData) {
                    // Check if token is not expired
                    if (time() > $impersonationData['expires_at']) {
                        // Token expired, clear cache
                        \Illuminate\Support\Facades\Cache::forget($impersonationKey);
                        return response()->json(['has_impersonation' => false]);
                    }
                    
                    try {
                        // Verify the token
                        $verifier = app(Verifier::class);
                        $verifiedToken = $verifier->verifyIdToken($impersonationData['token']);
                        
                        // Check if token is for the correct restaurant
                        if ($verifiedToken->getClaim('uid') === $impersonationData['restaurant_uid']) {
                            return response()->json([
                                'has_impersonation' => true,
                                'restaurant_uid' => $impersonationData['restaurant_uid'],
                                'restaurant_name' => $impersonationData['restaurant_name'],
                                'token' => $impersonationData['token'],
                                'cache_key' => $impersonationKey
                            ]);
                        }
                    } catch (InvalidToken $e) {
                        Log::warning('Invalid impersonation token: ' . $e->getMessage());
                        // Token is invalid, clear cache
                        \Illuminate\Support\Facades\Cache::forget($impersonationKey);
                    }
                }
            }
            
            return response()->json(['has_impersonation' => false]);
            
        } catch (\Exception $e) {
            Log::error('Error checking impersonation: ' . $e->getMessage());
            return response()->json(['has_impersonation' => false]);
        }
    }
    
    /**
     * Process the impersonation and log in the user
     */
    public function processImpersonation(Request $request)
    {
        try {
            $cacheKey = $request->input('cache_key');
            
            if ($cacheKey) {
                // Retrieve impersonation data from cache
                $impersonationData = \Illuminate\Support\Facades\Cache::get($cacheKey);
                
                if ($impersonationData) {
                    // Check if token is not expired
                    if (time() > $impersonationData['expires_at']) {
                        \Illuminate\Support\Facades\Cache::forget($cacheKey);
                        return response()->json([
                            'success' => false,
                            'message' => 'Impersonation token has expired'
                        ], 400);
                    }
                    
                    try {
                        // Verify and use the token
                        $verifier = app(Verifier::class);
                        $verifiedToken = $verifier->verifyIdToken($impersonationData['token']);
                        
                        if ($verifiedToken->getClaim('uid') === $impersonationData['restaurant_uid']) {
                            // Clear the cache
                            \Illuminate\Support\Facades\Cache::forget($cacheKey);
                            
                            // Set impersonation flag in session
                            session([
                                'is_impersonated' => true,
                                'impersonated_restaurant_uid' => $impersonationData['restaurant_uid'],
                                'impersonated_restaurant_name' => $impersonationData['restaurant_name'],
                                'impersonated_at' => time()
                            ]);
                            
                            Log::info("Admin impersonation successful for restaurant: {$impersonationData['restaurant_name']} (UID: {$impersonationData['restaurant_uid']})");
                            
                            return response()->json([
                                'success' => true,
                                'message' => 'Impersonation successful',
                                'restaurant_name' => $impersonationData['restaurant_name'],
                                'restaurant_uid' => $impersonationData['restaurant_uid']
                            ]);
                        }
                    } catch (InvalidToken $e) {
                        Log::warning('Invalid impersonation token during processing: ' . $e->getMessage());
                        \Illuminate\Support\Facades\Cache::forget($cacheKey);
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid impersonation token'
                        ], 400);
                    }
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No impersonation token found'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Error processing impersonation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing impersonation'
            ], 500);
        }
    }
    
    /**
     * Clear impersonation session data
     */
    private function clearImpersonationSession()
    {
        session()->forget([
            'impersonation_token',
            'impersonation_restaurant_uid',
            'impersonation_restaurant_id',
            'impersonation_restaurant_name',
            'impersonation_timestamp',
            'impersonation_admin_id'
        ]);
    }
    
    /**
     * End impersonation session
     */
    public function endImpersonation(Request $request)
    {
        try {
            // Clear impersonation flags
            session()->forget([
                'is_impersonated',
                'impersonated_restaurant_uid',
                'impersonated_restaurant_name',
                'impersonated_at'
            ]);
            
            Log::info('Admin impersonation ended');
            
            return response()->json([
                'success' => true,
                'message' => 'Impersonation ended successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error ending impersonation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error ending impersonation'
            ], 500);
        }
    }
    
    /**
     * Get current impersonation status
     */
    public function getImpersonationStatus(Request $request)
    {
        $isImpersonated = session('is_impersonated', false);
        
        if ($isImpersonated) {
            return response()->json([
                'is_impersonated' => true,
                'restaurant_uid' => session('impersonated_restaurant_uid'),
                'restaurant_name' => session('impersonated_restaurant_name'),
                'impersonated_at' => session('impersonated_at')
            ]);
        }
        
        return response()->json(['is_impersonated' => false]);
    }
}
