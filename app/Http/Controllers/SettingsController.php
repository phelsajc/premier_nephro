<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Settings;
use DB;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start ? $request->start : 0;
        $val = $request->searchTerm2;
        if ($val != '' || $start > 0) {
            $data = DB::connection('mysql')->select("select * from settings where name like '%" . $val . "%' LIMIT $length offset $start");
            $count = DB::connection('mysql')->select("select * from settings where name like '%" . $val . "%' ");
        } else {
            $data = DB::connection('mysql')->select("select * from settings LIMIT $length");
            $count = DB::connection('mysql')->select("select * from settings");
        }
        $count_all_record = DB::connection('mysql')->select("select count(*) as count from settings");
        $data_array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['id'] = $value->id;
            $arr['name'] = $value->name;
            $arr['value'] = $value->value;
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
        $datasets = array(
            [
                "data" => $data_array,
                "count" => $page_count,
                "showing" =>
                    sizeof($count_all_record) > 0 ?
                    "Showing " . (($start + 10) - 9) . " to " . ($start + 10 > $count_all_record[0]->count ?
                        $count_all_record[0]->count :
                        $start + 10) . " of " . $count_all_record[0]->count : '',
            ]
        );
        return response()->json($datasets);
    }

    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $p = new Settings;
        $p->name = $request->name;
        $p->value = $request->value;
        $p->save();
        return true;
    }

    public function edit($id)
    {
        $data = Settings::where(['id' => $id])->first();
        return response()->json($data);
    }

    public function update(Request $request)
    {
        Settings::where(['id' => $request->id])->update([
            'name' => $request->name,
            'value' => $request->value,
        ]);
        return true;
    }

    public function Delete($id)
    {
        Settings::where('id', $id)->delete();
        return true;
    }
}
