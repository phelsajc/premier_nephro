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
use App\Model\Settings;
use DB;
use Carbon\Carbon;
use PhpParser\Comment\Doc;

class CensusController extends Controller
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
        $data = Phic::where(['id' => $id])->first();
        return response()->json($data);
    }
    public function update(Request $request)
    {
        Phic::where(['id' => $request->data['id']])->update([
            'status' => $request->data['status'] ? 'PAID' : 'UNPAID',
            'remarks' => $request->data['remarks'],
            'updated_by' => auth()->id(),
            'updated_dt' => date('Y-m-d'),
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
    public function report2(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->data['fdate']), 'Y-m-d');
        $tdate = date_format(date_create($request->data['tdate']), 'Y-m-d');
        $doctors = $request->data['doctors'];
        $doctors = $request->data['doctors'];
        if ($doctors != 'All') {
            $data = DB::connection('mysql')->select("
                select p.name,s.schedule from schedule s 
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and
                s.doctor = $doctors and s.status = 'ACTIVE'
                order by s.schedule ASC;
            ");
        } else {
            $data = DB::connection('mysql')->select("
            select p.name,s.schedule from schedule s 
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
                order by s.schedule ASC;
            ");
        }
        $data_array = array();
        $data_array2 = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr2 = array();
            $arr['name'] = $value->name;
            $arr['dates'] = date_format(date_create($value->schedule), 'm/d/Y');
            $arr2['Patient'] = $value->name;
            $arr2['Date'] = date_format(date_create($value->schedule), 'm/d/Y');
            $data_array[] = $arr;
            $data_array2[] = $arr2;
        }
        // return response()->json($data_array);
        $datasets["data"] = $data_array;
        $datasets["export"] = $data_array2;
        return response()->json($datasets);
    }
    public function report(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        $doctors = $request->doctors;
        $doctors = $request->doctors;
        if ($doctors != 'All') {
            $data = DB::connection('mysql')->select("
                select p.name,s.schedule from schedule s 
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and
                s.doctor = $doctors and s.status = 'ACTIVE'
                order by s.schedule ASC;
            ");
        } else {
            $data = DB::connection('mysql')->select("
            select p.name,s.schedule from schedule s 
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
                order by s.schedule ASC;
            ");
        }
        $data_array = array();
        $data_array2 = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr2 = array();
            $arr['name'] = $value->name;
            $arr['dates'] = date_format(date_create($value->schedule), 'm/d/Y');
            $arr2['Patient'] = $value->name;
            $arr2['Date'] = date_format(date_create($value->schedule), 'm/d/Y');
            $data_array[] = $arr;
            $data_array2[] = $arr2;
        }
        $datasets["data"] = $data_array;
        $datasets["export"] = $data_array2;
        return response()->json($datasets);
    }

    /* public function report_px_old(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        $px = $request->patient;
        if (!$request->isall) {
            $data = DB::connection('mysql')->select("
                select p.name,s.schedule,s.doctor from schedule s 
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and
                s.patient_id = $px and s.status = 'ACTIVE'
                order by s.schedule ASC;
            ");
        } else {
            $data = DB::connection('mysql')->select("
            select p.name,s.schedule,s.doctor from schedule s 
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
                order by s.schedule ASC;
            ");
        }
        $data_array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['name'] = $value->name;
            $doctor = Doctors::where(['id' => $value->doctor])->first();
            $arr['doctor'] = $doctor->name;
            $arr['dates'] = date_format(date_create($value->schedule), 'm/d/Y');
            $data_array[] = $arr;
        }
        return response()->json($data_array);
    } */

    public function report_px(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->fdate), 'Y-m-d');
        $tdate = date_format(date_create($request->tdate), 'Y-m-d');
        //$doctors = $request->data['doctors'];
        $px = $request->patient;
        if (!$request->isall) {
            $data = DB::connection('mysql')->select("
                select p.name,s.schedule,s.doctor,s.patient_id,s.id as schedule_id  from schedule s 
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and
                s.patient_id = $px and s.status = 'ACTIVE'
                order by s.schedule ASC;
            ");
        } else {
            $data = DB::connection('mysql')->select("
            select p.name,s.schedule,s.doctor,s.patient_id,s.id as schedule_id  from schedule s 
                left join patients p on s.patient_id = p.id
                where s.schedule between '$fdate' and '$tdate' and s.status = 'ACTIVE'
                group by s.patient_id
                order by s.schedule ASC;
            ");
        }
        $data_array = array();
        if ($request->isall) {
            foreach ($data as $key => $value) {
                $arr = array();
                $arr['schedule_id'] = $value->schedule_id;
                $arr['doctor_id'] = $value->doctor;
                $arr['schedule'] = $value->schedule;
                $arr['pid'] = $value->patient_id;
                $arr['name'] = $value->name;
                $doctor = Doctors::where(['id' => $value->doctor])->first();
                $arr['doctor'] = $doctor->name;

                //if($request->isall){
                $get_dates = DB::connection('mysql')->select("
                SELECT schedule, patient_id from schedule
                    where schedule between '$fdate' and '$tdate' and patient_id = '$value->patient_id' and status = 'ACTIVE'
                ");
                $check_copay = DB::connection('mysql')->select("
                    SELECT * from co_pay
                        where date_session = '$value->schedule' and patient_id = '$value->patient_id' and status = 'ACTIVE'
                    ");
                $date_of_sessions = '';
                $date_of_sessionsArr = array();
                $date_of_sessions = '';
                foreach ($get_dates as $gkey => $gvalue) {
                    $date_of_sessionsArr_set = array();
                    $s_date = date_format(date_create($gvalue->schedule), 'F d');
                    $date_of_sessionsArr_set['date'] = $s_date;
                    $data_sessions = Phic::where(['date_session' => date_format(date_create($gvalue->schedule), 'Y-m-d'), 'patient_id' => $gvalue->patient_id])->first();
                    $date_of_sessionsArr_set['status'] = $data_sessions ? $data_sessions->status : '';
                    $date_of_sessionsArr_set['id'] = $data_sessions ? $data_sessions->id : null;
                    $date_of_sessionsArr_set['x'] = date_format(date_create($gvalue->schedule), 'Y-m-d');
                    $date_of_sessionsArr_set['y'] = $gvalue->patient_id;
                    $date_of_sessions .= date_format(date_create($gvalue->schedule), 'F d') . ', ';
                    $date_of_sessionsArr[] = $date_of_sessionsArr_set;
                    $date_of_sessions .= date_format(date_create($gvalue->schedule), 'F d Y') . "\n";
                }
                $arr['datesArr'] = $date_of_sessionsArr;
                $arr['datesArr2'] = $date_of_sessions;
                $arr['dates'] = date_format(date_create($value->schedule), 'm/d/Y');
                $arr['copay'] = $check_copay[0]->free == 0 ? false : true;

                /* }else{            
                    $data_array = array();
                    foreach ($data as $key => $value) {
                        $arr = array();
                        $arr['name'] =  $value->name;
                        $doctor  = Doctors::where(['id'=>$value->doctor])->first();
                        $arr['doctor'] =  $doctor->name;                    
                        $arr['dates'] =  date_format(date_create($value->schedule),'m/d/Y');
                        $data_array[] = $arr;
                    }
                } */
                $data_array[] = $arr;
            }
        } else {

            foreach ($data as $key => $value) {
                $arr = array();
                $arr['schedule_id'] = $value->schedule_id;
                $arr['name'] = $value->name;
                $doctor = Doctors::where(['id' => $value->doctor])->first();
                $arr['doctor'] = $doctor->name;
                $arr['doctor_id'] = $value->doctor;
                $arr['schedule'] = $value->schedule;
                $arr['pid'] = $value->patient_id;
                $check_copay = DB::connection('mysql')->select("
                    SELECT * from co_pay
                        where date_session = '$value->schedule' and patient_id = '$value->patient_id' and status = 'ACTIVE'
                    ");
                if ($check_copay) {
                    $arr['copay'] = $check_copay[0]->free == 0 ? false : true;
                } else {
                    $arr['copay'] = false;
                }
                /* $data_array = array();
                foreach ($data as $key => $value) {
                        $arr = array();
                        $arr['name'] =  $value->name;
                        $doctor  = Doctors::where(['id'=>$value->doctor])->first();
                        $arr['doctor'] =  $doctor->name;                    
                        $arr['dates'] =  date_format(date_create($value->schedule),'m/d/Y');
                        $data_array[] = $arr;
                }   */
                $arr['dates'] = date_format(date_create($value->schedule), 'm/d/Y');
                $data_array[] = $arr;
            }
        }


        $datasets = array();
        $datasets["data"] = $data_array;
        return response()->json($data_array);
    }

    public function getStartAndEndDatesByMonth($startDate, $endDate)
    {
        $datesByMonth = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($start->lte($end)) {
            // Get the month and year as the key (Y-m format)
            $monthKey = $start->format('Y-m');

            // If it's a new month, set the start date
            if (!isset($datesByMonth[$monthKey])) {
                $datesByMonth[$monthKey]['start'] = $start->format('Y-m-d');
            }

            // Update the end date for the current month
            $datesByMonth[$monthKey]['end'] = $start->format('Y-m-d');

            // If we've reached the last day of the current month, move to the next month
            $start->addDay();
        }

        return $datesByMonth;
    }

    public function revenue1(Request $request)
    {
        $rate1 = Settings::where('name', 'rate1')->first();
        $rate2 = Settings::where('name', 'rate2')->first();
        $rate3 = Settings::where('name', 'rate3')->first();
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->data['fdate']), 'Y-m-d');
        $tdate = date_format(date_create($request->data['tdate']), 'Y-m-d');
        $doctor = $request->data['doctor'];
        $datesByMonth = $this->getStartAndEndDatesByMonth($request->data['fdate'], $request->data['tdate']);
        if ($request->data['doctor'] == 0) {
            $data = DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,DATE_FORMAT(s.schedule, '%Y-%m') as month,DATE_FORMAT(s.schedule, '%Y-%m-%d') as rate_schedule, s.doctor FROM `schedule` s where s.status = 'ACTIVE' 
            and DATE_FORMAT(s.schedule, '%Y-%m-%d') between '$fdate' and '$tdate' 
            group by DATE_FORMAT(s.schedule, '%Y-%m');
        ");
        } else {
            $data = DB::connection('mysql')->select("
        SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,DATE_FORMAT(s.schedule, '%Y-%m') as month,DATE_FORMAT(s.schedule, '%Y-%m-%d') as rate_schedule, s.doctor FROM `schedule` s where s.status = 'ACTIVE' 
        and DATE_FORMAT(s.schedule, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
        group by DATE_FORMAT(s.schedule, '%Y-%m');
    ");
        }
        $withDoctorScript = '';
        $grpByDoctor = '';
        if ($doctor != 0) {
            $withDoctorScript = " and p.doctor = $doctor";
            $grpByDoctor = ', p.doctor';
        }

        $cntAllUnpaid = 0;
        $cntAllpaid = 0;
        $data_array = array();
        $monthArr = array();
        $netArr = array();
        $netAmtArr = array();
        $totalNet = 0;
        $totalPaid = 0;
        $totalBalance = 0;
        foreach ($data as $key => $value) {
            $arr = array();
            $mon_arr = array();
            $net_arr = array();
            $net_arr2 = array();

            $month = date_format(date_create($value->schedule), 'Y-m');
            $yearF = date_format(date_create($value->schedule), 'Y') . '-01';
            $yearT = date_format(date_create($value->schedule), 'Y') . '-12';
            if ($request->data['doctor'] == 0) {

                /*  $getPaidData = DB::connection('mysql')->select("
             SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
             and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
             group by DATE_FORMAT(s.date_session, '%Y-%m');
         "); */

                $getPaidData = DB::connection('mysql')->select("
                SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
                and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "'
                group by DATE_FORMAT(s.date_session, '%Y-%m');
            ");


            } else {
                $getPaidData = DB::connection('mysql')->select("
                SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
                and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor
                group by DATE_FORMAT(s.date_session, '%Y-%m');
            ");
            }


            /* $getTotalSession = DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.state = 'ACTIVE' 
            and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
            group by DATE_FORMAT(s.date_session, '%Y-%m');
        "); */



            $getUnPaidPatientSessions = DB::connection('mysql')->select("
        select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
    from phic p
    left join patients c on p.patient_id = c.id 
    where p.status = 'UNPAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor $withDoctorScript
 group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;");



            $getPaidPatientSessions = DB::connection('mysql')->select("
select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
from phic p
left join patients c on p.patient_id = c.id 
where p.status = 'PAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor $withDoctorScript
group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;");


            $arr['month'] = date_format(date_create(datetime: $value->schedule), 'F Y');
            $monthArr[] = date_format(date_create($value->schedule), 'F Y');
            $session = $value->cnt;
            $getMultiplier = 0;
            $arr['sessions'] = $session;
            if (Carbon::parse($value->rate_schedule)->lessThan('2024-07-01')) {
                $getMultiplier = ($rate1->value - 350);
                $gross = ($rate1->value - 350) * $session;
            }

            $givenDate = Carbon::parse($value->rate_schedule);
            $startDate = Carbon::parse('2024-07-01');
            $endDate = Carbon::parse('2024-10-08');
            if ($givenDate->between($startDate, $endDate)) {
                $getMultiplier = ($rate2->value - 350);
                $gross = ($rate2->value - 350) * $session;
            }

            // Parse the dates
            $givenDate = Carbon::parse($value->rate_schedule);
            $comparisonDate = Carbon::parse('2024-10-8');

            // Check if the given date is greater than the comparison date
            if ($givenDate->greaterThan($comparisonDate)) {
                $getMultiplier = ($rate3->value - 350);
                $gross = ($rate3->value - 350) * $session;
            }

            //$gross = 2250 * $session;
            if ($value->doctor == 6) {
                $share = $gross * 0.25;
                $tax = $share * 0.1;
                $net = $share * 0.9;
            } else {
                $share = $gross * 0.25;
                $tax = $share * 0.05;
                $net = $share * 0.95;
            }
            $pnet = 0;
            $balance = 0;
            if ($getPaidData) {
                //$pgross = 2250 * $getPaidData[0]->cnt;
                $pgross = $getMultiplier * $getPaidData[0]->cnt;
                $pshare = $pgross * 0.25;
                $ptax = $pshare * 0.05;
                $pnet = $pshare * 0.95;
                //$balance = $net - $pnet;
                $balance = $pnet - $net;
            }

            $paidAmt = $getPaidData ? $getPaidData[0]->cnt : 0;
            $unpaidAmt = $getPaidData ? $session - $getPaidData[0]->cnt : 0;

            $unpaid_pnet = 0;
            $unpaid_balance = 0;
            #calculate total_unpaid
            //if (!$getPaidData) {
            //$unpaid_pgross = 2250 * ($session - $getPaidData[0]->cnt);
            //$unpaid_pgross = $getMultiplier * ($session);
            $unpaid_pgross = $getMultiplier * ($unpaidAmt);
            $unpaid_pshare = $unpaid_pgross * 0.25;
            $unpaid_ptax = $unpaid_pshare * 0.05;
            $unpaid_pnet = $unpaid_pshare * 0.95;
            //$unpaid_balance = $net - $unpaid_pnet;
            $unpaid_balance = $unpaid_pnet;
            //}


            $getUnPaidPatientSessions_list = array();
            $total_upaid = 0;
            $total_upaid_Sessions = 0;
            foreach ($getUnPaidPatientSessions as $key_up => $value_up) {
                $arr_up = array();
                $arr_up['cnt'] = $value_up->cnt;
                $arr_up['cnt_sess'] = $value_up->cnt_sess;
                $arr_up['date_session'] = $value_up->date_session;
                $arr_up['id'] = $value_up->id;
                $arr_up['name'] = $value_up->name;
                $t = $getMultiplier * $value_up->cnt_sess;
                $arr_up['amt'] = $t;
                $total_upaid += $t;
                $total_upaid_Sessions += $value_up->cnt_sess;
                $getUnPaidPatientSessions_list[] = $arr_up;
            }

            $getPaidPatientSessions_list = array();
            $total_paid = 0;
            $total_paid_Sessions = 0;
            foreach ($getPaidPatientSessions as $key_up => $value_up) {
                $arr_up = array();
                $arr_up['cnt'] = $value_up->cnt;
                $arr_up['cnt_sess'] = $value_up->cnt_sess;
                $arr_up['date_session'] = $value_up->date_session;
                $arr_up['id'] = $value_up->id;
                $arr_up['name'] = $value_up->name;
                $t = $getMultiplier * $value_up->cnt_sess;
                $arr_up['amt'] = $t;
                $total_paid += $t;
                $total_paid_Sessions += $value_up->cnt_sess;
                $getPaidPatientSessions_list[] = $arr_up;
            }

            $calc_multiplier = $total_upaid_Sessions * $getMultiplier;
            $calc_25 = $calc_multiplier * 0.25;
            $calc_5 = $calc_25 * 0.05;
            $calc_95 = $calc_25 * 0.95;
            $getUnPaidPatientSessions_list_additional['cnt'] = $calc_5;
            $getUnPaidPatientSessions_list_additional['cnt_sess'] = $calc_25;//total_upaid_Sessions*$getMultiplier;
            $getUnPaidPatientSessions_list_additional['date_session'] = '';
            $getUnPaidPatientSessions_list_additional['amt'] = $calc_95;
            $getUnPaidPatientSessions_list_additional['id'] = 1;
            $getUnPaidPatientSessions_list_additional['name'] = "25% Premier Sharing";
            $getUnPaidPatientSessions_list[] = $getUnPaidPatientSessions_list_additional;


            $calc_multiplier_paid = $total_paid_Sessions * $getMultiplier;
            $calc_25_paid = $calc_multiplier_paid * 0.25;
            $calc_5_paid = $calc_25_paid * 0.05;
            $calc_95_paid = $calc_25_paid * 0.95;
            $getPaidPatientSessions_list_additional['cnt'] = $calc_5_paid;
            $getPaidPatientSessions_list_additional['cnt_sess'] = $calc_25_paid;//total_upaid_Sessions*$getMultiplier;
            $getPaidPatientSessions_list_additional['date_session'] = '';
            $getPaidPatientSessions_list_additional['amt'] = $calc_95_paid;
            $getPaidPatientSessions_list_additional['id'] = 1;
            $getPaidPatientSessions_list_additional['name'] = "25% Premier Sharing";
            $getPaidPatientSessions_list[] = $getPaidPatientSessions_list_additional;

            $arr['gross'] = $gross;
            $arr['value->rate_schedule'] = $value->rate_schedule;
            $arr['share'] = $share;
            $arr['tax'] = $tax;
            $arr['net'] = $net;
            $net_arr2[] = $net;
            $arr['datesByMonth'] = $datesByMonth;

            $net_arr['data'] = $net; //$net_arr2;
            $net_arr['name'] = "Net";
            $cntAllpaid += $paidAmt;
            $cntAllUnpaid += $unpaidAmt;
            $arr['session_paid'] = $paidAmt;
            $arr['session_unpaid'] = $unpaidAmt;
            $arr['total'] = $pnet;
            $arr['total_unpaid'] = $unpaid_pnet;
            $arr['total_unpaid_for_summary'] = $total_upaid;
            $arr['total_paid_for_summary'] = $total_paid;
            $arr['balance'] = $balance;
            $arr['getPaidData'] = $getPaidData;
            //$arr['getPaidDataxxx'] = $session . ' ' . $getPaidData[0]->cnt . ' ' . $getTotalSession[0]->cnt;
            $arr['getUnPaidPatientSessions'] = $getUnPaidPatientSessions_list;
            $arr['getPaidPatientSessions'] = $getPaidPatientSessions_list;
            $arr['total_payment'] = $unpaid_pnet + $pnet;
            $data_array[] = $arr;
            $netArr[] = $session;
            $netAmtArr[] = $netArr;
            $totalNet += $net;
            $totalPaid += $pnet;
            //$totalBalance += $balance; WORKING
            $totalBalance += $unpaid_balance;
        }

        if ($request->data['status'] == "Unpaid") {
            $claimStatus = "p.status = 'UNPAID' and";
        } elseif ($request->data['status'] == "Paid") {
            $claimStatus = "p.status = 'PAID' and";
        } else {
            $claimStatus = " p.status in ('PAID','UNPAID')    and";
        }


        $getPatientAllSessions = DB::connection('mysql')->select(" 
      select c.name,p.patient_id,count(p.date_session) as cnt, p.doctor as docid,GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ',') as dates" .
            ",GROUP_CONCAT(p.date_session  SEPARATOR '|') as fdates" .
            ", GROUP_CONCAT( (select name from doctors where id = p.doctor group by name ) SEPARATOR ',') as doc-- , d.name
    from phic p
    left join patients c on p.patient_id = c.id 
    where $claimStatus  state = 'ACTIVE'  and  DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate'  and '$tdate' $withDoctorScript group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id $grpByDoctor ;");


        $cntAll = 0;
        $formatAllSessions = array();
        foreach ($getPatientAllSessions as $key => $value) {
            $pid = $value->patient_id;
            $skeds = explode("|", $value->fdates);
            $sked_str = '';
            foreach ($skeds as $skey => $svalue) {
                $sked_str .= "'" . $svalue . "',";
            }
            $str = implode(',', array_unique(explode(',', $value->doc)));
            $sked_str = rtrim($sked_str, ", ");
            $check_session = DB::connection('mysql')->select("Select * from schedule where patient_id = $pid and schedule in ($sked_str) and status = 'Active'");
            $newDate = '';
            foreach ($check_session as $ckey => $cvalue) {
                $newDate .= date_format((date_create($cvalue->schedule)), 'F d,Y') . ', ';
            }

            $arr = array();
            $arr['cnt'] = count($check_session);//$value->cnt;
            $arr['dates'] = $newDate;//$value->dates;
            $arr['fdates'] = $value->fdates;
            $arr['id'] = $value->docid;
            $arr['check_session'] = $check_session;
            $str = implode(',', array_unique(explode(',', $value->doc)));
            $arr['doc'] = $str;
            $arr['name'] = $value->name;
            $cntAll += count($check_session);
            $formatAllSessions[] = $arr;
        }


        $datasets = array();
        $datasets["data"] = $data_array;
        $datasets["data2"] = $data;
        $datasets["tdate"] = $tdate;
        $datasets["fdate"] = $fdate;
        $datasets['getPatientAllSessions'] = $formatAllSessions;
        $datasets['cntAll'] = $cntAll;
        $datasets['allunpaid'] = $cntAllUnpaid;
        $datasets['allpaid'] = $cntAllpaid;
        $datasets["month"] = $monthArr;
        $datasets["net"] = array(["name" => 'Net', 'data' => $netArr]);
        $datasets["totalNet"] = $totalNet;
        $datasets["totalPaid"] = $totalPaid;
        $datasets["totalBalance"] = $totalBalance;
        $datasets["sql"] = " ";
        return response()->json($datasets);
    }

    public function revenue2(Request $request)
    {
        $rate1 = Settings::where('name', 'rate1')->first();
        $rate2 = Settings::where('name', 'rate2')->first();
        $rate3 = Settings::where('name', 'rate3')->first();
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->data['fdate']), 'Y-m-d');
        $tdate = date_format(date_create($request->data['tdate']), 'Y-m-d');
        $doctor = $request->data['doctor'];
        $datesByMonth = $this->getStartAndEndDatesByMonth($request->data['fdate'], $request->data['tdate']);
        if ($request->data['doctor'] == 0) {
            $data = DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,DATE_FORMAT(s.schedule, '%Y-%m') as month,DATE_FORMAT(s.schedule, '%Y-%m-%d') as rate_schedule, s.doctor FROM `schedule` s where s.status = 'ACTIVE' 
            and DATE_FORMAT(s.schedule, '%Y-%m-%d') between '$fdate' and '$tdate' 
            group by DATE_FORMAT(s.schedule, '%Y-%m');
        ");
        } else {
            $data = DB::connection('mysql')->select("
        SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,DATE_FORMAT(s.schedule, '%Y-%m') as month,DATE_FORMAT(s.schedule, '%Y-%m-%d') as rate_schedule, s.doctor FROM `schedule` s where s.status = 'ACTIVE' 
        and DATE_FORMAT(s.schedule, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
        group by DATE_FORMAT(s.schedule, '%Y-%m');
    ");
        }
        $withDoctorScript = '';
        $grpByDoctor = '';
        if ($doctor != 0) {
            $withDoctorScript = " and p.doctor = $doctor";
            $grpByDoctor = ', p.doctor';
        }

        $cntAllUnpaid = 0;
        $cntAllpaid = 0;
        $data_array = array();
        $monthArr = array();
        $netArr = array();
        $netAmtArr = array();
        $totalNet = 0;
        $totalPaid = 0;
        $totalBalance = 0;
        foreach ($data as $key => $value) {
            $arr = array();
            $mon_arr = array();
            $net_arr = array();
            $net_arr2 = array();

            $month = date_format(date_create($value->schedule), 'Y-m');
            $yearF = date_format(date_create($value->schedule), 'Y') . '-01';
            $yearT = date_format(date_create($value->schedule), 'Y') . '-12';
            if ($request->data['doctor'] == 0) {

                /*  $getPaidData = DB::connection('mysql')->select("
             SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
             and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
             group by DATE_FORMAT(s.date_session, '%Y-%m');
         "); */

                $getPaidData = DB::connection('mysql')->select("
                SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
                and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "'
                group by DATE_FORMAT(s.date_session, '%Y-%m');
            ");


            } else {
                $getPaidData = DB::connection('mysql')->select("
                SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
                and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor
                group by DATE_FORMAT(s.date_session, '%Y-%m');
            ");
            }


            /* $getTotalSession = DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.state = 'ACTIVE' 
            and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
            group by DATE_FORMAT(s.date_session, '%Y-%m');
        "); */



            $getUnPaidPatientSessions = DB::connection('mysql')->select("
        select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
    from phic p
    left join patients c on p.patient_id = c.id 
    where p.status = 'UNPAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor $withDoctorScript
 group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;");



            $getPaidPatientSessions = DB::connection('mysql')->select("
select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
from phic p
left join patients c on p.patient_id = c.id 
where p.status = 'PAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor $withDoctorScript
group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;");


            $arr['month'] = date_format(date_create(datetime: $value->schedule), 'F Y');
            $monthArr[] = date_format(date_create($value->schedule), 'F Y');
            $session = $value->cnt;
            $getMultiplier = 0;
            $arr['sessions'] = $session;
            if (Carbon::parse($value->rate_schedule)->lessThan('2024-07-01')) {
                $getMultiplier = ($rate1->value - 350);
                $gross = ($rate1->value - 350) * $session;
            }

            $givenDate = Carbon::parse($value->rate_schedule);
            $startDate = Carbon::parse('2024-07-01');
            $endDate = Carbon::parse('2024-10-08');
            if ($givenDate->between($startDate, $endDate)) {
                $getMultiplier = ($rate2->value - 350);
                $gross = ($rate2->value - 350) * $session;
            }

            // Parse the dates
            $givenDate = Carbon::parse($value->rate_schedule);
            $comparisonDate = Carbon::parse('2024-10-8');

            // Check if the given date is greater than the comparison date
            if ($givenDate->greaterThan($comparisonDate)) {
                $getMultiplier = ($rate3->value - 350);
                $gross = ($rate3->value - 350) * $session;
            }

            //$gross = 2250 * $session;
            if ($value->doctor == 6) {
                $share = $gross * 0.25;
                $tax = $share * 0.1;
                $net = $share * 0.9;
            } else {
                $share = $gross * 0.25;
                $tax = $share * 0.05;
                $net = $share * 0.95;
            }
            $pnet = 0;
            $balance = 0;
            if ($getPaidData) {
                //$pgross = 2250 * $getPaidData[0]->cnt;
                $pgross = $getMultiplier * $getPaidData[0]->cnt;
                $pshare = $pgross * 0.25;
                $ptax = $pshare * 0.05;
                $pnet = $pshare * 0.95;
                //$balance = $net - $pnet;
                $balance = $pnet - $net;
            }

            $paidAmt = $getPaidData ? $getPaidData[0]->cnt : 0;
            //$unpaidAmt = $getPaidData ? $session - $getPaidData[0]->cnt : 0;
            $unpaidAmt = $getPaidData ? $session - $getPaidData[0]->cnt : $session;

            $unpaid_pnet = 0;
            $unpaid_balance = 0;
            #calculate total_unpaid
            //if (!$getPaidData) {
            //$unpaid_pgross = 2250 * ($session - $getPaidData[0]->cnt);
            //$unpaid_pgross = $getMultiplier * ($session);
            $unpaid_pgross = $getMultiplier * ($unpaidAmt);
            $unpaid_pshare = $unpaid_pgross * 0.25;
            $unpaid_ptax = $unpaid_pshare * 0.05;
            $unpaid_pnet = $unpaid_pshare * 0.95;
            //$unpaid_balance = $net - $unpaid_pnet;
            $unpaid_balance = $unpaid_pnet;
            //}


            $getUnPaidPatientSessions_list = array();
            $total_upaid = 0;
            $total_upaid_Sessions = 0;
            foreach ($getUnPaidPatientSessions as $key_up => $value_up) {
                $arr_up = array();
                $arr_up['cnt'] = $value_up->cnt;
                $arr_up['cnt_sess'] = $value_up->cnt_sess;
                $arr_up['date_session'] = $value_up->date_session;
                $arr_up['id'] = $value_up->id;
                $arr_up['name'] = $value_up->name;
                $t = $getMultiplier * $value_up->cnt_sess;
                $arr_up['amt'] = $t;
                $total_upaid += $t;
                $total_upaid_Sessions += $value_up->cnt_sess;
                $getUnPaidPatientSessions_list[] = $arr_up;
            }

            $getPaidPatientSessions_list = array();
            $total_paid = 0;
            $total_paid_Sessions = 0;
            foreach ($getPaidPatientSessions as $key_up => $value_up) {
                $arr_up = array();
                $arr_up['cnt'] = $value_up->cnt;
                $arr_up['cnt_sess'] = $value_up->cnt_sess;
                $arr_up['date_session'] = $value_up->date_session;
                $arr_up['id'] = $value_up->id;
                $arr_up['name'] = $value_up->name;
                $t = $getMultiplier * $value_up->cnt_sess;
                $arr_up['amt'] = $t;
                $total_paid += $t;
                $total_paid_Sessions += $value_up->cnt_sess;
                $getPaidPatientSessions_list[] = $arr_up;
            }

            $calc_multiplier = $total_upaid_Sessions * $getMultiplier;
            $calc_25 = $calc_multiplier * 0.25;
            $calc_5 = $calc_25 * 0.05;
            $calc_95 = $calc_25 * 0.95;
            $getUnPaidPatientSessions_list_additional['cnt'] = $calc_5;
            $getUnPaidPatientSessions_list_additional['cnt_sess'] = $calc_25;//total_upaid_Sessions*$getMultiplier;
            $getUnPaidPatientSessions_list_additional['date_session'] = '';
            $getUnPaidPatientSessions_list_additional['amt'] = $calc_95;
            $getUnPaidPatientSessions_list_additional['id'] = 1;
            $getUnPaidPatientSessions_list_additional['name'] = "25% Premier Sharing";
            $getUnPaidPatientSessions_list[] = $getUnPaidPatientSessions_list_additional;


            $calc_multiplier_paid = $total_paid_Sessions * $getMultiplier;
            $calc_25_paid = $calc_multiplier_paid * 0.25;
            $calc_5_paid = $calc_25_paid * 0.05;
            $calc_95_paid = $calc_25_paid * 0.95;
            $getPaidPatientSessions_list_additional['cnt'] = $calc_5_paid;
            $getPaidPatientSessions_list_additional['cnt_sess'] = $calc_25_paid;//total_upaid_Sessions*$getMultiplier;
            $getPaidPatientSessions_list_additional['date_session'] = '';
            $getPaidPatientSessions_list_additional['amt'] = $calc_95_paid;
            $getPaidPatientSessions_list_additional['id'] = 1;
            $getPaidPatientSessions_list_additional['name'] = "25% Premier Sharing";
            $getPaidPatientSessions_list[] = $getPaidPatientSessions_list_additional;

            $arr['gross'] = $gross;
            $arr['value->rate_schedule'] = $value->rate_schedule;
            $arr['share'] = $share;
            $arr['tax'] = $tax;
            $arr['net'] = $net;
            $net_arr2[] = $net;
            $arr['datesByMonth'] = $datesByMonth;

            $net_arr['data'] = $net; //$net_arr2;
            $net_arr['name'] = "Net";
            $cntAllpaid += $paidAmt;
            $cntAllUnpaid += $unpaidAmt;
            $arr['session_paid'] = $paidAmt;
            $arr['session_unpaid'] = $unpaidAmt;
            $arr['total'] = $pnet;
            $arr['total_unpaid'] = $unpaid_pnet;
            $arr['total_unpaid_for_summary'] = $total_upaid;
            $arr['total_paid_for_summary'] = $total_paid;
            $arr['balance'] = $balance;
            $arr['getPaidData'] = $getPaidData;
            $llll = $getPaidData ? $getPaidData[0]->cnt : 0;
            $arr['getPaidDataxxx'] = $session . ' ' . $llll;// . ' ' . $getTotalSession[0]->cnt;
            $arr['getUnPaidPatientSessions'] = $getUnPaidPatientSessions_list;
            $arr['getPaidPatientSessions'] = $getPaidPatientSessions_list;
            $arr['total_payment'] = $unpaid_pnet + $pnet;
            $data_array[] = $arr;
            $netArr[] = $session;
            $netAmtArr[] = $netArr;
            $totalNet += $net;
            $totalPaid += $pnet;
            //$totalBalance += $balance; WORKING
            $totalBalance += $unpaid_balance;
        }

        if ($request->data['status'] == "Unpaid") {
            $claimStatus = "p.status = 'UNPAID' and";
        } elseif ($request->data['status'] == "Paid") {
            $claimStatus = "p.status = 'PAID' and";
        } else {
            $claimStatus = " p.status in ('PAID','UNPAID')    and";
        }


        $getPatientAllSessions = DB::connection('mysql')->select(" 
      select c.name,p.patient_id,count(p.date_session) as cnt, p.doctor as docid,GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ',') as dates" .
            ",GROUP_CONCAT(p.date_session  SEPARATOR '|') as fdates" .
            ", GROUP_CONCAT( (select name from doctors where id = p.doctor group by name ) SEPARATOR ',') as doc-- , d.name
    from phic p
    left join patients c on p.patient_id = c.id 
    where $claimStatus  state = 'ACTIVE'  and  DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate'  and '$tdate' $withDoctorScript group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id $grpByDoctor ;");


        $cntAll = 0;
        $formatAllSessions = array();
        foreach ($getPatientAllSessions as $key => $value) {
            $pid = $value->patient_id;
            $skeds = explode("|", $value->fdates);
            $sked_str = '';
            foreach ($skeds as $skey => $svalue) {
                $sked_str .= "'" . $svalue . "',";
            }
            $str = implode(',', array_unique(explode(',', $value->doc)));
            $sked_str = rtrim($sked_str, ", ");
            $check_session = DB::connection('mysql')->select("Select * from schedule where patient_id = $pid and schedule in ($sked_str) and status = 'Active'");
            $newDate = '';
            foreach ($check_session as $ckey => $cvalue) {
                $newDate .= date_format((date_create($cvalue->schedule)), 'F d,Y') . ', ';
            }

            $arr = array();
            $arr['cnt'] = count($check_session);//$value->cnt;
            $arr['dates'] = $newDate;//$value->dates;
            $arr['fdates'] = $value->fdates;
            $arr['id'] = $value->docid;
            $arr['check_session'] = $check_session;
            $str = implode(',', array_unique(explode(',', $value->doc)));
            $arr['doc'] = $str;
            $arr['name'] = $value->name;
            $cntAll += count($check_session);
            $formatAllSessions[] = $arr;
        }


        $datasets = array();
        $datasets["data"] = $data_array;
        $datasets["data2"] = $data;
        $datasets["tdate"] = $tdate;
        $datasets["fdate"] = $fdate;
        $datasets['getPatientAllSessions'] = $formatAllSessions;
        $datasets['cntAll'] = $cntAll;
        $datasets['allunpaid'] = $cntAllUnpaid;
        $datasets['allpaid'] = $cntAllpaid;
        $datasets["month"] = $monthArr;
        $datasets["net"] = array(["name" => 'Net', 'data' => $netArr]);
        $datasets["totalNet"] = $totalNet;
        $datasets["totalPaid"] = $totalPaid;
        $datasets["totalBalance"] = $totalBalance;
        $datasets["sql"] = " ";
        return response()->json($datasets);
    }

    public function revenue3(Request $request)
    {
        $rate1 = Settings::where('name', 'rate1')->first();
        $rate2 = Settings::where('name', 'rate2')->first();
        $rate3 = Settings::where('name', 'rate3')->first();
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->data['fdate']), 'Y-m-d');
        $tdate = date_format(date_create($request->data['tdate']), 'Y-m-d');
        $doctor = $request->data['doctor'];
        $datesByMonth = $this->getStartAndEndDatesByMonth($request->data['fdate'], $request->data['tdate']);
        if ($request->data['doctor'] == 0) {
            $data = DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,DATE_FORMAT(s.schedule, '%Y-%m') as month,DATE_FORMAT(s.schedule, '%Y-%m-%d') as rate_schedule, s.doctor FROM `schedule` s where s.status = 'ACTIVE' 
            and DATE_FORMAT(s.schedule, '%Y-%m-%d') between '$fdate' and '$tdate' 
            group by DATE_FORMAT(s.schedule, '%Y-%m');
        ");
        } else {
            $data = DB::connection('mysql')->select("
        SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,DATE_FORMAT(s.schedule, '%Y-%m') as month,DATE_FORMAT(s.schedule, '%Y-%m-%d') as rate_schedule, s.doctor FROM `schedule` s where s.status = 'ACTIVE' 
        and DATE_FORMAT(s.schedule, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
        group by DATE_FORMAT(s.schedule, '%Y-%m');
    ");
        }
        $withDoctorScript = '';
        $grpByDoctor = '';
        if ($doctor != 0) {
            $withDoctorScript = " and p.doctor = $doctor";
            $grpByDoctor = ', p.doctor';
        }

        $cntAllUnpaid = 0;
        $cntAllpaid = 0;
        $data_array = array();
        $monthArr = array();
        $netArr = array();
        $netAmtArr = array();
        $totalNet = 0;
        $totalPaid = 0;
        $totalBalance = 0;
        foreach ($data as $key => $value) {
            $arr = array();
            $mon_arr = array();
            $net_arr = array();
            $net_arr2 = array();

            $month = date_format(date_create($value->schedule), 'Y-m');
            $yearF = date_format(date_create($value->schedule), 'Y') . '-01';
            $yearT = date_format(date_create($value->schedule), 'Y') . '-12';
            if ($request->data['doctor'] == 0) {

                /*  $getPaidData = DB::connection('mysql')->select("
             SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
             and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
             group by DATE_FORMAT(s.date_session, '%Y-%m');
         "); */

                $getPaidData = DB::connection('mysql')->select("
                SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
                and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "'
                group by DATE_FORMAT(s.date_session, '%Y-%m');
            ");


            } else {
                $getPaidData = DB::connection('mysql')->select("
                SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
                and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor
                group by DATE_FORMAT(s.date_session, '%Y-%m');
            ");
            }


            /* $getTotalSession = DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.state = 'ACTIVE' 
            and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
            group by DATE_FORMAT(s.date_session, '%Y-%m');
        "); */



            $getUnPaidPatientSessions = DB::connection('mysql')->select("
        select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
    from phic p
    left join patients c on p.patient_id = c.id 
    where p.status = 'UNPAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor $withDoctorScript
 group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;");



            $getPaidPatientSessions = DB::connection('mysql')->select("
select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
from phic p
left join patients c on p.patient_id = c.id 
where p.status = 'PAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor $withDoctorScript
group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;");


            $arr['month'] = date_format(date_create(datetime: $value->schedule), 'F Y');
            $monthArr[] = date_format(date_create($value->schedule), 'F Y');
            $session = $value->cnt;
            $getMultiplier = 0;
            $arr['sessions'] = $session;
            if (Carbon::parse($value->rate_schedule)->lessThan('2024-07-01')) {
                $getMultiplier = ($rate1->value - 350);
                $gross = ($rate1->value - 350) * $session;
            }

            $givenDate = Carbon::parse($value->rate_schedule);
            $startDate = Carbon::parse('2024-07-01');
            $endDate = Carbon::parse('2024-10-08');
            if ($givenDate->between($startDate, $endDate)) {
                $getMultiplier = ($rate2->value - 350);
                $gross = ($rate2->value - 350) * $session;
            }

            // Parse the dates
            $givenDate = Carbon::parse($value->rate_schedule);
            $comparisonDate = Carbon::parse('2024-10-8');

            // Check if the given date is greater than the comparison date
            if ($givenDate->greaterThan($comparisonDate)) {
                $getMultiplier = ($rate3->value - 350);
                $gross = ($rate3->value - 350) * $session;
            }

            //$gross = 2250 * $session;
            if ($value->doctor == 6) {
                $share = $gross * 0.25;
                $tax = $share * 0.1;
                $net = $share * 0.9;
            } else {
                $share = $gross * 0.25;
                $tax = $share * 0.05;
                $net = $share * 0.95;
            }
            $pnet = 0;
            $balance = 0;
            if ($getPaidData) {
                //$pgross = 2250 * $getPaidData[0]->cnt;
                $pgross = $getMultiplier * $getPaidData[0]->cnt;
                $pshare = $pgross * 0.25;
                $ptax = $pshare * 0.05;
                $pnet = $pshare * 0.95;
                //$balance = $net - $pnet;
                $balance = $pnet - $net;
            }

            $paidAmt = $getPaidData ? $getPaidData[0]->cnt : 0;
            //$unpaidAmt = $getPaidData ? $session - $getPaidData[0]->cnt : 0;
            $unpaidAmt = $getPaidData ? $session - $getPaidData[0]->cnt : $session;

            $unpaid_pnet = 0;
            $unpaid_balance = 0;
            #calculate total_unpaid
            //if (!$getPaidData) {
            //$unpaid_pgross = 2250 * ($session - $getPaidData[0]->cnt);
            //$unpaid_pgross = $getMultiplier * ($session);
            $unpaid_pgross = $getMultiplier * ($unpaidAmt);
            $unpaid_pshare = $unpaid_pgross * 0.25;
            $unpaid_ptax = $unpaid_pshare * 0.05;
            $unpaid_pnet = $unpaid_pshare * 0.95;
            //$unpaid_balance = $net - $unpaid_pnet;
            $unpaid_balance = $unpaid_pnet;
            //}


            $getUnPaidPatientSessions_list = array();
            $total_upaid = 0;
            $total_upaid_Sessions = 0;
            foreach ($getUnPaidPatientSessions as $key_up => $value_up) {
                $arr_up = array();
                $arr_up['cnt'] = $value_up->cnt;
                $arr_up['cnt_sess'] = $value_up->cnt_sess;
                $arr_up['date_session'] = $value_up->date_session;
                $arr_up['id'] = $value_up->id;
                $arr_up['name'] = $value_up->name;
                $t = $getMultiplier * $value_up->cnt_sess;
                $arr_up['amt'] = $t;
                $total_upaid += $t;
                $total_upaid_Sessions += $value_up->cnt_sess;
                $getUnPaidPatientSessions_list[] = $arr_up;
            }

            $getPaidPatientSessions_list = array();
            $total_paid = 0;
            $total_paid_Sessions = 0;
            foreach ($getPaidPatientSessions as $key_up => $value_up) {
                $arr_up = array();
                $arr_up['cnt'] = $value_up->cnt;
                $arr_up['cnt_sess'] = $value_up->cnt_sess;
                $arr_up['date_session'] = $value_up->date_session;
                $arr_up['id'] = $value_up->id;
                $arr_up['name'] = $value_up->name;
                $t = $getMultiplier * $value_up->cnt_sess;
                $arr_up['amt'] = $t;
                $total_paid += $t;
                $total_paid_Sessions += $value_up->cnt_sess;
                $getPaidPatientSessions_list[] = $arr_up;
            }

            $calc_multiplier = $total_upaid_Sessions * $getMultiplier;
            $calc_25 = $calc_multiplier * 0.25;
            $calc_5 = $calc_25 * 0.05;
            $calc_95 = $calc_25 * 0.95;
            $getUnPaidPatientSessions_list_additional['cnt'] = $calc_5;
            $getUnPaidPatientSessions_list_additional['cnt_sess'] = $calc_25;//total_upaid_Sessions*$getMultiplier;
            $getUnPaidPatientSessions_list_additional['date_session'] = '';
            $getUnPaidPatientSessions_list_additional['amt'] = $calc_95;
            $getUnPaidPatientSessions_list_additional['id'] = 1;
            $getUnPaidPatientSessions_list_additional['name'] = "25% Premier Sharing";
            $getUnPaidPatientSessions_list[] = $getUnPaidPatientSessions_list_additional;


            $calc_multiplier_paid = $total_paid_Sessions * $getMultiplier;
            $calc_25_paid = $calc_multiplier_paid * 0.25;
            $calc_5_paid = $calc_25_paid * 0.05;
            $calc_95_paid = $calc_25_paid * 0.95;
            $getPaidPatientSessions_list_additional['cnt'] = $calc_5_paid;
            $getPaidPatientSessions_list_additional['cnt_sess'] = $calc_25_paid;//total_upaid_Sessions*$getMultiplier;
            $getPaidPatientSessions_list_additional['date_session'] = '';
            $getPaidPatientSessions_list_additional['amt'] = $calc_95_paid;
            $getPaidPatientSessions_list_additional['id'] = 1;
            $getPaidPatientSessions_list_additional['name'] = "25% Premier Sharing";
            $getPaidPatientSessions_list[] = $getPaidPatientSessions_list_additional;

            $arr['gross'] = $gross;
            $arr['value->rate_schedule'] = $value->rate_schedule;
            $arr['share'] = $share;
            $arr['tax'] = $tax;
            $arr['net'] = $net;
            $net_arr2[] = $net;
            $arr['datesByMonth'] = $datesByMonth;

            $net_arr['data'] = $net; //$net_arr2;
            $net_arr['name'] = "Net";
            $cntAllpaid += $paidAmt;
            $cntAllUnpaid += $unpaidAmt;
            $arr['session_paid'] = $paidAmt;
            $arr['session_unpaid'] = $unpaidAmt;
            $arr['total'] = $pnet;
            $arr['total_unpaid'] = $unpaid_pnet;
            $arr['total_unpaid_for_summary'] = $total_upaid;
            $arr['total_paid_for_summary'] = $total_paid;
            $arr['balance'] = $balance;
            $arr['getPaidData'] = $getPaidData;
            $llll = $getPaidData ? $getPaidData[0]->cnt : 0;
            $arr['getPaidDataxxx'] = $session . ' ' . $llll;// . ' ' . $getTotalSession[0]->cnt;
            $arr['getUnPaidPatientSessions'] = $getUnPaidPatientSessions_list;
            $arr['getPaidPatientSessions'] = $getPaidPatientSessions_list;
            $arr['total_payment'] = $unpaid_pnet + $pnet;
            $data_array[] = $arr;
            $netArr[] = $session;
            $netAmtArr[] = $netArr;
            $totalNet += $net;
            $totalPaid += $pnet;
            //$totalBalance += $balance; WORKING
            $totalBalance += $unpaid_balance;
        }

        if ($request->data['status'] == "Unpaid") {
            $claimStatus = "p.status = 'UNPAID' and";
        } elseif ($request->data['status'] == "Paid") {
            $claimStatus = "p.status = 'PAID' and";
        } else {
            $claimStatus = " p.status in ('PAID','UNPAID')    and";
        }


        $getPatientAllSessions = DB::connection('mysql')->select(" 
      select c.name,p.patient_id,count(p.date_session) as cnt, p.doctor as docid,GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ',') as dates" .
            ",GROUP_CONCAT(p.date_session  SEPARATOR '|') as fdates" .
            ", GROUP_CONCAT( (select name from doctors where id = p.doctor group by name ) SEPARATOR ',') as doc-- , d.name
    from phic p
    left join patients c on p.patient_id = c.id 
    where $claimStatus  state = 'ACTIVE'  and  DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate'  and '$tdate' $withDoctorScript group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id $grpByDoctor ;");

        /* $getPatientAllSessions = DB::connection('mysql')->select("SELECT 
                sub.name,
                sum(sub.cnt) as sessions,
                sub.doctor,
                sub.date_session
            FROM (
                SELECT 
                    COUNT(p.id) AS cnt, 
                    d.name, 
                    p.doctor,
                    p.date_session
                FROM phic p
                LEFT JOIN doctors d ON d.id = p.doctor
            where
            $claimStatus
            p.state = 'ACTIVE' 
            and p.doctor = $doctor
            and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
            $withDoctorScript
            ) AS sub
            "); */

        $cntAll = 0;
        $formatAllSessions = array();
        foreach ($getPatientAllSessions as $key => $value) {
            $pid = $value->patient_id;
            $skeds = explode("|", $value->fdates);
            $sked_str = '';
            foreach ($skeds as $skey => $svalue) {
                $sked_str .= "'" . $svalue . "',";
            }
            $str = implode(',', array_unique(explode(',', $value->doc)));
            $sked_str = rtrim($sked_str, ", ");
            //$check_session = DB::connection('mysql')->select("Select * from schedule where patient_id = $pid and schedule in ($sked_str) and status = 'Active'");
            //$check_session = DB::connection('mysql')->select("Select * from schedule s left join phic p on s.patient_id = p.patient_id where s.patient_id = $pid and p.date_session in ($sked_str) and p.state = 'Active'");
            $check_session = DB::connection('mysql')->select("Select * from phic p where p.patient_id = $pid and p.date_session in ($sked_str) and p.state = 'Active' and p.status in ('PAID','UNPAID')");
            $newDate = '';
            foreach ($check_session as $ckey => $cvalue) {
                //$newDate .= date_format((date_create($cvalue->schedule)), 'F d,Y') . ', ';
                $newDate .= date_format((date_create($cvalue->date_session)), 'F d,Y') . ', ';
            }

            $arr = array();
            $arr['cnt'] = count($check_session);//$value->cnt;
            $arr['dates'] = $newDate;//$value->dates;
            $arr['fdates'] = $value->fdates;
            $arr['id'] = $value->docid;
            $arr['check_session'] = $check_session;
            $str = implode(',', array_unique(explode(',', $value->doc)));
            $arr['doc'] = $str;
            $arr['name'] = $value->name;
            $cntAll += count($check_session);
            $formatAllSessions[] = $arr;
        }


        $datasets = array();
        $datasets["data"] = $data_array;
        $datasets["data2"] = $data;
        $datasets["tdate"] = $tdate;
        $datasets["fdate"] = $fdate;
        $datasets['getPatientAllSessions'] = $formatAllSessions;
        $datasets['cntAll'] = $cntAll;
        $datasets['allunpaid'] = $cntAllUnpaid;
        $datasets['allpaid'] = $cntAllpaid;
        $datasets["month"] = $monthArr;
        $datasets["net"] = array(["name" => 'Net', 'data' => $netArr]);
        $datasets["totalNet"] = $totalNet;
        $datasets["totalPaid"] = $totalPaid;
        $datasets["totalBalance"] = $totalBalance;
        $datasets["sql"] = " ";
        return response()->json($datasets);
    }

    public function revenue5(Request $request)
    {
        $rate1 = Settings::where('name', 'rate1')->first();
        $rate2 = Settings::where('name', 'rate2')->first();
        $rate3 = Settings::where('name', 'rate3')->first();
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->data['fdate']), 'Y-m-d');
        $tdate = date_format(date_create($request->data['tdate']), 'Y-m-d');
        $doctor = $request->data['doctor'];
        $datesByMonth = $this->getStartAndEndDatesByMonth($request->data['fdate'], $request->data['tdate']);

        if ($request->data['status'] == "Unpaid") {
            $claimStatus_phic = "s.status = 'UNPAID' and";
        } elseif ($request->data['status'] == "Paid") {
            $claimStatus_phic = "s.status = 'PAID' and";
        } else {
            $claimStatus_phic = " s.status in ('PAID','UNPAID')    and";
        }



        if ($request->data['doctor'] == 0) {
            /* $data = DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,DATE_FORMAT(s.schedule, '%Y-%m') as month,DATE_FORMAT(s.schedule, '%Y-%m-%d') as rate_schedule, s.doctor FROM `schedule` s where s.status = 'ACTIVE' 
            and DATE_FORMAT(s.schedule, '%Y-%m-%d') between '$fdate' and '$tdate' 
            group by DATE_FORMAT(s.schedule, '%Y-%m');
        "); */

            $data = DB::connection('mysql')->select("
        SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule,DATE_FORMAT(s.date_session, '%Y-%m') as month,DATE_FORMAT(s.date_session, '%Y-%m-%d') as rate_schedule, s.doctor FROM `phic` s where 
        $claimStatus_phic s.state = 'ACTIVE' 
        and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate' 
        group by DATE_FORMAT(s.date_session, '%Y-%m');
    ");
        } else {
            $data = DB::connection('mysql')->select("
        SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,DATE_FORMAT(s.schedule, '%Y-%m') as month,DATE_FORMAT(s.schedule, '%Y-%m-%d') as rate_schedule, s.doctor FROM `schedule` s where s.status = 'ACTIVE' 
        and DATE_FORMAT(s.schedule, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
        group by DATE_FORMAT(s.schedule, '%Y-%m');
    ");
        }
        $withDoctorScript = '';
        $grpByDoctor = '';
        if ($doctor != 0) {
            $withDoctorScript = " and p.doctor = $doctor";
            $grpByDoctor = ', p.doctor';
        }

        $cntAllUnpaid = 0;
        $cntAllpaid = 0;
        $data_array = array();
        $monthArr = array();
        $netArr = array();
        $netAmtArr = array();
        $totalNet = 0;
        $totalPaid = 0;
        $totalBalance = 0;
        foreach ($data as $key => $value) {
            $arr = array();
            $mon_arr = array();
            $net_arr = array();
            $net_arr2 = array();

            $month = date_format(date_create($value->schedule), 'Y-m');
            $yearF = date_format(date_create($value->schedule), 'Y') . '-01';
            $yearT = date_format(date_create($value->schedule), 'Y') . '-12';
            if ($request->data['doctor'] == 0) {

                /*  $getPaidData = DB::connection('mysql')->select("
             SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
             and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
             group by DATE_FORMAT(s.date_session, '%Y-%m');
         "); */

                $getPaidData = DB::connection('mysql')->select("
                SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
                and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "'
                group by DATE_FORMAT(s.date_session, '%Y-%m');
            ");


            } else {
                $getPaidData = DB::connection('mysql')->select("
                SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
                and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor
                group by DATE_FORMAT(s.date_session, '%Y-%m');
            ");
            }


            /* $getTotalSession = DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.state = 'ACTIVE' 
            and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
            group by DATE_FORMAT(s.date_session, '%Y-%m');
        "); */



            $getUnPaidPatientSessions = DB::connection('mysql')->select("
        select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
    from phic p
    left join patients c on p.patient_id = c.id 
    where p.status = 'UNPAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor $withDoctorScript
 group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;");



            $getPaidPatientSessions = DB::connection('mysql')->select("
select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
from phic p
left join patients c on p.patient_id = c.id 
where p.status = 'PAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor $withDoctorScript
group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;");


            $arr['month'] = date_format(date_create($value->schedule), 'F Y');
            $monthArr[] = date_format(date_create($value->schedule), 'F Y');
            $session = $value->cnt;
            $getMultiplier = 0;
            $arr['sessions'] = $session;
            if (Carbon::parse($value->rate_schedule)->lessThan('2024-07-01')) {
                $getMultiplier = ($rate1->value - 350);
                $gross = ($rate1->value - 350) * $session;
            }

            $givenDate = Carbon::parse($value->rate_schedule);
            $startDate = Carbon::parse('2024-07-01');
            $endDate = Carbon::parse('2024-10-08');
            if ($givenDate->between($startDate, $endDate)) {
                $getMultiplier = ($rate2->value - 350);
                $gross = ($rate2->value - 350) * $session;
            }

            // Parse the dates
            $givenDate = Carbon::parse($value->rate_schedule);
            $comparisonDate = Carbon::parse('2024-10-8');

            // Check if the given date is greater than the comparison date
            if ($givenDate->greaterThan($comparisonDate)) {
                $getMultiplier = ($rate3->value - 350);
                $gross = ($rate3->value - 350) * $session;
            }

            //$gross = 2250 * $session;
            if ($value->doctor == 6) {
                $share = $gross * 0.25;
                $tax = $share * 0.1;
                $net = $share * 0.9;
            } else {
                $share = $gross * 0.25;
                $tax = $share * 0.05;
                $net = $share * 0.95;
            }
            $pnet = 0;
            $balance = 0;
            if ($getPaidData) {
                //$pgross = 2250 * $getPaidData[0]->cnt;
                $pgross = $getMultiplier * $getPaidData[0]->cnt;
                $pshare = $pgross * 0.25;
                $ptax = $pshare * 0.05;
                $pnet = $pshare * 0.95;
                //$balance = $net - $pnet;
                $balance = $pnet - $net;
            }

            $paidAmt = $getPaidData ? $getPaidData[0]->cnt : 0;
            //$unpaidAmt = $getPaidData ? $session - $getPaidData[0]->cnt : 0;
            $unpaidAmt = $getPaidData ? $session - $getPaidData[0]->cnt : $session;

            $unpaid_pnet = 0;
            $unpaid_balance = 0;
            #calculate total_unpaid
            //if (!$getPaidData) {
            //$unpaid_pgross = 2250 * ($session - $getPaidData[0]->cnt);
            //$unpaid_pgross = $getMultiplier * ($session);
            $unpaid_pgross = $getMultiplier * ($unpaidAmt);
            $unpaid_pshare = $unpaid_pgross * 0.25;
            $unpaid_ptax = $unpaid_pshare * 0.05;
            $unpaid_pnet = $unpaid_pshare * 0.95;
            //$unpaid_balance = $net - $unpaid_pnet;
            $unpaid_balance = $unpaid_pnet;
            //}


            $getUnPaidPatientSessions_list = array();
            $total_upaid = 0;
            $total_upaid_Sessions = 0;
            foreach ($getUnPaidPatientSessions as $key_up => $value_up) {
                $arr_up = array();
                $arr_up['cnt'] = $value_up->cnt;
                $arr_up['cnt_sess'] = $value_up->cnt_sess;
                $arr_up['date_session'] = $value_up->date_session;
                $arr_up['id'] = $value_up->id;
                $arr_up['name'] = $value_up->name;
                $t = $getMultiplier * $value_up->cnt_sess;
                $arr_up['amt'] = $t;
                $total_upaid += $t;
                $total_upaid_Sessions += $value_up->cnt_sess;
                $getUnPaidPatientSessions_list[] = $arr_up;
            }

            $getPaidPatientSessions_list = array();
            $total_paid = 0;
            $total_paid_Sessions = 0;
            foreach ($getPaidPatientSessions as $key_up => $value_up) {
                $arr_up = array();
                $arr_up['cnt'] = $value_up->cnt;
                $arr_up['cnt_sess'] = $value_up->cnt_sess;
                $arr_up['date_session'] = $value_up->date_session;
                $arr_up['id'] = $value_up->id;
                $arr_up['name'] = $value_up->name;
                $t = $getMultiplier * $value_up->cnt_sess;
                $arr_up['amt'] = $t;
                $total_paid += $t;
                $total_paid_Sessions += $value_up->cnt_sess;
                $getPaidPatientSessions_list[] = $arr_up;
            }

            $calc_multiplier = $total_upaid_Sessions * $getMultiplier;
            $calc_25 = $calc_multiplier * 0.25;
            $calc_5 = $calc_25 * 0.05;
            $calc_95 = $calc_25 * 0.95;
            $getUnPaidPatientSessions_list_additional['cnt'] = $calc_5;
            $getUnPaidPatientSessions_list_additional['cnt_sess'] = $calc_25;//total_upaid_Sessions*$getMultiplier;
            $getUnPaidPatientSessions_list_additional['date_session'] = '';
            $getUnPaidPatientSessions_list_additional['amt'] = $calc_95;
            $getUnPaidPatientSessions_list_additional['id'] = 1;
            $getUnPaidPatientSessions_list_additional['name'] = "25% Premier Sharing";
            $getUnPaidPatientSessions_list[] = $getUnPaidPatientSessions_list_additional;


            $calc_multiplier_paid = $total_paid_Sessions * $getMultiplier;
            $calc_25_paid = $calc_multiplier_paid * 0.25;
            $calc_5_paid = $calc_25_paid * 0.05;
            $calc_95_paid = $calc_25_paid * 0.95;
            $getPaidPatientSessions_list_additional['cnt'] = $calc_5_paid;
            $getPaidPatientSessions_list_additional['cnt_sess'] = $calc_25_paid;//total_upaid_Sessions*$getMultiplier;
            $getPaidPatientSessions_list_additional['date_session'] = '';
            $getPaidPatientSessions_list_additional['amt'] = $calc_95_paid;
            $getPaidPatientSessions_list_additional['id'] = 1;
            $getPaidPatientSessions_list_additional['name'] = "25% Premier Sharing";
            $getPaidPatientSessions_list[] = $getPaidPatientSessions_list_additional;

            $arr['gross'] = $gross;
            $arr['value->rate_schedule'] = $value->rate_schedule;
            $arr['share'] = $share;
            $arr['tax'] = $tax;
            $arr['net'] = $net;
            $net_arr2[] = $net;
            $arr['datesByMonth'] = $datesByMonth;

            $net_arr['data'] = $net; //$net_arr2;
            $net_arr['name'] = "Net";
            $cntAllpaid += $paidAmt;
            $cntAllUnpaid += $unpaidAmt;
            $arr['session_paid'] = $paidAmt;
            $arr['session_unpaid'] = $unpaidAmt;
            $arr['total'] = $pnet;
            $arr['total_unpaid'] = $unpaid_pnet;
            $arr['total_unpaid_for_summary'] = $total_upaid;
            $arr['total_paid_for_summary'] = $total_paid;
            $arr['balance'] = $balance;
            $arr['getPaidData'] = $getPaidData;
            $llll = $getPaidData ? $getPaidData[0]->cnt : 0;
            $arr['getPaidDataxxx'] = $session . ' ' . $llll;// . ' ' . $getTotalSession[0]->cnt;
            $arr['getUnPaidPatientSessions'] = $getUnPaidPatientSessions_list;
            $arr['getPaidPatientSessions'] = $getPaidPatientSessions_list;
            $arr['total_payment'] = $unpaid_pnet + $pnet;
            $data_array[] = $arr;
            $netArr[] = $session;
            $netAmtArr[] = $netArr;
            $totalNet += $net;
            $totalPaid += $pnet;
            //$totalBalance += $balance; WORKING
            $totalBalance += $unpaid_balance;
        }

        if ($request->data['status'] == "Unpaid") {
            $claimStatus = "p.status = 'UNPAID' and";
        } elseif ($request->data['status'] == "Paid") {
            $claimStatus = "p.status = 'PAID' and";
        } else {
            $claimStatus = " p.status in ('PAID','UNPAID')    and";
        }


        $getPatientAllSessions = DB::connection('mysql')->select(" 
      select c.name,p.patient_id,count(p.date_session) as cnt, p.doctor as docid,GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ',') as dates" .
            ",GROUP_CONCAT(p.date_session  SEPARATOR '|') as fdates" .
            ", GROUP_CONCAT( (select name from doctors where id = p.doctor group by name ) SEPARATOR ',') as doc-- , d.name
    from phic p
    left join patients c on p.patient_id = c.id 
    where $claimStatus  state = 'ACTIVE'  and  DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate'  and '$tdate' $withDoctorScript group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id $grpByDoctor ;");

        /* $getPatientAllSessions = DB::connection('mysql')->select("SELECT 
                sub.name,
                sum(sub.cnt) as sessions,
                sub.doctor,
                sub.date_session
            FROM (
                SELECT 
                    COUNT(p.id) AS cnt, 
                    d.name, 
                    p.doctor,
                    p.date_session
                FROM phic p
                LEFT JOIN doctors d ON d.id = p.doctor
            where
            $claimStatus
            p.state = 'ACTIVE' 
            and p.doctor = $doctor
            and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
            $withDoctorScript
            ) AS sub
            "); */

        $cntAll = 0;
        $formatAllSessions = array();
        foreach ($getPatientAllSessions as $key => $value) {
            $pid = $value->patient_id;
            $skeds = explode("|", $value->fdates);
            $sked_str = '';
            foreach ($skeds as $skey => $svalue) {
                $sked_str .= "'" . $svalue . "',";
            }
            $str = implode(',', array_unique(explode(',', $value->doc)));
            $sked_str = rtrim($sked_str, ", ");
            //$check_session = DB::connection('mysql')->select("Select * from schedule where patient_id = $pid and schedule in ($sked_str) and status = 'Active'");
            //$check_session = DB::connection('mysql')->select("Select * from schedule s left join phic p on s.patient_id = p.patient_id where s.patient_id = $pid and p.date_session in ($sked_str) and p.state = 'Active'");
            $check_session = DB::connection('mysql')->select("Select * from phic p where p.patient_id = $pid and p.date_session in ($sked_str) and p.state = 'Active' and p.status in ('PAID','UNPAID')");
            $newDate = '';
            foreach ($check_session as $ckey => $cvalue) {
                //$newDate .= date_format((date_create($cvalue->schedule)), 'F d,Y') . ', ';
                $newDate .= date_format((date_create($cvalue->date_session)), 'F d,Y') . ', ';
            }

            $arr = array();
            $arr['cnt'] = count($check_session);//$value->cnt;
            $arr['dates'] = $newDate;//$value->dates;
            $arr['fdates'] = $value->fdates;
            $arr['id'] = $value->docid;
            $arr['check_session'] = $check_session;
            $str = implode(',', array_unique(explode(',', $value->doc)));
            $arr['doc'] = $str;
            $arr['name'] = $value->name;
            $cntAll += count($check_session);
            $formatAllSessions[] = $arr;
        }


        $datasets = array();
        $datasets["data"] = $data_array;
        $datasets["data2"] = $data;
        $datasets["tdate"] = $tdate;
        $datasets["fdate"] = $fdate;
        $datasets['getPatientAllSessions'] = $formatAllSessions;
        $datasets['cntAll'] = $cntAll;
        $datasets['allunpaid'] = $cntAllUnpaid;
        $datasets['allpaid'] = $cntAllpaid;
        $datasets["month"] = $monthArr;
        $datasets["net"] = array(["name" => 'Net', 'data' => $netArr]);
        $datasets["totalNet"] = $totalNet;
        $datasets["totalPaid"] = $totalPaid;
        $datasets["totalBalance"] = $totalBalance;
        $datasets["sql"] = " ";
        return response()->json($datasets);
    }

    public function revenue(Request $request)
    {
        $rate1 = Settings::where('name', 'rate1')->first();
        $rate2 = Settings::where('name', 'rate2')->first();
        $rate3 = Settings::where('name', 'rate3')->first();
        $rate4 = Settings::where('name', 'rate4')->first();
        $getNewUP = 0;
        $getNewP = 0;
        date_default_timezone_set('Asia/Manila');
        $fdate = date_format(date_create($request->data['fdate']), 'Y-m-d');
        $tdate = date_format(date_create($request->data['tdate']), 'Y-m-d');
        $doctor = $request->data['doctor'];
        $datesByMonth = $this->getStartAndEndDatesByMonth($request->data['fdate'], $request->data['tdate']);

        if ($request->data['status'] == "Unpaid") {
            $claimStatus_phic = "s.status = 'UNPAID' and";
        } elseif ($request->data['status'] == "Paid") {
            $claimStatus_phic = "s.status = 'PAID' and";
        } else {
            $claimStatus_phic = " s.status in ('PAID','UNPAID')    and";
        }


        if ($request->data['doctor'] == 0) {
            /* $data = DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,DATE_FORMAT(s.schedule, '%Y-%m') as month,DATE_FORMAT(s.schedule, '%Y-%m-%d') as rate_schedule, s.doctor FROM `schedule` s where s.status = 'ACTIVE' 
            and DATE_FORMAT(s.schedule, '%Y-%m-%d') between '$fdate' and '$tdate' 
            group by DATE_FORMAT(s.schedule, '%Y-%m');
        "); */

            $data = DB::connection('mysql')->select("
        SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule,DATE_FORMAT(s.date_session, '%Y-%m') as month,DATE_FORMAT(s.date_session, '%Y-%m-%d') as rate_schedule, s.doctor FROM `phic` s where 
        $claimStatus_phic s.state = 'ACTIVE' 
        and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate' 
        group by DATE_FORMAT(s.date_session, '%Y-%m');
    ");
        } else {
            /* $data = DB::connection('mysql')->select("
        SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.schedule, '%Y-%m') as schedule,DATE_FORMAT(s.schedule, '%Y-%m') as month,DATE_FORMAT(s.schedule, '%Y-%m-%d') as rate_schedule, s.doctor FROM `schedule` s where s.status = 'ACTIVE' 
        and DATE_FORMAT(s.schedule, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
        group by DATE_FORMAT(s.schedule, '%Y-%m'); */

            $data = DB::connection('mysql')->select("
        SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule,DATE_FORMAT(s.date_session, '%Y-%m') as month,DATE_FORMAT(s.date_session, '%Y-%m-%d') as rate_schedule, s.doctor FROM `phic` s where 
        $claimStatus_phic s.state = 'ACTIVE' 
        and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
        group by DATE_FORMAT(s.date_session, '%Y-%m');
    ");
        }
        $withDoctorScript = '';
        $grpByDoctor = '';
        if ($doctor != 0) {
            $withDoctorScript = " and p.doctor = $doctor";
            $grpByDoctor = ', p.doctor';
        }

        $cntAllUnpaid = 0;
        $cntAllpaid = 0;
        $data_array = array();
        $monthArr = array();
        $netArr = array();
        $netAmtArr = array();
        $totalNet = 0;
        $totalPaid = 0;
        $totalBalance = 0;
        foreach ($data as $key => $value) {
            $arr = array();
            $mon_arr = array();
            $net_arr = array();
            $net_arr2 = array();

            $month = date_format(date_create($value->schedule), 'Y-m');
            $yearF = date_format(date_create($value->schedule), 'Y') . '-01';
            $yearT = date_format(date_create($value->schedule), 'Y') . '-12';
            if ($request->data['doctor'] == 0) {

                /*  $getPaidData = DB::connection('mysql')->select("
             SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
             and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
             group by DATE_FORMAT(s.date_session, '%Y-%m');
         "); */

                $getPaidData = DB::connection('mysql')->select("
                SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
                and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "'
                group by DATE_FORMAT(s.date_session, '%Y-%m');
            ");


            } else {
                 /* $getPaidData = DB::connection('mysql')->select("
                SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
                and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor
                group by DATE_FORMAT(s.date_session, '%Y-%m');
            "); */

            $getPaidData = DB::connection('mysql')->select(" SELECT DISTINCT 
            s.date_session, 
            COUNT(s.patient_id) AS cnt, 
            DATE_FORMAT(s.date_session, '%Y-%m') AS schedule 
        FROM `phic` s 
        WHERE s.state = 'ACTIVE' 
        and s.status = 'PAID'
            AND DATE_FORMAT(s.date_session, '%Y-%m-%d') BETWEEN '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "'
            AND s.doctor = $doctor 
            AND EXISTS (
                SELECT 1 
                FROM `schedule` sch 
                WHERE sch.schedule = s.date_session
                and sch.patient_id = s.patient_id
                and sch.status = 'ACTIVE'
            ) 
        GROUP BY DATE_FORMAT(s.date_session, '%Y-%m');  ");
            }


            /* $getTotalSession = DB::connection('mysql')->select("
            SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.state = 'ACTIVE' 
            and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate' and doctor=$doctor
            group by DATE_FORMAT(s.date_session, '%Y-%m');
        "); */



            /* $getUnPaidPatientSessions = DB::connection('mysql')->select("
            select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
        from phic p
        left join patients c on p.patient_id = c.id 
        where p.status = 'UNPAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor $withDoctorScript
     group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;"); */

           /*  $getUnPaidPatientSessions = DB::connection('mysql')->select("
        select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
    from phic p
    left join patients c on p.patient_id = c.id 
    where p.status = 'UNPAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' $withDoctorScript
 group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;"); */

 $getUnPaidPatientSessions = DB::connection('mysql')->select("SELECT 
                        c.name,
                        c.id,
                        p.id AS phic_id,
                        p.date_session,
                        COUNT(p.date_session) AS cnt_sess,
                        GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') AS cnt
                    FROM phic p
                    LEFT JOIN patients c ON p.patient_id = c.id
                    WHERE p.status = 'UNPAID' 
                        AND p.state = 'ACTIVE'  
                        AND DATE_FORMAT(p.date_session, '%Y-%m-%d') BETWEEN  '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "'
                        AND EXISTS (
                            SELECT 1 
                            FROM schedule s         
                            WHERE s.schedule = p.date_session
                            and s.patient_id = p.patient_id
                            and s.status = 'ACTIVE'
                        )
                        $withDoctorScript
                    GROUP BY DATE_FORMAT(p.date_session, '%Y-%m'), p.patient_id;");


 


            /* $getPaidPatientSessions = DB::connection('mysql')->select("
            select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
            from phic p
            left join patients c on p.patient_id = c.id 
            where p.status = 'PAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' and doctor=$doctor $withDoctorScript
            group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;"); */

            $getPaidPatientSessions = DB::connection('mysql')->select("
 select c.name,c.id,p.id,p.date_session,count(p.date_session) as cnt_sess, GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ' | ') as cnt
 from phic p
 left join patients c on p.patient_id = c.id 
 where p.status = 'PAID' and p.state = 'ACTIVE'  and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '" . $datesByMonth[$value->schedule]['start'] . "' and '" . $datesByMonth[$value->schedule]['end'] . "' $withDoctorScript
 group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id;");

 
            $defaultPercent= 0.25;

            $arr['month'] = date_format(date_create($value->schedule), 'F Y');
            $monthArr[] = date_format(date_create($value->schedule), 'F Y');
            $session = $value->cnt;
            $getMultiplier = 0;
            // Use the start date of the month to determine which rate to use based on date_session
            $dateSession = Carbon::parse($datesByMonth[$value->schedule]['start']);
            
            // Sept 1, 2021 - Aug 31, 2023: use rate1 and 0.25
            $startDate0 = Carbon::parse('2021-09-01');
            $endDate0 = Carbon::parse('2023-08-31');
            // Sept 1, 2023 - June 30, 2024: use rate1 and 0.27
            $startDate1 = Carbon::parse('2023-09-01');
            $endDate1 = Carbon::parse('2024-06-30');
            // July 1, 2024 - Oct 8, 2024: use rate2 and 0.27
            $startDate2 = Carbon::parse('2024-07-01');
            $endDate2 = Carbon::parse('2024-10-08');
            // Oct 9, 2024 - Aug 31, 2025: use rate4 and 0.27
            $startDate3 = Carbon::parse('2024-10-09');
            $endDate3 = Carbon::parse('2025-08-31');
            // Sept 1, 2025 - present: use rate3 and 0.27
            $startDate4 = Carbon::parse('2025-09-01');
            
            if ($dateSession->greaterThanOrEqualTo($startDate0) && $dateSession->lessThanOrEqualTo($endDate0)) {
                $getMultiplier = ($rate1->value - 0.25);
                $defaultPercent = 0.25;
                $gross = ($rate1->value - 350) * $session;
            }
            elseif ($dateSession->greaterThanOrEqualTo($startDate1) && $dateSession->lessThanOrEqualTo($endDate1)) {
                $getMultiplier = ($rate1->value - 0.27);
                $defaultPercent= 0.27;    
                $gross = ($rate1->value - 350) * $session;
            }
            elseif ($dateSession->greaterThanOrEqualTo($startDate2) && $dateSession->lessThanOrEqualTo($endDate2)) {
                $getMultiplier = ($rate2->value - 0.27);
                $defaultPercent= 0.27;    
                $gross = ($rate2->value - 350) * $session;
            }
            elseif ($dateSession->greaterThanOrEqualTo($startDate3) && $dateSession->lessThanOrEqualTo($endDate3)) {
                $getMultiplier = ($rate4->value - 0.27);
                $defaultPercent= 0.27;    
                $gross = ($rate4->value - 350) * $session;
            }
            elseif ($dateSession->greaterThanOrEqualTo($startDate4)) {
                $getMultiplier = ($rate3->value - 0.27);
                $defaultPercent= 0.3;    
                $gross = ($rate3->value - 350) * $session;
            }

            //$gross = 2250 * $session;
            if ($value->doctor == 6) {
                $share = $gross * $defaultPercent;//0.25;
                $tax = $share * 0.1;
                $net = $share * 0.9;
            } else {
                $share = $gross * $defaultPercent;//0.25;
                $tax = $share * 0.05;
                $net = $share * 0.95;
            }
            $pnet = 0;
            $balance = 0;
            if ($getPaidData) {
                //$pgross = 2250 * $getPaidData[0]->cnt;
                $pgross = $getMultiplier * $getPaidData[0]->cnt;
                $pshare = $pgross * 0.25;
                $ptax = $pshare * 0.05;
                $pnet = $pshare * 0.95;
                //$balance = $net - $pnet;
                $balance = $pnet - $net;
            }

            $getUnPaidPatientSessions_list = array();
            $total_upaid = 0;
            $total_upaid_Sessions = 0;
            foreach ($getUnPaidPatientSessions as $key_up => $value_up) {
                $arr_up = array();
                $arr_up['cnt'] = $value_up->cnt;
                $arr_up['cnt_sess'] = $value_up->cnt_sess;
                $arr_up['date_session'] = $value_up->date_session;
                $arr_up['id'] = $value_up->id;
                $arr_up['name'] = $value_up->name;
                $t = $getMultiplier * $value_up->cnt_sess;
                $arr_up['amt'] = $t;
                $total_upaid += $t;
                $total_upaid_Sessions += $value_up->cnt_sess;
                $getUnPaidPatientSessions_list[] = $arr_up;
            }

            $getPaidPatientSessions_list = array();
            $total_paid = 0;
            $total_paid_Sessions = 0;
            foreach ($getPaidPatientSessions as $key_up => $value_up) {
                $arr_up = array();
                $arr_up['cnt'] = 1;//$value_up->cnt;
                $arr_up['cnt_sess'] = $value_up->cnt_sess;
                $arr_up['date_session'] = $value_up->date_session;
                $arr_up['id'] = $value_up->id;
                $arr_up['name'] = $value_up->name;
                $t = $getMultiplier * $value_up->cnt_sess;
                $arr_up['amt'] = $t;
                $total_paid += $t;
                $total_paid_Sessions += $value_up->cnt_sess;
                $getPaidPatientSessions_list[] = $arr_up;
            }

            $paidAmt = $getPaidData ? $getPaidData[0]->cnt : 0;
            //$unpaidAmt = $getPaidData ? $session - $getPaidData[0]->cnt : 0;
            $tnos = $paidAmt + $total_upaid_Sessions;
            $unpaidAmt = $getPaidData ? $session - $getPaidData[0]->cnt : $session;
            //$new_unpaidAmt = $tnos -  $session;
            $new_unpaidAmt = $total_upaid_Sessions;

            $unpaid_pnet = 0;
            $unpaid_balance = 0;
            #calculate total_unpaid
            //if (!$getPaidData) {
            //$unpaid_pgross = 2250 * ($session - $getPaidData[0]->cnt);
            //$unpaid_pgross = $getMultiplier * ($session);
            //$unpaid_pgross = $getMultiplier * ($unpaidAmt);
            $unpaid_pgross = $getMultiplier * ($new_unpaidAmt);
            $unpaid_pshare = $unpaid_pgross * 0.25;
            $unpaid_ptax = $unpaid_pshare * 0.05;
            $unpaid_pnet = $unpaid_pshare * 0.95;
            //$unpaid_balance = $net - $unpaid_pnet;
            $unpaid_balance = $unpaid_pnet;
            //}

            $calc_multiplier = $total_upaid_Sessions * $getMultiplier;
            $calc_25 = $calc_multiplier * 0.25;
            $calc_5 = $calc_25 * 0.05;
            $calc_95 = $calc_25 * 0.95;
            $getUnPaidPatientSessions_list_additional['cnt'] = $calc_5;
            $getUnPaidPatientSessions_list_additional['cnt_sess'] = $calc_25;//total_upaid_Sessions*$getMultiplier;
            $getUnPaidPatientSessions_list_additional['date_session'] = '';
            $getUnPaidPatientSessions_list_additional['amt'] = $calc_95;
            $getUnPaidPatientSessions_list_additional['id'] = 1;
            $getUnPaidPatientSessions_list_additional['name'] = "25% Premier Sharing";
            $getUnPaidPatientSessions_list[] = $getUnPaidPatientSessions_list_additional;


            $calc_multiplier_paid = $total_paid_Sessions * $getMultiplier;
            $calc_25_paid = $calc_multiplier_paid * 0.25;
            $calc_5_paid = $calc_25_paid * 0.05;
            $calc_95_paid = $calc_25_paid * 0.95;
            $getPaidPatientSessions_list_additional['cnt'] = $calc_5_paid;
            $getPaidPatientSessions_list_additional['cnt_sess'] = $calc_25_paid;//total_upaid_Sessions*$getMultiplier;
            $getPaidPatientSessions_list_additional['date_session'] = '';
            $getPaidPatientSessions_list_additional['amt'] = $calc_95_paid;
            $getPaidPatientSessions_list_additional['id'] = 1;
            $getPaidPatientSessions_list_additional['name'] = "25% Premier Sharing";
            $getPaidPatientSessions_list[] = $getPaidPatientSessions_list_additional;

            $arr['gross'] = $gross;
            $arr['value->rate_schedule'] = $value->rate_schedule;
            $arr['share'] = $share;
            $arr['tax'] = $tax;
            $arr['net'] = $net;
            $net_arr2[] = $net;
            $arr['datesByMonth'] = $datesByMonth;

            $net_arr['data'] = $net; //$net_arr2;
            $net_arr['name'] = "Net";
            $cntAllpaid += $paidAmt;
            $cntAllUnpaid += $unpaidAmt;
            $arr['session_paid'] = $paidAmt;
            $arr['unpaidAmt'] = $unpaidAmt;
            $arr['session_unpaid'] = $session;//$unpaidAmt;
            $arr['total'] = $pnet;
            $arr['total_unpaid'] = $unpaid_pnet;
            $arr['total_unpaid_for_summary'] = $total_upaid;
            $arr['total_paid_for_summary'] = $total_paid;
            $arr['balance'] = $balance;
            $arr['total_upaid_Sessions'] = $total_upaid_Sessions;
            $arr['getPaidData'] = $getPaidData;

            $llll = $getPaidData ? $getPaidData[0]->cnt : 0;
            $arr['getPaidDataxxx'] = $session . ' ' . $llll;// . ' ' . $getTotalSession[0]->cnt;
            $arr['sessions'] = $session + $llll;//$session
            $arr['getUnPaidPatientSessions'] = $getUnPaidPatientSessions_list;
            $arr['getPaidPatientSessions'] = $getPaidPatientSessions_list;
            $arr['total_payment'] = $unpaid_pnet + $pnet;
            $arr['total_no_of_sessions'] = $paidAmt + $total_upaid_Sessions;
            $arr['new_unpaidAmt'] = $new_unpaidAmt;
            $arr['tnos'] = $tnos . ' ' . $session;
            $data_array[] = $arr;
            $netArr[] = $session;
            $netAmtArr[] = $netArr;
            $totalNet += $net;
            $totalPaid += $pnet;
            //$totalBalance += $balance; WORKING
            $totalBalance += $unpaid_balance;
            $getNewUP +=$new_unpaidAmt;
            $getNewP +=$paidAmt;
        }

        if ($request->data['status'] == "Unpaid") {
            $claimStatus = "p.status = 'UNPAID' and";
        } elseif ($request->data['status'] == "Paid") {
            $claimStatus = "p.status = 'PAID' and";
        } else {
            $claimStatus = " p.status in ('PAID','UNPAID')    and";
        }


        $getPatientAllSessions = DB::connection('mysql')->select(" 
      select c.name,p.patient_id,count(p.date_session) as cnt, p.doctor as docid,GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ',') as dates" .
            ",GROUP_CONCAT(p.date_session  SEPARATOR '|') as fdates" .
            ", GROUP_CONCAT( (select name from doctors where id = p.doctor group by name ) SEPARATOR ',') as doc-- , d.name
    from phic p
    left join patients c on p.patient_id = c.id 
    where $claimStatus  state = 'ACTIVE'  and  DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate'  and '$tdate' 
    
    AND EXISTS (
        SELECT 1 
        FROM schedule s         
        WHERE s.schedule = p.date_session
        and s.patient_id = p.patient_id
        and s.status = 'ACTIVE'
    ) 
    $withDoctorScript group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id $grpByDoctor ;");

        /* $getPatientAllSessions = DB::connection('mysql')->select("SELECT 
                sub.name,
                sum(sub.cnt) as sessions,
                sub.doctor,
                sub.date_session
            FROM (
                SELECT 
                    COUNT(p.id) AS cnt, 
                    d.name, 
                    p.doctor,
                    p.date_session
                FROM phic p
                LEFT JOIN doctors d ON d.id = p.doctor
            where
            $claimStatus
            p.state = 'ACTIVE' 
            and p.doctor = $doctor
            and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
            $withDoctorScript
            ) AS sub
            "); */

        $cntAll = 0;
        $formatAllSessions = array();
        foreach ($getPatientAllSessions as $key => $value) {
            $pid = $value->patient_id;
            $skeds = explode("|", $value->fdates);
            $sked_str = '';
            foreach ($skeds as $skey => $svalue) {
                $sked_str .= "'" . $svalue . "',";
            }
            $str = implode(',', array_unique(explode(',', $value->doc)));
            $sked_str = rtrim($sked_str, ", ");
            //$check_session = DB::connection('mysql')->select("Select * from schedule where patient_id = $pid and schedule in ($sked_str) and status = 'Active'");
            //$check_session = DB::connection('mysql')->select("Select * from schedule s left join phic p on s.patient_id = p.patient_id where s.patient_id = $pid and p.date_session in ($sked_str) and p.state = 'Active'");
            $check_session = DB::connection('mysql')->select("Select * from phic p where p.patient_id = $pid and p.date_session in ($sked_str) and p.state = 'Active' and p.status in ('PAID','UNPAID')");
            $newDate = '';
            foreach ($check_session as $ckey => $cvalue) {
                //$newDate .= date_format((date_create($cvalue->schedule)), 'F d,Y') . ', ';
                $newDate .= date_format((date_create($cvalue->date_session)), 'F d,Y') . ', ';
            }

            $arr = array();
            $arr['cnt'] = count($check_session);//$value->cnt;
            $arr['dates'] = $newDate;//$value->dates;
            $arr['fdates'] = $value->fdates;
            $arr['id'] = $value->docid;
            $arr['check_session'] = $check_session;
            $str = implode(',', array_unique(explode(',', $value->doc)));
            $arr['doc'] = $str;
            $arr['name'] = $value->name;
            $cntAll +=count($check_session);
            $formatAllSessions[] = $arr;
        }

       
        $datasets = array();
        $datasets["data"] = $data_array;
        $datasets["data2"] = $data;
        $datasets["tdate"] = $tdate;
        $datasets["fdate"] = $fdate;
        $datasets['getPatientAllSessions'] = $formatAllSessions;
        $datasets['cntAll_old'] = $cntAll;
        $datasets['allunpaid'] = $cntAllUnpaid;
        $datasets['allpaid'] = $cntAllpaid;
        $datasets["month"] = $monthArr;
        $datasets["net"] = array(["name" => 'Net', 'data' => $netArr]);
        $datasets["totalNet"] = $totalNet;
        $datasets["totalPaid"] = $totalPaid;
        $datasets["totalBalance"] = $totalBalance;
        $datasets["cntAll"] = $getNewUP;
        $datasets["getNewP"] = $getNewP;
        $datasets["datesByMonth"] = $datesByMonth;
        $datasets["datesByMonth"] = "SELECT count(s.patient_id) as cnt,DATE_FORMAT(s.date_session, '%Y-%m') as schedule FROM `phic` s where s.status = 'PAID'  and s.state = 'ACTIVE' 
            and DATE_FORMAT(s.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
            group by DATE_FORMAT(s.date_session, '%Y-%m')";
        $datasets["sql"] = "select c.name,p.patient_id,count(p.date_session) as cnt, p.doctor as docid,GROUP_CONCAT(DATE_FORMAT(p.date_session, '%M %d, %Y') SEPARATOR ',') as dates" .
        ",GROUP_CONCAT(p.date_session  SEPARATOR '|') as fdates" .
        ", GROUP_CONCAT( (select name from doctors where id = p.doctor group by name ) SEPARATOR ',') as doc-- , d.name
from phic p
left join patients c on p.patient_id = c.id 
where $claimStatus  state = 'ACTIVE'  and  DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate'  and '$tdate' $withDoctorScript group by DATE_FORMAT(p.date_session, '%Y-%m'),p.patient_id $grpByDoctor ; ";
        return response()->json($datasets);
    }

    public function summary_report(Request $request)
    {
        $rate1 = Settings::where('name', 'rate1')->first();
        $rate2 = Settings::where('name', 'rate2')->first();
        $rate3 = Settings::where('name', 'rate3')->first();


        $pf1 = Settings::where('name', 'pf1')->first();
        $pf2 = Settings::where('name', 'pf2')->first();

        $fdate = date_format(date_create($request->data['fdate']), 'Y-m-d');
        $tdate = date_format(date_create($request->data['tdate']), 'Y-m-d');
        $doctor = $request->data['doctor'];
        $claimStatus = '';

        if ($request->data['status'] == "Unpaid") {
            $claimStatus = "p.status = 'UNPAID' and";
        } elseif ($request->data['status'] == "Paid") {
            $claimStatus = "p.status = 'PAID' and";
        } else {
            $claimStatus = " p.status in ('PAID','UNPAID')    and";
        }

        if ($request->data['doctor'] == 0) {
            $data = DB::connection('mysql')->select(" SELECT sub.name,
                    sum(sub.cnt) as sessions,
            sub.doctor,
            sub.date_session,
            DATE_FORMAT(sub.date_session,'%Y-%m-%d') as yr_mon
                FROM (
                    SELECT 
                        COUNT(DISTINCT p.id) AS cnt, 
                        d.name, 
                        p.doctor,
                        p.date_session
                    FROM phic p
                    LEFT JOIN doctors d ON d.id = p.doctor
                where
                $claimStatus
                p.state = 'ACTIVE' 
                and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
                AND EXISTS (
            SELECT 1 
            FROM schedule  s       
            WHERE s.schedule = p.date_session
            and s.patient_id = p.patient_id
            and s.status = 'ACTIVE'
        )
                    GROUP BY d.id
                , DATE_FORMAT(p.date_session, '%Y-%m-%d') ) AS sub      GROUP BY sub.doctor,  DATE_FORMAT(sub.date_session,'%Y-%m')
                        ");
        } else {
            $data = DB::connection('mysql')->select("SELECT 
            sub.name,
            sum(sub.cnt) as sessions,
            sub.doctor,
            sub.date_session,
            DATE_FORMAT(sub.date_session,'%Y-%m-%d') as yr_mon
        FROM (
            SELECT 
                COUNT(DISTINCT p.id) AS cnt, 
                d.name, 
                p.doctor,
                p.date_session
            FROM phic p
            LEFT JOIN doctors d ON d.id = p.doctor
        where
        $claimStatus
        p.state = 'ACTIVE' 
        and p.doctor = $doctor
        and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
        AND EXISTS (
            SELECT 1 
            FROM schedule  s       
            WHERE s.schedule = p.date_session
            and s.patient_id = p.patient_id
            and s.status = 'ACTIVE'
        )
            GROUP BY DATE_FORMAT(p.date_session, '%Y-%m-%d')
        ) AS sub GROUP BY DATE_FORMAT(sub.date_session,'%Y-%m')
        ");
        }

        $data_array = array();
        $total_sessions = 0;
        $total_amount = 0;
        $total_ewt = 0;
        $total_net = 0;
        foreach ($data as $key => $value) {
            $total_sessions += $value->sessions;
            $arr = array();
            $arr['name'] = $value->name;
            $arr['sessions'] = $value->sessions;
            $getMultiplier = 0;
            $gross = 0;
            $pf = 0;

            $givenDatePF = Carbon::parse($value->date_session);
            $comparisonDatePF = Carbon::parse('2024-10-8');

            // Check if the given date is greater than the comparison date
            if ($givenDatePF->greaterThan($comparisonDatePF)) {
                $pf = $pf2->value;
            }else{
                $pf = $pf1->value;
            }
            // xxxx

            if (Carbon::parse($value->date_session)->lessThan('2024-07-01')) {
                /* $getMultiplier = ($rate1->value - 350);
                $gross = ($rate1->value - 350) * $value->sessions; */
                $getMultiplier = ($rate1->value - $pf);
                $gross = ($rate1->value - $pf) * $value->sessions;
            }

            $givenDate = Carbon::parse($value->date_session);
            $startDate = Carbon::parse('2024-07-01');
            $endDate = Carbon::parse('2024-10-08');
            if ($givenDate->between($startDate, $endDate)) {
                $getMultiplier = ($rate2->value - $pf);
                $gross = ($rate2->value - $pf) * $value->sessions;
            }

            // Parse the dates
            $givenDate = Carbon::parse($value->date_session);
            $comparisonDate = Carbon::parse('2024-10-8');

            // Check if the given date is greater than the comparison date
            if ($givenDate->greaterThan($comparisonDate)) {
                $getMultiplier = ($rate3->value - $pf);
                $gross = ($rate3->value - $pf) * $value->sessions;
            }
            $arr['gross'] = number_format((float) $gross, 2, '.', ',');
            /* if ($value->doctor != 5) {
                $ewt = $gross * 0.1;
                $net = $gross * 0.9;
            } else {
                $ewt = $gross * 0.05;
                $net = $gross * 0.95;
            } */
            $ewt = $gross * 0.05;
            $net = $gross * 0.95;
           /*  $arr['ewt'] = $gross * 0.1;
            $arr['net'] = $gross * 0.9; */
            $arr['ewt'] = number_format((float) $gross * 0.05, 2, '.', ',');
            $arr['net'] = number_format((float) $gross * 0.95, 2, '.', ',');
            $yr_mon = date_format(date_create($value->yr_mon), 'F d, Y');
            $arr['yr_mon'] = $yr_mon;
            $total_amount += $gross;
            $total_ewt += $ewt;
            $total_net += $net;
            $data_array[] = $arr;
        }

        /* $getMultiplierSharing = 0;
        if (Carbon::parse($fdate)->lessThan('2024-07-01')) {
            $getMultiplierSharing = ($rate1->value - 350);
        }

        $givenDate = Carbon::parse($fdate);
        $startDate = Carbon::parse('2024-07-01');
        $endDate = Carbon::parse('2024-10-08');
        if ($givenDate->between($startDate, $endDate)) {
            $getMultiplierSharing = ($rate2->value - 350);
        }

        $givenDate = Carbon::parse($fdate);
        $comparisonDate = Carbon::parse('2024-10-8');

        if ($givenDate->greaterThan($comparisonDate)) {
            $getMultiplierSharing = ($rate3->value - 350);
        } */

        $data_array_sharing['name'] = '';
        //$totalSharing = ($getMultiplierSharing * $total_sessions) * 0.27;
        $data_array_sharing['sessions'] = $total_sessions;
        $data_array_sharing['gross'] = number_format((float) $total_amount, 2, '.', ',');//$ totalSharing;
        $data_array_sharing['ewt'] = number_format((float) $total_ewt, 2, '.', ',');
        $data_array_sharing['net'] = number_format((float) $total_net, 2, '.', ',');
        $data_array[] = $data_array_sharing;


        return $data_array;
    }

    public function summary_pf_report(Request $request)
    {
        $rate1 = Settings::where('name', 'rate1')->first();
        $rate2 = Settings::where('name', 'rate2')->first();
        $rate3 = Settings::where('name', 'rate3')->first();


        $pf1 = Settings::where('name', 'pf1')->first();
        $pf2 = Settings::where('name', 'pf2')->first();

        $fdate = date_format(date_create($request->data['fdate']), 'Y-m-d');
        $tdate = date_format(date_create($request->data['tdate']), 'Y-m-d');
        $doctor = $request->data['doctor'];
        $claimStatus = '';

        if ($request->data['status'] == "Unpaid") {
            $claimStatus = "p.status = 'UNPAID' and";
        } elseif ($request->data['status'] == "Paid") {
            $claimStatus = "p.status = 'PAID' and";
        } else {
            $claimStatus = " p.status in ('PAID','UNPAID')    and";
        }

        if ($request->data['doctor'] == 0) {
            $data = DB::connection('mysql')->select(" SELECT sub.name,
                    sum(sub.cnt) as sessions,
            sub.doctor,
            sub.date_session,
            DATE_FORMAT(sub.date_session,'%Y-%m-%d') as yr_mon
                FROM (
                    SELECT 
                        COUNT(p.id) AS cnt, 
                        d.name, 
                        p.doctor,
                        p.date_session
                    FROM phic p
                    LEFT JOIN doctors d ON d.id = p.doctor
                where
                $claimStatus
                p.state = 'ACTIVE' 
                and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
                AND EXISTS (
            SELECT 1 
            FROM schedule  s       
            WHERE s.schedule = p.date_session
            and s.patient_id = p.patient_id
            and s.status = 'ACTIVE'
        )
                    GROUP BY d.id
                , DATE_FORMAT(p.date_session, '%Y-%m-%d') ) AS sub      GROUP BY sub.doctor,  DATE_FORMAT(sub.date_session,'%Y-%m')
                        ");
        } else {
            $data = DB::connection('mysql')->select("SELECT 
            sub.name,
            sum(sub.cnt) as sessions,
            sub.doctor,
            sub.date_session,
            DATE_FORMAT(sub.date_session,'%Y-%m-%d') as yr_mon
        FROM (
            SELECT 
                COUNT(DISTINCT p.id) AS cnt, 
                d.name, 
                p.doctor,
                p.date_session
            FROM phic p
            LEFT JOIN doctors d ON d.id = p.doctor
        where
        $claimStatus
        p.state = 'ACTIVE' 
        and p.doctor = $doctor
        and DATE_FORMAT(p.date_session, '%Y-%m-%d') between '$fdate' and '$tdate'
        AND EXISTS (
                            SELECT 1 
                            FROM schedule s         
                            WHERE s.schedule = p.date_session
                            and s.patient_id = p.patient_id
                            and s.status = 'ACTIVE'
                        )
            GROUP BY DATE_FORMAT(p.date_session, '%Y-%m-%d')
        ) AS sub GROUP BY DATE_FORMAT(sub.date_session,'%Y-%m')
        ");
        }

        $data_array = array();
        $total_sessions = 0;
        $total_amount = 0;
        $total_ewt = 0;
        $total_net = 0;
        foreach ($data as $key => $value) {
            $total_sessions += $value->sessions;
            $arr = array();
            $arr['name'] = $value->name;
            $arr['sessions'] = $value->sessions;
            $gross = 0;

            //$givenDatePF = Carbon::parse($fdate);
            $givenDatePF = Carbon::parse($value->date_session);
            $comparisonDatePF = Carbon::parse('2024-10-8');

            // Check if the given date is greater than the comparison date
            if ($givenDatePF->greaterThan($comparisonDatePF)) {
                $gross = $pf2->value * $value->sessions;
            }else{
                $gross = $pf1->value * $value->sessions;
            }

            $arr['gross'] = number_format((float) $gross, 2, '.', ',');
            $ewt = $gross * 0.05;
            $net = $gross * 0.95;
            $arr['ewt'] = number_format((float) $gross * 0.05, 2, '.', ',');
            $arr['net'] = number_format((float) $gross * 0.95, 2, '.', ',');
            $yr_mon = date_format(date_create($value->yr_mon), 'F d, Y');
            $arr['yr_mon'] = $yr_mon;
            $total_amount += $gross;
            $total_ewt += $ewt;
            $total_net += $net;
            $data_array[] = $arr;
        }

        $data_array_sharing['name'] = '';
        $data_array_sharing['sessions'] = $total_sessions;
        $data_array_sharing['gross'] = number_format((float) $total_amount, 2, '.', ',');//$ totalSharing;
        $data_array_sharing['ewt'] = number_format((float) $total_ewt, 2, '.', ',');
        $data_array_sharing['net'] = number_format((float) $total_net, 2, '.', ',');
        $data_array[] = $data_array_sharing;


        return $data_array;
    }    
}
