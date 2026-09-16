<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    public function dashboard(): View { return view('pages.dashboard'); }
    public function transactions(): View { return view('pages.transactions'); }
    public function accounts(): View { return view('pages.accounts'); }
    public function people(): View { return view('pages.people'); }
    public function loans(): View { return view('pages.loans'); }
    public function savings(): View { return view('pages.savings'); }
    public function budgets(): View { return view('pages.budgets'); }
    public function recurring(): View { return view('pages.recurring'); }
    public function categories(): View { return view('pages.categories'); }
    public function reports(): View { return view('pages.reports'); }
    public function notifications(): View { return view('pages.notifications'); }
    public function settings(): View { return view('pages.settings'); }
}
