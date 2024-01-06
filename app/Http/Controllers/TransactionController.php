<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Transaction;
use App\Model\Products;
use App\Model\Transaction_details;
use App\Model\Inventory;
use Illuminate\Support\Facades\Auth;
use App\Model\ReceivedProducts;
use App\Model\Company;
use App\Model\Cost;
use DB;

class TransactionController extends Controller
{  
    public function __construct()
    {
        //$this->middleware('auth:api');
        $this->middleware('JWT');
    }

    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start?$request->start:0;
        $val = $request->searchTerm2;
        if($val!=''||$start>0){   
            $data =  DB::connection('mysql')->select("select * from transaction where invoiceno like '%".$val."%' LIMIT $length offset $start");
            $count =  DB::connection('mysql')->select("select * from transaction where invoiceno like '%".$val."%' ");
        }else{
            $data =  DB::connection('mysql')->select("select * from transaction LIMIT $length");
            $count =  DB::connection('mysql')->select("select * from transaction");
        }
        
        $count_all_record =  DB::connection('mysql')->select("select count(*) as count from transaction");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $company_data = Company::where(['id'=>$value->companyid])->first();
            $arr['company'] =  $company_data->company;
            $arr['refno'] =  $value->referenceno;
            $arr['invno'] =  $value->invoiceno;
            $arr['id'] =  $value->id;
            $arr['tdate'] =  date_format(date_create($value->transactiondate),"F d Y");
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
        $p = new Transaction;
        $p->invoiceno = $request->head['particulars'];
        $p->companyid = $request->head['companyid']; 
        $p->created_by = $request->user;
        $p->created_dt = date('Y-m-d');
        $p->status = 1; 
        $p->transactiondate = date_create($request->head['dot']);
        $p->save();         
        
        $getTotal = 0;
        foreach ($request->items as $val ) {
            $d = new Transaction_details;
            $d->transaction_id = $p->id;
            $d->product_id = $val['id'];
            $d->product = $val['product'];
            $d->qty = $val['qty'];
            $d->companyid = $request->head['companyid']; 
            $d->free = $val['isfree']?1:0;
            $d->transactiondate = date_create($request->head['dot']);
            $d->total = $val['total'];
            $d->price = $val['price'];
            $getTotal += $val['total'];
            $d->save();         

            
            $l = new Inventory();
            $product = Products::where(['id'=>$val['id']])->first();
            $getLastPrice = DB::connection('mysql')->select("select * from original_cost order by id desc limit 1");
            $reques_pid = $val['id'];
            $check_inventory = DB::connection('mysql')->select("select * from inventory where pid  =  $reques_pid order by id desc limit 1");
            if($val['isfree']){
                $total_purchased =  0;
            }else{
                $total_purchased =  $val['qty'] * $val['price'];
            }

            $l->sold = $val['qty'];
            $l->total_qty = $check_inventory[0]->total_qty - ($val['qty'] + $val['free']);
            //$l->total_qty =  $val['isfree']?$check_inventory[0]->total_qty:$check_inventory[0]->total_qty - $val['qty'];
            $l->amount = $total_purchased;
            $l->amount_balance = $check_inventory[0]->amount_balance - $total_purchased;
            $l->product = $product->product;
            $l->created_dt = date("Y-m-d H:i");
            $l->created_by = Auth::user()->id; 
            $l->pid = $val['id'];
            $l->cost = $val['price'];  
            $l->originalcost = $getLastPrice?$getLastPrice[0]->cost:$val['price'];            
            $l->free = $val['free'];
            $l->particulars = $request->head['particulars'];
            $l->dop = date_create($request->head['dot']);
            $l->company_id = $request->head['companyid'];
            $l->transaction_dt = date("Y-m-d");
            $l->save();

            //Ledger of buying company
            $comp = $request->head['companyid'];
            $check_ledger = DB::connection('mysql')->select("select * from received_products where company_id = $comp order by id desc limit 1");
            $p = new ReceivedProducts;
            $p->dop = date_create($request->dop);
            $p->reference_no = $request->head['referenceNo'];
            $p->particulars = $request->head['particulars'];
            $p->sold = $val['qty'];
            $p->free = $val['free'];
            $p->remarks = $val['remarks'];
            $p->unit_price =  $val['price'];
            $p->balance = $check_ledger?$check_ledger[0]->balance + $total_purchased:$total_purchased;
            $p->product = $product->product;
            $p->total_purchase = $val['total'];
            $p->created_dt = date("Y-m-d H:i");
            $p->created_by = Auth::user()->id; 
            $p->pid = $val['id'];
            $p->company_id = $request->head['companyid'];
            $p->save();

        }
        Transaction::where(['id'=>$p->id])->update([
            'referenceno'=> "TR".sprintf("%04d", $p->id),
            'total'=> $getTotal,
        ]);

        
        return response()->json($p->id);
    }

    public function update(Request $request)
    {
        Transaction::where(['id'=>$request->id])->update([
            'invoiceno'=> $request->data['invoiceno'],
            'companyid'=> $request->data['companyid'],
            'transactiondate'=> $request->data['dot'],
            'updated_by'=> 1,
            'updated_dt'=>  date('Y-m-d'),
        ]);
        return true;
    }

    public function getTransaction($id)
    {
        $data = Transaction_details::where(['transaction_id'=>$id])->get();
        return response()->json($data);
    }

    public function getTransactionHeader($id)
    {
        $data = Transaction::where(['id'=>$id])->first();
        return response()->json($data);
    }

    public function report(Request $request)
    {
        /* $data =  DB::connection('mysql')->select("select to_char(tt.transactiondate,'Mon') as mon,extract(year from tt.transactiondate) as yyyy,tt.companyid,count(tt.companyid) as cnt
        from transaction tt group by 1,2,tt.companyid;"); */
        $sd = date_format(date_create($request->items['from']),'Y-m-d');
        $td = date_format(date_create($request->items['to']),'Y-m-d');
        $data =  DB::connection('mysql')->select("select  tt.companyid,count(tt.companyid) as cnt
        from transaction tt
         where tt.transactiondate between '$sd' and '$td' group by tt.companyid;");
       
        $start    = new \DateTime($request->items['from']);
        $start->modify('first day of this month');
        $end      = new \DateTime($request->items['to']);
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period   = new \DatePeriod($start, $interval, $end);
        //echo iterator_count($period);

        $data_array = array();
        $cat_array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr_data = array();
            $company_data = Company::where(['id'=>$value->companyid])->first(); 
            /* $tdata = DB::connection('mysql')->select("SELECT *
            FROM transaction_details td where transactiondate between '01 Feb 2023' and '28 Mar 2023' and companyid = $value->companyid"); */
            

            foreach ($period as $dt) {
                //echo $dt->format("Y-m") . "<br>\n";
                $cat_array[] = $dt->format("M");
                //$last_day = date('Y-m-d', strtotime($dt->format("Y-m-d")));
                $start_day = $dt->format("Y-m-d");
                $last_day = date('Y-m-t', strtotime($start_day));
                /* $tdata = DB::connection('mysql')->select("select  MONTH(tt.transactiondate) as mon,YEAR(tt.transactiondate) as yyyy,tt.companyid,count(tt.companyid) as cnt,sum(tt.total) as total
                from transaction_details tt
                where tt.transactiondate between '$start_day' and '$last_day' and companyid = $value->companyid
                group by 1,2,tt.companyid;"); */
                //$q = "select  MONTH(tt.transactiondate) as mon,YEAR(tt.transactiondate) as yyyy,tt.companyid,count(tt.companyid) as cnt,sum(tt.total) as total
                //from transaction_details tt
                /*where (date(tt.transactiondate) between date('$start_day') and date('$last_day')) and companyid = $value->companyid*/
                //where date(tt.transactiondate) = '$start_day' and companyid = $value->companyid
                //group by 1,2,tt.companyid;";
                $q = "select  MONTH(tt.transactiondate) as mon,YEAR(tt.transactiondate) as yyyy,tt.companyid,count(tt.companyid) as cnt,sum(tt.total) as total from transaction_details tt where (date(tt.transactiondate) between date('$start_day') and date('$last_day')) and companyid = $value->companyid group by 1,2,tt.companyid;";
                $tdata = DB::connection('mysql')->select($q);
                $totals = 0;

                

                foreach ($tdata as $key2 => $value2) {
                    $totals += $value2->total;
                }
                $arr_data[] = $totals.' '.$start_day.' '.$last_day ;
               // $arr_data[] = $totals.' '.$last_day ;
                //unset($tdata);
            }


            $arr['name'] =  $company_data->company;
            $arr['data'] =  $arr_data;
            $data_array[] = $arr;
        }
        $datasets = array(["series"=>$data_array,'cat'=>array_unique($cat_array)]);
        return response()->json( $datasets);
    }
    
    public function DailyReport(Request $request){
        $date = date_format(date_create($request->items['date']),'Y-m-d');
        $query = DB::connection('mysql')->select("select * from transaction where date(transactiondate) = date('$date')");
        $data = array();
        $grandTotal = 0;
        foreach ($query as $key => $value ) {
            $sales = Transaction_details::where('transaction_id',$value->id)->get();
            $Company = Company::where('id',$value->companyid)->first();
            $total_sales = 0;
            $get_qty = 0;
            $get_price = 0;
            foreach ($sales as $key => $svalue) {
                $get_qty = $svalue->qty;
                $get_price = $svalue->price;
                $total_sales += $svalue->total;
            }
            $arr = array();
            $arr['company'] = $Company->company;
            $arr['inv'] = $value->invoiceno;
            $arr['sales'] = $total_sales;
            $arr['qty'] = $get_qty;//$sales->qty;
            $arr['price'] = $get_price;//$sales->price;
            $grandTotal +=$total_sales;  
            $data[] = $arr;
            $get_qty = 0;
            $get_price = 0;
        }
        $datasets = array(["data"=>$data,"count"=>$grandTotal,'query'=>$query,'q2'=>"select * from transaction where transactiondate = '$date'"]);
        return response()->json($datasets);
    }
    
    public function yearly_report(Request $request)
    {
        $sd = date_format(date_create($request->items['from']),'Y');
        $td = date_format(date_create($request->items['to']),'Y');
        $data =  DB::connection('mysql')->select("select  tt.companyid,count(tt.companyid) as cnt
        from transaction tt
         where YEAR(tt.transactiondate) between '$sd' and '$td' group by tt.companyid;");
       
        /* $start    = new \DateTime($request->items['from']);
        $start->modify('first day of this month');
        $end      = new \DateTime($request->items['to']);
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period   = new \DatePeriod($start, $interval, $end); */
        //echo iterator_count($period);
        
        $date1 = new \DateTime($request->items['from']);
        $date2 = new \DateTime($request->items['to']);
        $period = $date1->diff($date2);

        $data_array = array();
        $cat_array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr_data = array();
            $company_data = Company::where(['id'=>$value->companyid])->first();             

            for ($i=0; $i <=$period->y; $i++) { 
                $year = $sd+$i;
                $cat_array[] = $year;
                $tdata = DB::connection('mysql')->select("select YEAR(tt.transactiondate) as yyyy,tt.companyid,count(tt.companyid) as cnt,sum(tt.total) as total
                from transaction_details tt
                where YEAR(tt.transactiondate) between '$year' and '$year' and companyid = $value->companyid
                group by 1,tt.companyid;");
                $totals = 0;
                foreach ($tdata as $key2 => $value2) {
                    $totals += $value2->total;
                }
                $arr_data[] = $totals;
            }


            $arr['name'] =  $company_data->company;
            $arr['data'] =  $arr_data ;
            $data_array[] = $arr;
        }
        $datasets = array(["series"=>$data_array,'cat'=>array_unique($cat_array)]);
        return response()->json( $datasets);
    }
}
