<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AzureGroupService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class ValidateAzureToken
{
    protected $azureGroupService;

    public function __construct(AzureGroupService $azureGroupService)
    {
        $this->azureGroupService = $azureGroupService;
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            // Detailed request logging
            Log::info('Request details:', [
                'uri' => $request->getRequestUri(),
                'method' => $request->method(),
                'all_headers' => $request->headers->all(),
                'server_vars' => array_filter($_SERVER, function ($key) {
                    return strpos($key, 'HTTP_') === 0 || $key === 'CONTENT_TYPE' || $key === 'CONTENT_LENGTH';
                }, ARRAY_FILTER_USE_KEY),
                'authorization_header' => $request->header('Authorization'),
                'provider_token_header' => $request->header('Provider-Token'),
                'http_authorization' => $_SERVER['HTTP_AUTHORIZATION'] ?? 'not set',
                'bearer_token' => $request->bearerToken(),
            ]);

            $token = $request->header('Provider-Token') ?? $request->bearerToken();
            Log::info('Token received:', [
                'has_token' => ! empty($token),
                'token_length' => $token ? strlen($token) : 0,
                'token_start' => $token ? substr($token, 0, 10).'...' : null,
            ]);

            if (! $token) {
                Log::warning('No token provided in request');

                return response()->json(['error' => 'No token provided'], 401);
            }

            try {
                Log::info('Attempting to validate Azure token');
                $socialUser = Socialite::driver('azure')
                    ->stateless()
                    ->userFromToken($token);

                Log::info('Azure token validated', [
                    'email' => $socialUser->getEmail(),
                    'name' => $socialUser->getName(),
                ]);

                $user = User::firstOrCreate(
                    ['email' => $socialUser->getEmail()],
                    [
                        'name' => $socialUser->getName(),
                        'azure_id' => $socialUser->getId(),
                    ]
                );

                $user->update([
                    'azure_token' => $token,
                    'token_expires_at' => now()->addHours(1),
                ]);

                // Check if user is in the target group
                $isInTargetGroup = $this->azureGroupService->isUserInGroup($token);
                Log::info('User target group membership check', [
                    'user_id' => $user->id,
                    'is_in_target_group' => $isInTargetGroup,
                    'target_group_id' => config('azure.target_group_id'),
                ]);

                // Only proceed if user is in the target group
                if (! $isInTargetGroup) {
                    Log::warning('User is not in the required Azure AD group', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'target_group_id' => config('azure.target_group_id'),
                    ]);

                    return response()->json([
                        'error' => 'Forbidden',
                        'message' => 'You do not have access to this application',
                    ], 403);
                }

                // Authenticate the user
                auth()->login($user);
                Log::info('User authenticated successfully', ['user_id' => $user->id]);

            } catch (\Exception $e) {
                Log::error('Azure token validation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json(['error' => 'Invalid token'], 401);
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Middleware exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}
