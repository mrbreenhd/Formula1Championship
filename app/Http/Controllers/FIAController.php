<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportResponse;
use App\Models\Drivers;
use App\Models\Reports;
use App\Models\Race;
use Auth;

class FIAController extends Controller
{
    public function report_overview()
    {
        $reports = Reports::where('reporter_id', Auth::id())->get();
        return view('fia.reports.overview', compact('reports'));
    }

    public function report_create()
    {
        $drivers = Drivers::all();
        $races = Race::all();
        return view('fia.reports.create', compact('drivers', 'races'));
    }
    
    public function report_store(Request $request)
    {        
        Reports::create([            
            'race_id' => $request->event,
            'reporter_id' => $request->reporter_id,
            'reportee_id' => $request->reportee_id,
            'description' => $request->description,
            'reference' => $request->reference,
            'status' => 1
        ]);

        notify()->success('Report Created');
        return redirect(route('fia.report_overview'));
    }

    public function report_show($id)
    {
        $report = Reports::find($id);
        $responses = ReportResponse::where('report_id', $id)->get();
        $count = $responses->count();
        return view('fia.reports.show', compact('report', 'responses', 'count'));
    }

    public function report_respond($id, Request $request)
    {
        $response = ReportResponse::create([
            'report_id' => $id, 
            'responder' => $request->fia_offical,
            'description' => $request->description,
        ]);

        $report = Reports::find($id);
        $report->status = 3;
        $report->save();

        return redirect(route('fia.report_overview'));
    }

    public function report_withdraw($id)
    {
        $report = Reports::find($id);
        $report->status = 6;
        $report->save();

        notify()->success('Report Withdrawn');
        return redirect(route('fia.report_overview'));
    }

    public function fia_report_overview()
    {
        $reports = Reports::all();
        return view('fia.reports.drivers.overview', compact('reports'));
    }

    public function fia_report_show($id)
    {
        $report = Reports::find($id);
        $responses = ReportResponse::where('report_id', $id)->get();
        $count = $responses->count();
        return view('fia.reports.drivers.show', compact('report', 'responses', 'count'));
    }

    public function fia_report_respond($id, Request $request)
    {
        $response = ReportResponse::create([
            'report_id' => $id, 
            'responder' => $request->fia_offical,
            'description' => $request->description,
        ]);

        $report = Reports::find($id);
        $report->status = 2;
        $report->save();

        return redirect(route('fia.driver.report_overview'));
    }

    public function fia_report_close($id)
    {
        $report = Reports::find($id);
        $report->status = 5;
        $report->save();

        notify()->success('Report Closed');
        return redirect(route('fia.driver.report_overview'));
    }
}
