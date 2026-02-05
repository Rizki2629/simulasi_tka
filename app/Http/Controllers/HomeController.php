<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        return redirect('/dashboard');
    }
}
