<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Role_Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use \Morilog\Jalali\Jalalian;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RolesController extends Controller
{
    public function index()
    {
        return Inertia::render('Roles/Index', [
            'filters' => Request::all('search', 'trashed'),
            'roles' => Role::filter(Request::only('search', 'trashed'))
                ->paginate(10)
                ->through(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'translate' => $role->translate,
                    'description' => $role->description,
                    'active' => $role->active,
                    'deleted_at' => $role->deleted_at ? Jalalian::forge($role->deleted_at)->format('H:i Y/m/d') : null,

                ]),
            'page' => Request::get('page'),
        ]);
    }

    public function create()
    {
        $permissions = Permission::select('id', 'translate')->get();

        if (Request::get('modal') == 'true') {
            $data = [];
            $data['permissions'] = $permissions;
            return $data;
        }

        return Inertia::render('Roles/Create',[
            'permissions' => $permissions
        ]);
    }

    public function store()
    {
        Request::validate([
            'name' => ['required', 'max:20', Rule::unique('roles')],
            'translate' => ['required', 'max:20'],
            'description' => ['nullable', 'max:100'],
            'active' => ['required', 'boolean'],
            'permissions' => ['required', 'array'],
        ]);

        $role = Role::create([
            'name' => Request::get('name'),
            'translate' => Request::get('translate'),
            'description' => Request::get('description'),
            'active' => Request::get('active'),
        ]);

        if($role && $role->id && Request::get('permissions')){
            foreach (Request::get('permissions') as $row) {
                Role_Permission::create([
                    'role_id' => $role->id,
                    'permission_id' => $row,
                ]);
            }
        }

        return Redirect::route('roles')->with('success', 'نقش با موفقیت ثبت گردید.');
    }

    public function edit(Role $role)
    {
        if($role->id == 1){
            abort(403, 'دسترسی به این بخش را ندارید');
        }
        $permissions = [];
        foreach ($role->permissions as $row) {
          array_push($permissions, $row->permission_info->id);
        }

        $role = [
            'id' => $role->id,
            'name' => $role->name,
            'translate' => $role->translate,
            'description' => $role->description,
            'active' => $role->active,
            'deleted_at' => $role->deleted_at,
            'permissions' => $permissions
        ];

        if (Request::get('modal') == 'true') {
            $data = [];
            $data['permissions'] = Permission::select('id', 'translate')->get();
            $data['role'] = $role;

            return $data;
        }

        return Inertia::render('Roles/Edit', [
            'role' => $role,
            'permissions' => Permission::select('id', 'translate')->get()
        ]); 
    }

    public function update(Role $role)
    {
        if($role->id == 1 || $role->id == 2){
            abort(403, 'دسترسی به این بخش را ندارید');
        }
        Request::validate([
            'name' => ['required', 'max:20', Rule::unique('roles')->ignore($role->id)],
            'translate' => ['required', 'max:20'],
            'description' => ['nullable', 'max:100'],
            'active' => ['required', 'boolean'],
            'permissions' => ['required', 'array'],
        ]);

        $role->update(Request::only('name', 'translate', 'description', 'active'));
      
        if (Request::get('permissions')) {
            foreach (Request::get('permissions') as $row) {
                //if not exist insert
                if (!Role_Permission::where('role_id', $role->id)->where('permission_id', $row)->exists()) {
                    Role_Permission::create([
                        'role_id' => $role->id,
                        'permission_id' => $row,
                    ]);
                }
            }
            //and other not in array delete
            Role_Permission::where('role_id', $role->id)->whereNotIn('permission_id', Request::get('permissions'))->delete();
        }


        return Redirect::back()->with('success', 'نقش با موفقیت ویرایش گردید.'); 
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return Redirect::back()->with('success', 'نقش با موفقیت حذف گردید');
    }

    public function restore(Role $role)
    {
        $role->restore();

        return Redirect::back()->with('success', 'نقش با موفقیت بازیابی گردید');
    }
}