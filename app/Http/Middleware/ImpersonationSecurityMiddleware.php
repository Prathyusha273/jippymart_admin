<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseImpersonationService;

class ImpersonationSecurityMiddleware
{
    private $impersonationService;

    public function __construct()
    {
        $this->impersonationService = new FirebaseImpersonationService();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Rate limiting for impersonation requests
        $this->applyRateLimit($request);

        // Validate request origin and referrer
        $this->validateRequestOrigin($request);

        // Log all impersonation attempts
        $this->logImpersonationAttempt($request);

        return $next($request);
    }

    /**
     * Apply rate limiting to prevent abuse
     */
    private function applyRateLimit(Request $request)
    {
        $adminId = auth()->id();
        $key = "impersonation_rate_limit_{$adminId}";
        
        $attempts = Cache::get($key, 0);
        $maxAttempts = 50; // Max 10 impersonations per hour
        $decayMinutes = 60; // 1 hour

        if ($attempts >= $maxAttempts) {
            Log::warning('Impersonation rate limit exceeded', [
                'admin_id' => $adminId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            abort(429, 'Too many impersonation attempts. Please try again later.');
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
    }

    /**
     * Validate request origin and referrer for security
     */
    private function validateRequestOrigin(Request $request)
    {
        $allowedOrigins = [
            'admin.jippymart.in',
            'localhost', // For development
            '127.0.0.1'  // For development
        ];

        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $host = $request->getHost();

        // Check if request is from allowed origin
        $isValidOrigin = false;
        foreach ($allowedOrigins as $allowedOrigin) {
            if (strpos($origin, $allowedOrigin) !== false || 
                strpos($referer, $allowedOrigin) !== false ||
                strpos($host, $allowedOrigin) !== false) {
                $isValidOrigin = true;
                break;
            }
        }

        if (!$isValidOrigin && !app()->environment('local')) {
            Log::warning('Invalid impersonation request origin', [
                'origin' => $origin,
                'referer' => $referer,
                'host' => $host,
                'ip' => $request->ip(),
                'admin_id' => auth()->id()
            ]);

            abort(403, 'Invalid request origin');
        }
    }

    /**
     * Log impersonation attempts for security audit
     */
    private function logImpersonationAttempt(Request $request)
    {
        $logData = [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email ?? 'unknown',
            'restaurant_id' => $request->input('restaurant_id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'request_data' => $request->except(['_token', 'password'])
        ];

        Log::info('Impersonation attempt', $logData);
    }
}
