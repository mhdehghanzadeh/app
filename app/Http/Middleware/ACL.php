<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role_Permission;
use Illuminate\Support\Facades\Auth;

class ACL
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $opration)
    {
        $user = Auth::user();
        if( $user->role_id == 1){
            return $next($request);
        }
        $permission = Permission::where('name', $opration)->firstOrFail();
        if(Role_Permission::where('role_id', $user->role_id)->where('permission_id', $permission->id)->exists()){
            return $next($request);
        }
        abort(403, 'دسترسی به این بخش را ندارید');
    }
}
