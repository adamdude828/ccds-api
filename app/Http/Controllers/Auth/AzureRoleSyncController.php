<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AzureRoleSyncController extends Controller
{
    public function syncRoles(Request $request)
    {
        $user = auth()->user();

        // Get Azure groups from the request
        $azureGroups = $request->input('azure_groups', []);

        // Map Azure groups to application roles
        $roleMap = [
            'group-ccds-mis' => RoleType::MIS->value,
            'group-ccds-user' => RoleType::USER->value,
            'group-ccds-admin' => RoleType::ADMIN->value,
        ];

        // Determine which roles to assign based on Azure groups
        $assignedRoles = [];
        foreach ($azureGroups as $group) {
            if (isset($roleMap[$group])) {
                $assignedRoles[] = $roleMap[$group];
            }
        }

        // Sync roles - this will remove any roles not in the array
        $user->syncRoles($assignedRoles);

        return response()->json([
            'message' => 'Roles synced successfully',
            'roles' => $assignedRoles,
        ]);
    }
}
