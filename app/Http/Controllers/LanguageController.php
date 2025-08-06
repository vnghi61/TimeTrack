<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function setLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|in:vi,en'
        ]);

        Session::put('locale', $request->language);
        app()->setLocale($request->language);

        return response()->json([
            'message' => 'Language updated successfully',
            'language' => $request->language
        ]);
    }

    public function getLanguage()
    {
        $locale = Session::get('locale', config('app.locale'));
        
        return response()->json([
            'language' => $locale,
            'messages' => __('messages')
        ]);
    }
}