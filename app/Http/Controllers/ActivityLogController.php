<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ActivityLog;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get Avtivity log
     */
    function getLog(Request $request) {
        $date_from = Carbon::today()->startOfMonth();
        $date_to = Carbon::today()->endOfMonth();

        $logs = ActivityLog::query();


        if ($request->date_from) {    
            $date_from = $request->date_from;
            $date_to = $request->date_to; 
        }
        
        $logs = $logs->whereBetween('created_at', [$date_from, $date_to]);

        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;

        $data['logs'] = $logs->orderBy('created_at', 'desc')->paginate(50);

        return view('activityLog.log', $data);
    }


    /**
     * Get Avtivity log in PDF
     */
    function getLogPDF(Request $request, $date_from, $date_to) {
        $date_from = Carbon::today()->startOfMonth();
        $date_to = Carbon::today()->endOfMonth();

        $logs = ActivityLog::query();


        if ($request->date_from) {    
            $date_from = $request->date_from;
            $date_to = $request->date_to; 
        }
        
        $logs = $logs->whereBetween('created_at', [$date_from, $date_to]);

        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;

        $data['logs'] = $logs->orderBy('created_at', 'desc')->get();
        
        $pdf = \PDF::loadView('pdf.log', $data)->setPaper('a4', 'landscape');
        return $pdf->stream();
    }
}
