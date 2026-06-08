<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        return view("items.index");
    }

    public function show($item)
    {
        return view("items.show", compact("item"));
    }

    public function qrcode($item)
    {
        return view("items.qrcode", compact("item"));
    }
}
