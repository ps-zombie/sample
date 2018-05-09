<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

class UsersController extends Controller
{
	public function create(){
		return view('users.create');
	}
    //
	public function signup(){
		echo 321;
	}
}
