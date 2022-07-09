<?php

namespace App\Http\Controllers\Test;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    // 以下の記述を追加
    public function menu()
    {
        return view('test/menu');
    }
    // ここまで
}
