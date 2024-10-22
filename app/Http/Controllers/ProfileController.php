<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use App\Rules\PhoneNumber;
use Illuminate\Validation\Rule;
use App\Traits\General;

class ProfileController extends Controller
{

    use General;

    public function edit()
    {
        $user = Auth::user();
        if ($user->role_id != 2) {
            return Inertia::render('Profile/Admin', [
                'user' => [
                    'username' => $user->username,
                    'phone' => $user->phone,
                    'email' => $user->email,
                ],
            ]);
        }
        $contact = Contact::where('user_id', $user->id)->first();
        return Inertia::render('Profile/User', [
            'contact' => [
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'national_code' => $contact->national_code,
                'birthday' => $contact->birthday,
                'sex' => $contact->sex,
                'state' => $contact->state,
                'insurance' => $contact->insurance,
                'supplementary_insurance' => $contact->supplementary_insurance,
            ],
            'insurances' => [
                "هیچکدام",
                "تامین اجتماعی",
                "سلامت",
                "نیروهای مسلح",
                "خدمات درمانی",
                "سایر",
            ],
            'supplementary_insurances' => [
                "هیچکدام",
                "ایران",
                "آسیا",
                "پارسیان",
                "دانا",
                "رازی",
                "ما",
                "معلم",
                "پاسارگاد",
                "البرز",
                "دی",
                "ملت",
                "نوین",
                "سامان",
                "تجارت نو",
                "کوثر",
                "آرمان",
                "سینا",
                "تعارن",
                "آسماری",
                "سرمد",
                "میهن",
                "حافظ",
                "توسعه",
                "سایر",
            ],
            'states' => [
                'آذربایجان شرقی',
                'آذربایجان غربی	',
                'اردبیل',
                'اصفهان',
                'البرز',
                'ایلام',
                'بوشهر',
                'تهران',
                'چهارمحال و بختیاری',
                'خراسان جنوبی',
                'خراسان رضوی',
                'خراسان شمالی',
                'خوزستان',
                'زنجان',
                'سمنان',
                'سیستان و بلوچستان',
                'فارس',
                'قزوین',
                'قم	',
                'کردستان',
                'کرمان',
                'کرمانشاه',
                'کهگیلویه وبویراحمد',
                'گلستان',
                'گیلان',
                'لرستان',
                'مازندران',
                'مرکزی',
                'هرمزگان',
                'همدان',
                'یزد',
            ],
            'back' => Request::get('back')
        ]);

    }

    public function update()
    {
        $user = Auth::user();
        if ($user->role_id != 2) {
            Request::merge([
                'phone' => Request::get('phone') ? $this->to_english_numbers(Request::get('phone')) : null,
            ]);
            Request::validate([
                'phone' => ['required', 'max:11', new PhoneNumber, Rule::unique('users')->ignore($user->id)],
                'email' => ['required', 'max:50', 'email', Rule::unique('users')->ignore($user->id)],
                'username' => ['required', 'max:20', Rule::unique('users')->ignore($user->id)],
                'password' => ['nullable'],
            ]);
            $user->update(Request::only('username', 'phone', 'email'));
            if (Request::get('password')) {
                $user->update(['password' => Request::get('password')]);
            }
            return Redirect::back()->with('success', 'پروفایل با موفقیت ویرایش شد.');
        }
        $contact = Contact::where('user_id', $user->id)->first();
        Request::merge([
            'national_code' => Request::get('national_code') ? $this->to_english_numbers(Request::get('national_code')) : null,
            'unit_number' => Request::get('unit_number') ? $this->to_english_numbers(Request::get('unit_number')) : null,
        ]);
        Request::validate([
            'first_name' => ['required', 'max:20'],
            'last_name' => ['required', 'max:30', ],
            'national_code' => ['nullable', 'max:10', Rule::unique('contacts')->ignore($contact->id)],
            'unit_number' => ['nullable', 'integer', Rule::unique('contacts')->ignore($contact->id)],
            'birthday' => ['required'],
            'sex' => ['required'],
            'state' => ['nullable'],
            'insurance' => ['nullable'],
            'supplementary_insurance' => ['nullable'],
        ]);
        $contact->update(Request::only('first_name', 'last_name', 'national_code', 'birthday', 'sex', 'state', 'insurance', 'supplementary_insurance', 'unit_number'));
        if(Request::get('back')){
            return redirect('/')->with('success', 'پروفایل با موفقیت ویرایش شد.');
        }else{
            return Redirect::back()->with('success', 'پروفایل با موفقیت ویرایش شد.');
        }
        
    }


    public function change_password()
    {
        return Inertia::render('Profile/ChangePassword');
    }

    public function update_password(Request $request)
    {
        Request::validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ],['password.min' => 'رمز عبور میبایست بیشتر از 6 کاراکتر باشد',
        'password.confirmed' => 'تکرار رمز عبور اشتباه است']);

        $user = Auth::user();

        $user->update(['password' => Request::get('password')]);

        return redirect('/')->with('success', 'رمز عبور با موفقیت تغییر یافت');
    }

}
