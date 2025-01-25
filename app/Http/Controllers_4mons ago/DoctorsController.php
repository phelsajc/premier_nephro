<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Doctors;
use DB;

class DoctorsController extends Controller
{  
    function import(Request $request)
    {
        foreach ($request->data as $row => $value) {            
            $p = new Doctors;
            $p->name = $value['name'];
            $p->save();   
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
            $data =  DB::connection('mysql')->select("select * from doctors where name like '%".$val."%' LIMIT $length offset $start");
            $count =  DB::connection('mysql')->select("select * from doctors where name like '%".$val."%' ");
        }else{
            $data =  DB::connection('mysql')->select("select * from doctors LIMIT $length");
            $count =  DB::connection('mysql')->select("select * from doctors");
        }
        
        $count_all_record =  DB::connection('mysql')->select("select count(*) as count from doctors");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['id'] =  $value->id;
            $arr['name'] =  $value->name;
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
        $p = new Doctors;
        $p->name = $request->name;
        $p->save();         
        return true;
    }

    public function edit($id)
    {
        $data = Doctors::where(['id'=>$id])->first();
        return response()->json($data);
    }
    
    public function update(Request $request)
    {
        Doctors::where(['id'=>$request->id])->update([
            'name'=> $request->data['name'],
        ]);
        return true;
    }

    public function Delete($id)
    {
        Doctors::where('id',$id)->delete();
        return true;
    }  

    public function getDoctors()
    {
        $p = Doctors::all();
        return response()->json($p);
    } 
}
