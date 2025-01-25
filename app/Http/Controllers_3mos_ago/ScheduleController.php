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
            $getPhic = str_replace("X","0",$value['phicno']);
            $chck_px = DB::connection('mysql')->select("select * from patients where phic ='".$getPhic."' AND status = 'Active'");      
            //if($chck_px&&$chck_dct){
                $getCover = str_replace(" ","",$value['phic_accreditation']);
                $getScheduledDate = date_format(date_create($value['Schedule']),'Y-m-d');
            if($chck_px){
                //$chck_dct = DB::connection('mysql')->select("select * from doctors where phic_accreditation = '".$value['phic_accreditation']."'");
               #working $chck_dct = Doctors::Where(['phic_accreditation'=>$value['phic_accreditation']])->first();
                $chck_dct = DB::connection('mysql')->select("select * from doctors where phic_accreditation ='".$getCover."' or name = '".$value['doctor']."'"); 
                //$chck_dct = DB::connection('mysql')->select("select * from doctors where phic_accreditation ='".$value['phic_accreditation']."'"); 
                //$doctor  = $value['phic_accreditation']!=''||$value['phic_accreditation']!=null?$chck_dct[0]->id:($chck_px[0]->attending_doctor?$chck_px[0]->attending_doctor:null);
                //$doctor  = $value['phic_accreditation']!=''||$value['phic_accreditation']!=null?$chck_dct[0]->id:
                //if(($value['phic_accreditation']!=''||$value['phic_accreditation']!=null)&&$chck_dct){
                //if(($value['phic_accreditation']!=''||$value['phic_accreditation']!=null||$value['doctor']!=''||$value['doctor']!=null)&&sizeof($chck_dct)>0){
                if(sizeof($chck_dct)>0){
                    $doctor = $chck_dct[0]->id;
                }else{
                    if($chck_px[0]->attending_doctor){
                        $doctor = $chck_px[0]->attending_doctor;
                    }else{
                        $doctor = null;
                    }
                }
                /* $doctor  = $value['phic_accreditation']!=''||$value['phic_accreditation']!=null?$chck_dct[0]->id:
                ($chck_px[0]->attending_doctor?
                $chck_px[0]->attending_doctor:null); */
                $check_schedule = Schedule::where(['patient_id'=>$chck_px[0]->id,'schedule'=>$getScheduledDate,'status'=>'ACTIVE'])->first();
                if($doctor!=null&&$check_schedule==null){
                    $p = new Schedule;
                    $p->schedule = $getScheduledDate;
                    $p->created_dt = date("Y-m-d");
                    $p->created_by =  auth()->id();
                    $p->doctor = $doctor;
                    $p->patient_id = $chck_px?$chck_px[0]->id:0;
                    $p->save();
                          
                    $c = new Copay;
                    $c->date_session = $getScheduledDate;
                    $c->created_dt = date("Y-m-d");
                    $c->created_by =  auth()->id();
                    $c->doctor = $p->doctor;
                    $c->patient_id = $p->patient_id;
                    $c->save();  
                    
                    $ph = new Phic;
                    $ph->date_session = $getScheduledDate;
                    $ph->created_dt = date("Y-m-d");
                    $ph->created_by =  auth()->id();
                    $ph->doctor = $p->doctor;
                    $ph->patient_id = $p->patient_id;
                    $ph->save();
                }else{
                    $p = new FailedSchedule;
                    $p->schedule = $getScheduledDate;
                    $p->created_dt = date("Y-m-d");
                    $p->created_by =  auth()->id();
                    //$p->doctor = $value['phic_accreditation']!=''||$value['phic_accreditation']!=null?$chck_dct[0]->id:($chck_px[0]->attending_doctor?$chck_px[0]->attending_doctor:null);
                    if(($getCover!=''||$getCover!=null)&&$chck_dct){
                        $doctor = $chck_dct[0]->id;
                    }else{
                        if($chck_px[0]->attending_doctor){
                            $doctor = $chck_px[0]->attending_doctor;
                        }else{
                            $doctor = null;
                        }
                    }
                    $p->doctor = $doctor;//$value['phic_accreditation']!=''||$value['phic_accreditation']!=null?$chck_dct[0]->id:($chck_px[0]->attending_doctor?$chck_px[0]->attending_doctor:null);
                    $p->patient_id = $chck_px?$chck_px[0]->id:null;
                    $p->status = $check_schedule!=null?$chck_px[0]->name.' HAS DUPLICATE SCHEDULE '.$value['Schedule']:$chck_px[0]->name.' HAS NO DOCTOR';
                    $p->save();                
                }  
            }else{
                //dd(empty($chck_px));exit;
                $p = new FailedSchedule;
                $p->schedule = $getScheduledDate;
                $p->created_dt = date("Y-m-d");
                $p->created_by =  auth()->id();
                $p->doctor = $getCover;
                $p->patient_id = null;
                $pstatus = 'BLANK';
                if(empty($chck_px)){
                    $pstatus = false;
                }else{
                    $pstatus = $chck_px[0]->status;
                }
                $p->status =  $value['Customer'].' '.$getPhic.' '.$pstatus.' HAS NO RECORD. PLEASE ADD FIRST TO PATIENT MASTER FILE.';
                //$value['Customer'].' '.$value['phicno'].' '.(empty($chck_px)?'':$chck_px[0]->status).' HAS NO RECORD. PLEASE ADD FIRST TO PATIENT MASTER FILE.';
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
            $data =  DB::connection('mysql')->select("select s.*,s.id as schedule_id,p.* from schedule s left join patients p on s.patient_id = p.id where (p.name like '%".$val."%' or s.schedule = '".date('Y-m-d')."') and s.status = 'ACTIVE' order by s.schedule asc LIMIT $length offset $start ");
            $count =  DB::connection('mysql')->select("select s.*,s.id as schedule_id,p.* from schedule s left join patients p on s.patient_id = p.id where (p.name like '%".$val."%' or s.schedule = '".date('Y-m-d')."') and s.status = 'ACTIVE' order by s.schedule asc");
        }else{
            $data =  DB::connection('mysql')->select("select s.*,s.id as schedule_id,p.* from schedule s left join patients p on s.patient_id = p.id where s.schedule = '".date('Y-m-d')."' and s.status = 'ACTIVE' order by s.schedule asc LIMIT $length ");
            $count =  DB::connection('mysql')->select("select s.*,s.id as schedule_id,p.* from schedule s left join patients p on s.patient_id = p.id where s.schedule = '".date('Y-m-d')."' and s.status = 'ACTIVE' order by s.schedule asc");
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
        $check_data = Schedule::where(['patient_id'=>$request->patientid,'status'=>'ACTIVE','schedule'=>date_format(date_create($request->schedule),'Y-m-d')])->first();
        if(!$check_data){
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
           /*  $user = Auth::user()->id;
            return response()->json($user); */
            return response()->json(false);
        }else{
            return response()->json(true);
        } 
    }

    public function edit($id)
    {
        $data = Schedule::where(['id'=>$id])->first();
        return response()->json($data);
    }
    
    public function update(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $pxDctr = Patients::where(['id'=>$request->pxid])->first();
        
        $logs = new Transaction_log();
        $logs->action = 'Update by '.auth()->user()->name.' on '.date('F d, Y H:i:s');
        $logs->module = 'SCHEDULE';
        $logs->phic_id = $request->sessid;
        $logs->remarks = 'UPDATE SCHEDULE OF '.$pxDctr->name.' FROM '.$request->originalDate.' TO '.$request->schedule;
        $logs->save();

        Schedule::where(['id'=>$request->sessid])->update([
            'schedule'=> $request->schedule,
            'doctor'=> $request->doctor!=0?$request->doctor:$pxDctr->attending_doctor,
        ]);
        $copay = Copay::where(['date_session'=>$request->originalDate,'patient_id'=>$request->pxid,'status'=>'ACTIVE'])->first();
        Copay::where(['id'=>$copay->id])->update([
            'date_session'=> $request->schedule,
            'doctor'=> $request->doctor!=0?$request->doctor:$pxDctr->attending_doctor,
        ]);
        $phic = Phic::where(['date_session'=>$request->originalDate,'patient_id'=>$request->pxid,'state'=>'ACTIVE'])->first();
        Phic::where(['id'=>$phic->id])->update([
            'date_session'=> $request->schedule,
            'doctor'=> $request->doctor!=0?$request->doctor:$pxDctr->attending_doctor,
            'updated_by' =>  auth()->id(),
            'updated_dt' =>  date('Y-m-d'),
        ]);
        return $request->doctor!=0?$request->doctor:$pxDctr->attending_doctor;
    }

    public function Delete($id)
    {
        date_default_timezone_set('Asia/Manila');
        $get_schedule = Schedule::where(['id'=>$id])->first();
        $get_patient = Patients::where(['id'=>$get_schedule->patient_id])->first();
        
        $logs = new Transaction_log();
        $logs->action = 'Update by '.auth()->user()->name.' on '.date('F d, Y H:i:s');
        $logs->module = 'SCHEDULE';
        $logs->phic_id = $id;
        $logs->remarks = 'DELETE SCHEDULE OF '.$get_patient->name.' '.$get_schedule->schedule;
        $logs->save();

        Schedule::where(['id'=>$id])->update([
            'status'=> 'INACTIVE',
        ]);
        Copay::where(['patient_id'=>$get_schedule->patient_id,'date_session'=>$get_schedule->schedule,'status'=>'ACTIVE'])->update([
            'status'=> 'INACTIVE',
        ]);
        Phic::where(['patient_id'=>$get_schedule->patient_id,'date_session'=>$get_schedule->schedule,'state'=>'ACTIVE'])->update([
            'state'=> 'INACTIVE',
        ]);
        $p = new FailedSchedule;
        $p->status = 'DELETE SESSION OF '.$get_patient->name.' on '.$get_schedule->schedule;
        $p->created_dt = date("Y-m-d H:i");
        $p->schedule = $get_schedule->schedule;
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
