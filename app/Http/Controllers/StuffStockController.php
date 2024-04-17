<?php

namespace App\Http\Controllers;

use App\Models\Stuff;
use App\Models\SoftDeletes;
use App\Models\StuffStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StuffStockController extends Controller
{
    public function index()
    {
        $stuffStock = StuffStock::all();

        return response()->json([
            'success' => true,
            'message' => 'Lihat semua barang masuk',
            'data' => $stuffStock
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stuff_id' => 'required',
            'total_avaible' => 'required',
            'total_defect' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'semua kolom wajib di isi',
                'data' => $validator->errors()
            ], 400);
        } else {

            $stock = StuffStock::updateOrCreate([
                'stuff_id' => $request->input('stuff_id'),
            ], [
                'total_avaible' => $request->input('total_avaible'),
                'total_defect' => $request->input('total_defect'),
            ]);

            if ($stock) {
                return response()->json([
                    'success' => true,
                    'message' => 'stock berhasil disimpan',
                    'data' => $stock,
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'stock gagal disimpan',
                ], 400);
            }
        }
    }

    public function show($id)
    {
        try {
            $stock = StuffStock::with('stuff')->find($id);

            return response()->json([
                'success' => true,
                'message' => "lihat stock dengan id $id",
                'data' => $stock
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => "data stock dengan id $id tidak ditemukan"
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stock = StuffStock::with('stuff')->find($id);

            $stuff_id = ($request->stuff_id) ? $request->stuff_id : $stock->stuff_id;
            $total_avaible = ($request->total_avaible) ? $request->total_avaible : $stock->total_avaible;
            $total_defect = ($request->total_defect) ? $request->total_defect : $stock->total_defect;

            if ($stock) {
                $stock->update([
                    'stuff_id' => $stuff_id,
                    'total_avaible' => $total_avaible,
                    'total_defect' => $total_defect
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil mengubah data stock dengan id $id",
                    'data' => $stock,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Proses Gagal!"
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => "Proses Gagal! data stock dengan id $id tidak ditemukan!",
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $stuffstock = StuffStock::findOrFail($id);

            $stuffstock->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil hapus data stock dengan id $id",
                'data' => ['id' => $id,]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => "Proses gagal! data stock dengan id $id tidak ditemukan",
            ], 404);
        }
    }



    

    public function restore($id)
    {
        try{
            $stock = StuffStock::onlyTrashed()->findOrFail($id);
            $has_stock = StuffStock::where('stuff_id', $stock->stuff_id)->get();

            if (has_stock->count() == 1){
                $message = "Data stok sudah ada, tidak boleh ada duplikat data stok untuk satu baranf silakan update data stok dengan id stock 
                $stock->stuff_id";
            } else {
                $stock->restore();
                $message = 'Berhasil mengembalikan data yang telah di hapus!';
            }
            return ApiFormatter::sendResponse(200, true, $message, ['id' => $id, 'stuff_id' => $stock->stuff_id]);
        } catch (\Throwable $th){
            return ApiFormatter::sendResponse(404, false, "Proses gagal silahkan coba lagi!", $th->getMessage());
        }
    }
    public function restoreAll(){
        try{
            $stocks = StuffStock::onlyTrashed()->restore();
            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan data yang telah di hapus");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal silakan coba lagi", $th->getMessage());
        }
    }

}