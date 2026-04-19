<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function faq(): View
    {
        return view('pages.faq');
    }

    public function apiGuide(): View
    {
        return view('pages.api-guide');
    }
}
