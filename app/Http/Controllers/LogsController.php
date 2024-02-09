<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Model\Logs;
use DB;

class LogsController extends Controller
{  
    
    /**
    * Create a new AuthController instance.
    *
    * @return void
    */
   public function __construct()
   {
       $this->middleware('JWT');
   }

    public function report(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate),'Y-m-d');
        $tdate = date_format(date_create($request->tdate),'Y-m-d');
        $data = DB::connection('mysql')->select(" SELECT * from failed_scheduled  where schedule between '$fdate' and '$tdate' group by status");
        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['status'] =  $value->status;
            $arr['date'] =  $value->schedule;
            $data_array[] = $arr;
        }
        $datasets['data'] = $data_array;
        return response()->json($datasets);
    }
}
