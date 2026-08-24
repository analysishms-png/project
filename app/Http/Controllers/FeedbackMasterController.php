<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Companyreg;
use App\Models\States;
use App\Models\CompanyLog;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Exception;


class FeedbackMasterController extends Controller
{
    protected $username;
    protected $email;
    protected $propertyid;
    protected $currenttime;
    protected $ptlngth;
    protected $prpid;
    protected $compcode;
    protected $ncurdate;
    protected $datemanage;

    private const GUEST_METHODS = ['feedback', 'getRoomDetails', 'store', 'index', 'myStay', 'hotelInfo', 'myProfile', 'roomserviceqr', 'serviceRequest', 'expressCheckout', 'submitExpressCheckout'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!isset(Auth::user()->name)) {
                return redirect('/');
            }

            $this->username = Auth::user()->name;
            $this->email = Auth::user()->email;
            $this->propertyid = $this->propertyid;
            $this->compcode = Companyreg::where('propertyid', $this->propertyid)->value('comp_code');
            $this->ncurdate = DB::table('enviro_general')->where('propertyid', $this->propertyid)->value('ncur');
            $this->ptlngth = strlen($this->propertyid);
            date_default_timezone_set('Asia/Kolkata');
            $this->currenttime = date('Y-m-d H:i:s');
            $this->datemanage = DateHelper::calculateDateRanges($this->ncurdate);
            return $next($request);
        })->except(self::GUEST_METHODS);

        $this->middleware(function ($request, $next) {
            date_default_timezone_set('Asia/Kolkata');
            $this->currenttime = date('Y-m-d H:i:s');

            $propertyid = $request->route('propertyid') ?? $request->input('propertyid');

            if ($propertyid) {
                $this->propertyid = $propertyid;
                $this->ncurdate = DB::table('enviro_general')->where('propertyid', $propertyid)->value('ncur');
            }

            return $next($request);
        })->only(self::GUEST_METHODS);
    }
    public function ncurfetch()
    {
        $ncurdate = DB::table('enviro_general')
            ->where('propertyid', $this->propertyid)
            ->value('ncur');
        return $ncurdate;
    }

    public function feedback($propertyid)
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        $company = Companyreg::where('propertyid', $propertyid)->first();

        $feedbackData = DB::table('feedbackmaster')
            ->where('propertyid', $propertyid)
            ->where('isactive', 1)
            ->orderBy('displayorder', 'asc')
            ->get();

        $feedbackId = request()->cookie('feedback_submitted_' . $propertyid);
        $alreadySubmitted = (bool) $feedbackId;

        $submittedData = null;
        $categoryRatingsData = collect();

        if ($alreadySubmitted) {
            $submittedData = DB::table('feedbackhdr as FH')
                ->leftJoin('guestprof as G', 'FH.guestprof', '=', 'G.guestcode')
                ->select('FH.*', 'G.name as guest_name')
                ->where('FH.propertyid', $propertyid)
                ->where('FH.feedbackid', $feedbackId)
                ->first();

            if ($submittedData) {
                $categoryRatingsData = DB::table('feedbackdtl as FD')
                    ->join('feedbackmaster as FM', 'FD.questioncode', '=', 'FM.questioncode')
                    ->where('FD.propertyid', $propertyid)
                    ->where('FD.feedbackid', $feedbackId)
                    ->select('FM.question', 'FD.overallrating')
                    ->get();
            }
        }

        $googleReviewUrl = trim((string) DB::table('enviro_form')
            ->where('propertyid', $propertyid)
            ->value('googlereviewurl'));

        return view('property.roomservice.feedback', compact(
            'company',
            'feedbackData',
            'propertyid',
            'alreadySubmitted',
            'submittedData',
            'categoryRatingsData',
            'googleReviewUrl'
        ));
    }

    public function getRoomDetails(Request $request)
    {
        $roomNo = $request->input('room_no');

        $data = DB::table('roomocc as R')
            ->leftJoin('guestprof as G', function ($join) {
                $join->on('R.guestprof', '=', 'G.guestcode')
                    ->on('R.sno1', '=', 'G.sno1');
            })
            ->leftJoin('room_cat as RC', 'R.roomcat', '=', 'RC.cat_code')
            ->select(
                'R.roomno as RoomNo',
                'G.name as GuestName',
                'G.mobile_no as MobileNo',
                'G.email_id as emailID',
                'RC.image_path',
                'R.chkoutdate',
                'R.sno1',
                'R.guestprof',
                DB::raw("CONVERT(R.docid USING binary) as docid"),
                DB::raw("CONVERT(R.folioNo USING binary) as folioNo")
            )
            ->where('R.propertyid', $this->propertyid)
            ->where('R.roomno', $roomNo)
            ->where(function ($q) {
                $q->whereNull('R.chkoutdate')->orWhere('R.chkoutdate', $this->ncurdate);
            })
            ->first();

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'No active guest found for this room.']);
        }

        $imageUrl = null;
        if (!empty($data->image_path)) {
            $imageUrl = str_starts_with($data->image_path, 'http')
                ? $data->image_path
                : asset('storage/' . ltrim($data->image_path, '/'));
        }

        return response()->json([
            'success'            => true,
            'room_no'            => $data->RoomNo,
            'guest_name'         => $data->GuestName ?? 'N/A',
            'mobile_no'          => $data->MobileNo ?? 'N/A',
            'email'              => $data->emailID ?? 'N/A',
            'checkout_date'      => $data->chkoutdate ? \Carbon\Carbon::parse($data->chkoutdate)->format('d M Y') : 'N/A',
            'checkout_date_iso'  => $data->chkoutdate ? \Carbon\Carbon::parse($data->chkoutdate)->format('Y-m-d') : '',
            'image_path'         => $imageUrl,
            'sno1'               => $data->sno1,
            'folio_no'           => $data->folioNo,
            'docid'              => $data->docid,
            'guestprof'          => $data->guestprof,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_no'      => 'required',
            'purpose'      => 'required',
            'improvements' => 'required',
        ]);

        $roomData = DB::table('roomocc as R')
            ->select('R.sno1', 'R.guestprof', DB::raw("CONVERT(R.docid USING binary) as docid"), DB::raw("CONVERT(R.folioNo USING binary) as folioNo"))
            ->where('R.propertyid', $this->propertyid)
            ->where('R.roomno', $request->input('room_no'))
            ->where(function ($q) {
                $q->whereNull('R.chkoutdate')->orWhere('R.chkoutdate', $this->ncurdate);
            })
            ->first();

        if (!$roomData) {
            return back()->withInput()->with('error', 'Could not verify room details. Please search the room again before submitting.');
        }

        DB::beginTransaction();
        try {
            $newFeedbackId = DB::table('feedbackhdr')
                ->where('propertyid', $this->propertyid)
                ->lockForUpdate()
                ->max('feedbackid');

            $newFeedbackId = $newFeedbackId ? $newFeedbackId + 1 : 1;

            $userComment   = $request->input('comments');
            $overallRating = (int) $request->input('overall_rating', 0);

            $finalComment = $userComment;
            if ($overallRating >= 4) {
                $finalComment = $userComment . ' | Eligible for Google Review';
            }

            DB::table('feedbackhdr')->insert([
                'feedbackid'     => $newFeedbackId,
                'propertyid'     => $this->propertyid,
                'mobileno'       => $request->input('mobile_no'),
                'mailid'         => $request->input('email'),
                'guestprof'      => $roomData->guestprof,
                'roomno'         => $request->input('room_no'),
                'docid'          => $roomData->docid,
                'folioNo'        => $roomData->folioNo,
                'sno1'           => $roomData->sno1,
                'deedbackdate'   => $this->currenttime,
                'feedbacksource' => 'QR',
                'overallrating'  => $overallRating,
                'recommend'      => strtoupper($request->input('recommend')),
                'comments'       => $finalComment,
                'improve'        => $request->input('improvements'),
                'purpose'        => $request->input('purpose'),
            ]);

            $categoryRatings = json_decode($request->input('category_ratings', '{}'), true);
            if (is_array($categoryRatings)) {
                $sno = 1;
                foreach ($categoryRatings as $questionCode => $rating) {
                    DB::table('feedbackdtl')->insert([
                        'propertyid'    => $this->propertyid,
                        'feedbackid'    => $newFeedbackId,
                        'sno'           => $sno,
                        'questioncode'  => $questionCode,
                        'overallrating' => $rating,
                    ]);
                    $sno++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Something went wrong while saving your feedback.');
        }

        // Cookie mein ab feedbackid store hota hai (sirf '1' nahi), taaki
        // feedback() method dobara load hone par isi ID se guest ka poora
        // submitted data (rating/comment/improve) wapas fetch kar sake.
        return redirect()->route('feedback', ['propertyid' => $request->input('propertyid')])
            ->with('success', 'Thank you! Your feedback has been submitted.')
            ->withCookie(cookie('feedback_submitted_' . $request->input('propertyid'), (string) $newFeedbackId, 1440));
    }

    public function feedbackQrGenerate(Request $request)
    {
        try {
            $compdata = companydata();

            $url = url('/feedback/' . $compdata->propertyid);
            $toptext = 'Guest Feedback';

            $logo = null;
            if (!empty($compdata->logo)) {
                $path = storage_path('app/public/admin/property_logo/' . $compdata->logo);
                if (file_exists($path)) {
                    $logo = $path;
                }
            }
            if (!$logo) {
                $fallback = public_path('assets/img/logo.png');
                $logo = file_exists($fallback) ? $fallback : null;
            }

            $builder = Builder::create()
                ->writer(new PngWriter())
                ->data($url)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(512)
                ->margin(10)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin);

            if ($logo && file_exists($logo)) {
                $builder
                    ->logoPath($logo)
                    ->logoResizeToWidth(100)
                    ->logoPunchoutBackground(true);
            }

            $result  = $builder->build();
            $qrImage = imagecreatefromstring($result->getString());
            $qrWidth  = imagesx($qrImage);
            $qrHeight = imagesy($qrImage);

            $fontSize   = 20;
            $fontPath   = realpath(__DIR__ . '/../../../vendor/endroid/qr-code/assets/noto_sans.otf');
            $textBox    = imagettfbbox($fontSize, 0, $fontPath, $toptext);
            $textWidth  = $textBox[2] - $textBox[0];
            $textHeight = $textBox[1] - $textBox[7];
            $padding    = 15;
            $headerHeight = $textHeight + $padding * 2;

            $finalWidth  = max($qrWidth, $textWidth + 20);
            $finalHeight = $qrHeight + $headerHeight;
            $finalImage  = imagecreatetruecolor($finalWidth, $finalHeight);

            $white = imagecolorallocate($finalImage, 255, 255, 255);
            $black = imagecolorallocate($finalImage, 0, 0, 0);
            imagefill($finalImage, 0, 0, $white);

            $textX = intval(($finalWidth - $textWidth) / 2);
            $textY = $padding + $textHeight;
            imagettftext($finalImage, $fontSize, 0, $textX, $textY, $black, $fontPath, $toptext);

            $qrX = intval(($finalWidth - $qrWidth) / 2);
            imagecopy($finalImage, $qrImage, $qrX, $headerHeight, 0, 0, $qrWidth, $qrHeight);
            imagedestroy($qrImage);

            ob_start();
            imagepng($finalImage);
            $imageData = ob_get_clean();
            imagedestroy($finalImage);

            return response()->json([
                'success'   => true,
                'message'   => 'Feedback QR Code generated successfully',
                'file_data' => 'data:image/png;base64,' . base64_encode($imageData),
                'filename'  => 'Feedback_QR_Property_' . $compdata->propertyid . '.png',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function feedbackreport(Request $request)
    {
        $fromdate = $this->ncurdate;
        $company  = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)
            ->where('state_code', $company->state_code)
            ->value('name');

        return view('property.feedbackreport', compact('company', 'statename', 'fromdate'));
    }

    public function feedbackreportdata(Request $request)
    {
        $fromdate   = $request->fromdate;
        $todate     = $request->todate;
        $propertyid = $this->propertyid;

        $data = DB::table('feedbackhdr as FH')
            ->leftJoin('guestprof as G', 'FH.guestprof', '=', 'G.guestcode') // ya subquery wala fix jo pehle bataya
            ->leftJoin('feedbackdtl as FD', function ($join) {
                $join->on('FH.feedbackid', '=', 'FD.feedbackid')
                    ->on('FH.propertyid', '=', 'FD.propertyid');
            })
            ->leftJoin('feedbackmaster as FM', function ($join) {
                $join->on('FD.questioncode', '=', 'FM.questioncode')
                    ->on('FD.propertyid', '=', 'FM.propertyid'); // agar feedbackmaster mein bhi propertyid column hai
            })
            ->select(
                'FH.feedbackid',
                'FH.deedbackdate',
                DB::raw('(SELECT G.name FROM guestprof G WHERE G.guestcode = FH.guestprof LIMIT 1) as guestname'),
                'FH.roomno',
                'FH.mobileno',
                'FH.mailid',
                'FH.folioNo',
                'FH.purpose',
                'FH.overallrating',
                'FH.comments',
                'FH.improve',
                'FM.question',
                'FD.overallrating as detailrating'
            )
            ->where('FH.propertyid', $propertyid)
            ->when($fromdate, function ($q) use ($fromdate) {
                $q->whereDate('FH.deedbackdate', '>=', $fromdate);
            })
            ->when($todate, function ($q) use ($todate) {
                $q->whereDate('FH.deedbackdate', '<=', $todate);
            })
            ->orderBy('FH.feedbackid')
            ->orderBy('FH.deedbackdate')
            ->get();
        return response()->json(['data' => $data]);
    }


    public function roomserviceqr($propertyid, $roomno)
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        $company = Companyreg::where('propertyid', $propertyid)->first();
        $ncurdate = $this->ncurFor($propertyid);

        $guestRow = DB::table('roomocc as R')
            ->leftJoin('guestprof as G', function ($join) {
                $join->on('R.guestprof', '=', 'G.guestcode')
                    ->on('R.sno1', '=', 'G.sno1');
            })
            ->select('G.name as guest_name', 'R.roomno')
            ->where('R.propertyid', $propertyid)
            ->where('R.roomno', $roomno)
            ->where(function ($q) use ($ncurdate) {
                $q->whereNull('R.chkoutdate')->orWhere('R.chkoutdate', $ncurdate);
            })
            ->first();

        $guest = [
            'guest_name' => $guestRow->guest_name ?? 'Demo Guest',
            'room_no'    => $guestRow->roomno ?? $roomno,
        ];

        return view('property.roomservice.roomserviceqr', compact('company', 'propertyid', 'roomno', 'guest'));
    }

    private function ncurFor($propertyid)
    {
        return DB::table('enviro_general')->where('propertyid', $propertyid)->value('ncur');
    }
    public function index($propertyid, $roomno)
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        $company = Companyreg::where('propertyid', $propertyid)->first();
        $ncurdate = $this->ncurFor($propertyid);

        $guestRow = DB::table('roomocc as R')
            ->select('R.name as guest_name', 'R.roomno')
            ->where('R.propertyid', $propertyid)
            ->where('R.roomno', $roomno)
            ->where(function ($q) {
                $q->whereNull('R.type')->orWhere('R.type', '');
            })
            ->where(function ($q) use ($ncurdate) {
                $q->whereNull('R.chkoutdate')->orWhere('R.chkoutdate', $ncurdate);
            })
            ->first();

        if (!$guestRow) {
            $receptionNo = DB::table('compservicefacillities')
                ->where('propertyid', $propertyid)
                ->where('isactive', 1)
                ->where('service', 'Reception')
                ->value('remark');

            return view('property.roomservice.roomnotoccupied', compact('company', 'propertyid', 'roomno', 'receptionNo'));
        }

        $guest = [
            'guest_name' => $guestRow->guest_name ?? 'Demo Guest',
            'room_no'    => $guestRow->roomno ?? $roomno,
        ];
        $serviceItemsRaw = DB::table('hkamentiesmaster as H')
            ->join('itemmast as I', function ($join) use ($propertyid) {
                $join->on('H.item', '=', 'I.Code')
                    ->where('I.Property_ID', $propertyid);
            })
            ->select('H.type', 'I.Name')
            ->where('H.propertyid', $propertyid)
            ->whereIn('H.type', ['Linen', 'Amenities'])
            ->where('I.ActiveYN', 'Y')
            ->orderBy('H.srno')
            ->get();

        $serviceItems = $serviceItemsRaw->groupBy('type')->map(function ($group) {
            return $group->pluck('Name');
        });

        if ($serviceItems->isEmpty()) {
            $serviceItems = collect([
                'Linen'     => collect(['Bedsheet', 'Pillow', 'Blanket', 'Towel']),
                'Amenities' => collect(['Shampoo', 'Soap', 'Slippers', 'Drinking Water']),
            ]);
        }

        return view('property.roomservice.guestportal', compact('company', 'guest', 'propertyid', 'roomno', 'serviceItems'));
    }
    public function myStay($propertyid, $roomno)
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        $company = Companyreg::where('propertyid', $propertyid)->first();
        $ncurdate = $this->ncurFor($propertyid);

        $stay = DB::table('roomocc as RC')
            ->leftJoin('room_cat as C', 'RC.roomcat', '=', 'C.cat_code')
            ->select(
                'RC.docid',
                'RC.sno1',
                'RC.name as guest_name',
                'RC.roomno',
                'C.name as room_type',
                'RC.chkindate',
                'RC.depdate',
                'RC.nodays',
                DB::raw('(SELECT SUM(P.amtdr) - SUM(P.amtcr) FROM paycharge P WHERE P.folionodocid = RC.docid AND P.sno1 = RC.sno1) as current_bill')
            )
            ->where('RC.propertyid', $propertyid)
            ->where('RC.roomno', $roomno)
            ->where(function ($q) {
                $q->whereNull('RC.type')->orWhere('RC.type', '');
            })
            ->where(function ($q) use ($ncurdate) {
                $q->whereNull('RC.chkoutdate')->orWhere('RC.chkoutdate', $ncurdate);
            })
            ->first();

        // Room me koi active check-in nahi hai
        if (!$stay) {
            $receptionNo = DB::table('compservicefacillities')
                ->where('propertyid', $propertyid)
                ->where('isactive', 1)
                ->where('service', 'Reception')
                ->value('remark');

            return view('property.roomservice.roomnotoccupied', compact('company', 'propertyid', 'roomno', 'receptionNo'));
        }

        return view('property.roomservice.mystay', compact('company', 'stay', 'propertyid', 'roomno'));
    }

    public function hotelInfo($propertyid, $roomno)
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        $ncurdate = $this->ncurFor($propertyid);
        $company = Companyreg::where('propertyid', $propertyid)->first();

        $guestRow = DB::table('roomocc as R')
            ->select('R.roomno')
            ->where('R.propertyid', $propertyid)
            ->where('R.roomno', $roomno)
            ->where(function ($q) {
                $q->whereNull('R.type')->orWhere('R.type', '');
            })
            ->where(function ($q) use ($ncurdate) {
                $q->whereNull('R.chkoutdate')->orWhere('R.chkoutdate', $ncurdate);
            })
            ->first();

        if (!$guestRow) {
            $receptionNo = DB::table('compservicefacillities')
                ->where('propertyid', $propertyid)
                ->where('isactive', 1)
                ->where('service', 'Reception')
                ->value('remark');

            return view('property.roomservice.roomnotoccupied', compact('company', 'propertyid', 'roomno', 'receptionNo'));
        }

        $hotel = DB::table('company')
            ->select('comp_name', 'address1', 'address2', 'city', 'mobile', 'email', 'website')
            ->where('propertyid', $propertyid)
            ->first();

        if (!$hotel) {
            $hotel = (object) [
                'comp_name' => 'Demo Hotel',
                'address1'  => '123 Demo Street',
                'address2'  => 'Demo Area',
                'city'      => 'Demo City',
                'mobile'    => '9999999999',
                'email'     => 'info@demohotel.test',
                'website'   => 'https://example.com',
            ];
        }

        $services = DB::table('compservicefacillities')
            ->where('propertyid', $propertyid)
            ->where('isactive', 1)
            ->orderBy('servicehdr')
            ->orderBy('displayorder')
            ->orderBy('service')
            ->get()
            ->groupBy('servicehdr');

        $outlets = DB::table('depart as D')
            ->leftJoin('hkfloors as F', 'D.floor', '=', 'F.code')
            ->select('D.name', 'F.name as floor_name', 'D.timing')
            ->where('D.propertyid', $propertyid)
            ->where('D.nature', 'outlet')
            ->get();

        if ($services->isEmpty()) {
            $services = collect([
                'General' => collect([
                    (object) ['service' => 'Free Wi-Fi', 'remark' => 'All areas'],
                    (object) ['service' => 'Swimming Pool', 'remark' => '6 AM - 9 PM'],
                    (object) ['service' => 'Gym', 'remark' => '24 Hours'],
                ]),
            ]);
        }

        if ($outlets->isEmpty()) {
            $outlets = collect([
                (object) ['name' => 'Demo Restaurant', 'floor_name' => 'Ground Floor', 'timing' => '7 AM - 11 PM'],
                (object) ['name' => 'Demo Cafe', 'floor_name' => '1st Floor', 'timing' => '8 AM - 8 PM'],
            ]);
        }

        return view('property.roomservice.hotelinfo', compact('company', 'hotel', 'services', 'outlets', 'propertyid', 'roomno'));
    }

    public function myProfile($propertyid, $roomno)
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        $company = Companyreg::where('propertyid', $propertyid)->first();
        $ncurdate = $this->ncurFor($propertyid);

        $profile = DB::table('roomocc as RC')
            ->leftJoin('guestprof as G', function ($join) {
                $join->on('RC.guestprof', '=', 'G.guestcode')
                    ->on('RC.sno1', '=', 'G.sno1');
            })
            ->leftJoin('cities as C', 'G.city', '=', 'C.city_code')
            ->select(
                'RC.docid',
                'RC.sno1',
                'G.pic_path',
                DB::raw('COALESCE(NULLIF(RC.name, \'\'), G.name) as name'),
                'G.add1',
                'G.add2',
                'C.cityname',
                'G.nationality',
                'G.mobile_no',
                'G.email_id',
                'G.dob',
                'G.anniversary'
            )
            ->where('RC.propertyid', $propertyid)
            ->where('RC.roomno', $roomno)
            ->where(function ($q) {
                $q->whereNull('RC.type')->orWhere('RC.type', '');
            })
            ->where(function ($q) use ($ncurdate) {
                $q->whereNull('RC.chkoutdate')->orWhere('RC.chkoutdate', $ncurdate);
            })
            ->first();

        // Room me koi active check-in nahi hai
        if (!$profile) {
            $receptionNo = DB::table('compservicefacillities')
                ->where('propertyid', $propertyid)
                ->where('isactive', 1)
                ->where('service', 'Reception')
                ->value('remark');

            return view('property.roomservice.roomnotoccupied', compact('company', 'propertyid', 'roomno', 'receptionNo'));
        }

        $previousVisits = collect();
        if (!empty($profile->mobile_no)) {
            $previousVisits = DB::table('roomocc as RC')
                ->leftJoin('guestprof as G', 'RC.guestprof', '=', 'G.guestcode')
                ->leftJoin('feedbackhdr as F', 'RC.docid', '=', 'F.docid')
                ->select(
                    'RC.chkindate',
                    'RC.roomno as RoomNo',
                    'RC.nodays as Nights',
                    'F.overallrating'
                )
                ->where('RC.propertyid', $propertyid)
                ->where('G.mobile_no', $profile->mobile_no)
                ->where('RC.type', '!=', 'C')
                ->orderBy('RC.chkindate', 'desc')
                ->get();
        }

        $picUrl = null;
        if ($profile && !empty($profile->pic_path)) {
            $picUrl = str_starts_with($profile->pic_path, 'http')
                ? $profile->pic_path
                : asset('storage/' . ltrim($profile->pic_path, '/'));
        }

        return view('property.roomservice.myprofile', compact('company', 'profile', 'picUrl', 'previousVisits', 'propertyid', 'roomno'));
    }
    public function serviceRequest(Request $request, $propertyid, $roomno)
    {
        $request->validate([
            'type'    => 'required|string|max:15',
            'items'   => 'required|array|min:1',
            'items.*' => 'string|max:100',
            'notes'   => 'nullable|string|max:35',
        ]);

        $ncurdate = $this->ncurFor($propertyid);
        date_default_timezone_set('Asia/Kolkata');

        $roomData = DB::table('roomocc as R')
            ->select(
                'R.guestprof',
                'R.name as guest_name',
                DB::raw("CONVERT(R.docid USING binary) as docid")
            )
            ->where('R.propertyid', $propertyid)
            ->where('R.roomno', $roomno)
            ->where(function ($q) {
                $q->whereNull('R.type')->orWhere('R.type', '');
            })
            ->where(function ($q) use ($ncurdate) {
                $q->whereNull('R.chkoutdate')->orWhere('R.chkoutdate', $ncurdate);
            })
            ->first();

        // Room me koi active check-in nahi hai — request save mat karo
        if (!$roomData) {
            return response()->json([
                'success' => false,
                'message' => 'This room is currently not occupied. Guest services are available only after check-in.',
            ], 422);
        }

        $now = now();

        DB::beginTransaction();
        try {
            $lastNo = DB::table('servicerequesthdr')
                ->where('propertyid', $propertyid)
                ->lockForUpdate()
                ->max(DB::raw('CAST(requestno AS UNSIGNED)'));

            $newRequestNo = (string) ($lastNo ? $lastNo + 1 : 1);

            DB::table('servicerequesthdr')->insert([
                'propertyid'    => $propertyid,
                'foliodocid'    => $roomData->docid ?? null,
                'guestprof'     => $roomData->guestprof ?? null,
                'roomno'        => $roomno,
                'requestno'     => $newRequestNo,
                'requestdate'   => date('Y-m-d'),
                'requesttime'   => date('H:i:s'),
                'status'        => 'Pending',
                'requestedfrom' => 'QR Portal',
                'remarks'       => $request->input('notes'),
                'assigneddate'  => $now,
            ]);

            $sno = 1;
            foreach ($request->input('items') as $item) {
                DB::table('servicerequesthdrdtl')->insert([
                    'propertyid'  => $propertyid,
                    'roomno'      => $roomno,
                    'requestno'   => $newRequestNo,
                    'sno'         => $sno,
                    'itemname'    => \Illuminate\Support\Str::limit($item, 15, ''),
                    'requesttype' => $request->input('type'),
                    'status'      => 'Pending',
                ]);
                $sno++;
            }

            DB::commit();

            return response()->json(['success' => true, 'requestno' => $newRequestNo]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Could not save your request. Please try again.',
            ]);
        }
    }


    public function guestPortalQrGenerate(Request $request)
    {
        try {
            $compdata = companydata(); // existing helper, jaisa feedbackQrGenerate/hkqrgenerator mein use hota hai
            $roomno = $request->input('rcode');

            if (!$roomno) {
                return response()->json(['success' => false, 'message' => 'Room number missing.']);
            }

            $url = url('/guest-portal/' . $compdata->propertyid . '/' . $roomno);
            $toptext = 'Room ' . $roomno . ' Guest Portal';

            $logo = null;
            if (!empty($compdata->logo)) {
                $path = storage_path('app/public/admin/property_logo/' . $compdata->logo);
                if (file_exists($path)) {
                    $logo = $path;
                }
            }
            if (!$logo) {
                $fallback = public_path('assets/img/logo.png');
                $logo = file_exists($fallback) ? $fallback : null;
            }

            $builder = Builder::create()
                ->writer(new PngWriter())
                ->data($url)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(512)
                ->margin(10)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin);

            if ($logo && file_exists($logo)) {
                $builder
                    ->logoPath($logo)
                    ->logoResizeToWidth(100)
                    ->logoPunchoutBackground(true);
            }

            $result  = $builder->build();
            $qrImage = imagecreatefromstring($result->getString());
            $qrWidth  = imagesx($qrImage);
            $qrHeight = imagesy($qrImage);

            $fontSize   = 20;
            $fontPath   = realpath(__DIR__ . '/../../../vendor/endroid/qr-code/assets/noto_sans.otf');
            $textBox    = imagettfbbox($fontSize, 0, $fontPath, $toptext);
            $textWidth  = $textBox[2] - $textBox[0];
            $textHeight = $textBox[1] - $textBox[7];
            $padding    = 15;
            $headerHeight = $textHeight + $padding * 2;

            $finalWidth  = max($qrWidth, $textWidth + 20);
            $finalHeight = $qrHeight + $headerHeight;
            $finalImage  = imagecreatetruecolor($finalWidth, $finalHeight);

            $white = imagecolorallocate($finalImage, 255, 255, 255);
            $black = imagecolorallocate($finalImage, 0, 0, 0);
            imagefill($finalImage, 0, 0, $white);

            $textX = intval(($finalWidth - $textWidth) / 2);
            $textY = $padding + $textHeight;
            imagettftext($finalImage, $fontSize, 0, $textX, $textY, $black, $fontPath, $toptext);

            $qrX = intval(($finalWidth - $qrWidth) / 2);
            imagecopy($finalImage, $qrImage, $qrX, $headerHeight, 0, 0, $qrWidth, $qrHeight);
            imagedestroy($qrImage);

            ob_start();
            imagepng($finalImage);
            $imageData = ob_get_clean();
            imagedestroy($finalImage);

            return response()->json([
                'success'   => true,
                'message'   => 'Guest Portal QR generated successfully',
                'file_data' => 'data:image/png;base64,' . base64_encode($imageData),
                'filename'  => 'GuestPortal_QR_Room_' . $roomno . '.png',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * NAYA METHOD: Jab staff "View" par click kare, tab is method ko call karo.
     * Ye viewtime + viewuser save karega, aur status ko 'Pending' se 'In Progress' kar dega.
     * Sirf tab update hoga jab status abhi Pending ho (dobara view karne par overwrite nahi hoga).
     */
    public function viewServiceRequest(Request $request)
    {
        $request->validate([
            'requestno' => 'required|string',
            'roomno'    => 'nullable|string',
        ]);

        try {
            $propertyid = $this->propertyid;
            // viewuser column varchar(15) hai, isliye 15 char tak limit kar diya
            $viewUser = substr(Auth::user()->name ?? Auth::user()->u_name ?? 'Staff', 0, 15);

            DB::beginTransaction();

            $query = DB::table('servicerequesthdr')
                ->where('propertyid', $propertyid)
                ->where('requestno', $request->requestno)
                ->where('status', 'Pending'); // sirf Pending status par hi view-track ho

            if ($request->filled('roomno')) {
                $query->where('roomno', $request->roomno);
            }

            $updated = $query->update([
                'status'   => 'In Progress',
                'viewtime' => date('H:i:s'),
                'viewuser' => $viewUser,
            ]);

            DB::commit();

            return response()->json(['status' => 'success', 'updated' => $updated]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function acceptServiceRequest(Request $request)
    {
        $request->validate([
            'requestno' => 'required|string',
            'roomno'    => 'nullable|string',
        ]);

        try {
            $propertyid = $this->propertyid;
            $staffName  = Auth::user()->name ?? Auth::user()->u_name ?? 'Staff';

            DB::beginTransaction();

            $query = DB::table('servicerequesthdr')
                ->where('propertyid', $propertyid)
                ->where('requestno', $request->requestno)
                ->whereIn('status', ['Pending', 'In Progress']);

            if ($request->filled('roomno')) {
                $query->where('roomno', $request->roomno);
            }

            $updated = $query->update([
                'status'     => 'Delivered',
                'closedby'   => $staffName,
                'closeddate' => now(),
            ]);

            if ($updated === 0) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'No pending request found (already processed?).']);
            }

            $dtlQuery = DB::table('servicerequesthdrdtl')
                ->where('propertyid', $propertyid)
                ->where('requestno', $request->requestno);

            if ($request->filled('roomno')) {
                $dtlQuery->where('roomno', $request->roomno);
            }

            $dtlQuery->update([
                'status'    => 'Delivered',
                'closedate' => date('Y-m-d'),
                'closetime' => date('H:i:s'),
            ]);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Service request accepted.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function rejectServiceRequest(Request $request)
    {
        $request->validate([
            'requestno' => 'required|string',
            'roomno'    => 'nullable|string',
        ]);

        try {
            $propertyid = $this->propertyid;
            $staffName  = Auth::user()->name ?? Auth::user()->u_name ?? 'Staff';

            DB::beginTransaction();

            $query = DB::table('servicerequesthdr')
                ->where('propertyid', $propertyid)
                ->where('requestno', $request->requestno)
                ->whereIn('status', ['Pending', 'In Progress']);

            if ($request->filled('roomno')) {
                $query->where('roomno', $request->roomno);
            }

            $updated = $query->update([
                'status'     => 'Cancelled',
                'closedby'   => $staffName,
                'closeddate' => now(),
            ]);

            if ($updated === 0) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'No pending request found (already processed?).']);
            }

            $dtlQuery = DB::table('servicerequesthdrdtl')
                ->where('propertyid', $propertyid)
                ->where('requestno', $request->requestno);

            if ($request->filled('roomno')) {
                $dtlQuery->where('roomno', $request->roomno);
            }

            $dtlQuery->update([
                'status'    => 'Cancelled',
                'closedate' => date('Y-m-d'),
                'closetime' => date('H:i:s'),
            ]);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Service request rejected.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }
    public function expressCheckout($propertyid, $roomno)
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        $company = Companyreg::where('propertyid', $propertyid)->first();
        $ncurdate = $this->ncurFor($propertyid);

        $stay = DB::table('roomocc as RC')
            ->select(
                'RC.docid',
                'RC.sno1',
                'RC.name as guest_name',
                'RC.roomno',
                'RC.depdate',
                DB::raw('(SELECT SUM(P.amtdr) - SUM(P.amtcr) FROM paycharge P WHERE CONVERT(P.folionodocid USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(RC.docid USING utf8mb4) COLLATE utf8mb4_general_ci AND P.sno1 = RC.sno1) as current_bill')
            )
            ->where('RC.propertyid', $propertyid)
            ->where('RC.roomno', $roomno)
            ->where(function ($q) {
                $q->whereNull('RC.type')->orWhere('RC.type', '');
            })
            ->where(function ($q) use ($ncurdate) {
                $q->whereNull('RC.chkoutdate')->orWhere('RC.chkoutdate', $ncurdate);
            })
            ->first();

        if (!$stay) {
            $receptionNo = DB::table('compservicefacillities')
                ->where('propertyid', $propertyid)
                ->where('isactive', 1)
                ->where('service', 'Reception')
                ->value('remark');

            return view('property.roomservice.roomnotoccupied', compact('company', 'propertyid', 'roomno', 'receptionNo'));
        }

        // Already ek pending checkout-request bheja ja chuka hai kya
        $alreadyRequested = DB::table('servicerequesthdr as SH')
            ->join('servicerequesthdrdtl as SD', function ($join) {
                $join->on(DB::raw('CONVERT(SD.requestno USING utf8mb4) COLLATE utf8mb4_general_ci'), '=', DB::raw('CONVERT(SH.requestno USING utf8mb4) COLLATE utf8mb4_general_ci'))
                    ->on(DB::raw('CONVERT(SD.roomno USING utf8mb4) COLLATE utf8mb4_general_ci'), '=', DB::raw('CONVERT(SH.roomno USING utf8mb4) COLLATE utf8mb4_general_ci'))
                    ->on(DB::raw('CONVERT(SD.propertyid USING utf8mb4) COLLATE utf8mb4_general_ci'), '=', DB::raw('CONVERT(SH.propertyid USING utf8mb4) COLLATE utf8mb4_general_ci'));
            })
            ->where('SH.propertyid', $propertyid)
            ->where('SH.roomno', $roomno)
            ->where('SH.status', 'Pending')
            ->where('SD.requesttype', 'Checkout')
            ->exists();

        return view('property.roomservice.expresscheckout', compact('company', 'stay', 'propertyid', 'roomno', 'alreadyRequested'));
    }

    public function submitExpressCheckout(Request $request, $propertyid, $roomno)
    {
        $request->validate([
            'payment_method' => 'required|string|in:Credit Card,UPI,Company Credit,Pay at Reception',
            'confirm'         => 'required|accepted',
        ]);

        $ncurdate = $this->ncurFor($propertyid);
        date_default_timezone_set('Asia/Kolkata');

        $stay = DB::table('roomocc as RC')
            ->select(
                'RC.docid',
                'RC.guestprof',
                'RC.name as guest_name',
                'RC.depdate',
                DB::raw('(SELECT SUM(P.amtdr) - SUM(P.amtcr) FROM paycharge P WHERE P.folionodocid = RC.docid AND P.sno1 = RC.sno1) as current_bill')
            )
            ->where('RC.propertyid', $propertyid)
            ->where('RC.roomno', $roomno)
            ->where(function ($q) {
                $q->whereNull('RC.type')->orWhere('RC.type', '');
            })
            ->where(function ($q) use ($ncurdate) {
                $q->whereNull('RC.chkoutdate')->orWhere('RC.chkoutdate', $ncurdate);
            })
            ->first();

        if (!$stay) {
            return response()->json([
                'success' => false,
                'message' => 'This room is currently not occupied.',
            ], 422);
        }

        $now = now();
        $billAmount = number_format((float) ($stay->current_bill ?? 0), 2);
        $paymentMethod = $request->input('payment_method');

        DB::beginTransaction();
        try {
            $lastNo = DB::table('servicerequesthdr')
                ->where('propertyid', $propertyid)
                ->lockForUpdate()
                ->max(DB::raw('CAST(requestno AS UNSIGNED)'));

            $newRequestNo = (string) ($lastNo ? $lastNo + 1 : 1);

            DB::table('servicerequesthdr')->insert([
                'propertyid'    => $propertyid,
                'foliodocid'    => $stay->docid ?? null,
                'guestprof'     => $stay->guestprof ?? null,
                'roomno'        => $roomno,
                'requestno'     => $newRequestNo,
                'requestdate'   => date('Y-m-d'),
                'requesttime'   => date('H:i:s'),
                'status'        => 'Pending',
                'requestedfrom' => 'QR Portal',
                'remarks'       => "Express Checkout - Bill: ₹{$billAmount} - Payment: {$paymentMethod}",
                'assigneddate'  => $now,
            ]);

            DB::table('servicerequesthdrdtl')->insert([
                'propertyid'  => $propertyid,
                'roomno'      => $roomno,
                'requestno'   => $newRequestNo,
                'sno'         => 1,
                'itemname'    => \Illuminate\Support\Str::limit($paymentMethod, 15, ''),
                'requesttype' => 'Checkout',
                'status'      => 'Pending',
            ]);

            DB::commit();

            return response()->json(['success' => true, 'requestno' => $newRequestNo]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Could not submit checkout request. Please try again.',
            ]);
        }
    }

    // ─── Service Request Register Report ─────────────────────────────────────

    /**
     * Show Service Request Register page.
     */
    public function servicerequestregister(Request $request)
    {
        $company   = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)
                           ->where('state_code', $company->state_code)
                           ->value('name');

        return view('property.servicerequestregister', [
            'company'   => $company,
            'statename' => $statename,
            'fromdate'  => $this->ncurdate,
        ]);
    }

    /**
     * Fetch Service Request Register data with date filter.
     *
     * SELECT SH.requestno, SH.requestdate, SH.requesttime, SH.roomno,
     *        G.name as GuestName, SH.remarks, SH.status,
     *        SD.requesttype as Type, SD.itemname as Particular,
     *        SD.closedate, SD.closetime, SH.closedby as CloseUser
     * FROM servicerequesthdr SH
     * LEFT JOIN guestfolio G ON SH.foliodocid = G.docid
     * LEFT JOIN servicerequesthdrdtl SD ON SH.requestno = SD.requestno
     * WHERE SH.propertyid = ?
     * ORDER BY SH.requestdate, SH.requestno
     */
    public function fetchservicerequestregister(Request $request)
    {
        try {
            $fromdate   = $request->input('fromdate', $this->ncurdate);
            $todate     = $request->input('todate',   $this->ncurdate);
            $propertyId = $this->propertyid;

            $rows = DB::table('servicerequesthdr as SH')
                ->leftJoin('guestfolio as G',         'SH.foliodocid', '=', 'G.docid')
                ->leftJoin('servicerequesthdrdtl as SD', 'SH.requestno', '=', 'SD.requestno')
                ->where('SH.propertyid', $propertyId)
                ->whereBetween('SH.requestdate', [$fromdate, $todate])
                ->select(
                    'SH.requestno',
                    'SH.requestdate',
                    'SH.requesttime',
                    'SH.roomno',
                    DB::raw('G.name as GuestName'),
                    'SH.remarks',
                    'SH.status',
                    DB::raw('SD.requesttype as Type'),
                    DB::raw('SD.itemname as Particular'),
                    'SD.closedate',
                    'SD.closetime',
                    DB::raw('SH.closedby as CloseUser')
                )
                ->orderBy('SH.requestdate', 'ASC')
                ->orderBy('SH.requestno',   'ASC')
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Daily Service Summary Report ─────────────────────────────────────────

    /**
     * Show Daily Service Summary page.
     */
    public function dailyservicesummary(Request $request)
    {
        $company   = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)
                           ->where('state_code', $company->state_code)
                           ->value('name');

        return view('property.dailyservicesummary', [
            'company'   => $company,
            'statename' => $statename,
            'fromdate'  => $this->ncurdate,
        ]);
    }

    /**
     * Fetch Daily Service Summary with date filter.
     * 
     * SELECT requestDate,
     *        COUNT(*) TotalRequests,
     *        SUM(Status='Pending') Pending,
     *        SUM(Status='Accepted') Accepted,
     *        SUM(Status='In Progress') InProgress,
     *        SUM(Status='Delivered') Delivered,
     *        SUM(Status='Cancelled') Cancelled,
     *        ROUND(AVG(TIMESTAMPDIFF(MINUTE,requestdate,AssignedDate)),2) AvgResponse,
     *        ROUND(AVG(TIMESTAMPDIFF(MINUTE,requestdate,closeddate)),2) AvgCompletion
     * FROM servicerequesthdr
     * WHERE propertyid = ? AND RequestDate BETWEEN ? AND ?
     * GROUP BY DATE(RequestDate)
     * ORDER BY DATE(RequestDate)
     */
    public function fetchdailyservicesummary(Request $request)
    {
        try {
            $fromdate   = $request->input('fromdate', $this->ncurdate);
            $todate     = $request->input('todate',   $this->ncurdate);
            $propertyId = $this->propertyid;

            $rows = DB::table('servicerequesthdr')
                ->where('propertyid', $propertyId)
                ->whereBetween('requestdate', [$fromdate, $todate])
                ->select(
                    DB::raw('DATE(requestdate) as requestDate'),
                    DB::raw('COUNT(*) as TotalRequests'),
                    DB::raw("SUM(CASE WHEN Status='Pending' THEN 1 ELSE 0 END) as Pending"),
                    DB::raw("SUM(CASE WHEN Status='Accepted' THEN 1 ELSE 0 END) as Accepted"),
                    DB::raw("SUM(CASE WHEN Status='In Progress' THEN 1 ELSE 0 END) as InProgress"),
                    DB::raw("SUM(CASE WHEN Status='Delivered' THEN 1 ELSE 0 END) as Delivered"),
                    DB::raw("SUM(CASE WHEN Status='Cancelled' THEN 1 ELSE 0 END) as Cancelled"),
                    DB::raw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, requestdate, AssignedDate)), 2) as AvgResponse'),
                    DB::raw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, requestdate, closeddate)), 2) as AvgCompletion')
                )
                ->groupBy(DB::raw('DATE(requestdate)'))
                ->orderBy(DB::raw('DATE(requestdate)'), 'ASC')
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

