<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Companyreg;
use App\Models\EnviroGeneral;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            SupportTicket::updateUserApStatus((int) Auth::id(), 'A');
        }

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }


    public function login(Request $request)
    {

        $credentials = $request->validate([
            'u_name' => 'required',
            'propertyid' => 'required',
            'password' => 'required'
        ]);

        // $companydcode = Companyreg::where('propertyid', $credentials['propertyid'])->orderBy('comp_code', 'DESC')->first();

        $userWithEmail = User::leftJoin('company', 'company.propertyid', '=', 'users.propertyid')
            ->where(function ($query) use ($credentials) {
                $query->where('users.u_name', $credentials['u_name'])
                    ->orWhere('users.email', $credentials['u_name']);
            })
            ->where('users.propertyid', $credentials['propertyid'])
            ->where('users.status', 1)
            ->orderBy('company.comp_code', 'DESC')
            ->first();



        if (!$userWithEmail) {
            return back()->withErrors(['u_name' => 'Account does not exist'])->withInput();
        }


        // ═══════════════════════════════════════════════════════════════════
        // EXPIRY CHECK DISABLED — serial key / license expiry enforcement
        // removed. All properties can now login regardless of expdate.
        // Previously: decrypted enviro_general.expdate and compared against ncur.
        // To re-enable, uncomment the block below.
        // ═══════════════════════════════════════════════════════════════════
        // $envgeneral = EnviroGeneral::where('propertyid', $userWithEmail->propertyid)->first();
        // if ($envgeneral && $envgeneral->expdate && $envgeneral->propertyid != 103) {
        //     $expdate = Crypt::decryptString($envgeneral->expdate);
        //     $ncurdate = $envgeneral->ncur;
        //     if ($expdate < $ncurdate) {
        //         return back()->withErrors(['u_name' => 'Your account is expired. Please contact your software vendor.']);
        //     }
        // }

        // if ($userWithEmail->status != 1) {
        //     return back()->withErrors(['u_name' => 'Account is not active'])->withInput();
        // }

        if (Auth::attempt($credentials)) {
            $user = Auth::user()->role;

            switch ($user) {
                case 1:
                    return redirect('/superadmin');
                    break;
                case 2:
                    return redirect('/company');
                    break;
                case 3:
                    return redirect('/user');
                    break;
                case 4:
                    return redirect('/staff');
                    break;
                case 5:
                    return redirect('/frontlogin');
                    break;
                default:
                    Auth::logout();
                    return back()->with('u_name', 'Oops Something went wrong');
            }
        } else {
            return back()->withErrors(['password' => 'Invalid password'])->withInput();
        }
    }

    public function reactlogin(Request $request)
    {
        $data = $request->json()->all();

        $request->validate([
            'username' => 'required|string',
            'property_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // Log::info('Login Request:', $data);

        $user = User::where('name', $data['username'])
            ->where('propertyid', $data['property_id'])
            ->first();

        // Log::info('User Found:', ['user' => $user]);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Account does not exist',
            ]);
        }

        if ($user->status !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Account is not active',
            ]);
        }

        if ($user && Hash::check($data['password'], $user->password)) {

            $userdata = User::select('users.*')
                ->where('users.propertyid', $user->propertyid)
                ->where('users.u_name', $user->u_name)
                // ->where('users.useroradmin', 'user')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $userdata,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }
    }

    public function reactlogout(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }
}
