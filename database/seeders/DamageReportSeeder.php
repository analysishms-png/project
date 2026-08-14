<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DamageReportSeeder extends Seeder
{
    /**
     * Seed realistic Damage Report records (hkdamage) for property 103.
     *
     * Reusable via:  php artisan db:seed --class=DamageReportSeeder
     */
    public function run(): void
    {
        $propertyId = 103;

        // Idempotency guard — don't duplicate if already seeded for this property
        $existing = DB::table('hkdamage')->where('propertyid', $propertyId)->count();
        if ($existing >= 25) {
            $this->command->info("DamageReportSeeder: property {$propertyId} already has {$existing} records — skipping.");

            return;
        }

        // Real room numbers from this property
        $rooms = DB::table('room_mast')
            ->where('propertyid', $propertyId)
            ->where('type', 'RO')
            ->where('inclcount', 'Y')
            ->pluck('rcode')
            ->all();

        if (empty($rooms)) {
            $this->command->error("DamageReportSeeder: no rooms found for property {$propertyId}.");

            return;
        }

        // item => description pairs per damage type
        $damageTypes = [
            'Furniture' => [
                'Bed headboard scratched'      => 'Deep scratch marks on the wooden bed headboard.',
                'Wardrobe door hinge broken'   => 'Wardrobe door hinge is loose and off its track.',
                'Study table leg cracked'      => 'Crack on the rear leg of the study table.',
                'Chair armrest broken'         => 'Right armrest of the lounge chair is broken.',
                'Bedside drawer jammed'        => 'Bedside table drawer is stuck and cannot be opened.',
            ],
            'Electronic' => [
                'TV remote not working'        => 'TV remote buttons are unresponsive, needs replacement.',
                'AC remote missing'            => 'Air conditioner remote is missing from the room.',
                'Reading lamp not working'     => 'Bedside reading lamp does not switch on.',
                'Wall socket loose'            => 'Wall power socket is loose, needs immediate check.',
                'Mini bar fridge not cooling'  => 'In-room mini bar fridge is not cooling properly.',
            ],
            'Plumbing' => [
                'Washbasin tap leaking'        => 'Washbasin tap is dripping continuously.',
                'Shower head clogged'          => 'Shower head has low pressure due to clogging.',
                'Toilet flush not working'     => 'Toilet flush button is stuck and not flushing.',
                'Toilet seat cracked'          => 'Crack on the toilet seat cover.',
                'Under-sink pipe leaking'      => 'Water leaking from pipe under the washbasin.',
            ],
            'Bathroom' => [
                'Bathroom mirror cracked'      => 'Corner crack on the bathroom mirror.',
                'Shower glass seal broken'     => 'Rubber seal of the shower glass door is torn.',
                'Towel rack fallen off'        => 'Towel rack has come off the wall.',
                'Bathtub drain slow'           => 'Bathtub drain is slow, water accumulates during use.',
            ],
            'Safety' => [
                'Smoke detector beeping'       => 'Smoke detector is beeping intermittently.',
                'Door chain broken'            => 'Room door safety chain is broken.',
                'Window lock broken'           => 'Window lock does not engage properly.',
                'Emergency light not working'  => 'Emergency exit light in the room is not working.',
            ],
        ];

        // Weighted status mix: ~47% Pending, ~27% In Progress, ~27% Resolved
        $statuses = ['Pending', 'Pending', 'Pending', 'Pending', 'Pending', 'Pending', 'Pending',
                     'In Progress', 'In Progress', 'In Progress', 'In Progress',
                     'Resolved', 'Resolved', 'Resolved', 'Resolved'];

        $uNames = ['Ram Kumar', 'Sita Devi', 'Mohan Lal', 'Asha Rani', 'Vikram Singh', 'Sunita Sharma'];

        // Continue damageid numbering from any existing records
        $lastId = DB::table('hkdamage')->where('propertyid', $propertyId)->max('damageid') ?? 0;

        $total   = 30;
        $rows    = [];
        $types   = array_keys($damageTypes);
        $timeNow = date('Y-m-d H:i:s');

        for ($i = 0; $i < $total; $i++) {
            $type = $types[array_rand($types)];
            $pair = $damageTypes[$type];

            // pick a random item => description pair
            $item        = array_rand($pair);
            $description = $pair[$item];

            // date within the last 30 days
            $daysAgo = rand(0, 29);
            $date    = date('Y-m-d', strtotime("-{$daysAgo} days"));

            // entry datetime = same day, working hours
            $entdt = date('Y-m-d H:i:s', strtotime($date . ' ' . sprintf('%02d:%02d:00', rand(8, 20), rand(0, 59))));

            $lastId++;

            $rows[] = [
                'propertyid'  => $propertyId,
                'damageid'    => $lastId,
                'roomno'      => $rooms[array_rand($rooms)],
                'date'        => $date,
                'damagetype'  => $type,
                'item'        => $item,
                'description' => $description,
                'status'      => $statuses[array_rand($statuses)],
                'u_name'      => $uNames[array_rand($uNames)],
                'u_entdt'     => $entdt,
                'u_ae'        => 'a',
            ];
        }

        DB::table('hkdamage')->insert($rows);

        $this->command->info("DamageReportSeeder: seeded {$total} damage report records for property {$propertyId}.");
    }
}
