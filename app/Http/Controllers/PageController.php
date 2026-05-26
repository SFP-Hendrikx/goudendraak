<?php

namespace App\Http\Controllers;

use App\Models\Offer;
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
        $offers = Offer::query()
            ->visibleThisWeek()
            ->with(['menuItems' => function ($query) {
                $query
                    ->where('is_actief', true)
                    ->orderBy('soortgerecht')
                    ->orderBy('menunummer')
                    ->orderBy('menu_toevoeging')
                    ->orderBy('naam');
            }])
            ->orderBy('startdatum')
            ->orderBy('titel')
            ->get();

        return view('pages.offers', [
            'offers' => $offers,
        ]);
    }
}
