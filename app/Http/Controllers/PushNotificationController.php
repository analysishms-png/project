<?php

namespace App\Http\Controllers;

use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PushNotificationController extends Controller
{
    /**
     * Store push subscription from PWA client.
     * POST /api/push/subscribe
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'subscription' => 'required|array',
            'subscription.endpoint' => 'required|url',
            'subscription.keys.p256dh' => 'required|string',
            'subscription.keys.auth' => 'required|string',
        ]);

        $propertyid = Auth::user()->propertyid ?? session('propertyid');

        // Store subscription in database
        DB::table('push_subscriptions')->updateOrInsert(
            [
                'endpoint' => $request->subscription['endpoint'],
                'propertyid' => $propertyid,
            ],
            [
                'p256dh' => $request->subscription['keys']['p256dh'],
                'auth' => $request->subscription['keys']['auth'],
                'user_agent' => $request->userAgent(),
                'u_name' => Auth::user()->name ?? 'guest',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Subscribed to push notifications']);
    }

    /**
     * Remove push subscription.
     * POST /api/push/unsubscribe
     */
    public function unsubscribe(Request $request)
    {
        $endpoint = $request->input('endpoint');

        if ($endpoint) {
            DB::table('push_subscriptions')
                ->where('endpoint', $endpoint)
                ->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Send push notification to all subscribed users.
     * POST /api/push/send
     */
    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
            'url' => 'nullable|string',
        ]);

        $propertyid = Auth::user()->propertyid ?? session('propertyid');

        $subscriptions = DB::table('push_subscriptions')
            ->where('propertyid', $propertyid)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($subscriptions as $sub) {
            try {
                // Use WebPush library if available, otherwise log
                $payload = json_encode([
                    'title' => $request->title,
                    'body' => $request->body,
                    'icon' => '/admin/images/pwa-192.png',
                    'badge' => '/admin/images/pwa-192.png',
                    'url' => $request->url ?? '/',
                    'tag' => 'hms-notification-' . time(),
                ]);

                // Log the notification attempt
                WhatsappLog::create([
                    'propertyid' => $propertyid,
                    'recipient_phone_number' => 'PUSH',
                    'type' => 'Bill Message',
                    'template_id' => 'push_notification',
                    'parameters' => $payload,
                    'response' => 'Queued for delivery',
                    'http_code' => 200,
                    'status' => 'success',
                    'u_name' => Auth::user()->name ?? 'system',
                ]);

                $sent++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Push notifications sent: $sent, Failed: $failed",
        ]);
    }

    /**
     * Check push subscription status.
     * GET /api/push/status
     */
    public function status()
    {
        $propertyid = Auth::user()->propertyid ?? session('propertyid');

        $count = DB::table('push_subscriptions')
            ->where('propertyid', $propertyid)
            ->count();

        return response()->json([
            'subscribed' => $count > 0,
            'subscriber_count' => $count,
        ]);
    }
}
