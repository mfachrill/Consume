<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\InboundStuff;
use App\Helpers\ApiFormatter;
use App\Models\StuffStock;
use App\Models\Stuff;

class InboundStuffController extends Controller
{

    public function __construct()
{
    $this->middleware('auth:api');
}

public function index()
    {
        $inboundStuff = InboundStuff::all();

        return ApiFormatter::sendResponse(200, true, "Lihat semua barang masuk", $inboundStuff);

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Lihat semua barang masuk',
        //     'data' => $inboundStuff
        // ], 200);
    }

public function store(Request $request)
{
    try {
        $this->validate($request, [
            'stuff_id' => 'required',
            'total' => 'required',
            'date' => 'required',
            'proff_file' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
        ]); 

        if($request->hasFile('proff_file')) {
            $proff = $request->file('proff_file'); 
            $destinationPath = 'upload-images/'; // destionationPath = untuk memasukan file ke folder tujuan 
            $proffName = date('YmdHis') . "." . $proff->getClientOriginalExtension();
            $proff->move($destinationPath, $proffName); 
        }
        $createStock = InboundStuff::create([
            'stuff_id' => $request->stuff_id,
            'total' => $request->total,
            'date' => $request->date,
            'proff_file' => $proffName,
        ]);

        if ($createStock){
            $getStuff = Stuff::where('id', $request->stuff_id)->first();
            $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();

            if (!$getStuffStock){
                $updateStock = StuffStock::create([
                    'stuff_id' => $request->stuff_id,
                    'total_available' => $request->total,
                    'total_defec' => 0,
                ]);
            } else {
                $updateStock = $getStuffStock->update([
                    'stuff_id' => $request->stuff_id,
                    'total_available' =>$getStuffStock['total_available'] + $request->total,
                    'total_defec' => $getStuffStock['total_defec'],
                ]);
            }

            if ($updateStock) {
                $getStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
                $stuff = [
                    'stuff' => $getStuff,
                    'InboundStuff' => $createStock,
                    'stuffStock' => $getStock
                ];

                return ApiFormatter::sendResponse(200, 'Successfully Create A Inbound Stuff Data', $stuff);
            } else {
                return ApiFormatter::sendResponse(400, false, 'Failed To Update A Stuff Stock Data');
            }
        } else {
        }
    } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400, false, $err->getMessage());
    }
}
  

    public function destroy($id)
    {
        try {
            $inboundData = InboundStuff::where('id', $id)->first();

            $total_avaible = (int)$inboundData['total_avaible'] - (int)$inboundData['total'];
            $minusTotalStock = StuffStock::where('stuff_id', $inboundData['stuff_id'])->update(['total_avaible' => $total_avaible]);

            if ($minusTotalStock) {
                $updatedStuffwhithInboundAndStock = Stuff::where('id', $inboundData['stuff_id'])->with('inboundStuff', 'stuffStock')->first();

                $inboundData->delete();
                return ApiFormatter::sendResponse(200, 'succes', $updatedStuffwhithInboundAndStock);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    public function trash()
    {
        try{
            $data= InboundStuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        }catch(\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    
    public function restore(InboundStuff $inboundStuff, $id)
    {
        try {
            $checkProses = InboundStuff::onlyTrashed()->where('id', $id)->restore();
    
            if ($checkProses) {
                $restoredData = InboundStuff::find($id);
    
                $totalRestored = $restoredData->total;
                $stuffId = $restoredData->stuff_id;
                $stuffStock = StuffStock::where('stuff_id', $stuffId)->first();
                
                if ($stuffStock) {
                    $stuffStock->total_available += $totalRestored;
                    $stuffStock->save();
                }
    
                return ApiFormatter::sendResponse(200, 'success', $restoredData);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function deletePermanent(InboundStuff $inboundStuff, Request $request, $id)
    {
        try {
            $getInbound = InboundStuff::onlyTrashed()->where('id',$id)->first();

            unlink(base_path('public/proff/'.$getInbound->proff_file));
            $checkProses = InboundStuff::where('id', $id)->forceDelete();
    
            return ApiFormatter::sendResponse(200, 'success', 'Data inbound-stuff berhasil dihapus permanen');
        } catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }   
    
    private function deleteAssociatedFile(InboundStuff $inboundStuff)
    {
        // Mendapatkan jalur lengkap ke direktori public
        $publicPath = $_SERVER['DOCUMENT_ROOT'] . '/public/proff';

    
        // Menggabungkan jalur file dengan jalur direktori public
         $filePath = public_path('proff/'.$inboundStuff->proff_file);
    
        // Periksa apakah file ada
        if (file_exists($filePath)) {
            // Hapus file jika ada
            unlink(base_path($filePath));
        }
    }
    
}