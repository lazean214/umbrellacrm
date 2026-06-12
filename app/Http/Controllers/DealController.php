<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Deal;

class DealController extends Controller
{
	public function show($deal)
	{
		$deal = Deal::with(['contacts', 'companies'])->findOrFail($deal);
		return view('deals.show', compact('deal'));
	}
}
