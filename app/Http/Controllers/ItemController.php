<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    public function index()
    {
        return view("items.index");
    }

    public function show(Item $item)
    {
        $item->load(["category", "location"]);
        return view("items.show", compact("item"));
    }

    public function qrcode(Item $item)
    {
        $item->load(["category", "location"]);
        $qrData = json_encode([
            "id" => $item->id,
            "code" => $item->code,
            "name" => $item->name,
            "location" => $item->location->name ?? "-",
            "stock" => $item->stock,
            "unit" => $item->unit,
        ]);
        return view("items.qrcode", compact("item", "qrData"));
    }
}
