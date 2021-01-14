<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ActivityLog;
use App\TempActivityLog;

class TempActivityLogController extends Controller
{
    public function moveFromTempToActual() {
    
        $tempLog = TempActivityLog::where('is_logged', 0)->first();
        // dd($tempLog->logs);
    
        if ($tempLog) {
            foreach ($tempLog->logs[0] as $logs) {
                foreach ($logs as $log) {
                    // dd($log);
                    $log0 = isset($log[0]) ? $log[0] : null;
                    $log1 = isset($log[1]) ? $log[1] : null;
                    $log2 = isset($log[2]) ? $log[2] : null;
                    $log3 = isset($log[3]) ? $log[3] : null;
                    $log4 = isset($log[4]) ? $log[4] : null;
                    $log5 = isset($log[5]) ? $log[5] : null;
    
                    $activityLog = new ActivityLog;
                    $activityLog->logThis($log0, $log1, $log2, $log3, $log4, $log5);
                }            
            }
            $tempLog->is_logged = 1;
            $tempLog->save();
        }

        return "done";
        
    }
}
