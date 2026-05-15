<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class CrmManualController extends Controller
{
    public function show()
    {
        $company = auth()->user()->company ?? null;
        return view('admin.crm.manual.index', compact('company'));
    }

    public function pdf()
    {
        $company = auth()->user()->company ?? null;
        $pdf = Pdf::loadView('admin.crm.manual.pdf', compact('company'))
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'dpi' => 110,
                'defaultFont' => 'helvetica',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);
        return $pdf->stream('manual-crm.pdf');
    }
}
