<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookManageController extends Controller
{
    //アプリのルート'/'をviewのbookフォルダの'index.blade.php'に指定する
    public function index() {
        return view('book.index');
    }
}
