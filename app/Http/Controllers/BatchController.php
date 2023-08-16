<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Batch;
use DB;

class BatchController extends Controller
{  
    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start?$request->start:0;
        $val = $request->searchTerm2;
        if($val!=''||$start>0){   
            $data =  DB::connection('mysql')->select("select * from batch where batch like '%".$val."%' LIMIT $length offset $start");
            $count =  DB::connection('mysql')->select("select * from batch where batch like '%".$val."%' ");
        }else{
            $data =  DB::connection('mysql')->select("select * from batch LIMIT $length");
            $count =  DB::connection('mysql')->select("select * from batch");
        }
        
        $count_all_record =  DB::connection('mysql')->select("select count(*) as count from batch");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['id'] =  $value->id;
            $arr['batch'] =  $value->batch;
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
        
        $datasets["data"] = $data_array;
        $datasets["count"] = $page_count;
        $datasets["showing"] = "Showing ".(($start+10)-9)." to ".($start+10>$count_all_record[0]->count?$count_all_record[0]->count:$start+10)." of ".$count_all_record[0]->count;
        $datasets["patient"] = $data_array;
        return response()->json($datasets);
    } 

    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $p = new Batch;
        $p->batch = $request->batch;
        $p->save();         
        return true;
    }

    public function edit($id)
    {
        $data = Batch::where(['id'=>$id])->first();
        return response()->json($data);
    }
    
    public function update(Request $request)
    {
        Batch::where(['id'=>$request->id])->update([
            'batch'=> $request->batch,
        ]);
        return true;
    }

    public function Delete($id)
    {
        Batch::where('id',$id)->delete();
        return true;
    }  
}
