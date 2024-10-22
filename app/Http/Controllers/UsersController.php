<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Role;
use App\Models\User;
use App\Rules\PhoneNumber;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use \Morilog\Jalali\Jalalian;
use App\Traits\General;

class UsersController extends Controller
{
    use General;

    public function index()
    {
        return Inertia::render('Users/Index', [
            'filters' => Request::all('search', 'role', 'trashed'),
            'users' => User::filter(Request::only('search', 'role', 'trashed'))
                ->orderBy('created_at', 'DESC')
                ->whereHas('role', function ($query) {
                    return $query->where('name', '!=', 'contact');
                })
                ->paginate(10)
                ->through(fn($user) => [
                    'username' => $user->username,
                    'id' => $user->id,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'role_id' => $user->role ? $user->role->translate : null,
                    'active' => $user->active,
                    'created_at' => Jalalian::forge($user->created_at)->format('H:i Y/m/d'),
                    'deleted_at' => $user->deleted_at ? Jalalian::forge($user->deleted_at)->format('H:i Y/m/d') : null,
                ]),
            'page' => Request::get('page'),
        ]);
    }

    public function create()
    {
        $roles = Role::orderBy('id')
            ->get()
            ->map
            ->only('id', 'translate');

        if (Request::get('modal') == 'true') {
            $data = [];
            $data['roles'] = $roles;
            return $data;
        }

        return Inertia::render('Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store()
    {
        Request::merge([
            'phone' => $this->to_english_numbers(Request::get('phone')),
        ]);
        Request::validate([
            'username' => ['required', 'max:20', Rule::unique('users')],
            'phone' => ['required', 'max:11', new PhoneNumber, Rule::unique('users')],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')],
            'password' => ['required'],
            'role_id' => ['required'],
            'active' => ['required', 'boolean'],
        ]);

        User::create([
            'username' => Request::get('username'),
            'phone' => Request::get('phone'),
            'email' => Request::get('email'),
            'password' => Request::get('password'),
            'role_id' => Request::get('role_id'),
            'active' => Request::get('active'),
        ]);

        return Redirect::route('users')->with('success', 'کاربر با موفقیت ثبت گردید');
    }

    public function edit(User $user)
    {

        $roles = Role::orderBy('id')
            ->get()
            ->map
            ->only('id', 'translate');

        $user = [
            'id' => $user->id,
            'username' => $user->username,
            'phone' => $user->phone,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'active' => $user->active,
            'deleted_at' => $user->deleted_at,
        ];

        if (Request::get('modal') == 'true') {
                $data = [];
                $data['user'] = $user;
                $data['roles'] = $roles;
                return $data;
        }

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(User $user)
    {
        Request::merge([
            'phone' => $this->to_english_numbers(Request::get('phone')),
        ]);
        Request::validate([
            'phone' => ['required', 'max:11', new PhoneNumber, Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'max:20', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable'],
            'role_id' => ['required'],
            'active' => ['required', 'boolean'],
        ]);

        $user->update(Request::only('username', 'phone', 'email', 'role_id', 'active'));

        if (Request::get('password')) {
            $user->update(['password' => Request::get('password')]);
        }

        return Redirect::back()->with('success', 'کاربر با موفقیت ویرایش گردید');
    }

    public function destroy(User $user)
    {
        $user->delete();
        $contact = Contact::where('user_id', $user->id);
        $contact->delete();
        return Redirect::back()->with('success', 'کاربر با موفقیت حذف گردید');
    }

    public function restore(User $user)
    {
        $user->restore();
        $contact = Contact::where('user_id', $user->id);
        $contact->restore();
        return Redirect::back()->with('success', 'کاربر با موفقیت بازیابی گردید');
    }
}
