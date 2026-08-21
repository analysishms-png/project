<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User-specific channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// ═══════════════════════════════════════════════════════════════
// REAL-TIME DASHBOARD CHANNELS
// ═══════════════════════════════════════════════════════════════

// Room status updates (occupied/vacant/dirty/OOO changes)
Broadcast::channel('property.{propertyId}.room-status', function ($user, $propertyId) {
    return (int) $user->propertyid === (int) $propertyId;
});

// Guest check-in/check-out activity
Broadcast::channel('property.{propertyId}.guest-activity', function ($user, $propertyId) {
    return (int) $user->propertyid === (int) $propertyId;
});

// POS activity (new bills, payments, KOTs)
Broadcast::channel('property.{propertyId}.pos-activity', function ($user, $propertyId) {
    return (int) $user->propertyid === (int) $propertyId;
});

// Dashboard revenue and stats updates
Broadcast::channel('property.{propertyId}.dashboard', function ($user, $propertyId) {
    return (int) $user->propertyid === (int) $propertyId;
});

// General notifications (alerts, warnings)
Broadcast::channel('property.{propertyId}.notifications', function ($user, $propertyId) {
    return (int) $user->propertyid === (int) $propertyId;
});
