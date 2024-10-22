<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Notification_Seen;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use \Morilog\Jalali\Jalalian;

class NotificationsController extends Controller
{
    
    public function index()
    {
        dd(1);
        return Inertia::render('Notifications/Index', [
            'filters' => Request::all('search', 'trashed'),
            'notifications' => Notification::filter(Request::only('search', 'trashed'))
                ->paginate(10)
                ->through(fn($notification) => [
                    'id' => $notification->id,
                    'subject' => $notification->subject,
                    'descriptions' => $notification->descriptions,
                    'active' => $notification->active,
                    'type' => $notification->type,
                    'created_at' => $notification->created_at,
                    'deleted_at' => $notification->deleted_at,
                ]),
            'page' => Request::get('page'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Notifications/Create');
    }

    public function store()
    {
        Request::validate([
            'subject' => ['required', 'max:200'],
            'descriptions' => ['required'],
            'active' => ['required', 'boolean'],
        ]);

        Notification::create([
            'subject' => Request::get('subject'),
            'descriptions' => Request::get('descriptions'),
            'active' => Request::get('active'),
        ]);

        return Redirect::route('notifications')->with('success', 'اعلان با موفقیت ثبت گردید.');
    }

    public function edit(Notification $notification)
    {
        $notification = [
            'id' => $notification->id,
            'subject' => $notification->subject,
            'descriptions' => $notification->descriptions,
            'active' => $notification->active,
            'created_at' => $notification->created_at,
            'deleted_at' => $notification->deleted_at,
        ];  

        if (Request::get('modal') == 'true') {
            $data = [];
            $data['notification'] = $notification;
            return $data;
        }

        return Inertia::render('Notifications/Edit', [
            'notification' => $notification,
        ]);
    }

    public function update(Notification $notification)
    {
        Request::validate([
            'subject' => ['required', 'max:200'],
            'descriptions' => ['required'],
            'active' => ['required', 'boolean'],
        ]);

        $notification->update(Request::only('subject', 'descriptions', 'active'));

        return Redirect::back()->with('success', 'اعلان با موفقیت ویرایش گردید.');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        return Redirect::back()->with('success', 'اعلان با موفقیت حذف گردید');
    }

    public function restore(Notification $notification)
    {
        $notification->restore();

        return Redirect::back()->with('success', 'اعلان با موفقیت بازیابی گردید');
    } 

    public function contact_index()
    {
        dd(1);
        return Inertia::render('Notifications/ContactIndex', [
            'filters' => Request::all('type'),
            'notifications' => Notification::filter(Request::only('type'))
            ->orderBy('created_at', 'asc')
            ->paginate(10)
            ->through(fn($notification) => [
                'id' => $notification->id,
                'subject' => $notification->subject,
                'created_at' => $notification->created_at,
            ]),
            'unread' => Notification::filter(['type' => 'unread'])->where('active', true)->count(),
            'all' => Notification::where('active', true)->count(),

        ]);
    }

    public function show(Notification $notification)
    {
        Notification_Seen::firstOrCreate(
            [ 'notification_id' => $notification->id ],
            [ 'user_id' => Auth::user()->id ]
        );
        $notification = [
            'id' => $notification->id,
            'subject' => $notification->subject,
            'descriptions' => $notification->descriptions,
            'created_at' => $notification->created_at,
        ];
        if (Request::get('modal') == 'true') {
            $data = [];
            $data['notification'] = $notification;
            return $data;
        }
        return Inertia::render('Notifications/show', [
            'notification' => $notification,
        ]);
    }
    
}
