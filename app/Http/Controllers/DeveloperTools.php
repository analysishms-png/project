<?php

namespace App\Http\Controllers;

use App\Models\ApiClient;
use App\Models\Companyreg;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\FlareClient\Api;

class DeveloperTools extends Controller
{
    public function opendevelopertools(Request $request)
    {
        $propertyid = base64_decode($request->input('propertyid'));
        $company = Companyreg::where('propertyid', $propertyid)->first();
        $chkapi = ApiClient::where('propertyid', $propertyid)->first();
        if (!$chkapi) {
            $chkapi = new ApiClient();
            $chkapi->propertyid = $propertyid;
            $chkapi->save();
        }

        return view(
            'admin.developertools',
            [
                'company' => $company,
                'chkapi' => $chkapi
            ]
        );
    }

    public function generate(Request $request)
    {
        $request->validate([
            'propertyid' => 'required|integer'
        ]);

        $chkalredy = ApiClient::where('propertyid', $request->propertyid)->first();
        if ($chkalredy && $chkalredy->api_key) {
            return back()->with('error', 'API keys already exist for this property');
        }

        $apiKey = 'ana_' . Str::random(40);
        $plainBearerToken = Str::random(64);

        ApiClient::updateOrCreate(
            ['propertyid' => $request->propertyid],
            [
                'api_key' => $apiKey,
                'bearer_token' => $plainBearerToken,
                'is_active' => 1
            ]
        );

        return back()->with('success', 'Api Keys Generated');
    }

    public function download($propertyid)
    {
        $client = ApiClient::where('propertyid', $propertyid)->firstOrFail();

        $filename = "api_client_" . $propertyid . ".xls";

        return response()->streamDownload(function () use ($client) {
            echo "Property ID\tAPI Key\tBearer Token\n";
            echo $client->propertyid . "\t" . $client->api_key . "\t" . $client->bearer_token;
        }, $filename, [
            "Content-Type" => "application/vnd.ms-excel"
        ]);
    }
}
