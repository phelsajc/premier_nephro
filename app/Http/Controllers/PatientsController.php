<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Patients;
use App\Model\Doctors;
use DB;

class PatientsController extends Controller
{  
    function import(Request $request)
    {
        //oreach ($request->data as $row => $value) {    
        foreach ((array) $request->data  as $value) {
            if($value['Customer']!=null||$value['Customer']!="") {
                $p = new Patients;
                $p->name = $value['Customer'];
                $p->birthdate = date_create($value['DOB']);
                $p->city = $value['City'];
                $p->address = $value['Address'];
                $p->contact_no = $value['Contact'];
                $p->phic = $value['Philhealth_No'];
                $p->phic = $value['Philhealth_No'];
                $p->patient_type = $value['Type'];
                $p->civil_status = $value['Civil_Status'];
                $p->created_dt = date("Y-m-d");
                $chck_dct = DB::connection('mysql')->select("select * from doctors where name like '%".$value['Doctor']."%'");
                $p->attending_doctor = $chck_dct?$chck_dct[0]->id:null;
                $p->save();   
            }       
        }
        return true;        
    }

    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start?$request->start:0;
        $val = $request->searchTerm2;
        if($val!=''||$start>0){   
            $data =  DB::connection('mysql')->select("select * from patients where name like '%".$val."%' LIMIT $length offset $start");
            $count =  DB::connection('mysql')->select("select * from patients where name like '%".$val."%' ");
        }else{
            $data =  DB::connection('mysql')->select("select * from patients LIMIT $length");
            $count =  DB::connection('mysql')->select("select * from patients");
        }
        
        $count_all_record =  DB::connection('mysql')->select("select count(*) as count from patients");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['id'] =  $value->id;
            $arr['name'] =  $value->name;
            $arr['dob'] =  $value->birthdate;
            $arr['sex'] =  $value->sex;
            $arr['doctor'] =  $value->attending_doctor;
            $arr['address'] =  $value->address;
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
        $datasets = array(["data"=>$data_array,"count"=>$page_count,"showing"=>"Showing ".(($start+10)-9)." to ".($start+10>$count_all_record[0]->count?$count_all_record[0]->count:$start+10)." of ".$count_all_record[0]->count, "patient"=>$data_array]);
        return response()->json($datasets);
    } 

    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $p = new Patients;
        $p->name = $request->name;
        $p->birthdate = date_create($request->dob);
        $p->sex = $request->sex;
        $p->address = $request->address;
        $p->contact_no = $request->contact;
        $p->attending_doctor = $request->doctor;
        $p->status = $request->status;
        $p->save();         
        return true;
    }

    public function edit($id)
    {
        $data = Patients::where(['id'=>$id])->first();
        return response()->json($data);
    }
    
    public function update(Request $request)
    {
        Patients::where(['id'=>$request->id])->update([
            'name'=> $request->data['name'],
            'birthdate'=> $request->data['dob'],
            'sex'=> $request->data['sex'],
            'address'=> $request->data['address'],
            'contact_no'=> $request->data['contact'],
            'attending_doctor'=> $request->data['doctor'],
            'status'=> $request->data['status'],
        ]);
        return true;
    }

    public function Delete($id)
    {
        Patients::where('id',$id)->delete();
        return true;
    }  

    public function find(Request $request)
    {
        $data =  DB::connection('mysql')->select("select * from patients where name like '%".$request->searchVal."%' ");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['id'] =  $value->id;
            $arr['name'] =  $value->name;;
            $data_array[] = $arr;
        }
        //$datasets = array(["data"=>$data_array,"count"=>$page_count,"showing"=>"Showing ".(($start+10)-9)." to ".($start+10>$count_all_record[0]->count?$count_all_record[0]->count:$start+10)." of ".$count_all_record[0]->count, "patient"=>$data_array]);
        return response()->json($data_array);
    }
}
