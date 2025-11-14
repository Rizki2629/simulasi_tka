<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SoalController extends Controller
{
    public function index()
    {
        return view('soal.index');
    }

    public function create()
    {
        return view('soal.create');
    }

    public function store(Request $request)
    {
        // Logic untuk menyimpan soal
        return redirect()->route('soal.index')->with('success', 'Soal berhasil disimpan');
    }
}
