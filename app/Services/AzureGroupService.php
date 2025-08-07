<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AzureGroupService
{
    /**
     * Cache TTL in minutes for group membership
     */
    const CACHE_TTL = 30;

    /**
     * Maximum number of groups to fetch per page (Microsoft Graph limit is 999)
     */
    const MAX_PAGE_SIZE = 999;

    /**
     * Check if the user with the given token is a member of the target Azure AD group
     * Uses Redis cache to store results for 30 minutes before checking the API again
     *
     * @param  string  $token  The Azure AD token
     * @param  string|null  $groupId  Optional specific group ID to check, defaults to configured target group
     * @return bool True if the user is a member of the group, false otherwise
     */
    public function isUserInGroup(string $token, ?string $groupId = null): bool
    {
        try {
            // Get target group ID from config or use provided group ID
            $targetGroupId = $groupId ?? config('azure.target_group_id');

            if (empty($targetGroupId)) {
                Log::warning('No target group ID found in config');

                return false;
            }

            // Extract object ID from token
            $objectId = $this->getObjectIdFromToken($token);
            if (! $objectId) {
                Log::warning('Could not extract object ID from token');

                return false;
            }

            // Create cache key based on user's object ID and target group
            $cacheKey = "user_group_membership:{$objectId}:{$targetGroupId}";

            // Check cache first
            if (Cache::has($cacheKey)) {
                $isInGroup = Cache::get($cacheKey);
                Log::info('Retrieved group membership from cache', [
                    'object_id' => $objectId,
                    'group_id' => $targetGroupId,
                    'is_in_group' => $isInGroup,
                ]);

                return $isInGroup;
            }

            // If not in cache, check via API
            Log::info('Checking if user is in group via API', [
                'object_id' => $objectId,
                'group_id' => $targetGroupId,
            ]);

            // Check all pages of group memberships
            $isInGroup = $this->checkGroupMembershipWithPagination($token, $objectId, $targetGroupId);

            // Cache the result for 30 minutes
            Cache::put($cacheKey, $isInGroup, now()->addMinutes(self::CACHE_TTL));

            return $isInGroup;
        } catch (\Exception $e) {
            Log::error('Error checking group membership', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Check group membership with pagination support
     *
     * @param  string  $token  The Azure AD token
     * @param  string  $objectId  The user's object ID
     * @param  string  $targetGroupId  The target group ID to check
     * @return bool True if the user is a member of the group, false otherwise
     */
    private function checkGroupMembershipWithPagination(string $token, string $objectId, string $targetGroupId): bool
    {
        $isInGroup = false;
        $pageCount = 0;
        $totalGroups = 0;
        
        // Start with the initial URL with maximum page size
        $url = "https://graph.microsoft.com/v1.0/users/{$objectId}/memberOf?\$top=" . self::MAX_PAGE_SIZE;

        while ($url && !$isInGroup) {
            $pageCount++;
            
            // Make request to Microsoft Graph API
            $response = Http::withToken($token)->get($url);

            if (!$response->successful()) {
                Log::warning('Failed to fetch user group memberships', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'page' => $pageCount,
                ]);
                return false;
            }

            $data = $response->json();
            $groups = $data['value'] ?? [];
            $totalGroups += count($groups);

            // Check if any of the memberships match our target group ID
            foreach ($groups as $group) {
                if (isset($group['id']) && $group['id'] === $targetGroupId) {
                    $isInGroup = true;
                    Log::info('User is a member of the target group', [
                        'user_object_id' => $objectId,
                        'group_id' => $targetGroupId,
                        'group_name' => $group['displayName'] ?? 'Unknown',
                        'found_on_page' => $pageCount,
                        'total_groups_checked' => $totalGroups,
                    ]);
                    break;
                }
            }

            // Get the next page URL if available and we haven't found the group yet
            $url = $isInGroup ? null : ($data['@odata.nextLink'] ?? null);
            
            if ($url) {
                Log::debug('Fetching next page of group memberships', [
                    'user_object_id' => $objectId,
                    'page' => $pageCount + 1,
                    'groups_checked_so_far' => $totalGroups,
                ]);
            }
        }

        if (!$isInGroup) {
            Log::info('User is NOT a member of the target group', [
                'user_object_id' => $objectId,
                'group_id' => $targetGroupId,
                'total_groups_checked' => $totalGroups,
                'pages_checked' => $pageCount,
            ]);
        }

        return $isInGroup;
    }

    /**
     * Extract the object ID (oid) from an Azure AD JWT token
     *
     * @param  string  $token  The JWT token
     * @return string|null The object ID or null if not found
     */
    private function getObjectIdFromToken(string $token): ?string
    {
        try {
            $tokenParts = explode('.', $token);
            if (count($tokenParts) >= 2) {
                // Decode the token payload (second part of JWT)
                $payload = json_decode(base64_decode(str_replace(
                    ['-', '_'],
                    ['+', '/'],
                    $tokenParts[1]
                )), true);

                // The 'oid' claim is the Azure AD object ID for the user
                return $payload['oid'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error extracting object ID from token', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
} 