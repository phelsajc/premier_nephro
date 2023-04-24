<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Company;
use DB;

class CompanyController extends Controller
{  
    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start?$request->start:0;
        $val = $request->searchTerm2;
        if($val!=''||$start>0){   
            $data =  DB::connection('mysql')->select("select * from company where company like '%".$val."%' LIMIT $length offset $start");
            $count =  DB::connection('mysql')->select("select * from company where company like '%".$val."%' ");
        }else{
            $data =  DB::connection('mysql')->select("select * from company LIMIT $length");
            $count =  DB::connection('mysql')->select("select * from company");
        }
        
        $count_all_record =  DB::connection('mysql')->select("select count(*) as count from company");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['company'] =  $value->company;
            $arr['desc'] =  $value->description;
            $arr['id'] =  $value->id;
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
        $p = new Company;
        $p->company = $request->company;
        $p->description = $request->desc;
        $p->address = $request->address; 
        $p->save(); 
        
        return true;
    }

    public function edit($id)
    {
        $data = Company::where(['id'=>$id])->first();
        return response()->json($data);
    }
    
    public function update(Request $request)
    {
        Company::where(['id'=>$request->id])->update([
            'company'=> $request->data['company'],
            'description'=> $request->data['desc'],
            'address'=> $request->data['address'],
        ]);
        return true;
    }

    public function Delete($id)
    {
        Company::where('id',$id)->delete();
        return true;
    }

    public function getCompanies()
    {
        $p = Company::all();
        return response()->json($p);
    }
   
}
