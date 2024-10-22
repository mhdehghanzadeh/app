<?php

namespace App\Http\Controllers;

use App\Models\Consultant;
use App\Models\Contact;
use App\Models\Counseling;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use \Morilog\Jalali\Jalalian;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()->role_id != 2) {
            return Inertia::render('Dashboard/Admin',[
               'info' => [
                'new_counselings' => Counseling::where('active', true)->count(),
                'active_counselings' => Counseling::where('active', true)->count(),
                'contacts' => Contact::count(),
                'income' => [
                    'total' => Payment::where('result', true)->where('verify', true)->sum('amount')
                ],
               ] 
            ]);
        }
        return Inertia::render('Dashboard/User', [
            'consultants' => Consultant::with('user')->get()
                ->transform(fn($consultant) => [
                    'id' => $consultant->id,
                    'online' => $consultant->user->isOnline(),
                    'last_seen' => Jalalian::forge($consultant->user->last_seen)->ago() ,
                    'name' => $consultant->name,
                    'specialty' => $consultant->specialty,
                    'npcode' => $consultant->npcode,
                    'about' => $consultant->about,
                    'photo' => $consultant->photo,
                    'location' => $consultant->location,
                    'telephone' => $consultant->telephone,
                    'time' => $consultant->time,
                    'price' => $consultant->price,
                    'adress' => $consultant->adress,
                ]),
        ]);
    }

    public function guide()
    {
        if (Auth::user()->role_id != 2) {
            return Inertia::render('Guide/Admin');
        }
    }

}
