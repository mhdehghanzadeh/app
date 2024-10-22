<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PermissionsController extends Controller
{
    public function index()
    {
        return Inertia::render('Permissions/Index', [
            'filters' => Request::all('search'),
            'permissions' => Permission::filter(Request::only('search'))
                ->paginate(10)
                ->through(fn($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'translate' => $permission->translate,
                    'description' => $permission->description,
                ]),
            'page' => Request::get('page'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Permissions/Create');
    }

    public function store()
    {
        Permission::create(
            Request::validate([
                'name' => ['required', 'max:20', Rule::unique('permissions')],
                'translate' => ['required', 'max:20'],
                'description' => ['nullable', 'max:100'],
            ])
        );

        return Redirect::route('permissions')->with('success', 'نقش با موفقیت ثبت گردید.');
    }

    public function edit(Permission $permission)
    {
        $permission = [
            'id' => $permission->id,
            'name' => $permission->name,
            'translate' => $permission->translate,
            'description' => $permission->description,
        ];

        if (Request::get('modal') == 'true') {
            $data = [];
            $data['permission'] = $permission;
            return $data;
        }

        return Inertia::render('Permissions/Edit', [
            'permission' => $permission,
        ]);
    }

    public function update(Permission $permission)
    {
        $permission->update(
            Request::validate([
                'name' => ['required', 'max:20', Rule::unique('permissions')->ignore($permission->id)],
                'translate' => ['required', 'max:20'],
                'description' => ['nullable', 'max:100'],
            ])
        );

        return Redirect::back()->with('success', 'مجوز با موفقیت ویرایش گردید.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return Redirect::back()->with('success', 'مجوز با موفقیت حذف گردید');
    }

}
