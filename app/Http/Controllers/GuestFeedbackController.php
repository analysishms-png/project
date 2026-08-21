<?php

namespace App\Http\Controllers;

use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GuestFeedbackController extends Controller
{
    protected $propertyid;

    public function __construct()
    {
        $this->propertyid = Auth::user()->propertyid ?? session('propertyid');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FEEDBACK DASHBOARD — Overview of all guest feedback
    // ═══════════════════════════════════════════════════════════════════════════

    public function dashboard()
    {
        $propertyid = $this->propertyid;

        // Total feedback
        $totalFeedback = DB::table('guest_feedback')
            ->where('propertyid', $propertyid)
            ->count();

        $completedFeedback = DB::table('guest_feedback')
            ->where('propertyid', $propertyid)
            ->where('survey_status', 'completed')
            ->count();

        $pendingSurveys = DB::table('guest_feedback')
            ->where('propertyid', $propertyid)
            ->where('survey_status', 'pending')
            ->count();

        $sentSurveys = DB::table('guest_feedback')
            ->where('propertyid', $propertyid)
            ->where('survey_status', 'sent')
            ->count();

        // Average ratings
        $avgRatings = DB::table('guest_feedback')
            ->where('propertyid', $propertyid)
            ->where('survey_status', 'completed')
            ->select(
                DB::raw('AVG(overall_rating) as avg_overall'),
                DB::raw('AVG(cleanliness_rating) as avg_cleanliness'),
                DB::raw('AVG(service_rating) as avg_service'),
                DB::raw('AVG(food_rating) as avg_food'),
                DB::raw('AVG(value_rating) as avg_value'),
                DB::raw('AVG(location_rating) as avg_location'),
                DB::raw('SUM(CASE WHEN would_recommend = 1 THEN 1 ELSE 0 END) as recommend_count')
            )
            ->first();

        // Response rate
        $respondedCount = DB::table('guest_feedback')
            ->where('propertyid', $propertyid)
            ->whereNotNull('response')
            ->count();

        $responseRate = $completedFeedback > 0 ? round(($respondedCount / $completedFeedback) * 100, 1) : 0;

        // Recent feedback
        $recentFeedback = DB::table('guest_feedback')
            ->where('propertyid', $propertyid)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Rating distribution
        $distribution = DB::table('guest_feedback')
            ->where('propertyid', $propertyid)
            ->where('survey_status', 'completed')
            ->select('overall_rating', DB::raw('COUNT(*) as cnt'))
            ->groupBy('overall_rating')
            ->orderBy('overall_rating')
            ->get();

        // Monthly trend
        $monthlyTrend = DB::table('guest_feedback')
            ->where('propertyid', $propertyid)
            ->where('survey_status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('AVG(overall_rating) as avg_rating'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('property.feedback.dashboard', compact(
            'totalFeedback', 'completedFeedback', 'pendingSurveys', 'sentSurveys',
            'avgRatings', 'responseRate', 'recentFeedback', 'distribution', 'monthlyTrend'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SURVEY FORM — Public mobile-friendly feedback form
    // ═══════════════════════════════════════════════════════════════════════════

    public function surveyForm($id)
    {
        $feedback = DB::table('guest_feedback')
            ->where('id', $id)
            ->first();

        if (!$feedback) {
            return view('property.feedback.not-found');
        }

        if ($feedback->survey_status === 'completed') {
            return view('property.feedback.already-completed', compact('feedback'));
        }

        return view('property.feedback.survey', compact('feedback'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SUBMIT SURVEY — Process guest feedback submission
    // ═══════════════════════════════════════════════════════════════════════════

    public function submitSurvey(Request $request, $id)
    {
        $request->validate([
            'overall_rating' => 'required|integer|min:1|max:5',
            'cleanliness_rating' => 'required|integer|min:1|max:5',
            'service_rating' => 'required|integer|min:1|max:5',
            'food_rating' => 'required|integer|min:1|max:5',
            'value_rating' => 'required|integer|min:1|max:5',
            'location_rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
            'would_recommend' => 'nullable|boolean',
        ]);

        $updated = DB::table('guest_feedback')
            ->where('id', $id)
            ->where('survey_status', '!=', 'completed')
            ->update([
                'overall_rating' => $request->overall_rating,
                'cleanliness_rating' => $request->cleanliness_rating,
                'service_rating' => $request->service_rating,
                'food_rating' => $request->food_rating,
                'value_rating' => $request->value_rating,
                'location_rating' => $request->location_rating,
                'comments' => $request->comments,
                'would_recommend' => $request->has('would_recommend') ? 1 : 0,
                'survey_status' => 'completed',
                'completed_at' => now(),
            ]);

        if ($updated) {
            return view('property.feedback.thank-you');
        }

        return back()->with('error', 'Unable to submit feedback');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // FEEDBACK LIST — All feedback with filters
    // ═══════════════════════════════════════════════════════════════════════════

    public function list(Request $request)
    {
        $propertyid = $this->propertyid;

        $query = DB::table('guest_feedback')
            ->where('propertyid', $propertyid);

        if ($request->filled('status')) {
            $query->where('survey_status', $request->status);
        }
        if ($request->filled('rating')) {
            $query->where('overall_rating', $request->rating);
        }
        if ($request->filled('from_date')) {
            $query->where('checkout_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('checkout_date', '<=', $request->to_date);
        }

        $feedback = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('property.feedback.list', compact('feedback'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // RESPOND TO FEEDBACK — Management response
    // ═══════════════════════════════════════════════════════════════════════════

    public function respond(Request $request, $id)
    {
        $request->validate([
            'response' => 'required|string|max:500',
        ]);

        DB::table('guest_feedback')
            ->where('id', $id)
            ->update([
                'response' => $request->response,
                'responded_by' => Auth::user()->name ?? 'admin',
                'responded_at' => now(),
            ]);

        return back()->with('success', 'Response saved');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SEND SURVEY — Send survey to a guest
    // ═══════════════════════════════════════════════════════════════════════════

    public function sendSurvey(Request $request)
    {
        $propertyid = $this->propertyid;
        $docid = $request->docid;

        // Get room occupancy data
        $roomOcc = DB::table('roomocc')
            ->where('propertyid', $propertyid)
            ->where('docid', $docid)
            ->first();

        if (!$roomOcc) {
            return response()->json(['success' => false, 'message' => 'Check-in record not found']);
        }

        // Get guest data
        $guest = DB::table('guestprof')
            ->where('propertyid', $propertyid)
            ->where('sno', $roomOcc->guestprof)
            ->first();

        $phone = ltrim($guest->mobile_no ?? '', '0');
        if (strlen($phone) == 10) $phone = '91' . $phone;

        // Create feedback record
        $feedbackId = DB::table('guest_feedback')->insertGetId([
            'propertyid' => $propertyid,
            'docid' => $docid,
            'roomno' => $roomOcc->roomno ?? '',
            'guest_name' => $roomOcc->name ?? $guest->name ?? '',
            'guest_email' => $guest->email_id ?? '',
            'guest_phone' => $phone,
            'checkin_date' => $roomOcc->chkindate ?? null,
            'checkout_date' => $roomOcc->chkoutdate ?? null,
            'survey_status' => 'sent',
            'sent_at' => now(),
            'u_name' => Auth::user()->name ?? 'system',
            'created_at' => now(),
        ]);

        // Send via WhatsApp if phone available
        if ($phone) {
            $surveyUrl = url('feedback/survey/' . $feedbackId);
            $message = "Dear {$roomOcc->name}, thank you for staying with us! We'd love your feedback. Please take 2 minutes to rate your stay: {$surveyUrl}";

            try {
                $wpenv = DB::table('enviro_whatsapp')
                    ->where('propertyid', $propertyid)
                    ->first();

                if ($wpenv && $wpenv->whatsappurl) {
                    \Http::withHeaders([
                        'Authorization' => 'Bearer ' . $wpenv->bearercode,
                        'Content-Type' => 'application/json',
                    ])->post(rtrim($wpenv->whatsappurl, '/'), [
                        'messaging_product' => 'whatsapp',
                        'to' => $phone,
                        'type' => 'text',
                        'text' => ['body' => $message],
                    ]);

                    WhatsappLog::create([
                        'propertyid' => $propertyid,
                        'recipient_phone_number' => $phone,
                        'type' => 'Bill Message',
                        'template_id' => 'feedback_survey',
                        'parameters' => json_encode(['feedback_id' => $feedbackId]),
                        'response' => 'Survey sent',
                        'http_code' => 200,
                        'status' => 'success',
                        'u_name' => Auth::user()->name ?? 'system',
                    ]);
                }
            } catch (\Exception $e) {
                // Log but don't fail
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Survey sent to ' . ($roomOcc->name ?? 'guest'),
            'survey_url' => url('feedback/survey/' . $feedbackId),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // AUTO-SEND SURVEYS — Cron job to send surveys to recent checkouts
    // ═══════════════════════════════════════════════════════════════════════════

    public function autoSendSurveys()
    {
        $propertyid = $this->propertyid;
        $yesterday = Carbon::yesterday()->toDateString();

        // Get yesterday's checkouts that don't have feedback yet
        $checkouts = DB::table('roomocc')
            ->where('propertyid', $propertyid)
            ->where('chkoutdate', $yesterday)
            ->where('u_ae', 'e')
            ->whereNotIn('docid', function ($q) use ($propertyid) {
                $q->select('docid')
                    ->from('guest_feedback')
                    ->where('propertyid', $propertyid);
            })
            ->get();

        $sent = 0;
        foreach ($checkouts as $co) {
            $guest = DB::table('guestprof')
                ->where('propertyid', $propertyid)
                ->where('sno', $co->guestprof)
                ->first();

            $phone = ltrim($guest->mobile_no ?? '', '0');
            if (strlen($phone) == 10) $phone = '91' . $phone;

            $feedbackId = DB::table('guest_feedback')->insertGetId([
                'propertyid' => $propertyid,
                'docid' => $co->docid,
                'roomno' => $co->roomno ?? '',
                'guest_name' => $co->name ?? '',
                'guest_email' => $guest->email_id ?? '',
                'guest_phone' => $phone,
                'checkin_date' => $co->chkindate ?? null,
                'checkout_date' => $co->chkoutdate ?? null,
                'survey_status' => 'sent',
                'sent_at' => now(),
                'u_name' => 'system',
                'created_at' => now(),
            ]);

            // Send WhatsApp
            if ($phone) {
                $surveyUrl = url('feedback/survey/' . $feedbackId);
                $message = "Dear {$co->name}, thank you for staying with us! We'd love your feedback: {$surveyUrl}";

                try {
                    $wpenv = DB::table('enviro_whatsapp')
                        ->where('propertyid', $propertyid)
                        ->first();

                    if ($wpenv && $wpenv->whatsappurl) {
                        \Http::withHeaders([
                            'Authorization' => 'Bearer ' . $wpenv->bearercode,
                            'Content-Type' => 'application/json',
                        ])->post(rtrim($wpenv->whatsappurl, '/'), [
                            'messaging_product' => 'whatsapp',
                            'to' => $phone,
                            'type' => 'text',
                            'text' => ['body' => $message],
                        ]);
                    }
                } catch (\Exception $e) {}
            }

            $sent++;
        }

        return response()->json([
            'success' => true,
            'message' => "Sent $sent feedback surveys",
            'sent' => $sent,
        ]);
    }
}
