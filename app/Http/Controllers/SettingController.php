<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Exception;
use App\Utils\JsonFormatter;

class SettingController extends Controller
{
    public function setRate(Request $request) {
        try {
            $setting = Setting::where('key', '=', 'rates')->first();
    
            $setting->value = $request->value;
            $setting->update();
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }
}
