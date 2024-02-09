<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Collections;
use App\Model\Company;
use DB;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start ? $request->start : 0;
        $val = $request->searchTerm2;
        if ($val != '' || $start > 0) {
            $data = DB::connection('mysql')->select("select * from collections where si_dr_no like '%" . $val . "%' LIMIT $length offset $start");
            $count = DB::connection('mysql')->select("select * from collections where si_dr_no like '%" . $val . "%' ");
        } else {
            $data = DB::connection('mysql')->select("select * from collections LIMIT $length");
            $count = DB::connection('mysql')->select("select * from collections");
        }

        $count_all_record = DB::connection('mysql')->select("select count(*) as count from collections");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $companydata = Company::where(['id' => $value->companyid])->first();
            $arr['id'] = $value->id;
            $arr['type'] = $value->type;
            $arr['check_date'] = $value->check_date;
            $arr['company'] = $companydata->company;
            $arr['si_dr_no'] = $value->si_dr_no;
            $arr['amount'] = $value->amount;
            $arr['details'] = $value->details;
            $arr['with_ewt_deductions'] = $value->with_ewt_deductions;
            $arr['date_deposited'] = $value->date_deposited;
            $arr['crno'] = $value->crno;
            $arr['dsno'] = $value->dsno;
            $data_array[] = $arr;
        }
        $page = sizeof($count) / $length;
        $getDecimal = explode(".", $page);
        $page_count = round(sizeof($count) / $length);
        if (sizeof($getDecimal) == 2) {
            if ($getDecimal[1] < 5) {
                $page_count = $getDecimal[0] + 1;
            }
        }
        $datasets = array(["data" => $data_array, "count" => $page_count, "showing" => "Showing " . (($start + 10) - 9) . " to " . ($start + 10 > $count_all_record[0]->count ? $count_all_record[0]->count : $start + 10) . " of " . $count_all_record[0]->count, "patient" => $data_array]);
        return response()->json($datasets);
    }

    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $p = new Collections;
        $p->type = $request->type;
        $p->check_date = date_create($request->chequeDate);
        $p->companyid = $request->company;
        $p->si_dr_no = $request->sidr;
        $p->amount = $request->amount;
        $p->details = $request->details;
        $p->with_ewt_deductions = $request->ewt;
        $p->date_deposited = date_create($request->dateDeposited);
        $p->crno = $request->crno;
        $p->dsno = $request->dsno;
        $p->created_by = $request->userid;
        $p->created_dt = date("Y-m-d");
        $p->save();
        return true;
    }

    public function edit($id)
    {
        $data = Collections::where(['id' => $id])->first();
        return response()->json($data);
    }

    public function update(Request $request)
    {
        Collections::where(['id' => $request->id])->update([
            'type' => $request->data['type'],
            'check_date' => $request->data['chequeDate'],
            'companyid' => $request->data['company'],
            'si_dr_no' => $request->data['sidr'],
            'amount' => $request->data['amount'],
            'details' => $request->data['details'],
            'with_ewt_deductions' => $request->data['ewt'],
            'date_deposited' => $request->data['dateDeposited'],
            'crno' => $request->data['crno'],
            'dsno' => $request->data['dsno'],
            'updated_by' => $request->data['userid'],
            'updated_dt' => date('Y-m-d'),
        ]);
        return true;
    }

    public function Delete($id)
    {
        Collections::where('id', $id)->delete();
        return true;
    }

    public function reports(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $date = date_format(date_create($request->items['date']), 'Y-m-d');
        $type = $request->items['type'];
        if ($type == "BOTH") {
            $data = DB::connection('mysql')->select("select * from collections where date(date_deposited) = date('$date')");
        } else {
            $data = DB::connection('mysql')->select("select * from collections where date(date_deposited) = date('$date') and type='$type'");
        }

        $data_array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $total = 0;
            $companydata = Company::where(['id' => $value->companyid])->first();
            $arr['type'] = $value->type;
            $arr['check_date'] = $value->check_date;
            $arr['company'] = $companydata->company;
            $arr['si_dr_no'] = $value->si_dr_no;
            $arr['amount'] = $value->amount;
            $arr['details'] = $value->details;
            $arr['with_ewt_deductions'] = $value->with_ewt_deductions;
            $arr['date_deposited'] = $value->date_deposited;
            $arr['crno'] = $value->crno;
            $arr['dsno'] = $value->dsno;
            $data_array[] = $arr;
        }
        $datasets = array(["data" => $data_array, "date" => $date,]);
        return response()->json($data_array);
    }
}
