<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Model\Schedule;
use App\Model\Copay;
use App\Model\Doctors;
use App\Model\Patients;
use App\Model\FailedSchedule;
use App\Model\Phic;
use App\Model\Transaction_log;
use DB;

class ScheduleController extends Controller
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
            $chck_dct = DB::connection('mysql')->select("select * from doctors where name like '%".$value['Incharge']."%'");
            $chck_px = DB::connection('mysql')->select("select * from patients where name like '%".$value['Customer']."%'");      
            $doctor  =$value['Incharge']!=''||$value['Incharge']!=null?$chck_dct[0]->id:($chck_px[0]->attending_doctor?$chck_px[0]->attending_doctor:null);
            $check_schedule = Schedule::where(['patient_id'=>$chck_px[0]->id,'schedule'=>date_create($value['Schedule']),'status'=>'ACTIVE'])->first();
            if($doctor!=null&&$check_schedule==null){
                $p = new Schedule;
                $p->schedule = date_create($value['Schedule']);
                $p->created_dt = date("Y-m-d");
                $p->created_by =  auth()->id();
                $p->doctor = $doctor;
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
            }else{
                $p = new FailedSchedule;
                $p->schedule = date_create($value['Schedule']);
                $p->created_dt = date("Y-m-d");
                $p->created_by =  auth()->id();
                $p->doctor = $value['Incharge']!=''||$value['Incharge']!=null?$chck_dct[0]->id:($chck_px[0]->attending_doctor?$chck_px[0]->attending_doctor:null);
                $p->patient_id = $chck_px?$chck_px[0]->id:null;
                $p->status = $check_schedule!=null?'DUPLICATE SCHEDULE':'NO DOCTOR';
                $p->save();                
            }                    
        }       
    }

    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start;//data?$request->data['start']:0;
        $val = $request->searchTerm2;//$request->data?$request->data['searchTerm2']:null;
        if($val!=''||$start>0){   
            $data =  DB::connection('mysql')->select("select s.*,s.id as schedule_id,p.* from schedule s left join patients p on s.patient_id = p.id where p.name like '%".$val."%' and s.status = 'ACTIVE' LIMIT $length offset $start");
            $count =  DB::connection('mysql')->select("select s.*,s.id as schedule_id,p.* from schedule s left join patients p on s.patient_id = p.id where p.name like '%".$val."%' and s.status = 'ACTIVE'");
        }else{
            $data =  DB::connection('mysql')->select("select s.*,s.id as schedule_id,p.* from schedule s left join patients p on s.patient_id = p.id where s.schedule = '".date('Y-m-d')."' and s.status = 'ACTIVE' LIMIT $length");
            $count =  DB::connection('mysql')->select("select s.*,s.id as schedule_id,p.* from schedule s left join patients p on s.patient_id = p.id where s.schedule = '".date('Y-m-d')."' and s.status = 'ACTIVE' ");
        }
        
        $count_all_record =  DB::connection('mysql')->select("select  count(*) as count from schedule s left join patients p on s.patient_id = p.id ");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['id'] =  $value->schedule_id;
            $arr['name'] =  $value->name;
            $arr['date'] =  date_format(date_create($value->schedule),'F d,Y');
            $incharge = Doctors::where(['id'=>$value->doctor])->first();
            $attending_doctor = Doctors::where(['id'=>$value->attending_doctor])->first();
            $arr['incharge_dctr'] = $value->doctor!=0 ?$incharge->name:'';
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
        $p->patient_id = $request->patientid;
        $p->schedule = $request->schedule;
        $pxDctr = Patients::where(['id'=>$request->patientid])->first();
        $p->doctor = $request->doctor!=0?$request->doctor:$pxDctr->attending_doctor;
        $p->save();          
        
        $c = new Copay;
        $c->date_session = date_create($request->schedule);
        $c->created_dt = date("Y-m-d");
        $c->created_by =  auth()->id();
        $c->doctor = $p->doctor;
        $c->patient_id = $p->patient_id;
        $c->save();  

        $ph = new Phic;
        $ph->date_session = date_create($request->schedule);
        $ph->created_dt = date("Y-m-d");
        $ph->created_by =  auth()->id();
        $ph->doctor = $p->doctor;
        $ph->patient_id = $p->patient_id;
        $ph->save();
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
        date_default_timezone_set('Asia/Manila');
        $get_schedule = Schedule::where(['id'=>$id])->first();
        Schedule::where(['id'=>$id])->update([
            'status'=> 'INACTIVE',
        ]);
        $get_patient = Patients::where(['id'=>$get_schedule->patient_id])->first();
        $p = new Transaction_log;
        $p->action = 'DELETE SESSION OF '.$get_patient->name.' on '.$get_schedule->schedule;
        $p->created_dt = date("Y-m-d H:i");
        $p->created_by = auth()->id();   
        $p->save();         
        return true;
    }  

    public function getDoctors()
    {
        $p = Schedule::all();
        return response()->json($p);
    } 
}
