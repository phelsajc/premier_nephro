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

class CensusController extends Controller
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
        $doctors = $request->data['doctors'];
        $doctors = $request->data['doctors'];
        if($doctors!='All'){
            $data =  DB::connection('mysql')->select("
                select p.name,s.schedule from schedule s 
                left join patients p on s.patient_id = p.id
                where DATE_FORMAT(s.schedule, '%Y-%m') = '$date' and
                s.doctor = $doctors and s.status = 'ACTIVE'
                order by s.schedule ASC;
            ");
        }else{
            $data =  DB::connection('mysql')->select("
            select p.name,s.schedule from schedule s 
                left join patients p on s.patient_id = p.id
                where DATE_FORMAT(s.schedule, '%Y-%m') = '$date' and s.status = 'ACTIVE'
                order by s.schedule ASC;
            ");
        }
        $data_array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['name'] =  $value->name;
            $arr['dates'] =  date_format(date_create($value->schedule),'m/d/Y');
            $data_array[] = $arr;
        }
        return response()->json($data_array);
    }

    public function revenue(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->data['fdate']),'Y-m');
        $tdate = date_format(date_create($request->data['tdate']),'Y-m');
        
        $data =  DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule FROM `schedule` s where s.status = 'ACTIVE' 
            and DATE_FORMAT(s.schedule, '%Y-%m') between '$fdate' and '$tdate' 
            group by DATE_FORMAT(s.schedule, '%Y-%m');
        ");     
     
        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['month'] =  date_format(date_create($value->schedule),'F');
            $session = $value->cnt;
            $arr['sessions'] =  $session;
            $gross = 2250 * $session;
            $share = $gross * 0.25;
            $tax = $share * 0.05;
            $net = $share * 0.95;

            $arr['gross'] =  $gross;
            $arr['share'] = $share;
            $arr['tax'] = $tax;
            $arr['net'] = $net;
            $data_array[] = $arr;
        }
        $datasets = array(["data"=>$data_array]);
        return response()->json($data_array);
    }
}
