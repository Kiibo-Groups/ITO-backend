<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class MaintenancesExport implements FromView, ShouldAutoSize
{
	private $maintenances;

	public function __construct($maintenances)  {
		$this->maintenances = $maintenances;
	}
   	public function view(): View
   	{
		return view('exports.maintenances', [
			'data' => $this->maintenances
		]);
   	}
}