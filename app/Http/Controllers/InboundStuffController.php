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
    public function store(Request $request)
    {
       
        try {
           
            $this->validate($request,[
                'stuff_id' => 'required',
                'total' => 'required',
                'date' => 'required',
                'proff_file' => 'required|image',
            ]);
            $Image = Str::random(5) .  "_". $request->file('proff_file')->getClientOriginalName();
            $request->file('proff_file')->move('upload-images', $Image);
            $pathImage = url('upload-images/' . $Image);
        
          $inboundData = InboundStuff::create([
            'stuff_id' => $request->stuff_id,
                'total' => $request->total,
                'date' => $request->date,
                'proof_file' => $pathImage,
          ]);

          if ($inboundData) {
            $stockData = StuffStock::where('stuff_id', $request->stuff_id)->first();
            if ($stockData) {
                $total_avaible = (int)$stockData['total_avaible'] + (int)$request->total;
                $stockData->update(['total_avaible' => $total_avaible]);
            }else { 
                StuffStock::create([
                    'stuff_id' => $request->stuff_id,
                    'total_avaible' => $request->total,
                    'total_defect' => 0,

                ]);
            }
            $stuffWithInboundAndStock = Stuff::where('id', $request->stuff_id)->with('inboundStuff','stuffStock')->first();
            return ApiFormatter::sendResponse(200, 'success', $stuffWithInboundAndStock);

          }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse( 400,'bad request', $err->getMessage());
            
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

            unlink(base_path('public/proof/'.$getInbound->proof_file));
            $checkProses = InboundStuff::where('id', $id)->forceDelete();
    
            return ApiFormatter::sendResponse(200, 'success', 'Data inbound-stuff berhasil dihapus permanen');
        } catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }   
    
    private function deleteAssociatedFile(InboundStuff $inboundStuff)
    {
        // Mendapatkan jalur lengkap ke direktori public
        $publicPath = $_SERVER['DOCUMENT_ROOT'] . '/public/proof';

    
        // Menggabungkan jalur file dengan jalur direktori public
         $filePath = public_path('proof/'.$inboundStuff->proof_file);
    
        // Periksa apakah file ada
        if (file_exists($filePath)) {
            // Hapus file jika ada
            unlink(base_path($filePath));
        }
    }
    
}