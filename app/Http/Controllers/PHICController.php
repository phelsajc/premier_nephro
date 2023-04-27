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

class PHICController extends Controller
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
        $data = Phic::where(['id'=>$id])->first();
        return response()->json($data);
    }
    
    public function update(Request $request)
    {
        Phic::where(['id'=>$request->data['id']])->update([
            'status'=> $request->data['status']?'PAID':'UNPAID',
            'remarks'=> $request->data['remarks'],
            'updated_by' => auth()->id(),
            'updated_dt' => date('Y-m-d'),
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
        $date = date_format(date_create($request->data['date']),'Y-m');
        $date2 = date_format(date_create($request->data['date']),'Y-m-d');
        $doctors = $request->data['doctors'];
        $getDoctor = Doctors::where(["id"=>$doctors])->first();
        $doctors = $request->data['doctors'];
        if($doctors!='All'){
            $data =  DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'), count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where DATE_FORMAT(s.schedule, '%Y-%m') = '$date' and
                s.doctor = $doctors
                group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
            ");
            //$getPaidClaims = Phic::where(["date_session"=>$date2,"doctor"=>$doctors,"status"=>"PAID"])->get();
            $getPaidClaims =  DB::connection('mysql')->select("
                select * from phic where DATE_FORMAT(date_session, '%Y-%m') = '$date' and status = 'PAID' and doctor = $doctors
            ");
        }else{
            $data =  DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'), count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where DATE_FORMAT(s.schedule, '%Y-%m') = '$date' 
                group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
            ");
            $getPaidClaims =  DB::connection('mysql')->select("
                select * from phic where DATE_FORMAT(date_session, '%Y-%m') = '$date' and status = 'PAID'
            ");
            //$getPaidClaims = Phic::where(["date_session"=>$date2,"status"=>"PAID"])->get();
        }        
     
        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $get_dates = DB::connection('mysql')->select("
            SELECT schedule, patient_id from schedule
                where DATE_FORMAT(schedule, '%Y-%m') = '$date' and patient_id = '$value->patient_id'
            ");
            $date_of_sessions = '';
            $date_of_sessionsArr = array();
            foreach ($get_dates as $gkey => $gvalue) {
                $date_of_sessionsArr_set = array();
                $s_date = date_format(date_create($gvalue->schedule),'F d');
                $date_of_sessionsArr_set['date'] = $s_date;
                $data_sessions = Phic::where(['date_session'=>date_format(date_create($gvalue->schedule),'Y-m-d'),'patient_id'=>$gvalue->patient_id])->first();
                $date_of_sessionsArr_set['status'] = $data_sessions?$data_sessions->status:'';
                $date_of_sessionsArr_set['id'] =$data_sessions?$data_sessions->id:null;
                $date_of_sessions.=date_format(date_create($gvalue->schedule),'F d').', ';
                $date_of_sessionsArr[] = $date_of_sessionsArr_set;
            }
            $arr['name'] =  $value->name;
            $arr['sessions'] =  $value->cnt;
            $arr['paidSessions'] =  $value->cnt;
            $arr['dates'] =  $date_of_sessions;
            $arr['datesArr'] =  $date_of_sessionsArr;
            $data_array[] = $arr;
            
            $date_of_sessions = '';
        }
        $datasets = array();
        $datasets["data"]=$data_array;
        $datasets["Doctors"]=$getDoctor;
        $datasets["getPaidClaims"]= count($getPaidClaims);        
        return response()->json($datasets);
    }
}
