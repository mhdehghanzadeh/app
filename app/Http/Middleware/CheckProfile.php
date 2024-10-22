<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Redirect;

class CheckProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $contact = Contact::where('user_id', auth()->user()->id)->first();
        if($contact->first_name && $contact->last_name && $contact->national_code && $contact->birthday){
            return $next($request);
        }else{
            return Redirect::route('profile.edit', ['back' => $request->getRequestUri()])->with('error', 'برای ادامه میبایست پروفایل خود را کامل نمایید');
        }
    }
}
