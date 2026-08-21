<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TelCallType;
use App\Models\TelCallCode;
use App\Models\CashCardMaster;
use App\Models\CashCardTrans;

class TelephoneController extends Controller
{
    public function __construct()
    {
        $this->propertyid = session('propertyid') ?? 103;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CALL TYPE MASTER
    // ═══════════════════════════════════════════════════════════════════════

    public function calltypelist()
    {
        $data = TelCallType::where('propertyid', $this->propertyid)->orderBy('code')->get();
        return view('property.telephone.calltypelist', compact('data'));
    }

    public function calltypestore(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10',
            'calltype' => 'required|string|max:50',
        ]);

        TelCallType::updateOrCreate(
            ['propertyid' => $this->propertyid, 'code' => $request->code],
            [
                'calltype' => $request->calltype,
                'u_name' => auth()->user()->u_name ?? 'sa',
                'u_entdt' => now(),
                'u_ae' => 'a',
                'site_code' => $this->propertyid,
            ]
        );

        return redirect()->route('calltypelist')->with('success', 'Call Type saved successfully!');
    }

    public function calltypedelete(Request $request)
    {
        TelCallType::where('propertyid', $this->propertyid)
            ->where('code', $request->code)
            ->delete();

        return redirect()->route('calltypelist')->with('success', 'Call Type deleted!');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CALL CODE MASTER
    // ═══════════════════════════════════════════════════════════════════════

    public function callcodelist()
    {
        $data = TelCallCode::where('propertyid', $this->propertyid)
            ->leftJoin('telcalltype', 'telcallcode.calltypecode', '=', 'telcalltype.code')
            ->select('telcallcode.*', 'telcalltype.calltype')
            ->orderBy('telcallcode.stdcode')
            ->get();

        $calltypes = TelCallType::where('propertyid', $this->propertyid)->orderBy('code')->get();

        return view('property.telephone.callcodelist', compact('data', 'calltypes'));
    }

    public function callcodestore(Request $request)
    {
        $request->validate([
            'stdcode' => 'required|string|max:20',
            'calltypecode' => 'required|string|max:10',
            'description' => 'nullable|string|max:100',
            'pulseinsec' => 'nullable|integer|min:0',
        ]);

        TelCallCode::updateOrCreate(
            ['propertyid' => $this->propertyid, 'stdcode' => $request->stdcode],
            [
                'calltypecode' => $request->calltypecode,
                'description' => $request->description ?? '',
                'pulseinsec' => $request->pulseinsec ?? 0,
                'u_name' => auth()->user()->u_name ?? 'sa',
                'u_entdt' => now(),
                'u_ae' => 'a',
                'site_code' => $this->propertyid,
            ]
        );

        return redirect()->route('callcodelist')->with('success', 'Call Code saved successfully!');
    }

    public function callcodedelete(Request $request)
    {
        TelCallCode::where('propertyid', $this->propertyid)
            ->where('stdcode', $request->stdcode)
            ->delete();

        return redirect()->route('callcodelist')->with('success', 'Call Code deleted!');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CASH CARD REGISTRATION
    // ═══════════════════════════════════════════════════════════════════════

    public function cashcardlist()
    {
        $data = CashCardMaster::where('propertyid', $this->propertyid)->orderByDesc('id')->get();
        return view('property.cashcard.cashcardlist', compact('data'));
    }

    public function cashcardregister()
    {
        $rooms = DB::table('roomocc')
            ->where('propertyid', $this->propertyid)
            ->where('activeYN', 'Y')
            ->pluck('roomno')
            ->toArray();

        return view('property.cashcard.cashcardregister', compact('rooms'));
    }

    public function cashcardstore(Request $request)
    {
        $request->validate([
            'cardno' => 'required|string|max:20',
            'guestname' => 'required|string|max:100',
            'roomno' => 'nullable|string|max:10',
            'balance' => 'required|numeric|min:0',
            'security' => 'nullable|numeric|min:0',
        ]);

        // Check duplicate card
        $exists = CashCardMaster::where('propertyid', $this->propertyid)
            ->where('cardno', $request->cardno)
            ->first();

        if ($exists) {
            return redirect()->back()->with('error', 'Card number already exists!');
        }

        DB::beginTransaction();
        try {
            // Create master record
            CashCardMaster::create([
                'propertyid' => $this->propertyid,
                'cardno' => $request->cardno,
                'guestname' => strtoupper($request->guestname),
                'roomno' => $request->roomno ?? '',
                'foliono' => '',
                'issuedate' => date('Y-m-d'),
                'expirydate' => date('Y-m-d', strtotime('+1 year')),
                'balance' => $request->balance,
                'security' => $request->security ?? 0,
                'status' => 'ACTIVE',
                'u_name' => auth()->user()->u_name ?? 'sa',
                'site_code' => $this->propertyid,
            ]);

            // Create initial transaction
            CashCardTrans::create([
                'propertyid' => $this->propertyid,
                'cardno' => $request->cardno,
                'vtype' => 'ISSUE',
                'vdate' => date('Y-m-d'),
                'amount' => $request->balance,
                'balance' => $request->balance,
                'paymode' => 'CASH',
                'roomno' => $request->roomno ?? '',
                'remark' => 'Card registered with initial balance',
                'u_name' => auth()->user()->u_name ?? 'sa',
                'site_code' => $this->propertyid,
            ]);

            DB::commit();
            return redirect()->route('cashcardlist')->with('success', 'Cash Card registered successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CASH CARD RECHARGE
    // ═══════════════════════════════════════════════════════════════════════

    public function cashcardrecharge()
    {
        $cards = CashCardMaster::where('propertyid', $this->propertyid)
            ->where('status', 'ACTIVE')
            ->orderBy('cardno')
            ->get();

        return view('property.cashcard.cashcardrecharge', compact('cards'));
    }

    public function cashcardrechargestore(Request $request)
    {
        $request->validate([
            'cardno' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'paymode' => 'nullable|string|max:20',
        ]);

        $card = CashCardMaster::where('propertyid', $this->propertyid)
            ->where('cardno', $request->cardno)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$card) {
            return redirect()->back()->with('error', 'Card not found or not active!');
        }

        DB::beginTransaction();
        try {
            $newBalance = $card->balance + $request->amount;

            // Update card balance
            $card->update(['balance' => $newBalance]);

            // Create transaction
            CashCardTrans::create([
                'propertyid' => $this->propertyid,
                'cardno' => $request->cardno,
                'vtype' => 'RECHARGE',
                'vdate' => date('Y-m-d'),
                'amount' => $request->amount,
                'balance' => $newBalance,
                'paymode' => $request->paymode ?? 'CASH',
                'remark' => 'Card recharged',
                'u_name' => auth()->user()->u_name ?? 'sa',
                'site_code' => $this->propertyid,
            ]);

            DB::commit();
            return redirect()->route('cashcardlist')->with('success', 'Card recharged successfully! New Balance: ₹' . number_format($newBalance, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CASH CARD REFUND
    // ═══════════════════════════════════════════════════════════════════════

    public function cashcardrefund()
    {
        $cards = CashCardMaster::where('propertyid', $this->propertyid)
            ->where('status', 'ACTIVE')
            ->where('balance', '>', 0)
            ->orderBy('cardno')
            ->get();

        return view('property.cashcard.cashcardrefund', compact('cards'));
    }

    public function cashcardrefundstore(Request $request)
    {
        $request->validate([
            'cardno' => 'required|string',
            'remark' => 'nullable|string|max:200',
        ]);

        $card = CashCardMaster::where('propertyid', $this->propertyid)
            ->where('cardno', $request->cardno)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$card) {
            return redirect()->back()->with('error', 'Card not found or not active!');
        }

        if ($card->balance <= 0) {
            return redirect()->back()->with('error', 'Card has no balance to refund!');
        }

        DB::beginTransaction();
        try {
            $refundAmount = $card->balance;

            // Update card status
            $card->update([
                'balance' => 0,
                'status' => 'REFUNDED',
            ]);

            // Create refund transaction
            CashCardTrans::create([
                'propertyid' => $this->propertyid,
                'cardno' => $request->cardno,
                'vtype' => 'REFUND',
                'vdate' => date('Y-m-d'),
                'amount' => -$refundAmount,
                'balance' => 0,
                'paymode' => 'CASH',
                'remark' => $request->remark ?? 'Full refund processed',
                'u_name' => auth()->user()->u_name ?? 'sa',
                'site_code' => $this->propertyid,
            ]);

            DB::commit();
            return redirect()->route('cashcardlist')->with('success', 'Refund of ₹' . number_format($refundAmount, 2) . ' processed!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CASH CARD TRANSACTION HISTORY
    // ═══════════════════════════════════════════════════════════════════════

    public function cashcardhistory(Request $request)
    {
        $cardno = $request->get('cardno');
        $data = collect();
        $card = null;

        if ($cardno) {
            $card = CashCardMaster::where('propertyid', $this->propertyid)
                ->where('cardno', $cardno)
                ->first();

            $data = CashCardTrans::where('propertyid', $this->propertyid)
                ->where('cardno', $cardno)
                ->orderBy('vdate')
                ->get();
        }

        $allCards = CashCardMaster::where('propertyid', $this->propertyid)->orderBy('cardno')->get();

        return view('property.cashcard.cashcardhistory', compact('data', 'card', 'allCards', 'cardno'));
    }
}
