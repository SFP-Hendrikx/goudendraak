<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    public function menu(): View
    {
        return view('pages.menu');
    }

    public function news(): View
    {
        return view('pages.news');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function offers(): View
    {
        return view('pages.offers');
    }
}
