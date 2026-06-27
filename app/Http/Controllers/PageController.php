<?php

namespace App\Http\Controllers;

use App\Services\ActiveOfferService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    public function menu(ActiveOfferService $activeOfferService): View
    {
        return view('pages.menu', [
            'menuGroups' => $activeOfferService->groupedMenuForDate(now()),
        ]);
    }

    public function news(): View
    {
        return view('pages.news');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function offers(ActiveOfferService $activeOfferService): View
    {
        return view('pages.offers', [
            'offers' => $activeOfferService->activeOffersForDate(now()),
        ]);
    }
}
