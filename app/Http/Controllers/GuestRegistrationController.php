<?php

namespace App\Http\Controllers;

use App\Models\Grpbookingdetail;
use App\Models\GuestProf;
use App\Models\RoomOcc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GuestRegistrationController extends Controller
{
    /**
     * Show the digital registration form for a reservation.
     * Public access — no auth required.
     * URL: /guest-registration/{reservation_no}
     */
    public function show($reservationNo)
    {
        $propertyid = session('propertyid') ?? request()->cookie('propertyid') ?? 103;

        // Find the reservation
        $booking = Grpbookingdetail::where('propertyid', $propertyid)
            ->where('DocId', $reservationNo)
            ->first();

        if (!$booking) {
            return view('property.guest-registration.not-found', [
                'reservationNo' => $reservationNo
            ]);
        }

        // Check if guest profile already exists
        $existingGuest = null;
        if ($booking->guestprof) {
            $existingGuest = GuestProf::where('propertyid', $propertyid)
                ->where('sno', $booking->guestprof)
                ->first();
        }

        return view('property.guest-registration.form', compact('booking', 'existingGuest', 'reservationNo'));
    }

    /**
     * Submit the digital registration form.
     * Creates/updates guest profile and links to reservation.
     */
    public function submit(Request $request, $reservationNo)
    {
        $propertyid = session('propertyid') ?? request()->cookie('propertyid') ?? 103;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'required|string|max:20',
            'gender' => 'nullable|string|max:10',
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'pincode' => 'nullable|string|max:10',
            'nationality' => 'nullable|string|max:50',
            'id_type' => 'nullable|string|max:50',
            'id_number' => 'nullable|string|max:50',
            'id_expiry' => 'nullable|date',
            'company_name' => 'nullable|string|max:255',
            'special_requests' => 'nullable|string|max:500',
            'arrival_time' => 'nullable|string|max:10',
            'purpose_of_visit' => 'nullable|string|max:100',
        ]);

        $booking = Grpbookingdetail::where('propertyid', $propertyid)
            ->where('DocId', $reservationNo)
            ->first();

        if (!$booking) {
            return back()->with('error', 'Reservation not found');
        }

        try {
            DB::beginTransaction();

            // Check if guest profile already exists
            $guest = null;
            if ($booking->guestprof) {
                $guest = GuestProf::where('propertyid', $propertyid)
                    ->where('sno', $booking->guestprof)
                    ->first();
            }

            if ($guest) {
                // Update existing profile with new data
                $guest->update([
                    'name' => $request->name,
                    'email_id' => $request->email ?? $guest->email_id,
                    'mobile_no' => substr($request->mobile, -10),
                    'gender' => $request->gender ?? $guest->gender,
                    'add1' => $request->address ?? $guest->add1,
                    'city' => $request->city ?? $guest->city,
                    'city_name' => $request->city ?? $guest->city_name,
                    'state_name' => $request->state ?? $guest->state_name,
                    'country_name' => $request->country ?? $guest->country_name,
                    'zip_code' => $request->pincode ?? $guest->zip_code,
                    'nationality' => $request->nationality ?? $guest->nationality,
                    'idproof_type' => $request->id_type ?? $guest->idproof_type,
                    'idproof_no' => $request->id_number ?? $guest->idproof_no,
                    'expiryDate' => $request->id_expiry ?? $guest->expiryDate,
                    'comments1' => $request->special_requests ?? $guest->comments1,
                    'spl_instr' => $request->special_requests ?? $guest->spl_instr,
                    'u_ae' => 'e',
                    'u_updatedt' => now(),
                ]);
            } else {
                // Create new guest profile
                $guest = GuestProf::create([
                    'propertyid' => $propertyid,
                    'name' => $request->name,
                    'email_id' => $request->email,
                    'mobile_no' => substr($request->mobile, -10),
                    'gender' => $request->gender,
                    'add1' => $request->address,
                    'city' => $request->city,
                    'city_name' => $request->city,
                    'state_name' => $request->state,
                    'country_name' => $request->country,
                    'zip_code' => $request->pincode,
                    'nationality' => $request->nationality,
                    'idproof_type' => $request->id_type,
                    'idproof_no' => $request->id_number,
                    'expiryDate' => $request->id_expiry,
                    'comments1' => $request->special_requests,
                    'spl_instr' => $request->special_requests,
                    'bill_to' => 'guest',
                    'guestcode' => 'WLK',
                    'u_name' => 'guest_portal',
                    'u_entdt' => now(),
                    'u_ae' => 'a',
                ]);

                // Link guest to reservation
                $booking->update(['guestprof' => $guest->sn]);
            }

            // Update special requests in booking
            if ($request->special_requests) {
                $booking->update([
                    'spl_instr' => $request->special_requests,
                ]);
            }

            DB::commit();

            return view('property.guest-registration.success', [
                'reservationNo' => $reservationNo,
                'guestName' => $request->name,
                'roomType' => $booking->RoomType ?? '',
                'arrivalDate' => $booking->ArrDate ?? '',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }
}
