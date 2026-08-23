<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
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
    // PAYROLL PARAMETER — Configure PF, ESI, working days, etc.
    // ═══════════════════════════════════════════════════════════════
    public function payrollParameter()
    {
        $param = DB::table('enviro_payroll')
            ->where('propertyid', $this->propertyid)
            ->first();

        return view('property.hrpayroll.payrollparameter', compact('param'));
    }

    public function savePayrollParameter(Request $request)
    {
        $request->validate([
            'pflimit' => 'nullable|numeric',
            'pfemployee' => 'nullable|numeric',
            'pfemployer' => 'nullable|numeric',
            'esilimit' => 'nullable|numeric',
            'esiemployee' => 'nullable|numeric',
            'gworkingdaysInamonth' => 'nullable|numeric',
            'gdayssalary' => 'nullable|numeric',
        ]);

        $data = [
            'pflimit' => $request->input('pflimit', 0),
            'pfemployee' => $request->input('pfemployee', 0),
            'pfemployer' => $request->input('pfemployer', 0),
            'esilimit' => $request->input('esilimit', 0),
            'esiemployee' => $request->input('esiemployee', 0),
            'esebasic' => $request->input('esebasic', 0),
            'esida' => $request->input('esida', 0),
            'esihra' => $request->input('esihra', 0),
            'esiconvey' => $request->input('esiconvey', 0),
            'esiother' => $request->input('esiother', 0),
            'esilta' => $request->input('esilta', 0),
            'gworkingdaysInamonth' => $request->input('gworkingdaysInamonth', 30),
            'gdayssalary' => $request->input('gdayssalary', 30),
            'gmonthconsidered' => $request->input('gmonthconsidered', 12),
            'salaryac' => $request->input('salaryac', ''),
            'loanac' => $request->input('loanac', ''),
            'advanceac' => $request->input('advanceac', ''),
            'u_name' => Auth::user()->name ?? 'system',
            'u_updatedt' => now(),
        ];

        $exists = DB::table('enviro_payroll')->where('propertyid', $this->propertyid)->first();
        if ($exists) {
            DB::table('enviro_payroll')->where('propertyid', $this->propertyid)->update($data);
        } else {
            $data['propertyid'] = $this->propertyid;
            $data['u_entdt'] = now();
            DB::table('enviro_payroll')->insert($data);
        }

        return redirect()->route('hr.payroll-parameter')->with('success', 'Payroll parameters saved');
    }

    // ═══════════════════════════════════════════════════════════════
    // SALARY CREATION — Generate monthly salary for employees
    // ═══════════════════════════════════════════════════════════════
    public function salaryCreation()
    {
        $departments = DB::table('depart')
            ->where('propertyid', $this->propertyid)
            ->orderBy('name')
            ->get();

        return view('property.hrpayroll.salarycreation', compact('departments'));
    }

    public function getEmployees(Request $request)
    {
        $query = DB::table('employee')
            ->where('propertyid', $this->propertyid)
            ->where('status', 'Y');

        if ($request->has('department') && $request->department) {
            $query->where('department', $request->department);
        }

        $employees = $query->orderBy('name')->get(['sno as id', 'name', 'empcode', 'department']);
        return response()->json($employees);
    }

    public function salaryCreationStore(Request $request)
    {
        $request->validate([
            'month' => 'required',
            'salarydate' => 'required|date',
            'department' => 'required',
            'employees' => 'required|array',
        ]);

        $prpid = $this->propertyid;
        $mthYear = $request->month; // Format: YYYY-MM
        $salaryDate = $request->salarydate;
        $employees = $request->employees;

        // Remove "select_all" if present
        if (in_array('select_all', $employees)) {
            $employees = DB::table('employee')
                ->where('propertyid', $prpid)
                ->where('department', $request->department)
                ->where('status', 'Y')
                ->pluck('empcode')
                ->toArray();
        }

        // Get payroll parameters
        $param = DB::table('enviro_payroll')->where('propertyid', $prpid)->first();
        $workingDays = $param->gworkingdaysInamonth ?? 30;
        $pfLimit = $param->pflimit ?? 15000;
        $pfEmployeePct = $param->pfemployee ?? 12;
        $esiLimit = $param->esilimit ?? 21000;
        $esiEmployeePct = $param->esiemployee ?? 0.75;

        $created = 0;

        foreach ($employees as $empCode) {
            // Skip if salary already exists for this month
            $exists = DB::table('salary')
                ->where('propertyid', $prpid)
                ->where('emp_code', $empCode)
                ->where('mth_year', $mthYear)
                ->first();

            if ($exists) continue;

            // Get employee details
            $emp = DB::table('employee')
                ->where('propertyid', $prpid)
                ->where('empcode', $empCode)
                ->first();

            if (!$emp) continue;

            // Get attendance for the month
            $attend = DB::table('attendance')
                ->where('propertyid', $prpid)
                ->where('empcode', $empCode)
                ->whereBetween('vdate', [
                    $mthYear . '-01',
                    date('Y-m-t', strtotime($mthYear . '-01'))
                ])
                ->first();

            // Calculate working days from attendance
            $presentDays = $workingDays;
            if ($attend) {
                $presentDays = $attend->firstshift + $attend->secondshift;
                if ($presentDays == 0) $presentDays = $workingDays;
            }

            $basic = $emp->basic ?? 0;
            $da = $emp->da ?? 0;
            $hra = $emp->hra ?? 0;
            $conveyance = $emp->conveyance ?? 0;
            $medical = $emp->medical ?? 0;
            $lta = $emp->lta ?? 0;
            $otherAllow = $emp->other_allow ?? 0;

            // Calculate prorated salary
            $proration = $presentDays / $workingDays;
            $grossBasic = round($basic * $proration, 2);
            $grossDA = round($da * $proration, 2);
            $grossHRA = round($hra * $proration, 2);

            // PF calculation
            $pfBase = min($grossBasic + $grossDA, $pfLimit);
            $pf = round($pfBase * $pfEmployeePct / 100, 2);

            // ESI calculation
            $esiBase = $grossBasic + $grossDA + $grossHRA + $conveyance + $medical;
            $esi = $esiBase <= $esiLimit ? round($esiBase * $esiEmployeePct / 100, 2) : 0;

            // Get loan/advance balance
            $loanBal = DB::table('loan')
                ->where('propertyid', $prpid)
                ->where('empcode', $empCode)
                ->sum('amount') ?? 0;

            $advanceBal = 0; // Calculate from advance entries

            // Net salary
            $gross = $grossBasic + $grossDA + $grossHRA + $conveyance + $medical + $lta + $otherAllow;
            $deductions = $pf + $esi + $loanBal + $advanceBal;
            $netSalary = round($gross - $deductions, 2);

            DB::table('salary')->insert([
                'propertyid' => $prpid,
                'mth_year' => $mthYear,
                'emp_code' => $empCode,
                'work_day' => $presentDays,
                'Basic' => $grossBasic,
                'da' => $grossDA,
                'hra' => $grossHRA,
                'conveyance' => $conveyance,
                'medical' => $medical,
                'lta' => $lta,
                'other_allow' => $otherAllow,
                'pf' => $pf,
                'epf' => $pf, // Employer PF = Employee PF
                'esi' => $esi,
                'loan' => $loanBal,
                'advance' => $advanceBal,
                'net_salary' => $netSalary,
                'loan_bal' => $loanBal,
                'adv_bal' => $advanceBal,
                'emp_basic' => $basic,
                'department' => $request->department,
                'u_name' => Auth::user()->name ?? 'system',
                'u_entdt' => now(),
                'u_ae' => 'a',
            ]);

            $created++;
        }

        return response()->json([
            'success' => true,
            'message' => "Salary created for {$created} employees"
        ]);
    }

    public function salaryDeletion(Request $request)
    {
        $request->validate(['month' => 'required']);

        $deleted = DB::table('salary')
            ->where('propertyid', $this->propertyid)
            ->where('mth_year', $request->month)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Salary records deleted for {$deleted} employees"
        ]);
    }

    public function salaryList(Request $request)
    {
        $month = $request->input('month', date('Y-m'));
        $department = $request->input('department', '');

        $query = DB::table('salary as S')
            ->leftJoin('employee as E', function ($j) {
                $j->on('E.empcode', '=', 'S.emp_code')
                  ->where('E.propertyid', $this->propertyid);
            })
            ->where('S.propertyid', $this->propertyid)
            ->where('S.mth_year', $month)
            ->select('S.*', 'E.name as emp_name', 'E.designation as desig_name');

        if ($department) {
            $query->where('S.department', $department);
        }

        $salaries = $query->orderBy('E.name')->get();
        $departments = DB::table('depart')->where('propertyid', $this->propertyid)->orderBy('name')->get();

        return view('property.hrpayroll.salarylist', compact('salaries', 'departments', 'month', 'department'));
    }

    // ═══════════════════════════════════════════════════════════════
    // PAY SLIP — Generate individual pay slip
    // ═══════════════════════════════════════════════════════════════
    public function paySlip(Request $request)
    {
        $month = $request->input('month', date('Y-m'));
        $empCode = $request->input('emp_code', '');

        $employees = DB::table('employee')
            ->where('propertyid', $this->propertyid)
            ->where('status', 'Y')
            ->orderBy('name')
            ->get();

        $salary = null;
        if ($empCode) {
            $salary = DB::table('salary as S')
                ->leftJoin('employee as E', function ($j) {
                    $j->on('E.empcode', '=', 'S.emp_code')
                      ->where('E.propertyid', $this->propertyid);
                })
                ->where('S.propertyid', $this->propertyid)
                ->where('S.emp_code', $empCode)
                ->where('S.mth_year', $month)
                ->select('S.*', 'E.name as emp_name', 'E.designation as desig_name', 'E.department as dept_name', 'E.father_name')
                ->first();
        }

        $company = DB::table('company')->where('propertyid', $this->propertyid)->first();

        return view('property.hrpayroll.payslip', compact('employees', 'salary', 'company', 'month', 'empCode'));
    }

    // ═══════════════════════════════════════════════════════════════
    // PAYROLL REGISTER — Monthly payroll summary
    // ═══════════════════════════════════════════════════════════════
    public function payrollRegister(Request $request)
    {
        $month = $request->input('month', date('Y-m'));
        $department = $request->input('department', '');

        $query = DB::table('salary as S')
            ->leftJoin('employee as E', function ($j) {
                $j->on('E.empcode', '=', 'S.emp_code')
                  ->where('E.propertyid', $this->propertyid);
            })
            ->where('S.propertyid', $this->propertyid)
            ->where('S.mth_year', $month);

        if ($department) {
            $query->where('S.department', $department);
        }

        $registers = $query->select(
            'S.*', 'E.name as emp_name', 'E.designation as desig_name'
        )->orderBy('E.name')->get();

        // Totals
        $totals = [
            'basic' => $registers->sum('Basic'),
            'da' => $registers->sum('da'),
            'hra' => $registers->sum('hra'),
            'conveyance' => $registers->sum('conveyance'),
            'medical' => $registers->sum('medical'),
            'lta' => $registers->sum('lta'),
            'other_allow' => $registers->sum('other_allow'),
            'pf' => $registers->sum('pf'),
            'esi' => $registers->sum('esi'),
            'loan' => $registers->sum('loan'),
            'advance' => $registers->sum('advance'),
            'net_salary' => $registers->sum('net_salary'),
        ];

        $departments = DB::table('depart')->where('propertyid', $this->propertyid)->orderBy('name')->get();

        return view('property.hrpayroll.payrollregister', compact('registers', 'totals', 'departments', 'month', 'department'));
    }

    // ═══════════════════════════════════════════════════════════════
    // PF STATEMENT — PF contribution statement
    // ═══════════════════════════════════════════════════════════════
    public function pfStatement(Request $request)
    {
        $month = $request->input('month', date('Y-m'));

        $pfData = DB::table('salary as S')
            ->leftJoin('employee as E', function ($j) {
                $j->on('E.empcode', '=', 'S.emp_code')
                  ->where('E.propertyid', $this->propertyid);
            })
            ->where('S.propertyid', $this->propertyid)
            ->where('S.mth_year', $month)
            ->where('S.pf', '>', 0)
            ->select('S.*', 'E.name as emp_name', 'E.empcode')
            ->orderBy('E.name')
            ->get();

        $totals = [
            'employee_pf' => $pfData->sum('pf'),
            'employer_pf' => $pfData->sum('epf'),
            'total_pf' => $pfData->sum('pf') + $pfData->sum('epf'),
        ];

        return view('property.hrpayroll.pfstatement', compact('pfData', 'totals', 'month'));
    }

    // ═══════════════════════════════════════════════════════════════
    // GRATUITY REPORT
    // ═══════════════════════════════════════════════════════════════
    public function gratuityReport(Request $request)
    {
        $employees = DB::table('employee')
            ->where('propertyid', $this->propertyid)
            ->where('status', 'Y')
            ->select('sno', 'empcode', 'name', 'designation', 'department', 'dateofjoining', 'basic')
            ->orderBy('name')
            ->get()
            ->map(function ($emp) {
                $joiningDate = $emp->dateofjoining ? date('Y-m-d', strtotime($emp->dateofjoining)) : null;
                $yearsOfService = $joiningDate ? round((strtotime('now') - strtotime($joiningDate)) / (365.25 * 86400), 1) : 0;
                // Gratuity = (Basic * 15/26) * years of service (min 5 years)
                $gratuity = ($yearsOfService >= 5) ? round(($emp->basic * 15 / 26) * $yearsOfService, 2) : 0;

                return [
                    'empcode' => $emp->empcode,
                    'name' => $emp->name,
                    'designation' => $emp->designation,
                    'department' => $emp->department,
                    'dateofjoining' => $emp->dateofjoining,
                    'basic' => $emp->basic,
                    'years_of_service' => $yearsOfService,
                    'gratuity_amount' => $gratuity,
                    'eligible' => $yearsOfService >= 5 ? 'Yes' : 'No',
                ];
            });

        return view('property.hrpayroll.gratuityreport', compact('employees'));
    }
}
