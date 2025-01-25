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

}
