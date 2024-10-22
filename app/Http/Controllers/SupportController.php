<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Ticket_Message;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function index()
    {
        return Inertia::render('Support/Index', [
            'filters' => Request::all('search', 'trashed'),
            'tickets' => Ticket::filter(Request::only('search', 'trashed'))
                ->where('user_id', auth()->user()->id)
                ->orderBy('created_at', 'asc')
                ->paginate(10)
                ->through(fn($ticket) => [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'section' => $ticket->section,
                    'section_name' => $ticket->section_name,
                    'active' => $ticket->active,
                    'created_at' => $ticket->created_at,
                ]),
            'page' => Request::get('page'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Support/Create');
    }

    public function store()
    {
        if(Ticket::where('active', true)->where('section', Request::get('section'))->where('user_id', auth()->user()->id)->count() > 0){
            return Redirect::back()->with('warning', 'در حال حاضر شما تیکت باز دارید');
        }

        Request::validate([
            'subject' => ['required', 'max:30'],
            'section' => ['required'],
            'description' => ['required', 'max:1000'],
            'file' => ['nullable', 'file', 'max:2048'],
        ]);

        $ticket = Ticket::create([
            'user_id' => auth()->user()->id,
            'subject' => Request::get('subject'),
            'section' => Request::get('section'),
        ]);

        Ticket_Message::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->user()->id,
            'message' => Request::get('description'),
            'file' => Request::file('file') ? Request::file('file')->store('public/tickets') : null,
        ]);

        return Redirect::route('support')->with('success', 'تیکت با موفقیت ارسال گردید.');
    }

    public function show(Ticket $ticket)
    {
        if (auth()->user()->id == $ticket->user_id) {
            return Inertia::render('Support/Show', [
                'ticket' => [
                    'id' => $ticket->id,
                    'name' => $ticket->name,
                    'subject' => $ticket->subject,
                    'section' => $ticket->section,
                    'section_name' => $ticket->section_name,
                    'active' => $ticket->active,
                    'created_at' => $ticket->created_at,
                    'messages' => $ticket->ticket_messages,
                ],
            ]);
        }
        abort(404);
    }

    public function message(Ticket $ticket)
    {
        if ($ticket->active && auth()->user()->id == $ticket->user_id) {
            Request::validate([
                'message' => ['required', 'max:1000'],
                'file' => ['nullable', 'file', 'max:2048'],
            ]);

            Ticket_Message::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->user()->id,
                'message' => Request::get('message'),
                'file' => Request::file('file') ? Request::file('file')->store('public/tickets') : null,
            ]);
    
            return Redirect::back()->with('success', 'پیام با موفقیت ارسال گردید.');
        }
        abort(404);
    }

    public function messageDestroy(Ticket_Message $ticket_message)
    {
        $first_message = Ticket_Message::where('ticket_id', $ticket_message->ticket_id)->where('user_id', auth()->user()->id)->first();
        if ($ticket_message->ticket->active && auth()->user()->id == $ticket_message->user_id && $ticket_message->id != $first_message->id) {
            $ticket_message->delete();
            return Redirect::back()->with('success', 'پیام با موفقیت حذف گردید.');
        }
        abort(404);
    }
}
