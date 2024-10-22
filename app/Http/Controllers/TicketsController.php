<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Ticket_Message;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use \Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    public function index()
    {
        return Inertia::render('Tickets/Index', [
            'filters' => Request::all('search', 'trashed', 'active'),
            'tickets' => Ticket::filter(Request::only('search', 'trashed', 'active'))
                ->whereIn('section', [1,2])
                ->paginate(10)
                ->through(fn($ticket) => [
                    'id' => $ticket->id,
                    'name' => $ticket->name,
                    'subject' => $ticket->subject,
                    'section' => $ticket->section,
                    'section_name' => $ticket->section_name,
                    'created_at' => $ticket->created_at,
                    'active' => $ticket->active,
                    'deleted_at' => $ticket->deleted_at,
                ]),
            'page' => Request::get('page'),
        ]);
    }

    public function list()
    {
        return Inertia::render('Ticket/Index', [
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
        return Inertia::render('Ticket/Create');
    }

    public function store()
    {
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

        return Redirect::route('ticket')->with('success', 'درخواست با موفقیت ارسال گردید.');
    }

    public function update(Ticket $ticket)
    {
        Request::validate([
            'active' => ['required', 'boolean'],
        ]);

        $ticket->update(Request::only('active'));

        return Redirect::route('tickets')->with('success', 'تیکت با موفقیت بسته شد');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return Redirect::back()->with('success', 'تیکت با موفقیت حذف گردید');
    }

    public function restore(Ticket $ticket)
    {
        $ticket->restore();
        return Redirect::back()->with('success', 'تیکت با موفقیت بازیابی گردید');
    }

    public function show(Ticket $ticket)
    {
        $ticket = [
            'id' => $ticket->id,
            'name' => $ticket->name,
            'subject' => $ticket->subject,
            'section' => $ticket->section,
            'section_name' => $ticket->section_name,
            'active' => $ticket->active,
            'created_at' => $ticket->created_at,
            'messages' => $ticket->ticket_messages,
        ];

        if (Request::get('modal') == 'true') {
            $data = [];
            $data['ticket'] = $ticket;
            return $data;
        }

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function message(Ticket $ticket)
    {
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

        return Redirect::back()->with('success', 'پیام با موفقیت ارسال گردید.')->with('modal',  Request::get('modal'));
    }

    public function messageDestroy(Ticket_Message $ticket_message)
    {
        $ticket_message->delete();
        return Redirect::back()->with('success', 'پیام با موفقیت حذف گردید.');
    }
}
