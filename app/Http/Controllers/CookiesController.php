<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class CookiesController extends Controller
{
    public function setDarkMode(Request $request): Response
    {
        $response = new Response('Hello World');
        $response->withCookie(cookie('dark_mode', true));
        return $response;
    }

    public function getCookie(Request $request)
    {
        $value = $request->cookie('dark_mode');
        echo Cookie::get('dark_mode');
    }
}
