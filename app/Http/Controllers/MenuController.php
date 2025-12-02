<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function show(Food $food)
    {
        return view('menu.show', compact('food'));
    }
}
