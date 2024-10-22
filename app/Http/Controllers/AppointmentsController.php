<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use App\Models\Appointment;
use DateTime;
use \Morilog\Jalali\Jalalian;

class AppointmentsController extends Controller
{
    public function index()
    {
        return Inertia::render('Appointments/Index', [
            'filters' => Request::all('search'),
            'appointments' => Appointment::paginate(10)->through(fn($appointment) => [
                'id' => $appointment->id,
                'consultant' => $appointment->consultant,
                'reserved_at' => $appointment->reserved_at ? Jalalian::forge($appointment->reserved_at)->format('H:i Y/m/d') : null,
                'created_at' => $appointment->created_at ? Jalalian::forge($appointment->created_at)->format('H:i Y/m/d') : null,
            ]),
            'page' => Request::get('page'),
        ]);
    }

    public function contact_index()
    {
        return Inertia::render('Appointments/ContactIndex', [
            'filters' => Request::all('search'),
            'appointments' => Appointment::paginate(10)->through(fn($appointment) => [
                'id' => $appointment->id,
                'consultant' => $appointment->consultant,
                'reserved_at' => $appointment->reserved_at ? Jalalian::forge($appointment->reserved_at)->format('H:i Y/m/d') : null,
                'created_at' => $appointment->created_at ? Jalalian::forge($appointment->created_at)->format('H:i Y/m/d') : null,
            ]),
            'page' => Request::get('page'),
        ]);
    }


    public function contact_create()
    {
        return Inertia::render('Appointments/ContactCreate');
    }

    public function contact_store()
    {
        Request::validate([
            'time' => ['required'],
            'date' => ['required'],
        ]);
       
        $date = '2023/' . Request::get('date') . ' ' . Request::get('time');
        $data = [
            'contact_id' => auth()->user()->contact->id,
            'consultant_id' => 1,
            'reserved_at' => new DateTime($date)
        ];
        Appointment::create($data);
        return Redirect::route('appointments.contact')->with('success', 'نوبت با موفقیت رزرو گردید');
    }

}
