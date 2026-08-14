<?php

namespace App\Listeners;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Reverb\Events\ChannelCreated;
use Laravel\Reverb\Events\ChannelRemoved;

class LogReverbChannelActivity
{
    /**
     * Handle channel creation events.
     */
    public function handleChannelCreated(ChannelCreated $event): void
    {
        try {
            $this->logChannelActivity('Create', $event->channel->name());
        } catch (Exception $e) {
            Log::error('Reverb Channel Creation Logging Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle channel removal events.
     */
    public function handleChannelRemoved(ChannelRemoved $event): void
    {
        try {
            $this->logChannelActivity('Disconnect', $event->channel->name());
        } catch (Exception $e) {
            Log::error('Reverb Channel Removal Logging Error: ' . $e->getMessage());
        }
    }

    /**
     * Log channel activity to the activity_logs table.
     *
     * Replaces the logging previously performed by App\WebSockets\LoggingChannelManager
     * (beyondcode/laravel-websockets) with the equivalent Reverb event hooks.
     */
    private function logChannelActivity(string $action, string $channelName): void
    {
        // Extract propertyid and username from the channel name pattern.
        $propertyid = null;
        $username = 'WebSocket User';

        if (preg_match('/property[._-]?(\d+)|(\d+)[-_]/', $channelName, $matches)) {
            $propertyid = $matches[1] ?? $matches[2];
        }

        // Skip logging if propertyid is 10 or not found.
        if (! $propertyid || $propertyid === '10' || $propertyid === 10) {
            return;
        }

        DB::table('activity_logs')->insert([
            'propertyid' => $propertyid,
            'username' => $username,
            'user_id' => null,
            'module' => 'WebSocket',
            'action' => $action . ' - ' . $channelName,
            'method' => 'WebSocket',
            'url' => 'ws://websocket/channel',
            'ip_address' => 'WebSocket Server',
            'user_agent' => 'WebSocket Client',
            'properties' => json_encode([
                'channel' => $channelName,
                'action' => $action,
                'source' => 'reverb_websocket',
            ]),
            'created_at' => now(),
        ]);
    }
}
