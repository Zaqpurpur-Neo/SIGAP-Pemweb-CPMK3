<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MutationController extends Controller
{
    public function index()
    {
        return view("mutations.index");
    }
}
