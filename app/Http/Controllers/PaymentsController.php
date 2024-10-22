<?php

namespace App\Http\Controllers;

use App\Models\Consultant;
use App\Models\Contact;
use App\Models\Counseling;
use App\Models\Payment;
use App\Traits\Chat;
use App\Traits\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment as Pay;
use Illuminate\Support\Facades\Redirect;

class PaymentsController extends Controller
{
    use Chat;
    use Notification;

    public function index()
    {
        return Inertia::render('Payments/Index', [
            'filters' => Request::all('search', 'sort', 'date', 'start', 'end'),
            'payments' => Payment::filter(Request::only('search', 'sort', 'date', 'start', 'end'))
                ->paginate(10)
                ->through(fn($payment) => [
                    'id' => $payment->id,
                    'user_name' => $payment->user_name,
                    'counseling_id' => $payment->counseling_id,
                    'transaction_id' => $payment->transaction_id,
                    'amount' => $payment->amount,
                    'reference_id' => $payment->reference_id,
                    'status' => $payment->status,
                    'status_label' => $payment->status_label,
                    'result' => $payment->result,
                    'driver' => $payment->driver,
                    'created_at' => $payment->created_at,
                ]),
            'page' => Request::get('page'),
        ]);
    }

    public function show(Payment $payment)
    {
        $payment = [
            'id' => $payment->id,
            'user_name' => $payment->user_name,
            'counseling_id' => $payment->counseling_id,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
            'reference_id' => $payment->reference_id,
            'status' => $payment->status,
            'status_label' => $payment->status_label,
            'result' => $payment->result,
            'driver' => $payment->driver,
            'created_at' => $payment->created_at,
        ];
        if (Request::get('modal') == 'true') {
            $data = [];
            $data['payment'] = $payment;
            return $data;
        }
        return Inertia::render('Payments/Show', [
            'payment' => $payment,
        ]);
    }

    /* public function fakesend(Consultant $consultant)
    {
        $payment = Payment::create([
            'contact_id' => auth()->user()->contact->id,
            'consultant_id' => $consultant->id,
            'driver' => 'zarinpal',
            'transaction_id' => 24254252,
            'amount' => $consultant->price,
            'result' => true, 
            'reference_id' => 3434325425, 
            'status' => 1
        ]);

        if (!$this->userInfo(Auth::user()->contact->id)->success) {
            //create rocket chat user
            $data = [
                'name' => Auth::user()->contact->first_name . ' ' . Auth::user()->contact->last_name,
                'email' => Auth::user()->phone . '@bazarol.ir',
                'username' => Auth::user()->phone,
                'password' => base64_encode(Auth::user()->phone),
            ];
            $user = $this->userCreate($data);
        }
        //else
        //login user and get token
        $data = [
            'username' => Auth::user()->phone,
            'password' => base64_encode(Auth::user()->phone),
        ];
        $user = $this->userLogin($data);
        //create room
        if ($user->status == "success" && $user->data->userId && $user->data->authToken) {
            $data = [
                'userId' => $user->data->userId,
                'authToken' => $user->data->authToken,
            ];
            $result = $this->createChat($data);
            $counseling = Counseling::create([
                'contact_id' => Auth::user()->contact->id,
                'consultant_id' => 1,
                'room' => $result->room->rid,
                'price' => $payment->amount,
                'result' => true,
            ]);
            $payment->update(['counseling_id' => $counseling->id]);
            //send message
            $data = [
                "channel"=> "@".auth()->user()->phone,
                'rid' => $result->room->rid,
                'msg' => 'سلام',
            ];
            $this->send_message($data);
        }

        return Inertia::render('Payments/Result', [
            'payment' => $payment,
        ]);
          
    }  */

    public function send(Consultant $consultant)
    {
        if(Counseling::where('active', true)->where('contact_id', Auth::user()->contact?->id )->exists()){
            return Redirect::back()->with('error', 'شما هنوز ویزیت فعال دارید');
        }

        if(!$this->checkConnection()){
            return Redirect::back()->with('error', 'خطا در اتصال لطفا بعدا امتحان کنید');
        }

        $zarin = Pay::purchase(
            (new Invoice)->amount($consultant->price),
            function ($driver, $transactionId) use ($consultant)  {
                Payment::create([
                    'contact_id' => auth()->user()->contact->id,
                    'consultant_id' => $consultant->id,
                    'driver' => 'zarinpal',
                    'transaction_id' => $transactionId,
                    'amount' => $consultant->price,
                ]);
            }
        )->pay()->toJson();
        $zarin = json_decode($zarin);
        return Inertia::location($zarin->action);
    }

    public function verify()
    {
        $payment = Payment::where('transaction_id', Request::get('Authority'))->first();
        if($payment && !$payment->verify){
            try {
                $receipt = Pay::amount($payment->amount)->transactionId($payment->transaction_id)->verify();
                $payment->update(['result' => true, 'reference_id' => $receipt->getReferenceId(), 'status' => 1, 'verify' => true]);
    
                //create counseling and redirect to result page
                //if user not exist in rocket chat
                if (!$this->userInfo(Auth::user()->phone)->success) {
                    //create rocket chat user
                    $data = [
                        'name' => Auth::user()->contact->first_name . ' ' . Auth::user()->contact->last_name,
                        'email' => Auth::user()->phone . '@bazarol.ir',
                        'username' => Auth::user()->phone,
                        'password' => base64_encode(Auth::user()->phone),
                    ];
                    $user = $this->userCreate($data);
                }
                //else
                //login user and get token
                $data = [
                    'username' => Auth::user()->phone,
                    'password' => base64_encode(Auth::user()->phone),
                ];
                $user = $this->userLogin($data);
                //create room
                if ($user->status == "success" && $user->data->userId && $user->data->authToken) {
                    $data = [
                        'userId' => $user->data->userId,
                        'authToken' => $user->data->authToken,
                    ];
                    $result = $this->createChat($data);
                    $counseling = Counseling::create([
                        'contact_id' => Auth::user()->contact->id,
                        'consultant_id' => 1,
                        'room' => $result->room->rid,
                        'price' => $payment->amount,
                        'result' => true,
                    ]);
                    $payment->update(['counseling_id' => $counseling->id]);
                    //send message
                    $message_line1 = "با عرض سلام";
                    $message_line2 = "آخرین آزمایش، سونوگرافی و... خود را ارسال بفرمایید";
                    $message_line3 = "حتما لیست تمام دارو هایی که در حال حاضر مصرف می‌کنید را به همراه دوز دارو و مقدار مصرف روزانه ارسال کنید";
                    $message_line4 = "ناراحتی های جسمی خود را ذکر کنید و به اختصار توضیح دهید";
                    $message_line5 = "وزن فعلی خود را اندازه گیری کنید و ذکر کنید";
                    $message_line6 = "اگر مبتلا به فشارخون بالا هستید یا داروی فشار خون مصرف می‌کنید، فشار خون خود را هم ۳ روز متوالی بگیرید و ارسال کنید ";
                    $message_line8 = "پیام های شما به دقت بررسی شده و حداکثر ظرف یک هفته کاری پاسخ داده خواهند شد";
                    $message_line9 = " از صبر و شکیبایی شما متشکریم";
                    if(auth()->user()->contact->sex == false && auth()->user()->contact->age < 50){
                        $message_line7 = "ذکر کنید که در حال حاضر عادت ماهیانه می‌شوید یا خیر؟ آیا منظم است؟" . "\n\n";
                    }else{
                        $message_line7 = '';
                    }
                    $message_all = $message_line1 . "\n\n " . $message_line2 . " \n\n " . $message_line3 . " \n\n " . $message_line4 . "\n\n" . $message_line5 . "\n\n" . $message_line6 . "\n\n" . $message_line7 . $message_line8 . "\n\n" . $message_line9;
                    $data = [
                        "channel"=> "@".auth()->user()->phone,
                        'rid' =>  $result->room->rid,
                        'msg' => $message_all,
                    ];
                    $this->send_message($data);
                    //send sms to contact
                    try {
                        $line1 = "کاربر گرامی ویزیت آنلاین شما با موفقیت ثبت گردید و تا یک هفته معتبر خواهد بود.";
                        $line2 = env('SITE_FULL_NAME');
                        $message = $line1 . "\r\n" . $line2;
                        $this->send_sms($message, auth()->user()->phone);
                    } catch (\Exception $exception) {
                        
                    }

                    //send sms to consultant
                    try {
                        $line1 = 'بیمار ' . Auth::user()->contact->gender_pronouns . ' ' . Auth::user()->contact->last_name . ' ویزیت آنلاین ثبت نمود.';
                        $line2 = 'لطفا برای پاسخ دهی به پنل مراجعه نمایید';
                        $message = $line1 . "\r\n" . $line2;
                        $this->send_sms($message, $payment->consultant->user->phone);
                    } catch (\Exception $exception) {
                        
                    }
                   
                }
                
            } catch (InvalidPaymentException $exception) {
                $payment->update(['result' => false, 'status' => 2]);
                //echo $exception->getMessage();
            }
        }
        return Inertia::render('Payments/Result', [
            'payment' => $payment,
        ]);
    }

    public function contact_index()
    {
        return Inertia::render('Payments/ContactIndex', [
            'filters' => Request::all('search', 'sort'),
            'payments' => Payment::filter(Request::only('search', 'sort'))
                ->orderBy('created_at', 'asc')
                ->where('contact_id', Auth::user()->contact->id)
                ->paginate(10)
                ->through(fn($payment) => [
                    'id' => $payment->id,
                    'user_name' => $payment->user_name,
                    'counseling_id' => $payment->counseling_id,
                    'transaction_id' => $payment->transaction_id_number,
                    'amount' => $payment->amount,
                    'reference_id' => $payment->reference_id,
                    'status' => $payment->status,
                    'status_label' => $payment->status_label,
                    'result' => $payment->result,
                    'driver' => $payment->driver,
                    'created_at' => $payment->created_at,
                ]),
            'page' => Request::get('page'),
        ]);
    }

}
