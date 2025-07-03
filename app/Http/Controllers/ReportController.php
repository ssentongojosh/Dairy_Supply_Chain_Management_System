<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    //
    public function index()
    {
        // This method can be used to display a list of reports or a dashboard
        return view('reports.reportsettings');
    }
}
