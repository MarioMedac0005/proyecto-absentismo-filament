<?php

namespace App\Http\Controllers;

use App\Exports\SubjectHoursExport;
use Maatwebsite\Excel\Facades\Excel;

class SubjectHoursExportController extends Controller
{
    public function __invoke()
    {
        return Excel::download(new SubjectHoursExport, 'horas_asignatura.xlsx');
    }
}
