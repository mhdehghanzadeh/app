<?php

namespace App\Http\Controllers;

use App\Models\Consultant;
use App\Models\Contact;
use App\Models\Counseling;
use App\Models\Payment;
use App\Traits\Chat;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use \Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Auth;
use App\Traits\Notification;

class CounselingsController extends Controller
{
    use Chat;
    use Notification;

    public function index()
    {
        return Inertia::render('Counselings/Index', [
            'filters' => Request::all('search', 'date', 'start', 'end'),
            'counselings' => Counseling::filter(Request::only('search', 'date', 'start', 'end'))
                ->with('contact')
                ->with('consultant')
                ->with('payment')
                ->paginate(10)
                ->through(fn($counseling) => [
                    'id' => $counseling->id,
                    'contact' => $counseling->contact,
                    'payment' => $counseling->payment,
                    'consultant' => $counseling->consultant,
                    'room' => $counseling->room,
                    'price' => $counseling->payment->amount,
                    'result' => $counseling->result,
                    'active' => $counseling->active,
                    'date' => $counseling->date,
                    'remaining' => $counseling->remaining,
                    'unread' => $this->unreadMessages([
                        'userId'=> env('ROCKET_CONSULTANT_ID'),
                        'authToken'=> env('ROCKET_CONSULTANT_TOKEN'),
                        'roomId'=> $counseling->room,
                    ])
                ]),
            'page' => Request::get('page'),
        ]);
    }

    public function show(Counseling $counseling)
    {
        if(($counseling->consultant->id == Auth::user()->consultant?->id || Auth::user()->role->name == 'assistant') && !$counseling->answer){
            try {
                $counseling->update(['answer' => true]);  
                $line1 = "کاربر گرامی ویزیت آنلاین شما توسط پزشک مشاهده و در حال پاسخ دهی میباشد.";
                $line2 = env('SITE_FULL_NAME');
                $message = $line1 . "\r\n" . $line2;
                $this->send_sms($message, $counseling->contact->user->phone);
            } catch (\Exception $exception) {
                
            }
        }

        $data = [
            'username' => 'drkhoshniat',
            'password' => 'mkn1345222',
        ];
        $user = $this->userLogin($data);
        return Inertia::render('Counselings/Chat', [
            'counseling' => [
                'id' => $counseling->id,
                'contact' => $counseling->contact,
                'room' => $counseling->room,
                'contact_online' => $counseling->contact->user->isOnline(),
                'contact_last_seen' => Jalalian::forge($counseling->contact->user->last_seen)->ago(),
                'price' => $counseling->price,
                'active' => $counseling->active,
                'date' => $counseling->date,
                'enable' => $counseling->enable,
                'remaining' => $counseling->remaining,
                'images' => $counseling->contact->images
            ],
            'user' => $user->data,
        ]);
    }

    public function update(Counseling $counseling)
    {
        Request::validate([
            'active' => ['required', 'boolean'],
        ]);

        $counseling->update(Request::only('active'));

        if(Request::get('active') == true){
            return Redirect::back()->with('success', 'ویزیت با موفقیت فعال شد');
        }
        return Redirect::route('counselings')->with('success', 'ویزیت با موفقیت پایان یافت');
    }

    public function contact_index()
    {
        $data = [
            'username' => Auth::user()->phone,
            'password' => base64_encode(Auth::user()->phone),
        ];
        $user = $this->userLogin($data);
        return Inertia::render('Counselings/ContactIndex', [
            'filters' => Request::all('search', 'sort'),
            'counselings' => Counseling::filter(Request::only('search', 'sort'))
                ->with('consultant')
                ->with('payment')
                ->orderBy('created_at', 'asc')
                ->where('contact_id', Auth::user()->contact->id)
                ->paginate(10)
                ->through(fn($counseling) => [
                    'id' => $counseling->id,
                    'consultant_name' => $counseling->consultant->name,
                    'consultant_specialty' => $counseling->consultant->specialty,
                    'consultant_photo' => $counseling->consultant->photo,
                    'payment_amount' => $counseling->payment->amount,
                    'enable' => $counseling->enable,
                    'remaining' => $counseling->remaining,
                    'date' => $counseling->date,
                    'price' => $counseling->payment->amount,
                    'unread' => $this->unreadMessages([
                        'userId'=> $user->data->userId,
                        'authToken'=> $user->data->authToken,
                        'roomId'=> $counseling->room,
                    ])
                ]),
        ]);
    }

    public function contact_show(Counseling $counseling)
    {
        if (Auth::user()->contact->id == $counseling->contact_id) {
            //login user and get token
            $data = [
                'username' => $counseling->contact->user->phone,
                'password' => base64_encode($counseling->contact->user->phone),
            ];
            $user = $this->userLogin($data);
            return Inertia::render('Counselings/ContactChat', [
                'counseling' => [
                    'id' => $counseling->id,
                    'contact_id' => $counseling->contact_id,
                    'phone' => $counseling->contact->user->phone,
                    'consultant_id' => $counseling->consultant_id,
                    'consultant_name' => $counseling->consultant->name,
                    'consultant_photo' => $counseling->consultant->photo,
                    'consultant_specialty' => $counseling->consultant->specialty,
                    'consultant_online' => $counseling->consultant->user->isOnline(),
                    'consultant_last_seen' => Jalalian::forge($counseling->consultant->user->last_seen)->ago(),
                    'room' => $counseling->room,
                    'price' => $counseling->price,
                    'result' => $counseling->result,
                    'active' => $counseling->active,
                    'created_at' => $counseling->created_at,
                    'timestamp' => strtotime($counseling->created_at),
                    'enable' => $counseling->enable,
                    'remaining' => $counseling->remaining,
                ],
                'user' => $user->data,
            ]);
        } 
        abort(404);
    }

}
