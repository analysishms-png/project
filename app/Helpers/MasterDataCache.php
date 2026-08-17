<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Master-data cache (PERF-03).
 *
 * The hottest master lists — travel agents, corporate companies, rooms, and
 * FOM revenue codes — are re-fetched on nearly every booking/walkin page
 * load. These lists change only through their master CRUD screens, so they
 * are cached per property and explicitly flushed on every write path.
 *
 * Cache driver is `file` (PERF-04), so entries survive across requests.
 * TTL is a safety net only — correctness relies on flush() being called on
 * every write (see the invalidation map in the callers).
 */
class MasterDataCache
{
    /** Safety-net TTL for master lists (24h). Explicit flush() keeps them fresh. */
    public const TTL = 86400;

    public const KEY_TRAVEL_AGENTS = 'travelagents';
    public const KEY_CORPORATES = 'corporates';
    public const KEY_COMPANIES_AGENTS = 'companiesagents';
    public const KEY_ROOMS = 'rooms';
    public const KEY_FOM_CHARGES = 'fomcharges';
    public const KEY_AVAIL_VERSION = 'availversion';

    /**
     * Availability cache TTL (safety net). Availability changes with every
     * booking, so flushAvailability() is the primary invalidation — the TTL
     * only bounds staleness for write paths not yet wired to flush.
     */
    public const AVAIL_TTL = 300;

    private static function key(string $propertyid, string $type): string
    {
        return 'masterdata.' . $propertyid . '.' . $type;
    }

    /**
     * Travel agents — subgroup where comp_type='Travel Agency'.
     */
    public static function travelAgents(string $propertyid)
    {
        return Cache::remember(
            self::key($propertyid, self::KEY_TRAVEL_AGENTS),
            self::TTL,
            function () use ($propertyid) {
                return DB::table('subgroup')
                    ->where('propertyid', $propertyid)
                    ->where('comp_type', 'Travel Agency')
                    ->orderBy('name', 'ASC')
                    ->get();
            }
        );
    }

    /**
     * Corporate companies — subgroup where comp_type='Corporate'.
     */
    public static function corporates(string $propertyid)
    {
        return Cache::remember(
            self::key($propertyid, self::KEY_CORPORATES),
            self::TTL,
            function () use ($propertyid) {
                return DB::table('subgroup')
                    ->where('propertyid', $propertyid)
                    ->where('comp_type', 'Corporate')
                    ->orderBy('name', 'ASC')
                    ->get();
            }
        );
    }

    /**
     * Combined corporate + travel-agent list (used on folio/room screens).
     */
    public static function companiesAndAgents(string $propertyid)
    {
        return Cache::remember(
            self::key($propertyid, self::KEY_COMPANIES_AGENTS),
            self::TTL,
            function () use ($propertyid) {
                return DB::table('subgroup')
                    ->where('propertyid', $propertyid)
                    ->whereIn('comp_type', ['Corporate', 'Travel Agency'])
                    ->orderBy('name', 'ASC')
                    ->get();
            }
        );
    }

    /**
     * Room list — room_mast for the property, ordered by name.
     */
    public static function rooms(string $propertyid)
    {
        return Cache::remember(
            self::key($propertyid, self::KEY_ROOMS),
            self::TTL,
            function () use ($propertyid) {
                return DB::table('room_mast')
                    ->where('propertyid', $propertyid)
                    ->orderBy('name', 'ASC')
                    ->get();
            }
        );
    }

    /**
     * FOM revenue codes — revmast field_type='C' for the FOM desk.
     */
    public static function fomCharges(string $propertyid)
    {
        return Cache::remember(
            self::key($propertyid, self::KEY_FOM_CHARGES),
            self::TTL,
            function () use ($propertyid) {
                return DB::table('revmast')
                    ->where('propertyid', $propertyid)
                    ->where('field_type', 'C')
                    ->where('Desk_code', 'FOM' . $propertyid)
                    ->orderBy('name', 'ASC')
                    ->get();
            }
        );
    }

    /**
     * Flush every master-data key for a property.
     * Call after any write to subgroup / room_mast / revmast.
     */
    public static function flush(string $propertyid): void
    {
        foreach ([
            self::KEY_TRAVEL_AGENTS,
            self::KEY_CORPORATES,
            self::KEY_COMPANIES_AGENTS,
            self::KEY_ROOMS,
            self::KEY_FOM_CHARGES,
        ] as $type) {
            Cache::forget(self::key($propertyid, $type));
        }
    }

    /**
     * Per-date room availability (PERF-03 follow-up).
     *
     * Keyed by (property, variant, room category, checkin, checkout) under the
     * property's current availability version — flushing availability bumps
     * the version, which makes every previously cached availability key for
     * that property unreachable in one cheap cache write (no key enumeration).
     *
     * The closure is the controller's exact query (walkin vs reservation
     * variants differ slightly); the helper only owns caching.
     */
    public static function availableRooms(string $propertyid, string $variant, string $roomcat, string $checkin, string $checkout, \Closure $builder)
    {
        $version = (int) Cache::get(self::key($propertyid, self::KEY_AVAIL_VERSION), 0);

        return Cache::remember(
            self::key($propertyid, 'avail.' . $version . '.' . $variant . '.' . $roomcat . '.' . $checkin . '.' . $checkout),
            self::AVAIL_TTL,
            $builder
        );
    }

    /**
     * Invalidate ALL cached availability for a property.
     * Call after any write to roomocc / grpbookingdetails / roomblockout
     * (booking create/update/delete, check-in/out, room move, blockout CRUD).
     */
    public static function flushAvailability(string $propertyid): void
    {
        $version = (int) Cache::get(self::key($propertyid, self::KEY_AVAIL_VERSION), 0);
        Cache::put(self::key($propertyid, self::KEY_AVAIL_VERSION), $version + 1);
    }
}
