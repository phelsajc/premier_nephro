<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Model\Schedule;
use App\Model\Copay;
use App\Model\Doctors;
use App\Model\Patients;
use App\Model\Phic;
use DB;

class CopayController extends Controller
{  
    
    /**
    * Create a new AuthController instance.
    *
    * @return void
    */
   public function __construct()
   {
       //$this->middleware('auth:api');
       $this->middleware('JWT');
   }

    function import(Request $request)
    {
    
        foreach ($request->data as $row => $value) {          
            $p = new Schedule;
            $p->schedule = date_create($value['Schedule']);
            $p->created_dt = date("Y-m-d");
            $p->created_by =  auth()->id();
            $chck_dct = DB::connection('mysql')->select("select * from doctors where name like '%".$value['Incharge']."%'");
            $chck_px = DB::connection('mysql')->select("select * from patients where name like '%".$value['Customer']."%'");
            $p->doctor = $value['Incharge']!=''||$value['Incharge']!=null?$chck_dct[0]->id:$chck_px[0]->attending_doctor;
            $p->patient_id = $chck_px?$chck_px[0]->id:0;
            $p->save();
                  
            $c = new Copay;
            $c->date_session = date_create($value['Schedule']);
            $c->created_dt = date("Y-m-d");
            $c->created_by =  auth()->id();
            $c->doctor = $p->doctor;
            $c->patient_id = $p->patient_id;
            $c->save();  
            
            $ph = new Phic;
            $ph->date_session = date_create($value['Schedule']);
            $ph->created_dt = date("Y-m-d");
            $ph->created_by =  auth()->id();
            $ph->doctor = $p->doctor;
            $ph->patient_id = $p->patient_id;
            $ph->save();   
        }       
    }

    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start?$request->start:0;
        $val = $request->searchTerm2;
        if($val!=''||$start>0){   
            $data =  DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where p.name like '%".$val."%' LIMIT $length offset $start");
            $count =  DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where p.name like '%".$val."%' ");
        }else{
            $data =  DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where s.schedule = '".date('Y-m-d')."' LIMIT $length");
            $count =  DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where s.schedule = '".date('Y-m-d')."'");
        }
        
        $count_all_record =  DB::connection('mysql')->select("select  count(*) as count from schedule s left join patients p on s.patient_id = p.id ");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['id'] =  $value->id;
            $arr['name'] =  $value->name;
            $incharge = Doctors::where(['id'=>$value->doctor])->first();
            $attending_doctor = Doctors::where(['id'=>$value->attending_doctor])->first();
            $arr['incharge_dctr'] = $incharge ?$incharge->name:'';
            $arr['attending_dctr'] =  $attending_doctor->name;
            $data_array[] = $arr;
        }
        $page = sizeof($count)/$length;
        $getDecimal =  explode(".",$page);
        $page_count = round(sizeof($count)/$length);
        if(sizeof($getDecimal)==2){            
            if($getDecimal[1]<5){
                $page_count = $getDecimal[0] + 1;
            }
        }
        $datasets = array([
            "data"=>$data_array,
            "count"=>$page_count,
            "showing"=>
            sizeof($count_all_record)>0?
            "Showing ".(($start+10)-9)." to ".($start+10>$count_all_record[0]->count?
            $count_all_record[0]->count:
            $start+10)." of ".$count_all_record[0]->count:'',
             "patient"=>$data_array]);
        return response()->json($datasets);
    } 

    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $p = new Schedule;
        $p->name = $request->name;
        $p->save();         
        return true;
    }

    public function edit($id)
    {
        $data = Schedule::where(['id'=>$id])->first();
        return response()->json($data);
    }
    
    public function update(Request $request)
    {
        Schedule::where(['id'=>$request->id])->update([
            'name'=> $request->data['name'],
        ]);
        return true;
    }

    public function Delete($id)
    {
        Schedule::where('id',$id)->delete();
        return true;
    }  

    public function getDoctors()
    {
        $p = Schedule::all();
        return response()->json($p);
    } 

    public function report(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate),'Y-m-d');
        $tdate = date_format(date_create($request->tdate),'Y-m-d');
        $doctors = $request->doctors;
        $getDoctor = Doctors::where(["id"=>$doctors])->first();
        if($doctors!='All'){
            /* $data =  DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'), count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where DATE_FORMAT(s.schedule, '%Y-%m') between '$date' and
                s.doctor = $doctors and s.status = 'ACTIVE'
                group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
            "); */
            $data =  DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'), count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and
                s.doctor = $doctors and s.status = 'ACTIVE'
            ");
        }else{
            /* $data =  DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'), count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where DATE_FORMAT(s.schedule, '%Y-%m') = '$date' and s.status = 'ACTIVE'
                group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
            "); */
            $data =  DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'), count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id,s.doctor
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
                group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id,s.doctor;
            ");
        }        
     
        $data_array = array();
        $data_array_export = array();
        $TotalNet = 0;
        $totalSesh = 0;

        foreach ($data as $key => $value) {
            $arr = array();
            $arr_export = array();
            /* $get_dates = DB::connection('mysql')->select("
            SELECT schedule from schedule
                where DATE_FORMAT(schedule, '%Y-%m') = '$date' and patient_id = '$value->patient_id'  and status = 'ACTIVE'
            "); */
            $get_dates = DB::connection('mysql')->select("
            SELECT schedule from schedule
                where schedule between '$fdate' and '$tdate' and patient_id = '$value->patient_id'  and status = 'ACTIVE'
            ");
            $date_of_sessions = '';
            $date_of_sessionsArr = array();
            foreach ($get_dates as $gkey => $gvalue) {
                $date_of_sessionsArr[] = date_format(date_create($gvalue->schedule),'F d');
                $date_of_sessions.=date_format(date_create($gvalue->schedule),'F d').', ';
            }
            $checkDr =  Doctors::where(['id'=>$value->doctor])->first();
            /*
            $arr_export['Doctor'] =   */
            $arr['name'] =  $value->name;
            $arr['sessions'] =  $value->cnt;
            $arr['dates'] =  $date_of_sessions;
            $arr['datesArr'] =  $date_of_sessionsArr;
            $data_array[] = $arr;
            $arr_export['Date'] = date_format(date_create($value->schedule),'m/d/Y');
            $arr_export['Name'] =  $value->name;
            $arr_export['Doctor'] =  $checkDr->name;
            $arr_export['Amount'] = 150;
            //$arr_export['No. of Sessions'] = count($get_dates); //$value->cnt;
            //$arr_export['Dates'] =  $date_of_sessions;
            $total_copay = count($get_dates) * 150;
            //$arr_export['Total'] =  $total_copay;
            $net = $total_copay * 0.9;
            $data_array_export[] = $arr_export;
            $TotalNet+=$net;
            $totalSesh+=$value->cnt;
            $date_of_sessions = '';
        }
        /* $datasets = array(["data"=>$data_array,"Doctors"=>$getDoctor,'dd'=>"SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'), count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id
        FROM `schedule` s
        left join patients p on s.patient_id = p.id
        where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
        group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;"]);
        return response()->json($data_array); */

        $datasets = array();
        
        $arr_export = array();
        $arr_export['Date'] = '';
        $arr_export['Name'] =  '';
        $arr_export['Doctor'] = 'Total';
        $tOTALSessionAMount = $totalSesh*150;
        $tOTALSessionAMountNet = $tOTALSessionAMount*0.9;
        $arr_export['Amount'] = $tOTALSessionAMount;
        $data_array_export[] = $arr_export;

        
        $arr_export['Date'] = '';
        $arr_export['Name'] =  '';
        $arr_export['Doctor'] = 'Less WT(10%)';
        $arr_export['Amount'] = $tOTALSessionAMount*0.1;
        $data_array_export[] = $arr_export;        
        
        $arr_export['Date'] = '';
        $arr_export['Name'] =  '';
        $arr_export['Doctor'] = 'NET';
        $arr_export['Amount'] = $tOTALSessionAMountNet;
        $data_array_export[] = $arr_export;

        $datasets["data"] = $data_array;
        $datasets["export"] = $data_array_export;
        $datasets["Doctors"] = $getDoctor;
        return response()->json($datasets);
    }
}
