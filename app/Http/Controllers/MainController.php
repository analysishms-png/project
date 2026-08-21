<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Companyreg;
use App\Models\EnviroGeneral;
use App\Models\Log;
use App\Models\MenuHelp;
use App\Models\UserModule;
use App\Models\UpdateLog; //created by ananya
use App\Models\User;
use Illuminate\Http\JsonResponse; //created by ananya
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Hash;

class MainController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!isset(Auth::user()->name)) {
                return redirect('/');
            }

            if (Auth::user()->propertyid != '10') {
                return redirect('/');
            }
            return $next($request);
        });
    }

    public function boot()
    {
        Paginator::useBootstrap();
    }
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function ExportTable()
    {
        echo '<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />';
        echo '<link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet" />';
        echo '<script src="https://code.jquery.com/jquery-3.5.1.js"></script>';
        echo '<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>';
        echo '<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>';
        echo '<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>';
        echo '<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>';
    }

    public function DownloadTable($tableName, $title, $columnsToExport, $columnToSearch)
    {
        $exportColumns = $columnsToExport;
        $exportColumnsJS = json_encode($exportColumns);
        $searchColumns = $columnToSearch;
        $searchColumnsJS = json_encode($searchColumns);
        echo "<script>$(document).ready(function() {
        let table = $('#$tableName').DataTable({
            dom: 'Bfrtip',
            pageLength: 15,
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Excel' + ' <i class=\"fa fa-file-excel-o\"></i>',
                    title: '$title',
                    filename: '$title',
                    exportOptions: {
                        columns: $exportColumnsJS
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: 'Csv' + ' <i class=\"fa-solid fa-file-csv\"></i>',
                    title: '$title',
                    filename: '$title',
                    exportOptions: {
                        columns: $exportColumnsJS
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Pdf' + ' <i class=\"fa fa-file-pdf-o\"></i>',
                    title: '$title',
                    filename: '$title',
                    exportOptions: {
                        columns: $exportColumnsJS
                    }
                },
                {
                    extend: 'print',
                    text: 'Print' + ' <i class=\"fa-solid fa-print\"></i>',
                    title: '$title',
                    filename: '$title',
                    exportOptions: {
                        columns: $exportColumnsJS
                    }
                }
            ],
            initComplete: function() {
                // Configure column-specific search inputs based on the specified columns
                let searchColumns = $searchColumnsJS;
                this.api().columns(searchColumns).every(function() {
                    let column = this;
                    let title = column.footer().textContent;
                    let input = document.createElement('input');
                    input.placeholder = title;
                    column.footer().replaceChildren(input);
                    input.addEventListener('keyup', () => {
                        if (column.search() !== input.value) {
                            column.search(input.value).draw();
                        }
                    });
                });
            }
            });
        });</script>";
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        if ($user) {
            $mail = $user->email;
            return view('admin.index');
        } else {
            return redirect()->route('login');
        }
    }

    public function openqrgenerate()
    {
        return view('admin.qrgenerate');
    }

    public function loadcompanylist(Request $request)
    {
        $Links = $this->ExportTable();
        $this->DownloadTable('companylist', 'Company Data Analysis HMS', [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $companies = Companyreg::orderBy('comp_name', 'ASC')->get();
        return view('admin.companies', ['companies' => $companies]);
    }

    public function loaduserlist(Request $request)
    {
        $userdata = DB::table('users')->where('role', '3', '4')->orderByDesc('created_at')->paginate(10);
        return view('admin.users', ['userdata' => $userdata]);
    }

    public function companyregister(Request $request)
    {
        $data['country'] = DB::table('tbl_country')->get();
        return view('admin.companyreg', $data);
    }

    public function opencountry(Request $request)
    {
        $Links = $this->ExportTable();
        $this->DownloadTable('countrytable', 'Country Data Analysis HMS', [0, 1, 2, 3], [1, 2, 3]);
        $countrydata = DB::table('tbl_country')->orderBy('name', 'ASC')->get();
        return view('admin.countryform', ['countrydata' => $countrydata]);
    }

    public function openstate(Request $request)
    {
        $Links = $this->ExportTable();
        $this->DownloadTable('statetable', 'State Data Analysis HMS', [0, 1, 2, 3], [1, 2, 3]);
        $data['country'] = DB::table('tbl_country')->get();
        $state_data = DB::table('tbl_state')->orderBy('name', 'ASC')->get();
        return view('admin.stateform', ['state_data' => $state_data], $data);
    }

    public function opencity(Request $request)
    {
        $Links = $this->ExportTable();
        $this->DownloadTable('cityformmain', 'City Data Analysis HMS', [0, 1, 2, 3], [1, 2, 3]);
        $data['country'] = DB::table('tbl_country')->get();
        $city_data = DB::table('tbl_city')
            ->join('tbl_state', 'tbl_state.state_code', '=', 'tbl_city.state')
            ->join('tbl_country', 'tbl_country.country_code', '=', 'tbl_city.country')
            ->select('tbl_country.name as countryname', 'tbl_state.name as statekanaam', 'tbl_city.*')
            ->orderBy('tbl_city.cityname', 'ASC')->get();
        return view('admin.cityform', ['city_data' => $city_data], $data);
    }

    public function openUpdateProperty(Request $request)
    {
        $property_id = base64_decode($request->input('propertyid'));
        $data['country'] = DB::table('tbl_country')->get();
        $companydata = DB::table('company')->where('propertyid', $property_id)->first();
        return view('admin.companyupdate', ['companydata' => $companydata], $data);
    }

    public function disablepropertyadmin(Request $request)
    {
        try {
            $property_id = base64_decode($request->input('propertyid'));
            $company_data = DB::table('company')->where('propertyid', $property_id)->first();
            $jaldiwahasehato📢 = DB::table('company')->where('propertyid', $property_id)->update(['status' => 0]);
            $jaldiwahasehato📢 = DB::table('users')->where('email', $company_data->email)->update(['status' => 0]);

            if ($jaldiwahasehato📢) {
                return back()->with('success', 'Company InActive Successfully');
            } else {
                return back()->with('error', 'Unable to Find Company Id');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function enableproperty(Request $request)
    {
        try {
            $property_id = base64_decode($request->input('propertyid'));
            $company_data = DB::table('company')->where('propertyid', $property_id)->first();
            $jaldiwahasehato📢 = DB::table('company')->where('propertyid', $property_id)->update(['status' => 1]);
            $jaldiwahasehato📢 = DB::table('users')->where('email', $company_data->email)->update(['status' => 1]);
            if ($jaldiwahasehato📢) {
                return back()->with('success', 'Company Active Successfully');
            } else {
                return back()->with('error', 'Unable to Find Company Id');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function getState(Request $request)
    {
        $cid = $request->post('cid');
        $state = DB::table('tbl_state')->where('country', $cid)->orderBy('name', 'asc')->get();
        $html = '<option value="">Select State</option>';
        foreach ($state as $list) {
            $html .= '<option value="' . $list->state_code . '">' . $list->name . '</option>';
        }
        echo $html;
    }

    public function getStateadmin(Request $request)
    {
        $cid = $request->post('cid');
        $state = DB::table('tbl_state')->where('country', $cid)->orderBy('name', 'asc')->get();
        $html = '<option value="">Select State</option>';
        foreach ($state as $list) {
            $html .= '<option value="' . $list->state_code . '">' . $list->name . '</option>';
        }
        echo $html;
    }

    public function check_mobile(Request $request)
    {
        $mobileNumber = $request->input('mobile');
        $result = DB::table('company')->where('mobile', $mobileNumber)->first();

        if ($result) {
            $companyName = $result->comp_name;
            echo "This mobile number is already registered with $companyName.";
        }
    }

    public function check_Sno(Request $request)
    {
        $sn_num = $request->input('sn_num');
        $result = DB::table('company')->where('sn_num', $sn_num)->first();

        if ($result) {
            $companyName = $result->comp_name;
            echo "This SN is registered with $companyName.";
        }
    }

    public function check_email(Request $request)
    {
        $emailId = $request->input('email');
        $result = DB::table('users')->where('email', $emailId)->first();

        if ($result) {
            $companyName = $result->name;
            return "This E-Mail Id is already registered with $companyName.";
        }
    }

    public function check_username(Request $request)
    {
        $username = $request->input('username') ?? $request->input('fullname');
        $result = DB::table('users')->where('u_name', $username)->first();
        if ($result) {
            $companyname = $result->email;
            return "This Username is already registered with $companyname.";
        }
    }

    public function store(Request $request)
    {
        $permission = revokeopen(201111);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|max:100',
            'username' => 'required|max:25',
            'logo_property' => 'required|file|image|mimes:jpeg,png,jpg,webp|max:5048',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors();
            return back()->with('error', $messages->first());
        }

        $inputUsername = $request->input('username');
        $existingusername = Companyreg::where('u_name', $inputUsername)->first();

        // if ($existingusername) {
        //     return back()->with('error', 'Username already exists!');
        // }

        $inputEmail = $request->input('email');
        // $existingemail = Companyreg::where('email', $inputEmail)->first();
        // if ($existingemail) {
        //     return back()->with('error', 'Email already exists!');
        // }

        $inputMobile = $request->input('mobile');
        // $existingmobile = Companyreg::where('mobile', $inputMobile)->first();
        // if ($existingmobile) {
        //     return back()->with('error', 'Mobile already exists!');
        // }

        $company = new Companyreg;
        $company->comp_name = $request->input('company_name');
        $company->u_name = $request->input('username');
        $company->mobile = $request->input('mobile');
        $company->email = $request->input('email');
        $company->pan_no = $request->input('pan_no');
        $company->start_dt = $request->input('start_date');
        $company->end_dt = $request->input('end_date');
        $company->sn_num = $request->input('sn_num');
        $company->gstin = $request->input('gstin');
        $company->division_code = $request->input('division_code');
        $company->legal_name = $request->input('legal_name');
        $company->trade_name = $request->input('trade_name');
        $company->address1 = $request->input('address1');
        $company->address2 = $request->input('address2');
        $company->state = $request->input('state_select');
        $company->city = $request->input('city');
        $company->pin = $request->input('pin');
        $company->role = 'Property';
        $company->website = $request->input('website') ?? '';
        $company->password = $request->input('password');
        $countrycode = $request->input('country_select');
        $company->nationality = DB::table('tbl_country')->where('country_code', $countrycode)->value('nationality');
        $company->country = $request->input('country_select');
        // Validate file uploads before processing
        $logo_property = $request->file('logo_property');
        if ($logo_property && !$logo_property->isValid()) {
            return redirect()->back()->with('error', 'Invalid logo file uploaded.');
        }
        $dealer_logo_property = $request->file('dealer_logo_property');
        if ($dealer_logo_property && !$dealer_logo_property->isValid()) {
            return redirect()->back()->with('error', 'Invalid dealer logo file uploaded.');
        }
        $company->logo = $inputMobile . $request->input('company_name') . time() . '.' . $logo_property->getClientOriginalExtension();

        $state_code = DB::table('tbl_state')->where('name', $company->state)->value('state_code');
        $company->state_code = $state_code;
        $company->acname = strtoupper($request->input('acname')) ?? '';
        $company->acnum = $request->input('acnum') ?? '';
        $company->ifsccode = $request->input('ifsccode') ?? '';
        $company->bankname = $request->input('bankname') ?? '';
        $company->branchname = $request->input('branchname') ?? '';
        $company->save();
        $filePath = $logo_property->storeAs('public/admin/property_logo', $company->logo);
        if ($dealer_logo_property && $dealer_logo_property->isValid()) {
            $company->dealer_logo = $inputMobile . 'dealer_' . $request->input('company_name') . time() . '.' . $dealer_logo_property->getClientOriginalExtension();
            $company->save();
            $dealerFilePath = $dealer_logo_property->storeAs('public/admin/dealer_logo', $company->dealer_logo);
        }
        Artisan::call('storage:link');

        $this->setFolderPermissions(storage_path('app'), 0777);
        $this->setFolderPermissions(storage_path('logs'), 0777);
        $this->setFolderPermissions(public_path('admin/property_logo'), 0777);
        $this->setFolderPermissions(public_path('admin/dealer_logo'), 0777);

        return back()->with('success', 'Company Registered successfully!');
    }



    public function companyupdate(Request $request)
    {
        $permission = revokeopen(201111);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|max:100',
            'username' => 'required|max:25',
            'logo_property' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:5048',
            'cover_image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:5048',
            'payment_qr_code' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:5048',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $property_id = $request->input('property_id');
        $companyData = Companyreg::where('propertyid', $property_id)->first();

        if (!$companyData) {
            return back()->with('error', 'Company not found!');
        }

        $countryInfo = DB::table('tbl_country')->where('country_code', $request->input('country_select'))->first();
        $stateInfo = DB::table('tbl_state')->where('state_code', $request->input('state_select'))->first();

        $fileConfigs = [
            'logo_property' => ['path' => 'storage/app/public/admin/property_logo', 'current' => $companyData->logo],
            'cover_image' => ['path' => 'storage/app/public/property/coverimage', 'current' => $companyData->cover_image],
            'payment_qr_code' => ['path' => 'storage/app/public/property/qrcode', 'current' => $companyData->payment_qr_code],
            'dealer_logo_property' => ['path' => 'storage/app/public/admin/dealer_logo', 'current' => $companyData->dealer_logo]
        ];

        $fileResults = [];
        foreach ($fileConfigs as $field => $config) {
            try {
                $file = $request->file($field);
                if ($file !== null) {
                    $fullPath = base_path($config['path']);

                    if (!is_dir($fullPath)) {
                        @mkdir($fullPath, 0777, true);
                    }

                    if ($config['current'] && file_exists($fullPath . '/' . $config['current'])) {
                        @unlink($fullPath . '/' . $config['current']);
                    }

                    $filename = $property_id . '_' . $field . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($fullPath, $filename);
                    $fileResults[$field] = $filename;
                } else {
                    $fileResults[$field] = $config['current'];
                }
            } catch (\Exception $e) {
                Log::error('File upload error for ' . $field . ': ' . $e->getMessage());
                $fileResults[$field] = $config['current'];
            }
        }

        $updateData = array_merge($request->only([
            'company_name',
            'username',
            'mobile',
            'email',
            'pan_no',
            'start_date',
            'end_date',
            'sn_num',
            'gstin',
            'division_code',
            'legal_name',
            'trade_name',
            'address1',
            'address2',
            'city',
            'pin',
            'website',
            'acname',
            'acnum',
            'ifsccode',
            'bankname',
            'branchname'
        ]), [
            'comp_name' => $request->input('company_name'),
            'u_name' => $request->input('username'),
            'start_dt' => $request->input('start_date'),
            'end_dt' => $request->input('end_date'),
            'state' => $stateInfo->name ?? '',
            'country' => $request->input('country_select'),
            'state_code' => $stateInfo->state_code ?? '',
            'nationality' => $countryInfo->nationality ?? '',
            'acname' => strtoupper($request->input('acname') ?? ''),
            'logo' => $fileResults['logo_property'],
            'cover_image' => $fileResults['cover_image'],
            'payment_qr_code' => $fileResults['payment_qr_code'],
            'dealer_logo' => $fileResults['dealer_logo_property'],
        ]);

        try {
            $companyData->update($updateData);
            DB::table('users')
                ->where('propertyid', $property_id)
                ->update([
                    'email' => $updateData['email'],
                    'updated_at' => now(),
                    'u_ae' => 'e',
                ]);

            $this->setFolderPermissions(storage_path('app/public/admin/property_logo'), 0777);
            $this->setFolderPermissions(storage_path('app/public/admin/dealer_logo'), 0777);
            $this->setFolderPermissions(storage_path('app/public/property/coverimage'), 0777);
            $this->setFolderPermissions(storage_path('app/public/property/qrcode'), 0777);

            return redirect('companylist')->with('success', 'Company Updated successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to update company: ' . $e->getMessage());
        }
    }

    public function submitcountry(Request $request)
    {
        $permission = revokeopen(201111);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $validatedData = $request->validate([
            'countryname' => 'required',
            'nationality' => 'required',
            'country_code' => 'required',
        ]);

        $countryname = $request->input('countryname');
        $country_code = $request->input('country_code');
        $existingcountryname = DB::table('tbl_country')->where('name', $countryname)->first();
        $existingcountry_code = DB::table('tbl_country')->where('country_code', $country_code)->first();

        if ($existingcountryname) {
            return back()->with('error', 'Country already exists!');
        }

        if ($existingcountry_code) {
            return back()->with('error', 'Country Code already exists!');
        }

        $data = [
            'name' => $request->input('countryname'),
            'nationality' => $request->input('nationality'),
            'country_code' => $request->input('country_code'),
        ];

        $data = Companyreg::InsertCountry(1, 'ANALYSIS', $data);
        return back()->with('success', 'Country Inserted successfully!');
    }

    public function check_country(Request $request)
    {
        $countryname = $request->input('countryname');
        $result = DB::table('tbl_country')->where('name', $countryname)->first();

        if ($result) {
            echo "This Country is already exists.";
        }
    }

    public function check_country_code(Request $request)
    {
        $country_code = $request->input('country_code');
        $result = DB::table('tbl_country')->where('country_code', $country_code)->first();

        if ($result) {
            echo "This Country Code is already exists.";
        }
    }

    public function submitstate(Request $request)
    {
        $permission = revokeopen(201111);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $validatedData = $request->validate([
            'country_select' => 'required',
            'state_name' => 'required',
            'state_code' => 'required',
        ]);

        $state_name = $request->input('state_name');
        $state_code = $request->input('state_code');
        $existingstatename = DB::table('tbl_state')->where('name', $state_name)->first();
        $existingstate_code = DB::table('tbl_state')->where('state_code', $state_code)->first();

        if ($existingstatename) {
            return back()->with('error', 'State already exists!');
        }

        if ($existingstate_code) {
            return back()->with('error', 'State Code already exists!');
        }

        $data = [
            'country' => $request->input('country_select'),
            'name' => $request->input('state_name'),
            'state_code' => $request->input('state_code'),
        ];

        $data = Companyreg::InsertState(1, 'ANALYSIS', $data);
        return back()->with('success', 'State Inserted successfully!');
    }

    public function check_state_insert(Request $request)
    {
        $state_name = $request->input('state_name');
        $result = DB::table('tbl_state')->where('name', $state_name)->first();

        if ($result) {
            echo "This State is already exists.";
        }
    }

    public function check_state_code(Request $request)
    {
        $state_code = $request->input('state_code');
        $result = DB::table('tbl_state')->where('state_code', $state_code)->first();

        if ($result) {
            echo "This State Code is already exists.";
        }
    }

    public function submitcity(Request $request)
    {
        $permission = revokeopen(201111);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $validatedData = $request->validate([
            'country' => 'required',
            'cityname' => 'required',
            'state' => 'required',
        ]);

        $cityname = $request->input('cityname');
        $zipcode = $request->input('zipcode');
        $existingcityname = DB::table('tbl_city')->where('cityname', $cityname)->first();
        $existingzip_code = DB::table('tbl_city')->where('zipcode', $zipcode)->first();

        if ($existingcityname) {
            return back()->with('error', 'City already exists!');
        }

        if ($existingzip_code) {
            return back()->with('error', 'Zip Code already exists!');
        }

        $data = [
            'country' => $request->input('country'),
            'cityname' => $request->input('cityname'),
            'zipcode' => $request->input('zipcode'),
            'state' => $request->input('state'),
        ];

        $data = Companyreg::InsertCity(1, 'ANALYSIS', $data);
        return back()->with('success', 'City Inserted successfully!');
    }

    public function check_city_name(Request $request)
    {
        $cityname = $request->input('cityname');
        $result = DB::table('tbl_city')->where('cityname', $cityname)->first();

        if ($result) {
            echo "This City is already exists.";
        }
    }

    public function check_zipcode(Request $request)
    {
        $zipcode = $request->input('zipcode');
        $result = DB::table('tbl_city')->where('zipcode', $zipcode)->first();

        if ($result) {
            echo "This Zip Code is already exists.";
        }
    }

    public function updatecountry(Request $request)
    {
        $country_code = base64_decode($request->input('country_code'));
        $country_data = DB::table('tbl_country')->where('country_code', $country_code)->first();
        return view('admin.updatecountryform', ['country_data' => $country_data]);
    }

    public function update_countrystore(Request $request)
    {
        $request->validate(
            [
                'countryname' => 'required',
                'country_code' => 'required',
                'nationality' => 'required',
            ]
        );
        $country_code_first = $request->input('country_code');
        $username = Auth::user()->name;
        $data = [
            'name' => $request->input('countryname'),
            'country_code' => $request->input('country_code'),
            'nationality' => $request->input('nationality'),
            'u_name' => $username,
        ];

        $update = Companyreg::update_country($country_code_first, $data);
        if ($update == true) {
            return back()->with('success', 'Country Updated Successfully');
        } else {
            return back()->with('error', 'Unable to Update Country');
        }
    }

    public function updatestate(Request $request)
    {
        $state_code = base64_decode($request->input('state_code'));
        $data['country'] = DB::table('tbl_country')->get();
        $state_data = DB::table('tbl_state')->where('state_code', $state_code)->first();
        return view('admin.updatestateform', ['state_data' => $state_data], $data);
    }

    public function update_statestore(Request $request)
    {
        $request->validate(
            [
                'country_select' => 'required',
                'state_name' => 'required',
                'state_code' => 'required',
            ]
        );

        $state_code = $request->input('state_code');
        $username = Auth::user()->name;
        $data = [
            'country' => $request->input('country_select'),
            'name' => $request->input('state_name'),
            'u_name' => $username,
        ];

        $update = Companyreg::update_state($state_code, $data);
        if ($update == true) {
            return back()->with('success', 'State Updated Successfully');
        } else {
            return back()->with('error', 'Unable to Update State');
        }
    }

    public function updatecity(Request $request)
    {
        $city_code = base64_decode($request->input('city_code'));
        $data['country'] = DB::table('tbl_country')->get();
        $city_data = DB::table('tbl_city')->where('city_code', $city_code)->first();
        return view('admin.updatecityform', ['city_data' => $city_data], $data);
    }

    public function citystoreupdate(Request $request)
    {
        $request->validate(
            [
                'cityname' => 'required',
                'country' => 'required',
                'state' => 'required',
            ]
        );

        $city_code = $request->input('city_code');
        $username = Auth::user()->name;
        $data = [
            'cityname' => $request->input('cityname'),
            'country' => $request->input('country'),
            'state' => $request->input('state'),
            'zipcode' => $request->input('zipcode'),
            'u_name' => $username,
        ];

        $update = Companyreg::update_city($city_code, $data);
        if ($update == true) {
            return back()->with('success', 'City Updated Successfully');
        } else {
            return back()->with('error', 'Unable to Update City');
        }
    }

    public function disableusermaster(Request $request)
    {
        try {
            $user_id = base64_decode($request->input('userId'));
            $jaldiwahasehato📢 = DB::table('users')->where('id', $user_id)->update(['status' => 0]);

            if ($jaldiwahasehato📢) {
                return back()->with('success', 'User InActive Successfully');
            } else {
                return back()->with('error', 'Unable to Find User Id');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function enableusermaster(Request $request)
    {
        try {
            $user_id = base64_decode($request->input('userId'));
            $jaldiwahasehato📢 = DB::table('users')->where('id', $user_id)->update(['status' => 1]);
            if ($jaldiwahasehato📢) {
                return back()->with('success', 'User Active Successfully');
            } else {
                return back()->with('error', 'Unable to Find User Id');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function openusermaster(Request $request)
    {
        $userdata = DB::table('users')
            ->where('propertyid', 20)->orderBy('u_name', 'ASC')->get();
        $data['property'] = DB::table('company')->get();
        return view('admin.usermaster', ['userdata' => $userdata], $data);
    }


    public function updateusermaster(Request $request)
    {

        $userid = base64_decode($request->input('userid'));
        $userdata = DB::table('users')->where('id', $userid)->first();
        $data['property'] = DB::table('company')->get();
        return view('admin.updateusermaster', ['userdata' => $userdata], $data);
    }

    public function submitusermaster(Request $request)
    {
        $permission = revokeopen(201111);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $inputUsername = $request->input('username');
        $existingusername = DB::table('users')->where('propertyid', '20')->where('name', $inputUsername)->first();

        if ($existingusername) {
            return back()->with('error', 'Username already exists!');
        }

        $inputEmail = $request->input('username');
        // generate unique email id
        do {
            $randomNumber = rand(1000, 9999);
            $inputEmail = $inputUsername . $randomNumber . '@hms.com';
            $existingemail = DB::table('users')->where('email', $inputEmail)->first();
        } while ($existingemail);

        $data = [
            'u_name' => $request->input('username'),
            'propertyid' => 20,
            'name' => $request->input('username'),
            'email' => $inputEmail,
            'role' => 1,
            'password' => bcrypt($request->input('password')),
        ];

        User::create($data);

        return back()->with('success', 'User Inserted successfully!');
    }

    public function update_usermasterstore(Request $request)
    {
        $request->validate(
            [
                'username' => 'required',
                'password' => 'required',
            ]
        );
        $userid = $request->input('userid');
        $data = [
            'u_name' => $request->input('username'),
            'name' => $request->input('username'),
            'password' => bcrypt($request->input('password')),
        ];

        $update = DB::table('users')->where('id', $userid)->update($data);
        if ($update == true) {
            return back()->with('success', 'User Updated Successfully');
        } else {
            return back()->with('error', 'Unable to Update User');
        }
    }

    public function updateUserAp(Request $request)
    {
        $permission = revokeopen(201111);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'ap_type' => 'required|in:A,P',
        ]);

        $updated = DB::table('users')
            ->where('id', $request->input('user_id'))
            ->update([
                'AP' => $request->input('ap_type'),
            ]);

        if ($updated) {
            return back()->with('success', 'User AP updated successfully.');
        }

        return back()->with('error', 'No changes were made.');
    }



    public function PermissionUpdate(Request $request)
    {
        $permission = revokeopen(201111);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $tablename = 'permission';
        $request->validate([
            'propertyid' => 'required',
        ]);
        $permicheck = $request->input('permicheck');
        $updateData = [];
        $permissions = ['mainsetup', 'reservation', 'frontoffice', 'housekeep', 'inventry', 'pointofsale', 'banquet', 'nightaudit', 'hrpayroll', 'extras'];
        foreach ($permissions as $permission) {
            $updateData['m' . array_search($permission, $permissions) + 1] = in_array($permission, $permicheck ?? []) ? '1' : '0';
        }
        if (!empty($updateData)) {
            DB::table($tablename)->where('propertyid', $request->input('propertyid'))->update($updateData);
            return response()->json(['message' => 'Permission Updated']);
        }
        return response()->json(['message' => 'Not Checked'], 500);
    }



    public function fetchUpdates(): JsonResponse
    {
        $updates = UpdateLog::orderBy('sn', 'desc')->get();
        return response()->json($updates);
    }

    // public function showUpdateForm(Request $request)
    // {
    //     $properties = Companyreg::groupBy('propertyid')->get();
    //     $envgeneral = EnviroGeneral::select('company.comp_name', 'enviro_general.propertyid', 'enviro_general.ncur', 'enviro_general.expdate', 'enviro_general.amount')
    //         ->leftJoin('company', 'company.propertyid', '=', 'enviro_general.propertyid')
    //         ->whereNot('enviro_general.propertyid', '103')
    //         ->orderBY('enviro_general.propertyid')
    //         ->get();

    //     return view('admin.paymode', ['properties' => $properties, 'envgeneral' => $envgeneral]);
    // }
    public function showUpdateForm()
    {
        $properties = Companyreg::groupBy('propertyid')->get();

        $envgeneral = DB::table('enviro_general')
            ->leftJoin('company', 'company.propertyid', '=', 'enviro_general.propertyid')
            ->select(
                'company.comp_name',
                'enviro_general.propertyid',
                'enviro_general.ncur',
                'enviro_general.expdate',
                'enviro_general.amount'
            )
            ->where('enviro_general.propertyid', '!=', '103')
            ->orderBy('enviro_general.propertyid')
            ->get();

        return view('admin.paymode', compact('properties', 'envgeneral'));
    }

    /**
     * Set folder permissions to 777 recursively
     *
     * @param string $path
     * @param int $permissions
     * @return void
     */
    private function setFolderPermissions($path, $permissions = 0777)
    {
        if (!file_exists($path)) {
            return;
        }

        try {
            // Try chmod first
            @chmod($path, $permissions);

            // Use shell command as fallback for Linux/Unix systems
            if (PHP_OS_FAMILY !== 'Windows') {
                @shell_exec('chmod -R 777 ' . escapeshellarg($path));
            }

            if (is_dir($path)) {
                $items = @scandir($path);
                if ($items) {
                    foreach ($items as $item) {
                        if ($item !== '.' && $item !== '..') {
                            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
                            if (is_dir($itemPath)) {
                                @chmod($itemPath, $permissions);
                                // Recursively set permissions
                                $this->setFolderPermissions($itemPath, $permissions);
                            } else {
                                @chmod($itemPath, $permissions);
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Silently continue if chmod fails
            Log::warning('Failed to set permissions on ' . $path . ': ' . $e->getMessage());
        }
    }
}
