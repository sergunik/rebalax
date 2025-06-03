<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(1);
        return view('dashboard.index', compact('user'));
    }
}
