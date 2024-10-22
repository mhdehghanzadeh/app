<?php

namespace App\Http\Controllers;

use App\Models\Consultant;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use \Morilog\Jalali\Jalalian;

class ConsultantsController extends Controller
{
    
    public function index()
    {
        return Inertia::render('Consultants/Index', [
            'filters' => Request::all('search', 'trashed'),
            'consultants' => Consultant::filter(Request::only('search', 'trashed'))
                ->paginate(10)
                ->through(fn($consultant) => [
                    'id' => $consultant->id,
                    'user_id' => $consultant->user_id,
                    'name' => $consultant->name,
                    'npcode' => $consultant->npcode,
                    'about' => $consultant->about,
                    'active' => $consultant->active,
                    'deleted_at' => $consultant->deleted_at ? Jalalian::forge($consultant->deleted_at)->format('H:i Y/m/d') : null,
                ]),
            'page' => Request::get('page'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Consultants/Create', [
            'users' => User::select('id', 'username', 'email')->get(),
        ]);
    }

    public function store()
    {
        Request::validate([
            'user_id' => ['required', 'integer'],
            'name' => ['required', 'max:50'],
            'about' => ['nullable'],
            'specialty' => ['nullable'],
            'npcode' => ['nullable', 'integer'],
            'adress' => ['nullable'],
            'location' => ['nullable'],
            'telephone' => ['nullable'],
            'time' => ['nullable'],
            'price' => ['required', 'integer'],
            'photo' => ['nullable', 'image'],
            'active' => ['required', 'boolean'],
        ]);

        Consultant::create([
            'user_id' => Request::get('user_id'),
            'name' => Request::get('name'),
            'about' => Request::get('about'),
            'specialty' => Request::get('specialty'),
            'npcode' => Request::get('npcode'),
            'adress' => Request::get('adress'),
            'location' => Request::get('location'),
            'telephone' => Request::get('telephone'),
            'time' => Request::get('time'),
            'price' => Request::get('price'),
            'photo' => Request::file('photo') ? Request::file('photo')->store('public/consultants') : null,
            'active' => Request::get('active'),
        ]);

        return Redirect::route('consultants')->with('success', 'پزشک با موفقیت ثبت گردید.');
    }

    public function edit(Consultant $consultant)
    {
        return Inertia::render('Consultants/Edit', [
            'consultant' => [
                'id' => $consultant->id,
                'user_id' => $consultant->user_id,
                'name' => $consultant->name,
                'about' => $consultant->about,
                'specialty' => $consultant->specialty,
                'npcode' => $consultant->npcode,
                'adress' => $consultant->adress,
                'location' => $consultant->location,
                'telephone' => $consultant->telephone,
                'time' => $consultant->time,
                'price' => $consultant->price,
                'photo' => $consultant->photo,
                'active' => $consultant->active,
                'deleted_at' => $consultant->deleted_at,
            ],
            'users' => User::select('id', 'username', 'email')->get(),
        ]);
    }

    public function update(Consultant $consultant)
    {
        Request::validate([
            'user_id' => ['required', 'integer'],
            'name' => ['required', 'max:50'],
            'about' => ['nullable'],
            'specialty' => ['nullable'],
            'npcode' => ['nullable', 'integer'],
            'adress' => ['nullable'],
            'location' => ['nullable'],
            'telephone' => ['nullable'],
            'time' => ['nullable'],
            'price' => ['required', 'integer'],
            'photo' => Request::file('photo') ? ['nullable', 'image'] : ['nullable'],
            'active' => ['required', 'boolean'],
        ]);

        $consultant->update(Request::only('user_id', 'name', 'about', 'specialty', 'npcode', 'adress', 'location', 'telephone', 'time', 'price', 'active'));

        if (Request::file('photo')) {
            $consultant->update(['photo' => Request::file('photo')->store('public/consultants')]);
        }

        return Redirect::back()->with('success', 'پزشک با موفقیت ویرایش گردید.');
    }

    public function destroy(Consultant $consultant)
    {
        $consultant->delete();

        return Redirect::back()->with('success', 'پزشک با موفقیت حذف گردید');
    }

    public function restore(Consultant $consultant)
    {
        $consultant->restore();

        return Redirect::back()->with('success', 'پزشک با موفقیت بازیابی گردید');
    }
}
