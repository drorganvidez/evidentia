<?php

namespace App\Http\Controllers;

use App\View\Dashboard;
use App\View\ManagementDashboard;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(Dashboard $dashboard, ManagementDashboard $management)
    {
        $user = Auth::user();

        // The president and the lecturer get the control panel of the course, not the page of a participant
        if ($user->roles->pluck('rol')->intersect(['PRESIDENT', 'LECTURE'])->isNotEmpty()) {
            return view('home.management', $management->for($user));
        }

        return view('home', $dashboard->for($user));
    }
}
