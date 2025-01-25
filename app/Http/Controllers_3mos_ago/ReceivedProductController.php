<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
//use Tymon\JWTAuth\Facades\JWTAuth;
//use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Model\Products;
use App\Model\Ledger;
use App\Model\MyLedger;
use App\Model\Inventory;
use App\Model\Cost;
use App\Model\ReceivedProducts;
use DB;

class ReceivedProductController extends Controller
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
   public function index(Request $request)
   {
       date_default_timezone_set('Asia/Manila');
       $length = 10;
       $start = $request->start?$request->start:0;
       $val = $request->searchTerm2;
       if($val!=''||$start>0){   
           $data =  DB::connection('mysql')->select("select * from received_products where product like '%".$val."%' LIMIT $length offset $start");
           $count =  DB::connection('mysql')->select("select * from received_products where product like '%".$val."%' ");
       }else{
           $data =  DB::connection('mysql')->select("select * from received_products LIMIT $length");
           $count =  DB::connection('mysql')->select("select * from received_products");
       }
       
       $count_all_record =  DB::connection('mysql')->select("select count(*) as count from received_products");

       $data_array = array();

       foreach ($data as $key => $value) {
           $product = Products::where(['id'=>$value->pid])->first();
           $arr = array();
           $arr['dop'] =  date_format(date_create($value->dop),'Y-m-d');
           $arr['reference'] =  $value->reference_no;
           $arr['particulars'] =  $value->particulars; 
           $arr['price'] =   $value->unit_price;
           $arr['purchased'] =  $value->purchase;
           $arr['payment'] =  $value->payment;
           $arr['balance'] =  $value->balance;
           $arr['remarks'] =  $value->remarks; 
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
       /* $datasets = array(["data"=>$data_array,"count"=>$page_count,"showing"=>"Showing ".(($start+10)-9)." to ".($start+10>$count_all_record[0]->count?$count_all_record[0]->count:$start+10)." of ".$count_all_record[0]->count, "patient"=>$data_array]);
       return response()->json($datasets); */

       $datasets["data"] = $data_array;
       $datasets["count"] = $page_count;
       $datasets["showing"] = "Showing ".(($start+10)-9)." to ".($start+10>$count_all_record[0]->count?$count_all_record[0]->count:$start+10)." of ".$count_all_record[0]->count;
       $datasets["patient"] = $data_array;
       return response()->json($datasets);
   }   

   
   public function ledger($cid)
   {
       date_default_timezone_set('Asia/Manila');
       $length = 10;
       $company = $cid;
       
       $data =  DB::connection('mysql')->select("select * from received_products where company_id = $company ");
       $count =  DB::connection('mysql')->select("select * from received_products where company_id = $company ");

       //$count_all_record =  DB::connection('mysql')->select("select count(*) as count from received_products");

       $data_array = array();

       foreach ($data as $key => $value) {
           $product = Products::where(['id'=>$value->pid])->first();
           $arr = array();
           $arr['dop'] =  date_format(date_create($value->dop),'Y-m-d');
           $arr['reference'] =  $value->reference_no;
           $arr['particulars'] =  $value->particulars; 
           $arr['price'] =   $value->sold?$value->unit_price:$value->cost;
           $arr['purchased'] =  $value->sold?$value->purchase:$value->quantity+$value->free;
           $arr['payment'] =  $value->payment;
           $arr['balance'] =  $value->balance;
           $arr['total_purchase'] =  $value->sold?$value->total_purchase:$value->quantity+$value->free;
           $arr['remarks'] =  $value->remarks; 
           $arr['check'] =  $value->checkno; 
           $arr['sold'] =  $value->sold; 
           $data_array[] = $arr;
       }
       $page = sizeof($data)/$length;
       $getDecimal =  explode(".",$page);
       $page_count = round(sizeof($data)/$length);
       if(sizeof($getDecimal)==2){            
           if($getDecimal[1]<5){
               $page_count = $getDecimal[0] + 1;
           }
       }
       /* $datasets = array(["data"=>$data_array,"count"=>$page_count,"showing"=>"Showing ".(($start+10)-9)." to ".($start+10>$count_all_record[0]->count?$count_all_record[0]->count:$start+10)." of ".$count_all_record[0]->count, "patient"=>$data_array]);
       return response()->json($datasets); */

       $datasets["data"] = $data_array;
       $datasets["count"] = $page_count;
       //$datasets["showing"] = "Showing ".(($start+10)-9)." to ".($start+10>$data[0]->count?$data[0]->count:$start+10)." of ".$data[0]->count;
       $datasets["patient"] = $data_array;
       return response()->json($datasets);
   }  
   
   
   public function sales(Request $request)
   {
       date_default_timezone_set('Asia/Manila');
       
       $data =  DB::connection('mysql')->select("select * from company");

       $data_array = array();
        $date = (explode("-",$request->fdate));
        $GrandtotalPurchase = 0;
        $GrandtotalQtyPurchase = 0;
        $GrandtotalCos = 0;
        $getProduct = null;
        foreach ($data as $key => $value) {
            $getAllCompany = DB::connection('mysql')->select("select * from inventory where month(dop) = '$date[1]' and year(dop) = '$date[0]' and sold>0 and company_id=$value->id and pid=$request->product");
            $arr = array();
            $checkProduct = Products::where(['id'=>$request->product])->first();
            $getProduct =    $checkProduct->product; 
            $totalPurchase = 0;
            $totalQtyPurchase = 0;
            $data_array2 = array();
            $data_array3 = array();
            $costOfSales = 0;
            $getOriginalCost = 0;
            foreach ($getAllCompany as $akey => $avalue) {
                $arr2 = array();
                $arr2['price'] =  $avalue->cost;
                $arr2['qty'] =  $avalue->sold;
                $data_array2[] = $arr2;
                $arr3 = array();
                $arr3['price'] = $avalue->originalcost;
                if(str_contains($value->company, 'RTSI')){
                    $cos_qty = $avalue->sold+$avalue->free;
                    $cos = ($avalue->sold+$avalue->free)*$avalue->originalcost;
                }else{
                    $cos_qty = $avalue->sold;
                    $cos = $avalue->sold*$avalue->originalcost;
                }
                $getOriginalCost = $avalue->originalcost;
                $arr3['qty'] = $cos_qty;
                $arr3['total_cost'] = $cos;
                $data_array3[] = $arr3;
                //$getTotal = $avalue->
                //$totalPurchase += $avalue->amount;
                $totalPurchase += $avalue->amount;
                $totalQtyPurchase += ($avalue->sold+$avalue->free);

                $costOfSales+=$cos;
            }
            $GrandtotalQtyPurchase += $totalQtyPurchase;
            $GrandtotalPurchase += $totalPurchase;
            $GrandtotalCos += $costOfSales;
            $arr['totalPurchase'] =  $totalPurchase;
            $arr['price'] =  $getOriginalCost;
            $arr['totalQtyPurchase'] =  $totalQtyPurchase;
            $arr['company'] =  $value->company;
            $arr['cost_of_sales'] =  number_format((float)$costOfSales, 2, '.', ',');
            $arr['details'] =  $data_array2;
            $arr['details3'] =  $data_array3;


            /* $arr['dop'] =  date_format(date_create($value->dop),'Y-m-d');
            $arr['reference'] =  $value->reference_no;
            $arr['particulars'] =  $value->particulars; 
            $arr['price'] =   $value->unit_price;
            $arr['purchased'] =  $value->purchase;
            $arr['payment'] =  $value->payment;
            $arr['balance'] =  $value->balance;
            $arr['total_purchase'] =  $value->total_purchase;
            $arr['remarks'] =  $value->remarks; 
            $arr['check'] =  $value->checkno; 
            $arr['sold'] =  $value->sold;  */
            if($totalQtyPurchase>0){
                $data_array[] = $arr;
            }
        }
        /* $page = sizeof($data)/$length;
        $getDecimal =  explode(".",$page);
        $page_count = round(sizeof($data)/$length);
        if(sizeof($getDecimal)==2){            
            if($getDecimal[1]<5){
                $page_count = $getDecimal[0] + 1;
            }
        } */
        
        $datasets["data"] = $data_array;
        $datasets["product"] = $getProduct;
       $datasets['GrandtotalQtyPurchase'] = $GrandtotalQtyPurchase;
       $datasets['GrandtotalPurchase'] = number_format((float)$GrandtotalPurchase, 2, '.', ',');
       $datasets['GrandtotalCos'] = number_format((float)$GrandtotalCos, 2, '.', ',');
       $totalIncome = $GrandtotalPurchase - $GrandtotalCos;
       $datasets['income'] =number_format((float)$totalIncome, 2, '.', ',');
       return response()->json($datasets);
   }   
    
    public function inventory(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start?$request->start:0;
        $val = $request->product;
        if($val!=''||$start>0){   
            $data =  DB::connection('mysql')->select("select * from inventory where pid like '%".$val."%' LIMIT $length offset $start");
            $count =  DB::connection('mysql')->select("select * from inventory where pid like '%".$val."%' ");
        }else{
            $data =  DB::connection('mysql')->select("select * from inventory LIMIT $length");
            $count =  DB::connection('mysql')->select("select * from inventory");
        }
        
        $count_all_record =  DB::connection('mysql')->select("select count(*) as count from inventory");

        $data_array = array();

        foreach ($data as $key => $value) {
            $product = Products::where(['id'=>$value->pid])->first();
            $arr = array();
            $arr['dop'] =  date_format(date_create($value->dop),'Y-m-d');
            $arr['particulars'] =  $value->particulars; 
            $arr['sold'] =  $value->sold+$value->free;
            $arr['balance'] =  $value->total_qty;
            $arr['cost'] =   $value->cost;
            $arr['amount'] =  $value->amount;
            $arr['amount_balance'] =  $value->amount_balance;
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
        /* $datasets = array(["data"=>$data_array,"count"=>$page_count,"showing"=>"Showing ".(($start+10)-9)." to ".($start+10>$count_all_record[0]->count?$count_all_record[0]->count:$start+10)." of ".$count_all_record[0]->count, "patient"=>$data_array]);
        return response()->json($datasets); */

        $datasets["data"] = $data_array;
        $datasets["count"] = $page_count;
        $datasets["showing"] = "Showing ".(($start+10)-9)." to ".($start+10>$count_all_record[0]->count?$count_all_record[0]->count:$start+10)." of ".$count_all_record[0]->count;
        $datasets["patient"] = $data_array;
        return response()->json($datasets);
    } 
  
    public function store(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $product = Products::where(['id'=>$request->pid])->first();
        //$check_ledger = DB::connection('mysql')->select("select * from received_products where pid  = $request->pid order by id desc limit 1");
        $check_ledger = DB::connection('mysql')->select("select * from received_products where company_id = $request->company order by id desc limit 1");
        $p = new ReceivedProducts;
        $p->product = $product->product;
        $p->quantity = $request->qty;
        $p->dop = date_create($request->dop);
        $p->created_dt = date("Y-m-d H:i");
        $p->created_by = Auth::user()->id; 
        $p->pid = $request->pid;
        $p->reference_no = $request->referenceNo;
        $p->particulars = $request->particulars;
        $p->unit_price = $request->price;
        $p->purchase = $request->purchase;
        $p->cost = $request->purchase/($request->qty+$request->free);;
        //$p->balance = $check_ledger?$check_ledger[0]->balance + $request->balance:$request->balance;
        $p->balance = $check_ledger?$check_ledger[0]->balance + $request->purchase:$request->balance;
        //$p->balance = $check_ledger?($check_ledger[0]->balance + $request->balance):$request->balance;
        $p->company_id = $request->company;
        $p->free = $request->free;
        $p->save();

        $check_inventory = DB::connection('mysql')->select("select * from inventory where pid  = $request->pid  order by id desc limit 1");

        //if($check_inventory!=null){
            $l = new Inventory();
            //$product = Inventory::where(['id'=>$request->pid])->first();
            $l->product = $product->product;
            $l->quantity = $request->qty+$request->free;
            $l->total_qty = $check_inventory?$check_inventory[0]->total_qty + $request->qty+$request->free:$request->qty+$request->free;
            $l->created_dt = date("Y-m-d H:i");
            $l->created_by = Auth::user()->id; 
            $l->pid = $request->pid;
            $l->particulars = $request->particulars;
            $l->cost = $request->purchase/($request->qty+$request->free);
            $l->sold = 0;
            //$l->balance = $check_inventory?$check_inventory[0]->total_qty + $request->qty+$request->free:$request->qty+$request->free;//$request->qty+$request->free;        
            $l->amount = $request->purchase; // user input
            $l->dop = date_create($request->dop);
            $l->received_id = $p->id;
    
            $l->amount_balance = $check_inventory?($check_inventory[0]->amount_balance + $request->purchase)-$check_inventory[0]->payment:$request->purchase;
            $l->company_id = $request->company;
            $l->transaction_dt = date("Y-m-d");
            $l->save();

            
            $c = new Cost();
            $c->product = $product->product;
            $c->pid = $request->pid;
            $c->created_dt = date("Y-m-d H:i");
            $c->cost = $request->purchase/($request->qty+$request->free);
            $c->save();
        //}
        return response()->json(Auth::user()->id);
    }

    public function payment(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $check_ledger = DB::connection('mysql')->select("select * from received_products where company_id  = $request->company order by id desc limit 1");
        $p = new ReceivedProducts;
        $p->dop = date_create($request->dop);
        $p->created_dt = date("Y-m-d H:i");
        $p->created_by = Auth::user()->id; 
        $p->reference_no = $request->referenceNo;
        $p->particulars = $request->particulars;
        $p->payment = $request->amount;
        $p->checkno = $request->checkno;
        $p->balance = $check_ledger?$check_ledger[0]->balance - $request->amount:$request->amount;
        $p->company_id = $request->company;
        $p->save();
        return response()->json(Auth::user()->id);
    }

    public function edit($id)
    {
        $data = ReceivedProducts::where(['id'=>$id])->first();
        return response()->json($data);
    }
    
    public function update(Request $request)
    {
        $product = Products::where(['id'=>$request->data['pid']])->first();
        ReceivedProducts::where(['id'=>$request->id])->update([
            'product'=> $product->product,
            'pid'=> $request->data['pid'],
            'quantity'=> $request->data['qty'],
            'uom'=> $request->data['uom'],
            'date_receive'=> date_create($request->data['dor']),
            'updated_by'=> auth()->id(),
            'updated_dt'=>   date_create(date("Y-m-d H:i")),
        ]);
        return response()->json(true);
        return true;
    }

    public function Delete($id)
    {
        ReceivedProducts::where('id',$id)->delete();
        return true;
    }

    public function searchProduct(Request $request){
        $query = DB::connection('mysql')->select("select * from received_products where product like '%$request->val%' or description like '%$request->val%'");
        $getLastPrice = DB::connection('mysql')->select("select * from origibal_cost order by id desc limit 1");
        $data = array();
        foreach ($query as $key => $value ) {
            $arr = array();
            $arr['id'] = $value->id;
            $arr['product'] = $value->product;
            $arr['description'] = $value->description;
            $arr['price'] =  $value->price;
            $arr['code'] = $value->code;
            $arr['qty'] = $value->quantity;
            $data[] = $arr;
        }
        return response()->json($data);
    }

    public function getLastBalance()
    {
        $data = DB::connection('mysql')->select("select * from received_products order by id desc limit 1");
        return $data;//>balance?$data:false;
    }
}
