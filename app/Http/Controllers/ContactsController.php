<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Contact_Image;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use \Morilog\Jalali\Jalalian;
use App\Traits\General;
use Illuminate\Validation\Rule;
use Image;
use Illuminate\Support\Facades\Storage;

class ContactsController extends Controller
{
    use General;

    public function index()
    {
        return Inertia::render('Contacts/Index', [
            'filters' => Request::all('search', 'sort'),
            'contacts' => Contact::filter(Request::only('search', 'sort'))
                ->with('user')
                ->with('counselings')
                ->whereNotNull('first_name')
                ->whereNotNull('last_name')
                ->orderBy('created_at', 'DESC')
                ->paginate(10)
                ->through(fn($contact) => [
                    'id' => $contact->id,
                    'first_name' => $contact->first_name,
                    'last_name' => $contact->last_name,
                    'unit_number' => $contact->unit_number,
                    'national_code' => $contact->national_code,
                    'birthday' => $contact->birthday ? date("Y") - intval(explode("-", $contact->birthday)[0]) : null,
                    'phone' => $contact->user->phone,
                    'sex' => $contact->sex,
                    'insurance' => $contact->insurance,
                    'active' => $contact->active,
                    'last_counseling' => $contact->counselings->last() ? Jalalian::forge($contact->counselings->last()->created_at)->format('H:i Y/m/d') : null,
                    'created_at' => $contact->created_at ? Jalalian::forge($contact->created_at)->format('H:i Y/m/d') : null,
                ]),
            'page' => Request::get('page'),
        ]);
    }

    public function show(Contact $contact)
    {

    }

    public function edit(Contact $contact)
    {
        $contact = [
            'id' => $contact->id,
            'user_id' => $contact->user_id,
            'unit_number' => $contact->unit_number,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'national_code' => $contact->national_code,
            'birthday' => $contact->birthday,
            'sex' => $contact->sex,
            'state' => $contact->state,
            'insurance' => $contact->insurance,
            'supplementary_insurance' => $contact->supplementary_insurance,
            'images' => $contact->images
        ];

        $insurances = [
            "هیچکدام",
            "تامین اجتماعی",
            "سلامت",
            "نیروهای مسلح",
            "خدمات درمانی",
            "سایر",
        ];

        $supplementary_insurances = [
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
        ];

        $states = [
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
        ];

        if (Request::get('modal') == 'true') {
            $data = [];
            $data['contact'] = $contact;
            $data['insurances'] = $insurances;
            $data['supplementary_insurances'] = $supplementary_insurances;
            $data['states'] = $states;
            return $data;
        }

        return Inertia::render('Contacts/Edit', [
            'contact' => $contact,
            'insurances' => $insurances,
            'supplementary_insurances' => $supplementary_insurances,
            'states' => $states,
        ]);
    }

    public function update(Contact $contact)
    {
        Request::merge([
            'national_code' => Request::get('national_code') ? $this->to_english_numbers(Request::get('national_code')) : null,
            'unit_number' => Request::get('unit_number') ? $this->to_english_numbers(Request::get('unit_number')) : null,
        ]);
        Request::validate([
            'first_name' => ['required', 'max:20'],
            'last_name' => ['required', 'max:30'],
            'unit_number' => ['nullable', 'integer', Rule::unique('contacts')->ignore($contact->id)],
            'national_code' => ['required', 'max:10', Rule::unique('contacts')->ignore($contact->id)],
            'birthday' => ['required'],
            'sex' => ['required'],
            'state' => ['nullable'],
            'insurance' => ['nullable'],
            'supplementary_insurance' => ['nullable'],
        ]);

        $contact->update(Request::only('first_name', 'last_name', 'unit_number', 'national_code', 'birthday', 'sex', 'state', 'insurance', 'supplementary_insurance'));


        return Redirect::back()->with('success', 'بیمار با موفقیت ویرایش گردید.');
    }

    public function update_images(Contact $contact)
    {
        if (Request::file('image')) {
            $file  = Request::file('image');
            
            $image = Image::make($file);
            $image->resize(1080, null, function ($constraint) {
                $constraint->aspectRatio();
            })->encode();;
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $result = Storage::disk('public')->put('contacts/' .$contact->unit_number . '/' . $filename, $image);
            if($result){
                Contact_Image::create([
                    'contact_id' => $contact->id,
                    'image' => 'public/contacts/' .$contact->unit_number . '/' . $filename,
                ]);
                return Redirect::back()->with('success', 'پرونده بیمار با موفقیت افزوده شد.')->with('modal',  Request::get('modal'));
            } 
        }
        return Redirect::back()->with('error', 'خطا')->with('modal',  Request::get('modal'));
    }

    public function destroy_images(Contact_Image $contact_image)
    {
        $contact_image->delete();

        return Redirect::back()->with('success', 'پرونده با موفقیت حذف گردید');
    }

}
