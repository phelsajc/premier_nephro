<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Products;
use App\Model\ReceivedProducts;
use App\Model\Transaction_details;
use DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $length = 10;
        $start = $request->start?$request->start:0;
        $val = $request->searchTerm2;
        if($val!=''||$start>0){   
            $data =  DB::connection('mysql')->select("select * from products where product like '%".$val."%' LIMIT $length offset $start");
            $count =  DB::connection('mysql')->select("select * from products where product like '%".$val."%' ");
        }else{
            $data =  DB::connection('mysql')->select("select * from products LIMIT $length");
            $count =  DB::connection('mysql')->select("select * from products");
        }
        
        $count_all_record =  DB::connection('mysql')->select("select count(*) as count from products");

        $data_array = array();

        foreach ($data as $key => $value) {
            $arr = array();
            $arr['id'] =  $value->id;
            $arr['name'] =  $value->product;
            $arr['desc'] =  $value->description;
            $arr['qty'] =  $value->quantity;
            $arr['uom'] =  $value->uom;
            $arr['dop'] =  $value->dop;  
            $arr['price'] =  $value->price;  
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
        $p = new Products;
        $p->product = $request->name;
        $p->description = $request->desc;
        //$p->quantity = $request->qty;
        $p->uom = $request->uom;
        //$p->dop = $request->dop;
        $p->code = $request->code;
        $p->price = $request->price;
        $p->created_dt = date("Y-m-d H:i");
        $p->created_by = auth()->id();   
        $p->save();
        return true;
    }

    public function edit($id)
    {
        $data = Products::where(['id'=>$id])->first();
        return response()->json($data);
    }
    
    public function update(Request $request)
    {
        Products::where(['id'=>$request->id])->update([
            'product'=> $request->data['name'],
            'description'=> $request->data['desc'],
            //'quantity'=> $request->data['qty'],
            'uom'=>  $request->data['uom'],
            //'dop'=> $request->data['dop'],
            'code'=> $request->data['code'],
            'updated_by'=> auth()->id(),
            'updated_dt'=>   date("Y-m-d H:i"),
            'price'=>  $request->data['price'],
        ]);
        return response()->json(true);
    }

    public function Delete($id)
    {
        Products::where('id',$id)->delete();
        return true;
    }

    public function searchProduct(Request $request){
        $query = DB::connection('mysql')->select("select * from products where product like '%$request->val%' or description like '%$request->val%'");
        $data = array();
        foreach ($query as $key => $value ) {
            $arr = array();
            $arr['id'] = $value->id;
            $arr['product'] = $value->product;
            $arr['description'] = $value->description;
            $arr['price'] =  $value->price;
            $arr['code'] = $value->code;
            $total_received = 0;
            $total_sales = 0;
            $total_qty_purchase = 0;
            $rec_prod = ReceivedProducts::where('pid',$value->id)->get();
            $sales = Transaction_details::where('product_id',$value->id)->get();
            foreach ($rec_prod as $key => $rvalue) {
                $total_received += $rvalue->quantity;
            }
            foreach ($sales as $key => $svalue) {
                $total_sales += $svalue->total;
            }
            foreach ($sales as $key => $qvalue) {
                $total_qty_purchase += $qvalue->qty;
            }
            $arr['qty'] = $total_received-$total_qty_purchase;
            $data[] = $arr;
        }
        return response()->json($data);
    }

    public function getProducts()
    {
        $data = Products::all();
        return response()->json($data);
    }

    public function stockInventory(){
        $query = DB::connection('mysql')->select("select * from products");
        $data = array();
        foreach ($query as $key => $value ) {
            $rec_prod = ReceivedProducts::where('pid',$value->id)->get();
            $sales = Transaction_details::where('product_id',$value->id)->get();
            $total_received = 0;
            $total_sales = 0;
            $total_qty_purchase = 0;
            foreach ($rec_prod as $key => $rvalue) {
                $total_received += $rvalue->quantity;
            }
            foreach ($sales as $key => $svalue) {
                $total_sales += $svalue->total;
            }
            foreach ($sales as $key => $qvalue) {
                $total_qty_purchase += $qvalue->qty;
            }
            $arr = array();
            $arr['codes'] = $value->code;
            $arr['products'] = $value->product;
            $arr['units'] = $value->uom;
            $arr['rec'] =  $total_received;
            $arr['sales'] = $total_qty_purchase;
            $arr['stock'] = $total_received-$total_qty_purchase;
            $arr['price'] = $value->price;
            $arr['total'] = 0;
            $data[] = $arr;
        }
        return response()->json($data);
    }
}
