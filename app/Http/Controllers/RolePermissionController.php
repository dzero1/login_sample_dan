<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolePermissionController extends Controller
{
    public function getRoles(Request $request, $id = null)
    {
        $user = $id ? User::where('id', $id)->first() : Auth::user();
        return ['roles' => $user->getRoleNames()];
    }
    
    public function assignRole(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        return ['success' => $user->assignRole($request->role)];
    }

    public function removeRole(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        return ['success' => $user->removeRole($request->role)];
    }

    public function getPermissions(Request $request, $id = null)
    {
        $user = $id ? User::where('id', $id)->first() : Auth::user();
        return ['permissions' => $user->getAllPermissions()];
    }
    
    public function assignPermissions(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        return ['success' => $user->givePermissionTo($request->permission)];
    }

    public function removePermissions(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        return ['success' => $user->revokePermissionTo($request->permission)];
    }
}
