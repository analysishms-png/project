<?php

namespace App\Http\Controllers;

use App\Models\EnviroWhatsapp;
use App\Models\WhatsappLog;
use App\Models\GuestProf;
use App\Models\RoomOcc;
use App\Models\Grpbookingdetail;
use App\Models\Paycharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CommunicationController extends Controller
{
    protected $propertyid;

    public function __construct()
    {
        $this->propertyid = Auth::user()->propertyid ?? session('propertyid');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // COMMUNICATION DASHBOARD — Centralized view of all communications
    // ═══════════════════════════════════════════════════════════════════════════

    public function dashboard()
    {
        $propertyid = $this->propertyid;
        $today = Carbon::today()->toDateString();

        // Communication statistics (last 30 days)
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateString();

        $totalSent = WhatsappLog::where('propertyid', $propertyid)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();

        $totalSuccess = WhatsappLog::where('propertyid', $propertyid)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->where('status', 'success')
            ->count();

        $totalFailed = WhatsappLog::where('propertyid', $propertyid)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->where('status', 'failed')
            ->count();

        // By type
        $byType = WhatsappLog::where('propertyid', $propertyid)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->select('type', DB::raw('count(*) as cnt'), DB::raw('SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_cnt'))
            ->groupBy('type')
            ->get();

        // Recent logs
        $recentLogs = WhatsappLog::where('propertyid', $propertyid)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // WhatsApp balance
        $wpenv = EnviroWhatsapp::where('propertyid', $propertyid)->first();
        $whatsappBalance = $wpenv->whatsappbal ?? 0;

        // Pending pre-arrivals (guests arriving today/tomorrow)
        $preArrivals = Grpbookingdetail::where('propertyid', $propertyid)
            ->where('ArrDate', '>=', $today)
            ->where('ArrDate', '<=', Carbon::tomorrow()->toDateString())
            ->where('DocId', 'like', 'RES%')
            ->orderBy('ArrDate')
            ->get()
            ->map(function ($r) {
                $phone = ltrim($r->MobileNo ?? $r->PhoneNo ?? '', '0');
                if (strlen($phone) == 10) $phone = '91' . $phone;
                return [
                    'reservation_no' => $r->DocId,
                    'guest_name' => $r->GuestName ?? $r->name ?? '',
                    'arrival_date' => $r->ArrDate,
                    'room_no' => $r->RoomNo ?? '',
                    'phone' => $phone,
                    'advance' => $r->Advance ?? 0,
                ];
            });

        // Pending checkout follow-ups (checked out yesterday)
        $yesterday = Carbon::yesterday()->toDateString();
        $recentCheckouts = RoomOcc::where('propertyid', $propertyid)
            ->where('chkoutdate', $yesterday)
            ->where('u_ae', 'e')
            ->limit(20)
            ->get()
            ->map(function ($r) {
                $phone = ltrim($r->guestprof->mobileno ?? '', '0');
                if (strlen($phone) == 10) $phone = '91' . $phone;
                return [
                    'docid' => $r->docid,
                    'name' => $r->name,
                    'roomno' => $r->roomno,
                    'chkoutdate' => $r->chkoutdate,
                    'phone' => $phone,
                ];
            });

        return view('property.communication.dashboard', compact(
            'totalSent', 'totalSuccess', 'totalFailed', 'byType',
            'recentLogs', 'whatsappBalance', 'preArrivals', 'recentCheckouts'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // COMMUNICATION LOG — Filterable log viewer
    // ═══════════════════════════════════════════════════════════════════════════

    public function log(Request $request)
    {
        $propertyid = $this->propertyid;

        $query = WhatsappLog::where('propertyid', $propertyid);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date . ' 23:59:59');
        }
        if ($request->filled('phone')) {
            $query->where('recipient_phone_number', 'like', '%' . $request->phone . '%');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        $types = ['Checkin', 'Checkout', 'Reservation', 'Reservation Cancel', 'KOT Bill', 'Bill Message', 'Balance Error'];

        return view('property.communication.log', compact('logs', 'types'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SEND MANUAL MESSAGE — Send WhatsApp/SMS to a guest
    // ═══════════════════════════════════════════════════════════════════════════

    public function sendManualMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:1000',
            'type' => 'required|string',
        ]);

        $propertyid = $this->propertyid;
        $wpenv = EnviroWhatsapp::where('propertyid', $propertyid)->first();

        if (!$wpenv || !$wpenv->whatsappurl) {
            return response()->json(['success' => false, 'message' => 'WhatsApp not configured']);
        }

        // Send via MuzzTech API
        $phone = ltrim($request->phone, '0');
        if (strlen($phone) == 10) $phone = '91' . $phone;

        try {
            $response = \Http::withHeaders([
                'Authorization' => 'Bearer ' . $wpenv->bearercode,
                'Content-Type' => 'application/json',
            ])->post(rtrim($wpenv->whatsappurl, '/'), [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'text',
                'text' => ['body' => $request->message],
            ]);

            $status = $response->successful() ? 'success' : 'failed';

            WhatsappLog::create([
                'propertyid' => $propertyid,
                'recipient_phone_number' => $phone,
                'type' => $request->type,
                'template_id' => 'manual',
                'parameters' => json_encode(['message' => $request->message]),
                'response' => $response->body(),
                'http_code' => $response->status(),
                'status' => $status,
                'u_name' => Auth::user()->name ?? 'system',
            ]);

            return response()->json(['success' => true, 'message' => 'Message sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send: ' . $e->getMessage()]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // BULK SEND — Send messages to multiple guests
    // ═══════════════════════════════════════════════════════════════════════════

    public function bulkSend(Request $request)
    {
        $request->validate([
            'phones' => 'required|array',
            'message' => 'required|string|max:1000',
            'type' => 'required|string',
        ]);

        $propertyid = $this->propertyid;
        $wpenv = EnviroWhatsapp::where('propertyid', $propertyid)->first();

        if (!$wpenv || !$wpenv->whatsappurl) {
            return response()->json(['success' => false, 'message' => 'WhatsApp not configured']);
        }

        $sent = 0;
        $failed = 0;

        foreach ($request->phones as $phone) {
            $phone = ltrim($phone, '0');
            if (strlen($phone) == 10) $phone = '91' . $phone;

            try {
                $response = \Http::withHeaders([
                    'Authorization' => 'Bearer ' . $wpenv->bearercode,
                    'Content-Type' => 'application/json',
                ])->post(rtrim($wpenv->whatsappurl, '/'), [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['body' => $request->message],
                ]);

                $status = $response->successful() ? 'success' : 'failed';
                if ($status === 'success') $sent++; else $failed++;

                WhatsappLog::create([
                    'propertyid' => $propertyid,
                    'recipient_phone_number' => $phone,
                    'type' => $request->type,
                    'template_id' => 'bulk',
                    'parameters' => json_encode(['message' => $request->message]),
                    'response' => $response->body(),
                    'http_code' => $response->status(),
                    'status' => $status,
                    'u_name' => Auth::user()->name ?? 'system',
                ]);
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sent: $sent, Failed: $failed",
            'sent' => $sent,
            'failed' => $failed,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SEND PRE-ARRIVAL — Send welcome message to arriving guests
    // ═══════════════════════════════════════════════════════════════════════════

    public function sendPreArrival(Request $request)
    {
        $propertyid = $this->propertyid;
        $docid = $request->docid;

        $booking = Grpbookingdetail::where('propertyid', $propertyid)
            ->where('DocId', $docid)
            ->first();

        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Reservation not found']);
        }

        $phone = ltrim($booking->MobileNo ?? $booking->PhoneNo ?? '', '0');
        if (strlen($phone) == 10) $phone = '91' . $phone;

        if (empty($phone)) {
            return response()->json(['success' => false, 'message' => 'No phone number found']);
        }

        // Use existing WhatsApp template
        $wpenv = EnviroWhatsapp::where('propertyid', $propertyid)->first();
        $templateMsg = $wpenv->reservation ?? "Dear {guest_name}, your reservation {reservation_no} at {hotel_name} is confirmed for {arrival_date}. Advance: {advance}. See you soon!";

        $msg = str_replace(
            ['{guest_name}', '{reservation_no}', '{arrival_date}', '{advance}', '{hotel_name}'],
            [$booking->GuestName ?? '', $booking->DocId, $booking->ArrDate, $booking->Advance ?? 0, $wpenv->whatsappdisplayname ?? 'Hotel'],
            $templateMsg
        );

        try {
            $response = \Http::withHeaders([
                'Authorization' => 'Bearer ' . $wpenv->bearercode,
                'Content-Type' => 'application/json',
            ])->post(rtrim($wpenv->whatsappurl, '/'), [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'text',
                'text' => ['body' => $msg],
            ]);

            $status = $response->successful() ? 'success' : 'failed';

            WhatsappLog::create([
                'propertyid' => $propertyid,
                'recipient_phone_number' => $phone,
                'type' => 'Reservation',
                'template_id' => 'pre_arrival',
                'parameters' => json_encode(['docid' => $docid, 'guest' => $booking->GuestName ?? '']),
                'response' => $response->body(),
                'http_code' => $response->status(),
                'status' => $status,
                'u_name' => Auth::user()->name ?? 'system',
            ]);

            return response()->json(['success' => true, 'message' => 'Pre-arrival message sent']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SEND CHECKOUT FOLLOW-UP — Thank you + review request
    // ═══════════════════════════════════════════════════════════════════════════

    public function sendCheckoutFollowup(Request $request)
    {
        $propertyid = $this->propertyid;
        $docid = $request->docid;

        $roomOcc = RoomOcc::where('propertyid', $propertyid)
            ->where('docid', $docid)
            ->first();

        if (!$roomOcc) {
            return response()->json(['success' => false, 'message' => 'Check-in record not found']);
        }

        $guest = GuestProf::where('propertyid', $propertyid)
            ->where('sno', $roomOcc->guestprof)
            ->first();

        $phone = ltrim($guest->mobileno ?? '', '0');
        if (strlen($phone) == 10) $phone = '91' . $phone;

        if (empty($phone)) {
            return response()->json(['success' => false, 'message' => 'No phone number found']);
        }

        $wpenv = EnviroWhatsapp::where('propertyid', $propertyid)->first();

        // Get bill amount from PayCharge
        $billAmount = Paycharge::where('propertyid', $propertyid)
            ->where('refdocid', $docid)
            ->where('vtype', '!=', 'ADV')
            ->sum('amtdr');

        $templateMsg = $wpenv->billmsgguest ?? "Thank you for staying with us! Bill Amount: {bill_amount} for Room {room_no}. We look forward to welcoming you again.";

        $msg = str_replace(
            ['{guest_name}', '{room_no}', '{bill_amount}', '{checkout_date}'],
            [$roomOcc->name ?? '', $roomOcc->roomno ?? '', number_format($billAmount, 2), $roomOcc->chkoutdate ?? ''],
            $templateMsg
        );

        try {
            $response = \Http::withHeaders([
                'Authorization' => 'Bearer ' . $wpenv->bearercode,
                'Content-Type' => 'application/json',
            ])->post(rtrim($wpenv->whatsappurl, '/'), [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'text',
                'text' => ['body' => $msg],
            ]);

            $status = $response->successful() ? 'success' : 'failed';

            WhatsappLog::create([
                'propertyid' => $propertyid,
                'recipient_phone_number' => $phone,
                'type' => 'Checkout',
                'template_id' => 'checkout_followup',
                'parameters' => json_encode(['docid' => $docid, 'guest' => $roomOcc->name ?? '']),
                'response' => $response->body(),
                'http_code' => $response->status(),
                'status' => $status,
                'u_name' => Auth::user()->name ?? 'system',
            ]);

            return response()->json(['success' => true, 'message' => 'Checkout follow-up sent']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // EMAIL TEMPLATES — Manage email templates
    // ═══════════════════════════════════════════════════════════════════════════

    public function emailTemplates()
    {
        $propertyid = $this->propertyid;

        // Get templates from enviro_whatsapp (shared config)
        $wpenv = EnviroWhatsapp::where('propertyid', $propertyid)->first();

        $templates = [
            'reservation_confirm' => [
                'name' => 'Reservation Confirmation',
                'subject' => 'Reservation Confirmed - {hotel_name}',
                'description' => 'Sent when reservation is confirmed',
                'variables' => ['guest_name', 'reservation_no', 'arrival_date', 'room_type', 'advance', 'hotel_name'],
            ],
            'pre_arrival' => [
                'name' => 'Pre-Arrival Welcome',
                'subject' => 'Welcome to {hotel_name} - Your stay starts {arrival_date}',
                'description' => 'Sent 1 day before arrival',
                'variables' => ['guest_name', 'arrival_date', 'room_no', 'hotel_name', 'check_in_time'],
            ],
            'checkin' => [
                'name' => 'Check-in Confirmation',
                'subject' => 'Check-in Confirmed - Room {room_no}',
                'description' => 'Sent at check-in',
                'variables' => ['guest_name', 'room_no', 'check_in_date', 'room_rate', 'hotel_name'],
            ],
            'checkout' => [
                'name' => 'Checkout Thank You',
                'subject' => 'Thank you for staying at {hotel_name}',
                'description' => 'Sent at checkout with bill summary',
                'variables' => ['guest_name', 'room_no', 'checkout_date', 'bill_amount', 'hotel_name'],
            ],
            'invoice' => [
                'name' => 'Invoice / Bill',
                'subject' => 'Invoice from {hotel_name} - {invoice_no}',
                'description' => 'Invoice attached as PDF',
                'variables' => ['guest_name', 'invoice_no', 'bill_amount', 'hotel_name'],
            ],
            'feedback' => [
                'name' => 'Guest Feedback Request',
                'subject' => 'How was your stay at {hotel_name}?',
                'description' => 'Sent 3 days after checkout',
                'variables' => ['guest_name', 'hotel_name', 'review_link'],
            ],
            'cancellation' => [
                'name' => 'Reservation Cancelled',
                'subject' => 'Reservation Cancelled - {reservation_no}',
                'description' => 'Sent when reservation is cancelled',
                'variables' => ['guest_name', 'reservation_no', 'cancel_date', 'refund_amount'],
            ],
            'payment_receipt' => [
                'name' => 'Payment Receipt',
                'subject' => 'Payment Received - {hotel_name}',
                'description' => 'Sent when payment is received',
                'variables' => ['guest_name', 'amount', 'payment_mode', 'room_no', 'hotel_name'],
            ],
        ];

        return view('property.communication.emailtemplates', compact('templates', 'wpenv'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SEND TEST EMAIL — Test email configuration
    // ═══════════════════════════════════════════════════════════════════════════

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            \Mail::raw("This is a test email from Analysis HMS.\n\nSent at: " . now()->format('Y-m-d H:i:s') . "\nProperty: " . $this->propertyid, function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Analysis HMS - Test Email');
            });

            return response()->json(['success' => true, 'message' => 'Test email sent to ' . $request->email]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Email failed: ' . $e->getMessage()]);
        }
    }
}
