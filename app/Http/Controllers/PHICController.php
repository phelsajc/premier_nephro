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
        $data = Phic::where(['id' => $id])->first();
        return response()->json($data);
    }

    public function update(Request $request)
    {
        $status = $request->status ? 'PAID' : 'UNPAID';
        Phic::where(['id' => $request->id])->update([
            'status' => $status,
            'remarks' => $request->remarks,
            'acpn_no' => $request->acpn,
            'iscash' => $request->iscash,
            'cash' => $request->cash,
            'updated_by' => auth()->id(),
            'updated_dt' => date('Y-m-d H:i:s'),
        ]);
        $logs = new Transaction_log();
        $logs->action = 'Update by ' . auth()->user()->name . ' on ' . date('F d, Y H:i:s');
        $logs->module = 'PHIC PAYMENT';
        $logs->phic_id = $request->id;
        $logs->remarks = $request->remarks . ' ' . $request->acpn . ' ' . $status;
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
            $data = DB::connection('mysql')->select("
            SELECT distinct p.name, s.patient_id
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and
                s.doctor = $doctors and s.status = 'ACTIVE'
                group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
            ");
            $getPaidClaims = DB::connection('mysql')->select("
                select * from phic where date_session between '$fdate' and '$tdate' and status = 'PAID' and doctor = $doctors
            ");
        } else {
            $data = DB::connection('mysql')->select("
            SELECT distinct p.name, s.patient_id
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and
                s.status = 'ACTIVE'
                group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
            ");
            $getPaidClaims = DB::connection('mysql')->select("
                select * from phic where date_session between '$fdate' and '$tdate' and status = 'PAID' and state = 'ACTIVE'
            ");
        }

        $data_array = array();
        $data_array_export = array();

        $total_paid_session = 0;
        $totalUnpaid = 0;
        $totalpaid = 0;
        foreach ($data as $key => $value) {
            $arr = array();
            $arr_export = array();
            if ($doctors != 'All') {
                $get_dates = DB::connection('mysql')->select("
                SELECT schedule, patient_id from schedule
                    where schedule between '$fdate' and '$tdate' and patient_id = $value->patient_id and doctor = $doctors and status = 'ACTIVE' order by schedule asc
                ");
            } else {
                $get_dates = DB::connection('mysql')->select("
                SELECT schedule, patient_id,doctor from schedule
                    where schedule between '$fdate' and '$tdate' and patient_id = $value->patient_id and status = 'ACTIVE'  order by schedule asc
                ");
            }

            $date_of_sessions = '';
            $date_of_sessionsArr = array();
            $paid_session = 0;
            $count_doctor_per_sessions = 0;
            foreach ($get_dates as $gkey => $gvalue) {
                $date_of_sessionsArr_set = array();
                $s_date = date_format(date_create($gvalue->schedule), 'F d');
                $date_of_sessionsArr_set['date'] = $s_date;

                $s_sched = date_format(date_create($gvalue->schedule), 'Y-m-d');

                if ($doctors != 'All') {
                    $data_sessions = DB::connection('mysql')->select("
                        SELECT * from phic
                            where date_session = '$s_sched' and patient_id = '$gvalue->patient_id' and state = 'ACTIVE' and doctor = $doctors
                        ");
                } else {
                    $data_sessions = DB::connection('mysql')->select("
                        SELECT * from phic
                            where date_session = '$s_sched' and patient_id = '$gvalue->patient_id' and state = 'ACTIVE' and doctor = $gvalue->doctor
                        ");
                }
                if ($data_sessions) {
                    $data_sessions[0]->status == 'PAID' ? $paid_session++ : 0;
                }
                if ($data_sessions) {
                    if ($data_sessions[0]->status == 'UNPAID') {
                        $totalUnpaid++;
                    }
                    if ($data_sessions[0]->status == 'PAID') {
                        $totalpaid++;
                    }
                }

                $date_of_sessionsArr_set['status'] = $data_sessions ? $data_sessions[0]->status : null;
                //$date_of_sessionsArr_set['ispaid'] = $data_sessions ? $data_sessions[0]->iscash : null;
                $date_of_sessionsArr_set['id'] = $data_sessions ? $data_sessions[0]->id : null;
                $date_of_sessionsArr_set['x'] = date_format(date_create($gvalue->schedule), 'Y-m-d');
                $date_of_sessionsArr_set['y'] = $gvalue->patient_id;
                $date_of_sessionsArr_set['updatedBy'] = $data_sessions ? ($data_sessions[0]->updated_by ? Helper::userDetail($data_sessions[0]->updated_by)->name . ' on ' . date_format(date_create($data_sessions[0]->updated_dt), 'F d,Y') : '') : '';
                $date_of_sessions .= date_format(date_create($gvalue->schedule), 'F d') . ', ';
                $date_of_sessionsArr[] = $date_of_sessionsArr_set;
            }
            $count_doctor_per_sessions += count($get_dates);

            $arr['name'] = $value->name;
            $arr['sessions'] = $count_doctor_per_sessions;
            $arr['script'] = "
            SELECT schedule, patient_id from schedule
                where schedule between '$fdate' and '$tdate' and patient_id = $value->patient_id and doctor = $doctors and status = 'ACTIVE' order by schedule asc
            ";

            $arr['paidSessions'] = $total_paid_session += $paid_session;
            $arr['dates'] = $date_of_sessions;
            $arr['pid'] = $value->patient_id;
            $arr['datesArr'] = $date_of_sessionsArr;

            $data_array[] = $arr;

            $arr_export['Name'] = $value->name;
            $arr_export['No. of Sessions'] = count($get_dates); //$value->cnt;
            $arr_export['Paid Sessions'] = $total_paid_session += $paid_session;
            $arr_export['Dates'] = $date_of_sessions;
            $data_array_export[] = $arr_export;
            $date_of_sessions = '';
        }
        $datasets = array();
        $datasets["data"] = $data_array;
        $datasets["export"] = $data_array_export;
        $datasets["Doctors"] = $getDoctor;
        $datasets['totalPaidSessions'] = $total_paid_session;
        $datasets["getPaidClaims"] = count($getPaidClaims);
        $datasets["getPaidClaims2"] = $getPaidClaims;
        $datasets["totalUnpaid"] = $totalUnpaid;
        $datasets["totalpaid"] = $totalpaid;
        return response()->json($datasets);
    }

    public function acpn_report(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        $doctors = $request->doctors;
        $getDoctor = Doctors::where(["id" => $doctors])->first();

        $getDoctor_arr = array();
        $sharingReport = array();
        $total_2250_net = 0;
        if ($doctors != 'All') {
            $data = DB::connection('mysql')->select("
            SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no FROM `schedule` s
            left join patients p on s.patient_id = p.id
            left join phic c on c.patient_id  = s.patient_id 
            where c.date_session between '$fdate' and '$tdate'  and 
            s.status = 'ACTIVE' and
            c.acpn_no is not null
            and s.doctor = $doctors
            group by s.patient_id, c.acpn_no order by p.name;
            ");
        } else {
            $data = DB::connection('mysql')->select("
            SELECT c.id,s.patient_id, p.name, s.patient_id,s.schedule,c.date_session,c.updated_by,c.updated_dt ,s.id,c.acpn_no,c.doctor FROM `schedule` s
            left join patients p on s.patient_id = p.id
            left join phic c on c.patient_id  = s.patient_id 
            where c.date_session between '$fdate' and '$tdate'  and 
            s.status = 'ACTIVE' and
            c.acpn_no is not null
            group by s.patient_id, c.acpn_no order by p.name;
            ");
            $getDoctor = Doctors::all();

            $total_sess = 0;
            $total_amnt_per_sess = 0;
            $total_amnt_per_w_tx = 0;
            $total_amnt_net = 0;
            $total_sharing_session = 0;
            $total_sharing_tax = 0;
            foreach ($getDoctor as $key => $value) {
                $getDoctor_sessions = DB::connection('mysql')->select("SELECT count(*) as count from phic   where remarks like '%$request->batch%' and status='PAID'  and doctor = $value->id ");
                $arr = array();
                $arr_sharing = array();

                /* $total_amt = $getDoctor_sessions[0]->count * 350;
                $total_amt_tx = $total_amt * .1;
                $total_amt_net = $total_amt * .9; */

                if ($value->id == 5) {
                    $total_amt = $getDoctor_sessions[0]->count * 350;
                    $total_amt_tx = $total_amt * .05;
                    $total_amt_net = $total_amt * .95;
                }else{
                    $total_amt = $getDoctor_sessions[0]->count * 350;
                    $total_amt_tx = $total_amt * .1;
                    $total_amt_net = $total_amt * .9;
                }

                $total_amt_sharing = $getDoctor_sessions[0]->count * 2250;
                $total_amt_tx_sharing = $total_amt_sharing * .25;
                $total_amt_tx_sharing_tax = $total_amt_tx_sharing * .05;
                $total_amt_net_sharing = $total_amt_tx_sharing - $total_amt_tx_sharing_tax;


                $arr['nephro'] = $value->name;
                $arr['sess'] = $getDoctor_sessions[0]->count;
                $arr['amount'] = "350";
                $arr['total'] = number_format($total_amt, 2).'xxxx';
                $arr['tx'] = number_format($total_amt_tx, 2);
                $arr['net'] = number_format($total_amt_net, 2);


                $arr_sharing['nephro'] = $value->name;
                $arr_sharing['sess'] = $getDoctor_sessions[0]->count;
                $arr_sharing['amount_sharing'] = "2,250";
                $arr_sharing['total'] = number_format($total_amt_sharing, 2);
                $arr_sharing['sharing'] = number_format($total_amt_tx_sharing, 2);
                $arr_sharing['tx'] = number_format($total_amt_tx_sharing_tax, 2);
                $arr_sharing['net'] = number_format($total_amt_net_sharing, 2);

                if ($getDoctor_sessions[0]->count > 0) {
                    $getDoctor_arr[] = $arr;
                    $sharingReport[] = $arr_sharing;
                }
                $total_sess += $getDoctor_sessions[0]->count;
                $total_amnt_per_sess += $total_amt;
                $total_amnt_per_w_tx += $total_amt_tx;
                $total_amnt_net += $total_amt_net;
                $total_2250_net += $total_amt_net_sharing;

                $total_sharing_session += $getDoctor_sessions[0]->count;
                $total_sharing_tax += $total_amt_tx_sharing_tax;
            }
            $getDoctor_arr_footer['nephro'] = 'Totalx';
            $getDoctor_arr_footer['sess'] = number_format($total_sess);
            $getDoctor_arr_footer['amount'] = "";
            $getDoctor_arr_footer['total'] = number_format($total_amnt_per_sess, 2);
            $getDoctor_arr_footer['tx'] = number_format($total_amnt_per_w_tx, 2);
            $getDoctor_arr_footer['net'] = number_format($total_amnt_net, 2);
            $getDoctor_arr[] = $getDoctor_arr_footer;


            $getDoctor_arr_footer2['nephro'] = 'Totaly';
            $getDoctor_arr_footer2['sess'] = number_format($total_sharing_session);
            $getDoctor_arr_footer2['amount'] = "";
            $getDoctor_arr_footer2['sharing'] = "";
            $getDoctor_arr_footer2['total'] = "";
            $getDoctor_arr_footer2['tx'] = number_format($total_sharing_tax, 2);
            $getDoctor_arr_footer2['net'] = number_format($total_2250_net, 2);
            $sharingReport[] = $getDoctor_arr_footer2;
        }


        $getPaidClaims = DB::connection('mysql')->select("
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


            if ($doctors != 'All') {
                $get_dates = DB::connection('mysql')->select("
                SELECT * from phic
                    where date_session between '$fdate' and '$tdate'  and patient_id = '$value->patient_id' and state <> 'INACTIVE' and status = 'PAID' 
                    and remarks like '%$request->batch%' and acpn_no = '$value->acpn_no' and doctor = $doctors
                ");
            } else {
                $get_dates = DB::connection('mysql')->select("
                SELECT * from phic
                    where date_session between '$fdate' and '$tdate'  and patient_id = '$value->patient_id' and state <> 'INACTIVE' and status = 'PAID' and 
                    remarks like '%$request->batch%' and acpn_no = '$value->acpn_no'
                ");
                if ($value->doctor == 6) {
                }
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
                $data_sessions = DB::connection('mysql')->select("
                SELECT * from phic
                    where  patient_id = '$gvalue->patient_id' and state = 'ACTIVE' and status = 'PAID' and acpn_no = '$value->acpn_no'
                ");
                if ($data_sessions) {
                    $data_sessions[0]->status = 'PAID' ? $paid_session++ : 0;
                }
                $date_of_sessionsArr_set['status'] = $data_sessions ? $data_sessions[0]->status : '';
                $date_of_sessionsArr_set['id'] = $data_sessions ? $data_sessions[0]->id : null;
                $date_of_sessionsArr_set['x'] = date_format(date_create($gvalue->date_session), 'Y-m-d');
                $date_of_sessionsArr_set['y'] = $gvalue->patient_id;
                $date_of_sessions .= date_format(date_create($gvalue->date_session), 'F d Y') . "\n";
                if ($gvalue->acpn_no == $acpnStr) {
                    $acpnStr = $gvalue->acpn_no;
                } else {
                    $acpnStr .= $gvalue->acpn_no;
                }
                $date_of_sessionsArr[] = $date_of_sessionsArr_set;
            }
            $getUser = User::where(['id' => $value->updated_by])->first();
            $arr['update_by'] = $getUser->name . ' on ' . date_format(date_create($value->updated_dt), 'F d, Y h:i:s A');
            $arr['name'] = $value->name;
            $no_of_sessions_paid = sizeof($date_of_sessionsArr);
            $arr['sessions'] = $no_of_sessions_paid;
            $arr['paidSessions'] = $total_paid_session += $paid_session;
            $arr['dates'] = $date_of_sessions;
            $arr['acpn'] = $acpnStr;
            $arr['datesArr'] = $date_of_sessionsArr;
            $arr['get_dates'] = $get_dates;
            $arr['cget_dates'] = count($get_dates);
            $arr['total'] = $no_of_sessions_paid * 350;
            $calculate_total = $no_of_sessions_paid * 350;
            $phic25 = $no_of_sessions_paid * 2250;
            $phic25_withtax = $phic25 * 0.25;
            $arr['phic25'] = $phic25;
            $arr['phic25tax'] = $phic25_withtax;
            $arr['ACPN No.'] = '';
            $Grandtotal_paid_session += $calculate_total;
            $Grandtotal_phic25sharing += $phic25;
            $Grandtotal_phic25sharing_withtax += $phic25_withtax;
            $arr_export['Name'] = $value->name;
            $arr_export['No. of Sessions'] = count($get_dates);
            $arr_export['nos'] = count($get_dates);
            $arr_export['Dates'] = ltrim($date_of_sessions, " ");
            $arr_export['PHIC NEPHRO 350'] = $calculate_total;
            $arr_export['phic'] = $calculate_total;
            $arr_export['acpns'] = $acpnStr ? $acpnStr : '';
            $arr_export['ACPN No.'] = $acpnStr ? $acpnStr : '';
            if (sizeof($get_dates) > 0) {
                $data_array[] = $arr;
                $data_array_export[] = $arr_export;
            }
            $date_of_sessions = '';
        }
        $datasets = array();
        if($doctors!="All"){
            if ($getDoctor->id == 5) {
                $arr_export['Name'] = '';
                $arr_export['No. of Sessions'] = '';
                $arr_export['nos'] = '';
                $arr_export['Dates'] = 'Total';
                $arr_export['PHIC NEPHRO 350'] = $Grandtotal_paid_session;
                $arr_export['phic'] = number_format($Grandtotal_paid_session, 2);
                $arr_export['ACPN No.'] = '';
                $arr_export['acpns'] = '';
                $data_array_export[] = $arr_export;

                $arr_export['Name'] = '';
                $arr_export['No. of Sessions'] = '';
                $arr_export['nos'] = '';
                $arr_export['Dates'] = 'Tax';
                $arr_export['PHIC NEPHRO 350'] = $Grandtotal_paid_session * 0.05;
                $arr_export['phic'] = number_format($Grandtotal_paid_session * 0.05, 2);
                $arr_export['ACPN No.'] = '';
                $data_array_export[] = $arr_export;

                $arr_export['Name'] = '';
                $arr_export['No. of Sessions'] = '';
                $arr_export['nos'] = '';
                $arr_export['Dates'] = 'Net';
                $arr_export['PHIC NEPHRO 350'] = $Grandtotal_paid_session * 0.95;
                $arr_export['phic'] = number_format($Grandtotal_paid_session * 0.95, 2);
                $arr_export['ACPN No.'] = '';
                $arr_export['acpns'] = '';
            } else {
                $arr_export['Name'] = '';
                $arr_export['No. of Sessions'] = '';
                $arr_export['nos'] = '';
                $arr_export['Dates'] = 'Total';
                $arr_export['PHIC NEPHRO 350'] = $Grandtotal_paid_session;
                $arr_export['phic'] = number_format($Grandtotal_paid_session, 2);
                $arr_export['ACPN No.'] = '';
                $arr_export['acpns'] = '';
                $data_array_export[] = $arr_export;

                $arr_export['Name'] = '';
                $arr_export['No. of Sessions'] = '';
                $arr_export['nos'] = '';
                $arr_export['Dates'] = 'Tax';
                $arr_export['PHIC NEPHRO 350'] = $Grandtotal_paid_session * 0.1;
                $arr_export['phic'] = number_format($Grandtotal_paid_session * 0.1, 2);
                $arr_export['ACPN No.'] = '';
                $data_array_export[] = $arr_export;

                $arr_export['Name'] = '';
                $arr_export['No. of Sessions'] = '';
                $arr_export['nos'] = '';
                $arr_export['Dates'] = 'Net';
                $arr_export['PHIC NEPHRO 350'] = $Grandtotal_paid_session * 0.9;
                $arr_export['phic'] = number_format($Grandtotal_paid_session * 0.9, 2);
                $arr_export['ACPN No.'] = '';
                $arr_export['acpns'] = '';
            }
        }


        $data_array_export[] = $arr_export;

        $datasets["data"] = $data_array;
        $datasets["export"] = $data_array_export;
        $datasets["Doctors"] = $getDoctor;
        $datasets['totalPaidSessions'] = $total_paid_session;
        $datasets['data2'] = $data;
        $datasets["getPaidClaims"] = count($getPaidClaims);

        $datasets['pdf'] = $getDoctor_arr;
        $datasets['sharing'] = $sharingReport;
        $datasets['total_sharing'] = number_format($total_2250_net, 2);
        return response()->json($datasets);
    }


    public function acpn_report_list(Request $request)
    {
        $acpn = explode(',', $request->acpn);
        $strAcpn = '';
        foreach ($acpn as $key => $value) {
            $strAcpn .= "'".$value . "',";
        }
        $strAcpn = rtrim($strAcpn, ",");
        $data_array = array();
        $data_arrayAcpn = array();
        $data_arrayAcpn_dctr = array();
        $total_amount = 0;
        $total_session = 0;
        $total_session_doctor = 0;
        $doctor_id = $request->doctor;
        $extend_script = $doctor_id != 0 ? " and doctor=" . $doctor_id : "";
        foreach ($acpn as $key => $value) {
            $arrAcpn = array();

            $acpn_data = DB::connection('mysql')->select("
            SELECT * from phic p left join patients a on p.patient_id = a.id where 
            p.acpn_no = '$value' and p.status = 'PAID' and p.state = 'ACTIVE' order by a.name asc ;
            ");
            $acpn_batch = DB::connection('mysql')->select(" select * from phic where acpn_no = '$value' group by acpn_no ");


            $arrAcpn['acpn'] = $value;
            $arrAcpn['session'] = count($acpn_data);
            $arrAcpn['amount'] = number_format(count($acpn_data) * 350, 2);
            $arrAcpn['batch'] = $acpn_batch ? $acpn_batch[0]->remarks : '';
            $data_arrayAcpn[] = $arrAcpn;
            $total_session += count($acpn_data);
            $total_amount += count($acpn_data) * 350;
            foreach ($acpn_data as $key => $value) {
                $arr = array();
                $arr['patient'] = Helper::patientDetail($value->patient_id)->name;
                $arr['batch'] = $value->remarks;
                $arr['doctor'] = Helper::doctorzDetail($value->doctor)->name;
                $arr['session'] = date_format(date_create($value->date_session), 'F d,Y');
                $arr['updated'] = Helper::userDetail($value->updated_by)->name . ' on ' . date_format(date_create($value->updated_dt), 'F d,Y');
                $data_array[] = $arr;
            }
        }
        /* 
        $totaltX = 0;
        $totalPf = 0;
        $totalEwt = 0;
        $totalNet = 0;
        $acpn_by_doctor = DB::connection('mysql')->select(" 
        select *,count(doctor) as cnt from phic where acpn_no = '$strAcpn'" . $extend_script . 
        " group by doctor ");
        foreach ($acpn_by_doctor as $key => $value) {
            $arrAcpnDctr = array();

            $pf = $value->cnt * 350;
            $ewt = $pf * .1;
            $net = $pf - $ewt;

            $totaltX += $value->cnt;
            $totalPf += $pf;
            $totalEwt += $ewt;
            $totalNet += $net;

            $arrAcpnDctr['nephro'] = Helper::doctorzDetail($value->doctor)->name;
            $arrAcpnDctr['tx'] = $value->cnt;
            $arrAcpnDctr['pf'] = number_format($pf, 2);
            $arrAcpnDctr['ewt'] = $ewt;
            $arrAcpnDctr['net'] = $net;
            $data_arrayAcpn_dctr[] = $arrAcpnDctr;
        } */


        $totaltX = 0;
        $totalPf = 0;
        $totalEwt = 0;
        $totalNet = 0;
        /* foreach ($acpn as $key => $value) {
            $acpn_by_doctor = DB::connection('mysql')->select(" 
        select *,count(doctor) as cnt from phic where acpn_no = '$value'" . $extend_script .
                " group by doctor ");
            foreach ($acpn_by_doctor as $key => $value) {
                $arrAcpnDctr = array();

                $pf = $value->cnt * 350;
                $ewt = $pf * .1;
                $net = $pf - $ewt;

                $totaltX += $value->cnt;
                $totalPf += $pf;
                $totalEwt += $ewt;
                $totalNet += $net;

                $arrAcpnDctr['nephro'] = Helper::doctorzDetail($value->doctor)->name;
                $arrAcpnDctr['tx'] = $value->cnt;
                $arrAcpnDctr['pf'] = number_format($pf, 2);
                $arrAcpnDctr['ewt'] = $ewt;
                $arrAcpnDctr['net'] = $net;
                $data_arrayAcpn_dctr[] = $arrAcpnDctr;
            }
        } */


        $getDoctors = Doctors::all();

        foreach ($getDoctors as $key => $getdvalue) {
                $getCnt = 0;
            //foreach ($acpn as $key => $value) {
               /*  $acpn_by_doctor = DB::connection('mysql')->select(" 
                select *,count(doctor) as cnt from phic where acpn_no = '$value'" . " and doctor = " . $getdvalue->id .
                " group by doctor "); */
                $acpn_by_doctor = DB::connection('mysql')->select(" 
                    select *,count(doctor) as cnt from phic where acpn_no in ($strAcpn)" . " and doctor = " . $getdvalue->id .
                    " group by doctor ");
                foreach ($acpn_by_doctor as $key => $value) {
                    $arrAcpnDctr = array();
                    $pf = $value->cnt * 350;
                    $ewt = $pf * .1;
                    $net = $pf - $ewt;

                    $totaltX += $value->cnt;
                    $totalPf += $pf;
                    $totalEwt += $ewt;
                    $totalNet += $net;

                    $getCnt = $value->cnt;

               // }

            }
            $arrAcpnDctr['nephro'] = Helper::doctorzDetail($getdvalue->id)->name;
            $arrAcpnDctr['tx'] = $getCnt;
            $arrAcpnDctr['pf'] = number_format($pf, 2);
            $arrAcpnDctr['ewt'] = $ewt;
            $arrAcpnDctr['net'] = $net;
            $data_arrayAcpn_dctr[] = $arrAcpnDctr;
        }


        $arrAcpnDctr = array();
        $arrAcpnDctr['nephro'] = "";
        $arrAcpnDctr['tx'] = $totaltX;
        $arrAcpnDctr['pf'] = number_format($totalPf, 2);
        $arrAcpnDctr['ewt'] = number_format($totalEwt, 2);
        $arrAcpnDctr['net'] = number_format($totalNet, 2);
        $data_arrayAcpn_dctr[] = $arrAcpnDctr;
        $total_session_doctor = $totaltX;
        $sharing_pf = (2250 * $totaltX) * 0.25;
        $sharing_ewt = $sharing_pf * 0.05;
        $sharing_net = $sharing_pf - $sharing_ewt;


        $arrAcpnDctr2 = array();
        $arrAcpnDctr2['nephro'] = "25% Premier Sharing";
        $arrAcpnDctr2['tx'] = "";
        $arrAcpnDctr2['pf'] = number_format($sharing_pf, 2);
        $arrAcpnDctr2['ewt'] = number_format($sharing_ewt, 2);
        $arrAcpnDctr2['net'] = number_format($sharing_net, 2);
        $data_arrayAcpn_dctr[] = $arrAcpnDctr2;

        /* $acpn_data =  DB::connection('mysql')->select("
            SELECT * from phic p left join patients a on p.patient_id = a.id where 
            p.acpn_no = '$request->acpn' and p.status = 'PAID' and p.state = 'ACTIVE' order by a.name asc;
            ");

        $data_array = array();
        foreach ($acpn_data as $key => $value) {
            $arr = array();
            $arr['patient'] =  Helper::patientDetail($value->patient_id)->name;
            $arr['batch'] =  $value->remarks;
            $arr['doctor'] = Helper::doctorzDetail($value->doctor)->name;
            $arr['session'] =  date_format(date_create($value->date_session),'F d,Y');
            $arr['updated'] =  Helper::userDetail($value->updated_by)->name. ' on '.date_format(date_create($value->updated_dt),'F d,Y');
            $data_array[] = $arr;
        } */


        $arrAcpn = array();
        $arrAcpn['acpn'] = 'Total';
        $arrAcpn['session'] = $total_session;
        $arrAcpn['amount'] = number_format($total_amount, 2);
        $arrAcpn['batch'] = '';
        $data_arrayAcpn[] = $arrAcpn;

        $datasets["acpn"] = $data_array;
        //$datasets["total"] = count($acpn_data);
        //$datasets["total_amount"] = count($acpn_data) * 350;
        $datasets["data_arrayAcpn"] = $data_arrayAcpn;
        $datasets["total"] = $total_session;
        $datasets["total_amount"] = $total_amount;
        $datasets["total_session_doctor"] = $total_session_doctor;
        $datasets["sharing_pf"] = number_format($sharing_pf, 2);
        $datasets["sharing_ewt"] = number_format($sharing_ewt, 2);
        $datasets["strAcpn"] = $strAcpn;
        $datasets["sharing_net"] = number_format($sharing_net, 2);
        $datasets["data_arrayAcpn_dctr"] = $data_arrayAcpn_dctr;
        $datasets["s"] = " select *,count(doctor) as cnt from phic where acpn_no = '$strAcpn'" . $extend_script . " group by doctor ";
        return response()->json($datasets);
    }

    public function acpn_report_list1(Request $request)
    {
        date_default_timezone_set('Asia/Manila');

        $data = DB::connection('mysql')->select("
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

            $arr['patient'] = Helper::patientDetail($value->patient_id)->name;
            $arr['batch'] = $value->remarks;
            $arr['doctor'] = Helper::doctorzDetail($value->doctor)->name;
            $arr['session'] = date_format(date_create($value->date_session), 'F d,Y');
            $arr['updated'] = Helper::userDetail($value->updated_by)->name . ' on ' . date_format(date_create($value->updated_dt), 'F d,Y');

            /* $get_dates  = DB::connection('mysql')->select("
            SELECT * from phic
                where  state <> 'INACTIVE' and status = 'PAID' and  acpn_no = '$acpn'
            ");  */
            $get_dates = DB::connection('mysql')->select("
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
            $arr['get_dates'] = $get_dates;
            //$arr['paidSessions'] =  $total_paid_session += $paid_session;
            $arr['paidSessions'] = $total_paid_session += $paid_session;
            if (sizeof($get_dates) > 0) {
                $data_array[] = $arr;
                $data_array_export[] = $arr_export;
            }
            $paid_session = 0;
        }
        $datasets = array();

        $datasets["acpn"] = $data_array;
        $datasets["total"] = count($data);
        $datasets['totalPaidSessions'] = $total_paid_session;

        return response()->json($datasets);
    }

    public function report_summary(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        $doctors = $request->doctors;
        $getDoctor = Doctors::where(["id" => $doctors])->first();

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
            /* $get_sessions =  DB::connection('mysql')->select("
            SELECT p.name,DATE_FORMAT(s.schedule, '%Y-%m'),p.attending_doctor, count(s.patient_id) as cnt, s.patient_id,s.schedule,s.id,s.doctor
                FROM `schedule` s
                left join patients p on s.patient_id = p.id
                left join phic c on p.id = c.patient_id
                where s.schedule between '$fdate' and '$tdate' and
                s.doctor = $value->id and s.status = 'ACTIVE' and c.remarks = 'BATCH-13'
                group by DATE_FORMAT(s.schedule, '%Y-%m'),s.patient_id;
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
            $total_Amt = $getCnt * 350;
            $arr['total_amount'] = number_format($total_Amt, 2);
            //$lwt =  $value->id==6?$total_Amt*0.05:$total_Amt*0.1;
            $lwt = $total_Amt * 0.1;
            $arr['less_wtx'] = number_format($lwt, 2);
            //$net = $value->id==6?$total_Amt*0.95:$total_Amt*0.9;
            $net = $total_Amt * 0.9;
            $arr['net'] = number_format($net, 2);
            $data_array[] = $arr;


            $arr_export['Nephologist'] = $value->name;
            $arr_export['# of session'] = number_format($getCnt, 2); //sizeof($get_sessions);
            $arr_export['Amount per'] = 350.00;
            $arr_export['Total Amount'] = $total_Amt;
            //$arr_export['Less WTX'] =  $value->id==6?$total_Amt*0.05:$total_Amt*0.1;
            $arr_export['Less WTX'] = $total_Amt * 0.1;
            //$tnet = $value->id==6?$total_Amt*0.95:$total_Amt*0.9;
            $tnet = $total_Amt * 0.9;
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


