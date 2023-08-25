<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Model\Schedule;
use App\Model\Copay;
use App\Model\Doctors;
use App\Model\Patients;
use App\Model\Transaction_log;
use App\Model\Phic;
use App\User;
use Helper; 
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
        date_default_timezone_set('Asia/Manila');
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
            $chck_dct = DB::connection('mysql')->select("select * from doctors where name like '%" . $value['Incharge'] . "%'");
            $chck_px = DB::connection('mysql')->select("select * from patients where name like '%" . $value['Customer'] . "%'");
            $p->doctor = $value['Incharge'] != '' || $value['Incharge'] != null ? $chck_dct[0]->id : $chck_px[0]->attending_doctor;
            $p->patient_id = $chck_px ? $chck_px[0]->id : 0;
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
        $start = $request->start ? $request->start : 0;
        $val = $request->searchTerm2;
        if ($val != '' || $start > 0) {
            $data =  DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where p.name like '%" . $val . "%' LIMIT $length offset $start");
            $count =  DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where p.name like '%" . $val . "%' ");
        } else {
            $data =  DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where s.schedule = '" . date('Y-m-d') . "' LIMIT $length");
            $count =  DB::connection('mysql')->select("select s.*,p.* from schedule s left join patients p on s.patient_id = p.id where s.schedule = '" . date('Y-m-d') . "'");
        }

        $count_all_record =  DB::connection('mysql')->select("select  count(*) as count from schedule s left join patients p on s.patient_id = p.id ");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['id'] =  $value->id;
            $arr['name'] =  $value->name;
            $incharge = Doctors::where(['id' => $value->doctor])->first();
            $attending_doctor = Doctors::where(['id' => $value->attending_doctor])->first();
            $arr['incharge_dctr'] = $incharge ? $incharge->name : '';
            $arr['attending_dctr'] =  $attending_doctor->name;
            $data_array[] = $arr;
        }
        $page = sizeof($count) / $length;
        $getDecimal =  explode(".", $page);
        $page_count = round(sizeof($count) / $length);
        if (sizeof($getDecimal) == 2) {
            if ($getDecimal[1] < 5) {
                $page_count = $getDecimal[0] + 1;
            }
        }
        $datasets = array([
            "data" => $data_array,
            "count" => $page_count,
            "showing" =>
            sizeof($count_all_record) > 0 ?
                "Showing " . (($start + 10) - 9) . " to " . ($start + 10 > $count_all_record[0]->count ?
                    $count_all_record[0]->count :
                    $start + 10) . " of " . $count_all_record[0]->count : '',
            "patient" => $data_array
        ]);
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
        $data = Phic::where(['id' => $id])->first();
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $status = $request->data['status'] ? 'PAID' : 'UNPAID';
        Phic::where(['id' => $request->data['id']])->update([
            'status' => $status,
            'remarks' => $request->data['remarks'],
            'acpn_no' => $request->data['acpn'],
            'updated_by' => auth()->id(),
            'updated_dt' => date('Y-m-d H:i:s'),
        ]);
        $logs = new Transaction_log();
        $logs->action = 'Update by '.auth()->user()->name.' on '.date('F d, Y H:i:s');
        $logs->module = 'PHIC PAYMENT';
        $logs->phic_id = $request->data['id'];
        $logs->remarks = $request->data['remarks'].' '.$request->data['acpn'].' '.$status;
        $logs->save();
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

    public function report(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        $doctors = $request->doctors;
        $getDoctor = Doctors::where(["id" => $doctors])->first();
        if ($doctors != 'All') {
            $data =  DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'), count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and
                s.doctor = $doctors and s.status = 'ACTIVE'
                group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
            ");
            $getPaidClaims =  DB::connection('mysql')->select("
                select * from phic where date_session between '$fdate' and '$tdate' and status = 'PAID' and doctor = $doctors
            ");
        } else {
            $data =  DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'), count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
                group by s.patient_id;
            ");
            $getPaidClaims =  DB::connection('mysql')->select("
                select * from phic where date_session between '$fdate' and '$tdate' and status = 'PAID'
            ");
        }

        $data_array = array();
        $data_array_export = array();

        $total_paid_session = 0;
        foreach ($data as $key => $value) {
            $arr = array();
            $arr_export = array();
            /*  $get_dates = DB::connection('mysql')->select("
            SELECT schedule, patient_id from schedule
                where schedule between '$fdate' and '$tdate' and patient_id = '$value->patient_id' and status = 'ACTIVE'
            "); */

            if ($doctors != 'All') {
                $get_dates = DB::connection('mysql')->select("
                SELECT schedule, patient_id from schedule
                    where schedule between '$fdate' and '$tdate' and patient_id = '$value->patient_id' and doctor = $doctors and status = 'ACTIVE' order by schedule asc
                ");
            } else {
                $get_dates = DB::connection('mysql')->select("
                SELECT schedule, patient_id from schedule
                    where schedule between '$fdate' and '$tdate' and patient_id = '$value->patient_id' and status = 'ACTIVE'  order by schedule asc
                ");
            }

            $date_of_sessions = '';
            $date_of_sessionsArr = array();
            $paid_session = 0;
            foreach ($get_dates as $gkey => $gvalue) {
                $date_of_sessionsArr_set = array();
                $s_date = date_format(date_create($gvalue->schedule), 'F d');
                $date_of_sessionsArr_set['date'] = $s_date;
                //$data_sessions = Phic::where(['date_session' => date_format(date_create($gvalue->schedule), 'Y-m-d'), 'patient_id' => $gvalue->patient_id])->first();
                $s_sched = date_format(date_create($gvalue->schedule), 'Y-m-d');
                /* $data_sessions  = DB::connection('mysql')->select("
                SELECT * from phic
                    where date_session = '$s_sched' and patient_id = '$gvalue->patient_id' and status <> 'INACTIVE'
                "); */
                /* $data_sessions  = DB::connection('mysql')->select("
                SELECT * from phic
                    where date_session = '$s_sched' and patient_id = '$gvalue->patient_id' and state <> 'INACTIVE'
                "); */

                $data_sessions  = DB::connection('mysql')->select("
                SELECT * from phic
                    where date_session = '$s_sched' and patient_id = '$gvalue->patient_id' and state = 'ACTIVE'
                ");
                if ($data_sessions) {
                    $data_sessions[0]->status == 'PAID' ? $paid_session++ : 0;
                }
                $date_of_sessionsArr_set['status'] = $data_sessions ? $data_sessions[0]->status : '';
                $date_of_sessionsArr_set['id'] = $data_sessions ? $data_sessions[0]->id : null;
                $date_of_sessionsArr_set['x'] = date_format(date_create($gvalue->schedule), 'Y-m-d');
                $date_of_sessionsArr_set['y'] = $gvalue->patient_id;
                $date_of_sessions .= date_format(date_create($gvalue->schedule), 'F d') . ', ';
                $date_of_sessionsArr[] = $date_of_sessionsArr_set;
            }
            $arr['name'] =  $value->name;
            //$arr['sessions'] = $value->cnt;
            $arr['sessions'] = count($get_dates);

            $arr['paidSessions'] =  $total_paid_session += $paid_session;
            $arr['dates'] =  $date_of_sessions;
            $arr['datesArr'] =  $date_of_sessionsArr;
            $data_array[] = $arr;

            $arr_export['Name'] =  $value->name;
            $arr_export['No. of Sessions'] = count($get_dates); //$value->cnt;
            $arr_export['Paid Sessions'] =  $total_paid_session += $paid_session;
            $arr_export['Dates'] =  $date_of_sessions;
            $data_array_export[] = $arr_export;
            $date_of_sessions = '';
        }
        $datasets = array();
        $datasets["data"] = $data_array;
        $datasets["export"] = $data_array_export;
        $datasets["Doctors"] = $getDoctor;
        $datasets['totalPaidSessions'] =  $total_paid_session;
        $datasets["getPaidClaims"] = count($getPaidClaims);
        return response()->json($datasets);
    }

    public function acpn_report(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        $doctors = $request->doctors;
        $getDoctor = Doctors::where(["id" => $doctors])->first();
        /* $data =  DB::connection('mysql')->select("
        SELECT p.name,  s.patient_id,s.schedule,s.id
            FROM `schedule` s
            left join patients p on s.patient_id = p.id
            where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
            group by s.patient_id;
        "); */
        /* $data =  DB::connection('mysql')->select("
        SELECT s.patient_id, p.name, s.patient_id,s.schedule,s.id,c.acpn_no FROM `schedule` s 
        left join patients p on s.patient_id = p.id 
        left join phic c on c.patient_id = s.patient_id 
        where c.date_session between '$fdate' and '$tdate' 
        and s.status = 'ACTIVE'
            group by c.acpn_no order by p.name;
        "); */

        if ($doctors != 'All') {
            $data =  DB::connection('mysql')->select("
            SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no FROM `schedule` s
            left join patients p on s.patient_id = p.id
            left join phic c on c.patient_id  = s.patient_id 
            where c.date_session between '$fdate' and '$tdate'  and 
            s.status = 'ACTIVE' and
            c.acpn_no is not null
            and s.doctor = $doctors
            group by s.patient_id, c.acpn_no order by p.name;
            ");
        }else{
            $data =  DB::connection('mysql')->select("
            SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no FROM `schedule` s
            left join patients p on s.patient_id = p.id
            left join phic c on c.patient_id  = s.patient_id 
            where c.date_session between '$fdate' and '$tdate'  and 
            s.status = 'ACTIVE' and
            c.acpn_no is not null
            group by s.patient_id, c.acpn_no order by p.name;
            ");
        }

        /* if ($doctors != 'All') {
            $data =  DB::connection('mysql')->select("
            SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no FROM `schedule` s
            left join patients p on s.patient_id = p.id
            left join phic c on c.patient_id  = s.patient_id 
            where c.date_session between '$fdate' and '$tdate'  and 
            c.status = 'PAID' and
            c.state = 'ACTIVE' and
            c.acpn_no is not null
            and s.doctor = $doctors
            group by s.patient_id, c.acpn_no order by p.name;
            ");
        }else{
            $data =  DB::connection('mysql')->select("
            SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no FROM `schedule` s
            left join patients p on s.patient_id = p.id
            left join phic c on c.patient_id  = s.patient_id 
            where c.date_session between '$fdate' and '$tdate'  and 
            c.status = 'PAID' and
            c.state = 'ACTIVE'
            group by s.patient_id, c.acpn_no order by p.name;
            ");
        } */



        $getPaidClaims =  DB::connection('mysql')->select("
            select * from phic where date_session between '$fdate' and '$tdate' and status = 'PAID'
        ");
        $data_array = array();
        $data_array_export = array();

        $total_paid_session = 0;
        $Grandtotal_paid_session = 0;
        $Grandtotal_phic25sharing = 0;
        $Grandtotal_phic25sharing_withtax = 0;
        foreach ($data as $key => $value) {
            $arr = array();
            $arr_export = array();

            /* $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where date_session between '$fdate' and '$tdate'  and patient_id = '$value->patient_id' and state <> 'INACTIVE' and status = 'PAID' and remarks like '%$request->batch%' group by acpn_no
            "); */

        if ($doctors != 'All') {
            $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where date_session between '$fdate' and '$tdate'  and patient_id = '$value->patient_id' and state <> 'INACTIVE' and 
                status = 'PAID' and remarks like '%$request->batch%' and acpn_no = '$value->acpn_no' and doctor = $doctors
            ");         
        }else{
            $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where date_session between '$fdate' and '$tdate'  and patient_id = '$value->patient_id' and state <> 'INACTIVE' and status = 'PAID' and 
                remarks like '%$request->batch%' and acpn_no = '$value->acpn_no'
            ");  
            /* $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where date_session between '$fdate' and '$tdate'  and patient_id = '$value->patient_id' and state = 'ACTIVE' and status = 'PAID' and 
                remarks like '%$request->batch%' and acpn_no = '$value->acpn_no'
            ");   */       
        }
            
     

            $date_of_sessions = '';
            $acpnStr = '';
            $date_of_sessionsArr = array();
            $paid_session = 0;
            foreach ($get_dates as $gkey => $gvalue) {
                $date_of_sessionsArr_set = array();
                $s_date = date_format(date_create($gvalue->date_session), 'F d, Y');
                $date_of_sessionsArr_set['date'] = $s_date;
                $s_sched = date_format(date_create($gvalue->date_session), 'Y-m-d');
                /* $data_sessions  = DB::connection('mysql')->select("
                SELECT * from phic
                    where date_session = '$s_sched' and patient_id = '$gvalue->patient_id' and state <> 'INACTIVE' and status = 'PAID' 
                "); */
                $data_sessions  = DB::connection('mysql')->select("
                SELECT * from phic
                    where  patient_id = '$gvalue->patient_id' and state = 'ACTIVE' and status = 'PAID' and acpn_no = '$value->acpn_no'
                ");
                if ($data_sessions) {
                    //$data_sessions[0]->status == 'PAID' ? $paid_session++ : 0;
                    $data_sessions[0]->status = 'PAID' ? $paid_session++ : 0;
                }
                $date_of_sessionsArr_set['status'] = $data_sessions ? $data_sessions[0]->status : '';
                $date_of_sessionsArr_set['id'] = $data_sessions ? $data_sessions[0]->id : null;
                $date_of_sessionsArr_set['x'] = date_format(date_create($gvalue->date_session), 'Y-m-d');
                $date_of_sessionsArr_set['y'] = $gvalue->patient_id;
                $date_of_sessions .= date_format(date_create($gvalue->date_session), 'F d Y')."\n";
                if($gvalue->acpn_no==$acpnStr){
                    $acpnStr = $gvalue->acpn_no;
                }else{
                    $acpnStr .= $gvalue->acpn_no;
                }
                $date_of_sessionsArr[] = $date_of_sessionsArr_set;
            }
            $getUser = User::where(['id'=>$value->updated_by])->first();
            $arr['update_by'] =  $getUser->name.' on '.date_format(date_create($value->updated_dt), 'F d, Y h:i:s A');
            $arr['name'] =  $value->name;
            $no_of_sessions_paid = sizeof($date_of_sessionsArr);
            $arr['sessions'] = $no_of_sessions_paid;
            $arr['paidSessions'] =  $total_paid_session += $paid_session;
            $arr['dates'] =  $date_of_sessions;
            $arr['acpn'] =  $acpnStr;
            $arr['datesArr'] =  $date_of_sessionsArr;
            $arr['get_dates'] =  $get_dates;
            $arr['cget_dates'] =  count($get_dates);
            $arr['total'] =  $no_of_sessions_paid * 350;
            $calculate_total = $no_of_sessions_paid * 350;
            $phic25 = $no_of_sessions_paid * 2250;
            $phic25_withtax = $phic25 * 0.25;
            $arr['phic25'] =  $phic25;
            $arr['phic25tax'] =  $phic25_withtax;
            $arr['ACPN No.'] =  '';//$value->remarks;
            $Grandtotal_paid_session += $calculate_total;
            $Grandtotal_phic25sharing += $phic25;
            $Grandtotal_phic25sharing_withtax += $phic25_withtax;
            $arr_export['Name'] =  $value->name;
            $arr_export['No. of Sessions'] = count($get_dates); 
            $arr_export['Dates'] =  ltrim($date_of_sessions," ");
            $arr_export['PHIC NEPHRO 350'] =  $calculate_total;
            $arr_export['PHIC Sharing 2250'] =  $phic25;
            $arr_export['PNCSI Sharing(25%)'] =  $phic25_withtax;
            $arr_export['ACPN No.'] = $acpnStr?$acpnStr:'';
            if(sizeof($get_dates)>0){
                $data_array[] = $arr;
                $data_array_export[] = $arr_export;
            }
            $date_of_sessions = '';
        }
        $datasets = array();
        
        $arr_export['Name'] =  '';
        $arr_export['No. of Sessions'] = '';
        $arr_export['Dates'] = 'Total';
        $arr_export['PHIC NEPHRO 350'] = $Grandtotal_paid_session;
        $arr_export['PHIC Sharing 2250'] = $Grandtotal_phic25sharing;
        $arr_export['PNCSI Sharing(25%)'] = $Grandtotal_phic25sharing_withtax;
        $arr_export['ACPN No.'] = '';
        $data_array_export[] = $arr_export;

        $datasets["data"] = $data_array;
        $datasets["export"] = $data_array_export;
        $datasets["Doctors"] = $getDoctor;
        $datasets['totalPaidSessions'] =  $total_paid_session;
        $datasets["getPaidClaims"] = count($getPaidClaims);        

        return response()->json($datasets);
    }

    public function acpn_report_1617(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        $doctors = $request->doctors;
        $getDoctor = Doctors::where(["id" => $doctors])->first();
        /* $data =  DB::connection('mysql')->select("
        SELECT p.name,  s.patient_id,s.schedule,s.id
            FROM `schedule` s
            left join patients p on s.patient_id = p.id
            where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
            group by s.patient_id;
        "); */
        /* $data =  DB::connection('mysql')->select("
        SELECT s.patient_id, p.name, s.patient_id,s.schedule,s.id,c.acpn_no FROM `schedule` s 
        left join patients p on s.patient_id = p.id 
        left join phic c on c.patient_id = s.patient_id 
        where c.date_session between '$fdate' and '$tdate' 
        and s.status = 'ACTIVE'
            group by c.acpn_no order by p.name;
        "); */

        /* if ($doctors != 'All') {
            $data =  DB::connection('mysql')->select("
            SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no FROM `schedule` s
            left join patients p on s.patient_id = p.id
            left join phic c on c.patient_id  = s.patient_id 
            where c.date_session between '$fdate' and '$tdate'  and 
            s.status = 'ACTIVE' and
            c.acpn_no is not null
            and s.doctor = $doctors
            group by s.patient_id, c.acpn_no order by p.name;
            ");
        }else{
            $data =  DB::connection('mysql')->select("
            SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no FROM `schedule` s
            left join patients p on s.patient_id = p.id
            left join phic c on c.patient_id  = s.patient_id 
            where c.date_session between '$fdate' and '$tdate'  and 
            s.status = 'ACTIVE' and
            c.acpn_no is not null
            group by s.patient_id, c.acpn_no order by p.name;
            ");
        } */

        if ($doctors != 'All') {
            $data =  DB::connection('mysql')->select("
            SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no FROM `schedule` s
            left join patients p on s.patient_id = p.id
            left join phic c on c.patient_id  = s.patient_id 
            where c.date_session between '$fdate' and '$tdate'  and 
            c.status = 'PAID' and
            c.state = 'ACTIVE' and
            c.acpn_no is not null
            and s.doctor = $doctors
            group by s.patient_id, c.acpn_no order by p.name;
            ");
        }else{
            $data =  DB::connection('mysql')->select("
            SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no FROM `schedule` s
            left join patients p on s.patient_id = p.id
            left join phic c on c.patient_id  = s.patient_id 
            where c.date_session between '$fdate' and '$tdate'  and 
            c.status = 'PAID' and
            c.state = 'ACTIVE'
            group by s.patient_id, c.acpn_no order by p.name;
            ");
        }



        $getPaidClaims =  DB::connection('mysql')->select("
            select * from phic where date_session between '$fdate' and '$tdate' and status = 'PAID'
        ");
        $data_array = array();
        $data_array_export = array();

        $total_paid_session = 0;
        $Grandtotal_paid_session = 0;
        $Grandtotal_phic25sharing = 0;
        $Grandtotal_phic25sharing_withtax = 0;
        foreach ($data as $key => $value) {
            $arr = array();
            $arr_export = array();

            /* $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where date_session between '$fdate' and '$tdate'  and patient_id = '$value->patient_id' and state <> 'INACTIVE' and status = 'PAID' and remarks like '%$request->batch%' group by acpn_no
            "); */

        if ($doctors != 'All') {
            $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where date_session between '$fdate' and '$tdate'  and patient_id = '$value->patient_id' and state <> 'INACTIVE' and 
                status = 'PAID' and remarks like '%$request->batch%' and acpn_no = '$value->acpn_no' and doctor = $doctors
            ");         
        }else{
           /*  $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where date_session between '$fdate' and '$tdate'  and patient_id = '$value->patient_id' and state <> 'INACTIVE' and status = 'PAID' and 
                remarks like '%$request->batch%' and acpn_no = '$value->acpn_no'
            ");  */ 
            $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where date_session between '$fdate' and '$tdate'  and patient_id = '$value->patient_id' and state = 'ACTIVE'  and 
                remarks like '%$request->batch%' and acpn_no = '$value->acpn_no'
            ");         
        }
            
     

            $date_of_sessions = '';
            $acpnStr = '';
            $date_of_sessionsArr = array();
            $paid_session = 0;
            foreach ($get_dates as $gkey => $gvalue) {
                $date_of_sessionsArr_set = array();
                $s_date = date_format(date_create($gvalue->date_session), 'F d, Y');
                $date_of_sessionsArr_set['date'] = $s_date;
                $s_sched = date_format(date_create($gvalue->date_session), 'Y-m-d');
                /* $data_sessions  = DB::connection('mysql')->select("
                SELECT * from phic
                    where date_session = '$s_sched' and patient_id = '$gvalue->patient_id' and state <> 'INACTIVE' and status = 'PAID' 
                "); */
                $data_sessions  = DB::connection('mysql')->select("
                SELECT * from phic
                    where  patient_id = '$gvalue->patient_id' and state = 'ACTIVE' and status = 'PAID' and acpn_no = '$value->acpn_no'
                ");
                if ($data_sessions) {
                    //$data_sessions[0]->status == 'PAID' ? $paid_session++ : 0;
                    $data_sessions[0]->status = 'PAID' ? $paid_session++ : 0;
                }
                $date_of_sessionsArr_set['status'] = $data_sessions ? $data_sessions[0]->status : '';
                $date_of_sessionsArr_set['id'] = $data_sessions ? $data_sessions[0]->id : null;
                $date_of_sessionsArr_set['x'] = date_format(date_create($gvalue->date_session), 'Y-m-d');
                $date_of_sessionsArr_set['y'] = $gvalue->patient_id;
                $date_of_sessions .= date_format(date_create($gvalue->date_session), 'F d Y')."\n";
                if($gvalue->acpn_no==$acpnStr){
                    $acpnStr = $gvalue->acpn_no;
                }else{
                    $acpnStr .= $gvalue->acpn_no;
                }
                $date_of_sessionsArr[] = $date_of_sessionsArr_set;
            }
            $getUser = User::where(['id'=>$value->updated_by])->first();
            $arr['update_by'] =  $getUser->name.' on '.date_format(date_create($value->updated_dt), 'F d, Y h:i:s A');
            $arr['name'] =  $value->name;
            $no_of_sessions_paid = sizeof($date_of_sessionsArr);
            $arr['sessions'] = $no_of_sessions_paid;
            $arr['paidSessions'] =  $total_paid_session += $paid_session;
            $arr['dates'] =  $date_of_sessions;
            $arr['acpn'] =  $acpnStr;
            $arr['datesArr'] =  $date_of_sessionsArr;
            $arr['get_dates'] =  $get_dates;
            $arr['cget_dates'] =  count($get_dates);
            $arr['total'] =  $no_of_sessions_paid * 350;
            $calculate_total = $no_of_sessions_paid * 350;
            $phic25 = $no_of_sessions_paid * 2250;
            $phic25_withtax = $phic25 * 0.25;
            $arr['phic25'] =  $phic25;
            $arr['phic25tax'] =  $phic25_withtax;
            $arr['ACPN No.'] =  '';//$value->remarks;
            $Grandtotal_paid_session += $calculate_total;
            $Grandtotal_phic25sharing += $phic25;
            $Grandtotal_phic25sharing_withtax += $phic25_withtax;
            $arr_export['Name'] =  $value->name;
            $arr_export['No. of Sessions'] = count($get_dates); 
            $arr_export['Dates'] =  ltrim($date_of_sessions," ");
            $arr_export['PHIC NEPHRO 350'] =  $calculate_total;
            $arr_export['PHIC Sharing 2250'] =  $phic25;
            $arr_export['PNCSI Sharing(25%)'] =  $phic25_withtax;
            $arr_export['ACPN No.'] = $acpnStr?$acpnStr:'';
            if(sizeof($get_dates)>0){
                $data_array[] = $arr;
                $data_array_export[] = $arr_export;
            }
            $date_of_sessions = '';
        }
        $datasets = array();
        
        $arr_export['Name'] =  '';
        $arr_export['No. of Sessions'] = '';
        $arr_export['Dates'] = 'Total';
        $arr_export['PHIC NEPHRO 350'] = $Grandtotal_paid_session;
        $arr_export['PHIC Sharing 2250'] = $Grandtotal_phic25sharing;
        $arr_export['PNCSI Sharing(25%)'] = $Grandtotal_phic25sharing_withtax;
        $arr_export['ACPN No.'] = '';
        $data_array_export[] = $arr_export;

        $datasets["data"] = $data_array;
        $datasets["export"] = $data_array_export;
        $datasets["Doctors"] = $getDoctor;
        $datasets['totalPaidSessions'] =  $total_paid_session;
        $datasets["getPaidClaims"] = count($getPaidClaims);        

        return response()->json($datasets);
    }

    public function acpn_report_list(Request $request)
    {
        $acpn_data = Phic::where(['acpn_no'=>$request->acpn,'status'=>'PAID','state'=>'ACTIVE'])->get();

        $data_array = array();
        foreach ($acpn_data as $key => $value) {
            $arr = array();
            $arr['patient'] =  Helper::patientDetail($value->patient_id)->name;
            $arr['batch'] =  $value->remarks;
            $arr['doctor'] = Helper::doctorzDetail($value->doctor)->name;
            $arr['session'] =  date_format(date_create($value->date_session),'F d,Y');
            $arr['updated'] =  Helper::userDetail($value->updated_by)->name. ' on '.date_format(date_create($value->updated_dt),'F d,Y');
            $data_array[] = $arr;
        }
        $datasets["acpn"] = $data_array;
        $datasets["total"] = count($acpn_data);
        $datasets["total_amount"] = count($acpn_data) * 350;
        return response()->json($datasets);
    }

    public function acpn_report_list1(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        
        $data =  DB::connection('mysql')->select("
        SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.remarks,c.doctor,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no FROM `schedule` s
        left join patients p on s.patient_id = p.id
        left join phic c on c.patient_id  = s.patient_id 
        where 
        c.acpn_no is not null
        group by s.patient_id, c.acpn_no order by p.name;
        ");


        $data_array = array();
        $data_array_export = array();

        $total_paid_session = 0;
        foreach ($data as $key => $value) {
            $arr = array();
            $arr_export = array();

            $arr['patient'] =  Helper::patientDetail($value->patient_id)->name;
            $arr['batch'] =  $value->remarks;
            $arr['doctor'] = Helper::doctorzDetail($value->doctor)->name;
            $arr['session'] =  date_format(date_create($value->date_session),'F d,Y');
            $arr['updated'] =  Helper::userDetail($value->updated_by)->name. ' on '.date_format(date_create($value->updated_dt),'F d,Y');
     
            /* $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where  state <> 'INACTIVE' and status = 'PAID' and  acpn_no = '$acpn'
            ");  */    
            $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where   acpn_no = '$request->acpn'
            ");     

            $paid_session = 0;
            foreach ($get_dates as $gkey => $gvalue) {
                $s_sched = date_format(date_create($gvalue->date_session), 'Y-m-d');
                /* $data_sessions  = DB::connection('mysql')->select("
                SELECT * from phic
                    where date_session = '$s_sched' and patient_id = '$gvalue->patient_id' and state <> 'INACTIVE' 
                "); */
                if ($gvalue->status == 'PAID') {
                    $paid_session++;
                }
            }
            $arr['get_dates'] =  $get_dates;
            //$arr['paidSessions'] =  $total_paid_session += $paid_session;
            $arr['paidSessions'] =  $total_paid_session += $paid_session;
            if(sizeof($get_dates)>0){
                $data_array[] = $arr;
                $data_array_export[] = $arr_export;
            }
            $paid_session = 0;
        }
        $datasets = array();
        
        $datasets["acpn"] = $data_array;
        $datasets["total"] = count($data);
        $datasets['totalPaidSessions'] =  $total_paid_session;

        return response()->json($datasets);
    }
}


