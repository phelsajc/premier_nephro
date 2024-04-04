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

    function import(Request $request)
    {

        foreach ($request->data as $row => $value) {
            $p = new Schedule;
            $p->schedule = date_create($value['Schedule']);
            $p->created_dt = date("Y-m-d");
            $p->created_by = auth()->id();
            $chck_dct = DB::connection('mysql')->select("select * from doctors where name like '%" . $value['Incharge'] . "%'");
            $chck_px = DB::connection('mysql')->select("select * from patients where name like '%" . $value['Customer'] . "%'");
            $p->doctor = $value['Incharge'] != '' || $value['Incharge'] != null ? $chck_dct[0]->id : $chck_px[0]->attending_doctor;
            $p->patient_id = $chck_px ? $chck_px[0]->id : 0;
            $p->save();

            $c = new Copay;
            $c->date_session = date_create($value['Schedule']);
            $c->created_dt = date("Y-m-d");
            $c->created_by = auth()->id();
            $c->doctor = $p->doctor;
            $c->patient_id = $p->patient_id;
            $c->save();

            $ph = new Phic;
            $ph->date_session = date_create($value['Schedule']);
            $ph->created_dt = date("Y-m-d");
            $ph->created_by = auth()->id();
            $ph->doctor = $p->doctor;
            $ph->patient_id = $p->patient_id;
            $ph->save();
        }
    }

    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start ? $request->start : 0;
        $val = $request->searchTerm2;
        if ($val != '' || $start > 0) {
            $data = DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where p.name like '%" . $val . "%' LIMIT $length offset $start");
            $count = DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where p.name like '%" . $val . "%' ");
        } else {
            $data = DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where s.schedule = '" . date('Y-m-d') . "' LIMIT $length");
            $count = DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where s.schedule = '" . date('Y-m-d') . "'");
        }

        $count_all_record = DB::connection('mysql')->select("select  count(*) as count from schedule s left join patients p on s.patient_id = p.id ");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['id'] = $value->id;
            $arr['name'] = $value->name;
            $incharge = Doctors::where(['id' => $value->doctor])->first();
            $attending_doctor = Doctors::where(['id' => $value->attending_doctor])->first();
            $arr['incharge_dctr'] = $incharge ? $incharge->name : '';
            $arr['attending_dctr'] = $attending_doctor->name;
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
                "patient" => $data_array
            ]
        );
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
        $data = Schedule::where(['id' => $id])->first();
        return response()->json($data);
    }

    public function update(Request $request)
    {
        Schedule::where(['id' => $request->id])->update([
            'name' => $request->data['name'],
        ]);
        return true;
    }

    public function Delete($id)
    {
        Schedule::where('id', $id)->delete();
        return true;
    }

    public function getDoctors()
    {
        $p = Schedule::all();
        return response()->json($p);
    }

    public function report_original2(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        $doctors = $request->doctors;
        $getDoctor = Doctors::where(["id" => $doctors])->first();
        if ($doctors != 'All') {
            $data = DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'),p.attending_doctor, count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id,s.doctor
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and
                s.doctor = $doctors and s.status = 'ACTIVE'
                group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
            ");

            $data_array = array();
            $data_array_export = array();
            $TotalNet = 0;
            $totalSesh = 0;
            $checkForVaron = false;

            foreach ($data as $key => $value) {
                $arr = array();
                $arr_export = array();
                $get_dates = DB::connection('mysql')->select("
                SELECT schedule from schedule
                    where schedule between '$fdate' and '$tdate' and patient_id = '$value->patient_id'  and status = 'ACTIVE'
                ");
                $date_of_sessions = '';
                $date_of_sessionsArr = array();
                foreach ($get_dates as $gkey => $gvalue) {
                    $date_of_sessionsArr[] = date_format(date_create($gvalue->schedule), 'F d');
                    $date_of_sessions .= date_format(date_create($gvalue->schedule), 'F d') . ', ';
                }
                $checkDr = Doctors::where(['id' => $value->doctor])->first();
                $checkAttndgDr = Doctors::where(['id' => $value->attending_doctor])->first();

                if ($value->doctor == 6) {
                    $checkForVaron = true;
                }

                $arr['name'] = $value->name;
                $arr['sessions'] = $value->cnt;
                $arr['dates'] = $date_of_sessions;
                $arr['datesArr'] = $date_of_sessionsArr;
                $data_array[] = $arr;
                $arr_export['Date'] = date_format(date_create($value->schedule), 'm/d/Y');
                $arr_export['Name'] = $value->name;
                $arr_export['NEPHROLOGIST'] = $checkAttndgDr ? $checkAttndgDr->name : '';
                $arr_export['PF'] = 150;
                $arr_export['T/C'] = $value->doctor != $value->attending_doctor ? 'To Cover by ' . $checkDr->name : '';
                $arr_export['tc'] = $value->doctor != $value->attending_doctor ? 'To Cover by ' . $checkDr->name : '';
                $arr_export[''] = $value->cnt;
                $arr_export['cnt'] = $value->cnt;
                $arr_export['Dates'] = $date_of_sessions;
                $total_copay = count($get_dates) * 150;
                $net = $total_copay * 0.9;
                $data_array_export[] = $arr_export;
                $TotalNet += $net;
                $totalSesh += $value->cnt;
                $date_of_sessions = '';
            }

            $datasets = array();

            $arr_export = array();
            $arr_export['Date'] = '';
            $arr_export['Name'] = '';
            $arr_export['NEPHROLOGIST'] = 'Totalx';
            $tOTALSessionAMount = $totalSesh * 150;
            $arr_export['PF'] = $tOTALSessionAMount;
            $arr_export['T/C'] = '';
            $arr_export[''] = $totalSesh;
            $arr_export['cnt'] = $totalSesh;
            $arr_export['Dates'] = '';
            $data_array_export[] = $arr_export;

            if ($checkForVaron) {
                $tOTALSessionAMountNet = $tOTALSessionAMount * 0.95;
                $arr_export['Date'] = '';
                $arr_export['Name'] = '';
                $arr_export['NEPHROLOGIST'] = 'Less WT(5%)';
                $arr_export['PF'] = $tOTALSessionAMount * 0.05;
                $arr_export['T/C'] = '';
                $arr_export[''] = '';
                $arr_export['Dates'] = '';
                $data_array_export[] = $arr_export;
            } else {
                $tOTALSessionAMountNet = $tOTALSessionAMount * 0.9;
                $arr_export['Date'] = '';
                $arr_export['Name'] = '';
                $arr_export['NEPHROLOGIST'] = 'Less WT(10%)';
                $arr_export['PF'] = $tOTALSessionAMount * 0.1;
                $arr_export['T/C'] = '';
                $arr_export[''] = '';
                $arr_export['Dates'] = '';
                $data_array_export[] = $arr_export;
            }


            $arr_export['Date'] = '';
            $arr_export['Name'] = '';
            $arr_export['NEPHROLOGIST'] = 'NET';
            $arr_export['PF'] = $tOTALSessionAMountNet;
            $arr_export['T/C'] = '';
            $arr_export[''] = '';
            $arr_export['Dates'] = '';
            $data_array_export[] = $arr_export;

            $datasets["data"] = $data_array;
            //$datasets["data2"] = $data;
            $datasets["export"] = $data_array_export;
            $datasets["Doctors"] = $getDoctor;
            $reportTitle = date_format(date_create($request->fdate), 'F Y');
            $datasets["month"] = $reportTitle;
            return response()->json($datasets);
        } else {
            $doctors = Doctors::all();
            $data_array_export = array();
            $totalNet = 0;

            /* $arr_export = array();
            $arr_export['Nephologist'] = '';
            $arr_export['# of Session'] =  '';
            $arr_export['Amount per Session'] = '';
            $arr_export['Total Amount'] = '';
            $arr_export['Less WTX (10%)/(5%)'] = '';
            $arr_export['Net'] = 'Net';
            $data_array_export[] = $arr_export; */

            $arr_export = array();
            $arr_export['Nephologist'] = '';
            $arr_export['# of session'] = '';
            $arr_export['Amount per'] = 'Session';
            $arr_export['Total Amount'] = '';
            $arr_export['Less WTX'] = '(10%)/(5%)';
            $arr_export['net'] = '';
            $data_array_export[] = $arr_export;

            foreach ($doctors as $key => $value) {
                # code...
                $arr = array();
                $arr_export = array();

                /* $get_sessions =  DB::connection('mysql')->select("
                SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'),p.attending_doctor, count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id,s.doctor
                    FROM `schedule` s
                    left join patients p on s.patient_id = p.id
                    where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
                    group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id,p.attending_doctor  order  by p.attending_doctor;
                "); */
                $get_sessions = DB::connection('mysql')->select("
                SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'),p.attending_doctor, count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id,s.doctor
                    FROM `schedule` s
                    left join patients p on s.patient_id = p.id
                    where s.schedule between '$fdate' and '$tdate' and
                    s.doctor = $value->id and s.status = 'ACTIVE'
                    group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
                ");

                $getCnt = 0;
                foreach ($get_sessions as $skey => $svalue) {
                    # code...
                    $getCnt += $svalue->cnt;
                }

                $arr['name'] = $value->name;
                $arr['sessions'] = $getCnt; //sizeof($get_sessions);
                $arr['session'] = $getCnt; //sizeof($get_sessions);
                $total_Amt = $getCnt * 150;
                $arr['total_amount'] = $total_Amt;
                $arr['less_wtx'] = $value->id == 6 ? $total_Amt * 0.05 : $total_Amt * 0.1;
                $arr['net'] = $value->id == 6 ? $total_Amt * 0.95 : $total_Amt * 0.9;
                $data_array[] = $arr;


                $arr_export['Nephologist'] = $value->name;
                $arr_export['# of session'] = $getCnt; //sizeof($get_sessions);
                $arr_export['Amount per'] = 150.00;
                $arr_export['Total Amount'] = $total_Amt;
                $arr_export['Less WTX'] = $value->id == 6 ? $total_Amt * 0.05 : $total_Amt * 0.1;
                $tnet = $value->id == 6 ? $total_Amt * 0.95 : $total_Amt * 0.9;
                $arr_export['net'] = $tnet;
                $data_array_export[] = $arr_export;
                $totalNet += $tnet;
            }

            $datasets = array();
            $datasets["data"] = $data_array;

            $arr_export = array();
            $arr_export['Nephologist'] = 'Total';
            $arr_export['# of session'] = '';
            $arr_export['Amount per'] = '';
            $arr_export['Total Amount'] = '';
            $arr_export['Less WTX'] = '';
            $arr_export['net'] = $totalNet;
            $data_array_export[] = $arr_export;

            $datasets["export"] = $data_array_export;
            $reportTitle = date_format(date_create($request->fdate), 'F Y');
            $datasets["month"] = $reportTitle;
            return response()->json($datasets);
        }
    }

    public function report(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        $doctors = $request->doctors;
        $getDoctor = Doctors::where(["id" => $doctors])->first();
        if ($doctors != 'All') {
            $data = DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,p.attending_doctor, s.patient_id,s.schedule,s.id,s.doctor
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and
                s.doctor = $doctors and s.status = 'ACTIVE';
            ");

            $data_array = array();
            $data_array_export = array();
            $TotalNet = 0;
            $totalSesh = 0;
            $checkForVaron = false;
            $checkForMarba = false;

            foreach ($data as $key => $value) {
                $arr = array();
                $arr_export = array();
                $get_dates = DB::connection('mysql')->select("
                SELECT schedule from schedule
                    where schedule between '$fdate' and '$tdate' and patient_id = '$value->patient_id'  and status = 'ACTIVE'
                ");
                $date_of_sessions = '';
                $date_of_sessionsArr = array();
                $count_per_patient = 0;
                foreach ($get_dates as $gkey => $gvalue) {
                    $date_of_sessionsArr[] = date_format(date_create($gvalue->schedule), 'F d');
                    $date_of_sessions .= date_format(date_create($gvalue->schedule), 'F d') . ', ';
                    $count_per_patient += 1;
                }
                $checkDr = Doctors::where(['id' => $value->doctor])->first();
                $checkAttndgDr = Doctors::where(['id' => $value->attending_doctor])->first();

                /* if ($value->doctor == 6) {
                    $checkForVaron = true;
                } */
                
                if ($value->doctor == 5) {
                    $checkForMarba = true;
                } 

                $arr['name'] = $value->name;
                $arr['sessions'] = '';
                $arr['dates'] = date_format(date_create($value->schedule), 'F d');
                $arr['datesArr'] = $date_of_sessionsArr;
                $data_array[] = $arr;
                $arr_export['Date'] = date_format(date_create($value->schedule), 'm/d/Y');
                $arr_export['Name'] = $value->name;
                $arr_export['NEPHROLOGIST'] = $checkAttndgDr ? $checkAttndgDr->name : '';
                $arr_export['PF'] = 150;
                $arr_export['T/C'] = $value->doctor != $value->attending_doctor ? 'To Cover by ' . $checkDr->name : '';
                $arr_export['tc'] = $value->doctor != $value->attending_doctor ?  $checkDr->name : '';
                $arr_export[''] = '';
                $total_copay = count($get_dates) * 150;
                $net = $total_copay * 0.9;
                $data_array_export[] = $arr_export;
                $TotalNet += $net;
            }

            $datasets = array();

            $arr_export = array();
            $arr_export['Date'] = '';
            $arr_export['Name'] = '';
            $arr_export['NEPHROLOGIST'] = 'Total';
            $tOTALSessionAMount = sizeof($data) * 150; 
            $arr_export['PF'] = $tOTALSessionAMount;
            $arr_export['T/C'] = '';
            $arr_export[''] = sizeof($data); 
            $arr_export['cnt'] = '';//sizeof($data);
            $arr_export['Dates'] = '';
            $data_array_export[] = $arr_export;

            if ($checkForVaron||$checkForMarba) {
                $tOTALSessionAMountNet = $tOTALSessionAMount * 0.95;
                $arr_export['Date'] = '';
                $arr_export['Name'] = '';
                $arr_export['NEPHROLOGIST'] = 'Less WT(5%)';
                $arr_export['PF'] = $tOTALSessionAMount * 0.05;
                $arr_export['T/C'] = '';
                $arr_export[''] = '';
                $arr_export['Dates'] = '';
                $arr_export['cnt'] = '';
                $data_array_export[] = $arr_export;
            } else {
                $tOTALSessionAMountNet = $tOTALSessionAMount * 0.9;
                $arr_export['Date'] = '';
                $arr_export['Name'] = '';
                $arr_export['NEPHROLOGIST'] = 'Less WT(10%)';
                $arr_export['PF'] = $tOTALSessionAMount * 0.1;
                $arr_export['T/C'] = '';
                $arr_export[''] = '';
                $arr_export['cnt'] = '';
                $arr_export['Dates'] = '';
                $data_array_export[] = $arr_export;
            }


            $arr_export['Date'] = '';
            $arr_export['Name'] = '';
            $arr_export['NEPHROLOGIST'] = 'NET';
            $arr_export['cnt'] = '';
            $arr_export['PF'] = $tOTALSessionAMountNet;
            $arr_export['T/C'] = '';
            $arr_export[''] = '';
            $arr_export['Dates'] = '';
            $data_array_export[] = $arr_export;

            $datasets["data"] = $data_array;
            $datasets["sessions"] = sizeof($data);
            //$datasets["data2"] = $data;
            $datasets["export"] = $data_array_export;
            $datasets["Doctors"] = $getDoctor;
            $reportTitle = date_format(date_create($request->fdate), 'F Y');
            $datasets["month"] = $reportTitle;
            return response()->json($datasets);
        } else {
            $doctors = Doctors::all();
            $data_array_export = array();
            $totalNet = 0;

            /* $arr_export = array();
            $arr_export['Nephologist'] = '';
            $arr_export['# of Session'] =  '';
            $arr_export['Amount per Session'] = '';
            $arr_export['Total Amount'] = '';
            $arr_export['Less WTX (10%)/(5%)'] = '';
            $arr_export['Net'] = 'Net';
            $data_array_export[] = $arr_export; */

            $arr_export = array();
            $arr_export['Nephologist'] = '';
            $arr_export['# of session'] = '';
            $arr_export['Amount per'] = 'Session';
            $arr_export['Total Amount'] = '';
            $arr_export['Less WTX'] = '(10%)/(5%)';
            $arr_export['net'] = '';
            $data_array_export[] = $arr_export;

            foreach ($doctors as $key => $value) {
                # code...
                $arr = array();
                $arr_export = array();

                /* $get_sessions =  DB::connection('mysql')->select("
                SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'),p.attending_doctor, count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id,s.doctor
                    FROM `schedule` s
                    left join patients p on s.patient_id = p.id
                    where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
                    group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id,p.attending_doctor  order  by p.attending_doctor;
                "); */
                $get_sessions = DB::connection('mysql')->select("
                SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'),p.attending_doctor, count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id,s.doctor
                    FROM `schedule` s
                    left join patients p on s.patient_id = p.id
                    where s.schedule between '$fdate' and '$tdate' and
                    s.doctor = $value->id and s.status = 'ACTIVE'
                    group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
                ");

                $getCnt = 0;
                foreach ($get_sessions as $skey => $svalue) {
                    # code...
                    $getCnt += $svalue->cnt;
                }

                $arr['name'] = $value->name;
                $arr['sessions'] = $getCnt; //sizeof($get_sessions);
                $arr['session'] = $getCnt; //sizeof($get_sessions);
                $arr['copay_amount'] = 150; // $getCnt; //sizeof($get_sessions);
                $total_Amt = $getCnt * 150;
                $arr['total_amount'] = number_format($total_Amt, 2);
                $lwt = $value->id == 6 ? $total_Amt * 0.05 : $total_Amt * 0.1;
                $arr['less_wtx'] = number_format($lwt, 2);
                $net = $value->id == 6 ? $total_Amt * 0.95 : $total_Amt * 0.9;
                $arr['net'] = number_format($net, 2);
                $data_array[] = $arr;


                $arr_export['Nephologist'] = $value->name;
                $arr_export['# of session'] = number_format($getCnt, 2); //sizeof($get_sessions);
                $arr_export['Amount per'] = 150.00;
                $arr_export['Total Amount'] = $total_Amt;
                $arr_export['Less WTX'] = $value->id == 6 ? $total_Amt * 0.05 : $total_Amt * 0.1;
                $tnet = $value->id == 6 ? $total_Amt * 0.95 : $total_Amt * 0.9;
                $data_array_export[] = $arr_export;
                $totalNet += $tnet;
            }

            $datasets = array();


            $datasets["data"] = $data_array;

            $arr_export = array();
            $arr_export['Nephologist'] = 'Total';
            $arr_export['# of session'] = '';
            $arr_export['Amount per'] = '';
            $arr_export['Total Amount'] = '';
            $arr_export['Less WTX'] = '';
            $arr_export['net'] = $totalNet;


            $data_array_export[] = $arr_export;



            $datasets["export"] = $data_array_export;
            $myPdf = new HomeInstructionCovidPdf($data_array_export);
            $reportTitle = date_format(date_create($request->fdate), 'F Y');
            $datasets["month"] = $reportTitle;
            return response()->json($datasets);
        }
    }

    public function Exportreport(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        $doctors = $request->doctors;
        $getDoctor = Doctors::where(["id" => $doctors])->first();

        $doctors = Doctors::all();
        $data_array_export = array();
        $totalNet = 0;

        $arr_export = array();
        $arr_export['Nephologist'] = '';
        $arr_export['# of session'] = '';
        $arr_export['Amount per'] = 'Session';
        $arr_export['Total Amount'] = '';
        $arr_export['Less WTX'] = '(10%)/(5%)';
        $arr_export['net'] = '';
        $data_array_export[] = $arr_export;

        foreach ($doctors as $key => $value) {
            $arr = array();
            $arr_export = array();

            $get_sessions = DB::connection('mysql')->select("
                SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'),p.attending_doctor, count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id,s.doctor
                    FROM `schedule` s
                    left join patients p on s.patient_id = p.id
                    where s.schedule between '$fdate' and '$tdate' and
                    s.doctor = $value->id and s.status = 'ACTIVE'
                    group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
                ");

            $getCnt = 0;
            foreach ($get_sessions as $skey => $svalue) {
                $getCnt += $svalue->cnt;
            }

            $arr['name'] = $value->name;
            $arr['sessions'] = $getCnt;
            $arr['session'] = $getCnt;
            $total_Amt = $getCnt * 150;
            $arr['total_amount'] = $total_Amt;
            $arr['less_wtx'] = $value->id == 6 ? $total_Amt * 0.05 : $total_Amt * 0.1;
            $arr['net'] = $value->id == 6 ? $total_Amt * 0.95 : $total_Amt * 0.9;
            $data_array[] = $arr;
            $arr_export['Nephologist'] = $value->name;
            $arr_export['# of session'] = $getCnt;
            $arr_export['Amount per'] = 150.00;
            $arr_export['Total Amount'] = $total_Amt;
            $arr_export['Less WTX'] = $value->id == 6 ? $total_Amt * 0.05 : $total_Amt * 0.1;
            $tnet = $value->id == 6 ? $total_Amt * 0.95 : $total_Amt * 0.9;
            $arr_export['net'] = $tnet;
            $data_array_export[] = $arr_export;
            $totalNet += $tnet;
        }

        $datasets = array();
        $datasets["data"] = $data_array;
        $arr_export = array();
        $arr_export['Nephologist'] = 'Total';
        $arr_export['# of session'] = '';
        $arr_export['Amount per'] = '';
        $arr_export['Total Amount'] = '';
        $arr_export['Less WTX'] = '';
        $arr_export['net'] = $totalNet;
        $data_array_export[] = $arr_export;
        $datasets["export"] = $data_array_export;
        $myPdf = new HomeInstructionCovidPdf();
        $myPdf->Output('I', "ChartRecordPdf.pdf", true);
        exit;
    }
}
