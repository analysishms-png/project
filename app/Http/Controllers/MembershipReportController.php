<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MembershipReportController extends Controller
{
    protected $propertyid;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->propertyid = session('propertyid') ?? Auth::user()->propertyid ?? 0;
            return $next($request);
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // MEM LED — Member Ledger (transactions against membership)
    // ═══════════════════════════════════════════════════════════════
    public function memberLedger(Request $request)
    {
        $fromdate = $request->input('fromdate', date('Y-m-d'));
        $todate = $request->input('todate', date('Y-m-d'));
        $memberCode = $request->input('member_code', '');

        $members = DB::table('subgroup')
            ->where('propertyid', $this->propertyid)
            ->where('mcode', 'Y')
            ->orderBy('name')
            ->get();

        $ledger = collect();
        if ($memberCode) {
            $ledger = DB::table('suntran as S')
                ->leftJoin('subgroup as SG', 'SG.sub_code', '=', 'S.accode')
                ->leftJoin('revmast as RM', 'RM.rev_code', '=', 'S.paycode')
                ->where('S.propertyid', $this->propertyid)
                ->where('S.accode', $memberCode)
                ->whereBetween('S.vdate', [$fromdate, $todate])
                ->select('S.*', 'SG.name as member_name', 'RM.name as paymode')
                ->orderBy('S.vdate')
                ->get();
        }

        $balance = $ledger->sum('dramt') - $ledger->sum('cramt');

        return view('property.membership.memberledger', compact('members', 'ledger', 'balance', 'fromdate', 'todate', 'memberCode'));
    }

    // ═══════════════════════════════════════════════════════════════
    // MEM SALES REGISTER — Member-wise sales
    // ═══════════════════════════════════════════════════════════════
    public function memberSalesRegister(Request $request)
    {
        $fromdate = $request->input('fromdate', date('Y-m-01'));
        $todate = $request->input('todate', date('Y-m-d'));

        $sales = DB::table('suntran as S')
            ->leftJoin('subgroup as SG', 'SG.sub_code', '=', 'S.accode')
            ->where('S.propertyid', $this->propertyid)
            ->where('SG.mcode', 'Y')
            ->whereBetween('S.vdate', [$fromdate, $todate])
            ->select(
                'S.accode',
                DB::raw('MAX(SG.name) as member_name'),
                DB::raw('COUNT(*) as txn_count'),
                DB::raw('SUM(S.dramt) as total_debit'),
                DB::raw('SUM(S.cramt) as total_credit'),
                DB::raw('SUM(S.dramt - S.cramt) as net_amount')
            )
            ->groupBy('S.accode')
            ->orderByDesc('net_amount')
            ->get();

        return view('property.membership.salesregister', compact('sales', 'fromdate', 'todate'));
    }

    // ═══════════════════════════════════════════════════════════════
    // MEM VISIT DETAIL — Member visit history
    // ═══════════════════════════════════════════════════════════════
    public function memberVisitDetail(Request $request)
    {
        $fromdate = $request->input('fromdate', date('Y-m-01'));
        $todate = $request->input('todate', date('Y-m-d'));
        $memberCode = $request->input('member_code', '');

        $query = DB::table('memsuntran as MS')
            ->leftJoin('subgroup as SG', 'SG.sub_code', '=', 'MS.memcode')
            ->where('MS.propertyid', $this->propertyid)
            ->whereBetween('MS.vdate', [$fromdate, $todate]);

        if ($memberCode) {
            $query->where('MS.memcode', $memberCode);
        }

        $visits = $query->select(
            'MS.*', 'SG.name as member_name'
        )->orderBy('MS.vdate', 'desc')->limit(200)->get();

        $members = DB::table('subgroup')
            ->where('propertyid', $this->propertyid)
            ->where('mcode', 'Y')
            ->orderBy('name')
            ->get();

        return view('property.membership.visitdetail', compact('visits', 'members', 'fromdate', 'todate', 'memberCode'));
    }

    // ═══════════════════════════════════════════════════════════════
    // MEM BIRTH/ANNIVERSARY — Upcoming birthdays and anniversaries
    // ═══════════════════════════════════════════════════════════════
    public function memberBirthAnniversary(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        // Get members with DOB in selected month
        $birthdays = DB::table('memberfamily as MF')
            ->leftJoin('subgroup as SG', 'SG.sub_code', '=', 'MF.subcode')
            ->where('MF.propertyid', $this->propertyid)
            ->whereRaw("MONTH(MF.dob) = ?", [$month])
            ->where('MF.dob', '!=', '0000-00-00')
            ->select('MF.*', 'SG.name as member_name')
            ->orderByRaw("DAY(MF.dob)")
            ->get();

        // Get members with wedding date in selected month
        $anniversaries = DB::table('memberfamily as MF')
            ->leftJoin('subgroup as SG', 'SG.sub_code', '=', 'MF.subcode')
            ->where('MF.propertyid', $this->propertyid)
            ->whereRaw("MONTH(MF.weddate) = ?", [$month])
            ->where('MF.weddate', '!=', '0000-00-00')
            ->select('MF.*', 'SG.name as member_name')
            ->orderByRaw("DAY(MF.weddate)")
            ->get();

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        return view('property.membership.birthanniversary', compact('birthdays', 'anniversaries', 'months', 'month', 'year'));
    }

    // ═══════════════════════════════════════════════════════════════
    // MEM MAILING LABELS — Print-ready member mailing labels
    // ═══════════════════════════════════════════════════════════════
    public function memberMailingLabels(Request $request)
    {
        $category = $request->input('category', '');

        $query = DB::table('subgroup as SG')
            ->leftJoin('member_categories as MC', 'MC.code', '=', 'SG.mcat')
            ->where('SG.propertyid', $this->propertyid)
            ->where('SG.mcode', 'Y')
            ->where('SG.status', 'Y');

        if ($category) {
            $query->where('SG.mcat', $category);
        }

        $members = $query->select('SG.*', 'MC.title as category_name')
            ->orderBy('SG.name')
            ->get();

        $categories = DB::table('member_categories')
            ->where('propertyid', $this->propertyid)
            ->orderBy('title')
            ->get();

        return view('property.membership.mailinglabels', compact('members', 'categories', 'category'));
    }

    // ═══════════════════════════════════════════════════════════════
    // MEM TAX REPORT — GST/Tax collected from member transactions
    // ═══════════════════════════════════════════════════════════════
    public function memberTaxReport(Request $request)
    {
        $fromdate = $request->input('fromdate', date('Y-m-01'));
        $todate = $request->input('todate', date('Y-m-d'));

        $taxData = DB::table('membill as MB')
            ->leftJoin('subgroup as SG', 'SG.sub_code', '=', 'MB.memcode')
            ->where('MB.propertyid', $this->propertyid)
            ->whereBetween('MB.vdate', [$fromdate, $todate])
            ->select(
                'MB.memcode',
                DB::raw('MAX(SG.name) as member_name'),
                DB::raw('SUM(MB.netamt) as net_amount'),
                DB::raw('SUM(MB.taxamt) as tax_amount'),
                DB::raw('SUM(MB.discamt) as disc_amount'),
                DB::raw('COUNT(*) as bill_count')
            )
            ->groupBy('MB.memcode')
            ->orderByDesc('tax_amount')
            ->get();

        return view('property.membership.taxreport', compact('taxData', 'fromdate', 'todate'));
    }

    // ═══════════════════════════════════════════════════════════════
    // MEM BILL MISSING REPORT — Bills expected but not generated
    // ═══════════════════════════════════════════════════════════════
    public function memberBillMissingReport(Request $request)
    {
        $fromdate = $request->input('fromdate', date('Y-m-01'));
        $todate = $request->input('todate', date('Y-m-d'));

        // Members with facility usage but no matching bill
        $missingBills = DB::table('subgroup as SG')
            ->leftJoin('memberfacilitymast as MF', function ($j) {
                $j->on('MF.memcode', '=', 'SG.sub_code')
                  ->where('MF.propertyid', $this->propertyid);
            })
            ->leftJoin('membill as MB', function ($j) use ($fromdate, $todate) {
                $j->on('MB.memcode', '=', 'SG.sub_code')
                  ->where('MB.propertyid', $this->propertyid)
                  ->whereBetween('MB.vdate', [$fromdate, $todate]);
            })
            ->where('SG.propertyid', $this->propertyid)
            ->where('SG.mcode', 'Y')
            ->whereNull('MB.sn')
            ->select('SG.*', 'MF.facility_code')
            ->orderBy('SG.name')
            ->get();

        return view('property.membership.billmissingreport', compact('missingBills', 'fromdate', 'todate'));
    }
}
