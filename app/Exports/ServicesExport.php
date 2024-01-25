<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class ServicesExport implements FromView, ShouldAutoSize
{
	private $services;

	public function __construct($services)  {
		$this->services = $services;
	}
   	public function view(): View
   	{
		return view('exports.services', [
			'data' => $this->services
		]);
   	}
}