<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use App\Models\StuffStock;
use App\Models\Lending;

use App\Models\Restoration;


class LendingController extends Controller
{


    public function index()
    {
        try {
            $data = Lending::with('stuff', 'user','restoration')->get();
            return ApiFormatter::sendResponse(200, 'success', $data);
          
        }catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }

    }
    public function store(Request $request)
    {
        try {

            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'user_id' => 'required',
                'notes' => 'required',
                'total_stuff' => 'required',
               
            ]);
            // return ApiFormatter::sendResponse(400, 'bad request', $request->all());
            $totalAvailable = StuffStock::where('stuff_id', $request->stuff_id)->value('total_available');

            if (is_null($totalAvailable)){

                return ApiFormatter::sendResponse(400, 'bad request', 'belum ada data inbound');

            }elseif ((int)$request->total_stuff > (int)$totalAvailable){
                return ApiFormatter::sendResponse(400,  'bad requesst', 'stok tidak tersedia');
            }else{
                $lending = Lending::create ([
                    'stuff_id'=> $request->stuff_id,
                    'date_time'=> $request->date_time,
                    'name'=> $request->name,
                    'notes'=> $request->notes ? $request->notes : '-',
                    'total_stuff'=> $request->total_stuff,
                    'user_id'=> auth()->user()->id,
                ]);
                $totalAvailableNow = (int)$totalAvailable - (int)$request->total_stuff;
                $stuffStock = StuffStock::where('stuff_id', $request->stuff_id)->update([$total_availableNow]);
                $dataLending = Lending::where('id', $lending['id'])->with('user','stuff','stuff.stuffStock')->first();

                return ApiFormatter::sendResponse(200, 'success', $dataLending);
        }   
        }catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function show ($Id)
    {
    try{
        $data = Lending::where('id',$id)->with('user','restoration', 'stuff', 'stuff.stuffStock')->first();

        return ApiFormatter::sendResponse(200, 'success', $data);
          
    } catch(\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
}
}
