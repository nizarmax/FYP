<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\order;
use App\Models\orderDetails;
use App\Models\orderPrintingInfo;
use App\Models\payment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class paymentController extends Controller
{
    public function index(Request $request){
        $bindingType = $request->input('binding-type');
        $color = $request->input('checkColor','off');
        $pageFormat = $request->input('pageFormat');
        $amount = $request->input('amount');
        $deliveryLocation = $request->input('deliveryLocation');
        $file = DB::table('files')->where('fileID', $request->input('fileID'))->first();
        return view('students/payment', [
            'bindingType' => $bindingType,
            'checkColor' => $color,
            'pageFormat' => $pageFormat,
            'amount' => $amount,
            'location' => $deliveryLocation,
            'file' => $file,
        ]);
    }

    public function chooseLocation(Request $request){
        $bindingType = $request->input('binding-type');
        $color = $request->input('checkColor','off');
        $pageFormat = $request->input('pageFormat');
        $amount = $request->input('amount');
        $fileID = $request->input('fileID');

        return view('students/choosePickUp', [
            'bindingType' => $bindingType,
            'checkColor' => $color,
            'pageFormat' => $pageFormat,
            'amount' => $amount,
            'fileID' => $fileID,
        ]);
    }

    public function orderCreate(Request $request){
        
        $createOrder = Order::create([
            'studentID' => Auth::user()->studentID,
            'orderDate' => Carbon::now("Asia/Kuala_Lumpur"),
            'status' => 'Pending',
        ]);
        $OrderID = $createOrder->id;

        Payment::create([
            'orderID' => $OrderID,
            'totalPrice' => $request->input('totalPrice'),
        ]);

        $createPrintingInfo = OrderPrintingInfo::create([
            'fileID' => $request->input('fileID'),
            'bindingType' => $request->input('bindingType'),
            'color' => $request->input('checkColor'),
            'pageFormat' => $request->input('pageFormat'),
            'numCopies' => $request->input('amount'),
            'location' => $request->input('deliveryLocation'),
        ]);

        
        $printingInfoID = $createPrintingInfo->id;
        
        OrderDetails::create([
            'orderID' => $OrderID,
            'orderPrintingInfoID' => $printingInfoID,
        ]);

        return redirect()->route('document')->with('paymentAlert','Payment Success');
    }
}
