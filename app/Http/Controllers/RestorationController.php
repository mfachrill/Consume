<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Lending;
use App\Models\Restoration;
use App\Models\StuffStock;
use App\Models\SoftDeletes;
use Illuminate\Http\Request;

class RestorationController extends Controller
{
    public function index()
    {
        $restoration = Restoration::with('user', 'lending')->get();

        return ApiFormatter::sendResponse(200, true, 'Lihat Semua Pengembalian Barang', $restoration);
    }

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function store(Request $request, $lending_id)
    {
        try {
            $this->validate($request, [
                // 'user_id' => 'required',
                // 'lending_id' => 'required',
                'date_time' => 'required',
                'total_good_stuff' => 'required',
                'total_defec_stuff' => 'required',
            ]);

            $lending = Lending::where('id', $lending_id)->first();

            $totalStuffRestoration = (int)$request->total_good_stuff + (int)$request->total_defec_stuff;
            if ((int)$totalStuffRestoration > (int)$lending['total_stuff']) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Total barang kembali lebih banyak dari barang pinjaman!');
            } else {
                $restoration = Restoration::updateOrCreate([
                    'lending_id' => $lending_id
                ], [
                    'date_time' => $request->date_time,
                    'total_good_stuff' => $request->total_good_stuff,
                    'total_defec_stuff' => $request->total_defec_stuff,
                    'user_id' => auth()->user()->id,
                ]);

                $stuffStock = StuffStock::where('stuff_id', $lending['stuff_id'])->first();
                $totalAvailableStock = (int)$stuffStock['total_available'] + (int)$request->total_good_stuff;
                $totalDefecStock = (int)$stuffStock['total_defec'] + (int)$request->total_defec_stuff;

                $stuffStock->update([
                    'total_available' => $totalAvailableStock,
                    'total_defec' => $totalDefecStock,
                ]);

                $lendingRestoration = Lending::where('id', $lending_id)->with('user', 'restoration', 'restoration.user', 'stuff', 'stuff.stuffStock')->first();
                return ApiFormatter::sendResponse(200, 'success', $lendingRestoration);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $data = Lending::where('id', $id)->with('user', 'restoration', 'restoration.user', 'stuff', 'stuff.stuffStock')->first();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $restoration = Restoration::findOrFail($id);
            $user_id = ($request->user_id) ? $request->user_id : $restoration->user_id;
            $lending_id = ($request->lending_id) ? $request->lending_id : $restoration->lending_id;
            $date_time = ($request->date_time) ? $request->date_time: $restoration->date_time;
            $total_good_stuff = ($request->total_good_stuff) ? $request->total_good_stuff : $restoration->total_good_stuff;
            $total_defec_stuff = ($request->total_defec_stuff) ? $request->total_defec_stuff : $restoration->total_defec_stuff;
    
            $restoration->update([
                'user_id' => $user_id,
                'lending_id' => $lending_id,
                'date_time' => $date_time,
                'total_good_stuff' => $total_good_stuff,
                'total_defec_stuff' => $total_defec_stuff,
            ]);
    
            return Apiformatter::sendResponse(200, true, "Berhasil Ubah data dengan id $id", $restoration);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    } 

    public function destroy($id)
    {
        try{
            $hasRestoration = Lending::where('id', $id)->whereHas('restoration')->exists();

            if ($hasRestoration) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Pinjaman sudah memiliki pengembalian, tidak dapat dibatalkan');
            }

            $lending = Lending::find($id);
            $totalStuff = $lending->total_stuff;
            $stuffId = $lending->stuff_id;

            $checkproses = $lending->delete();

            if ($checkproses) {
                $stuffStock = StuffStock::where('stuff_id', $stuffId)->first();
                if ($stuffStock) {
                    $stuffStock->total_available += $totalStuff;
                    $stuffStock->save();
                    $checkproses = $lending->delete();
                }

                return ApiFormatter::sendResponse(200, 'success', 'Berhasil hapus data Peminjaman');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $restoration = Restoration::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "Berhasil Hapus Data Barang", ['id' => $restoration]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $restoration = Restoration::findOrFail($id);

            $restoration->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil Mengembalikan data dengan id $id", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restoreAll()
        {
            try{
                $stuffs = Restoration::onlyTrashed();

                $stuffs->restore();
        
                // if ($stuffs->count() === 0) {
                //     return ApiFormatter::sendResponse(200, true, "Tidak ada data yang dihapus");
                // }
        
                return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan barang yang telah dihapus");
            }
            catch(\Throwable $th)
            {
                return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi", $th->getMessage());
            }
        }

    public function permanentDelete($id)
    {
            
    }
        
    public function permanentDeleteAll()
    {
           
    }
}







// public function destroy($id)
//     {
//         try {
//             // Periksa apakah peminjaman memiliki restorasi
//             $hasRestoration = Lending::where('id', $id)->whereHas('restoration')->exists();

//             if ($hasRestoration) {
//                 return ApiFormatter::sendResponse(400, 'bad request', 'Peminjaman sudah memiliki pengembalian, tidak dapat dibatalkan.');
//             }

//             // Ambil data peminjaman sebelum dihapus
//             $lending = Lending::find($id);
//             $totalStuff = $lending->total_stuff;
//             $stuffId = $lending->stuff_id;

//             // Hapus peminjaman
//             //$checkproses = $lending->delete();

//             // if ($checkproses) {
//                 // Kembalikan total_stuff ke total_available pada stuff_stock
//                 $stuffStock = StuffStock::where('stuff_id', $stuffId)->first();
//                 if ($stuffStock) {
//                     $stuffStock->total_avaliable += $totalStuff;
//                     $stuffStock->save();
//                     $checkproses = $lending->delete();
//                 }

//                 return ApiFormatter::sendResponse(200, 'success', 'Berhasil hapus data Peminjaman.');
//             // }
//         } catch (\Exception $err) {
//             return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
//         }
//     }
// }