<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('dashboard.index', compact('user'));
    }
}
