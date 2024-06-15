<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stuff;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiFormatter;

class StuffController extends Controller
{

    public function __construct()
{
    $this->middleware('auth:api');
}


    public function index()
    {
        $stuff = Stuff::with('stuffstock')->get();
        return ApiFormatter::sendResponse(200, true, "Lihat semua barang", $stuff);
    }

    public function store(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required',
        //     'category' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Semua Kolom Wajib Diisi',
        //         'data' => $validator->errors()
        //     ], 400);
        // } else {
        //     $stuff = Stuff::create([
        //         'name' => $request->input('name'),
        //         'category' => $request->input('category'),
        //     ]);

        //     if ($stuff) {
        //         return response()->json([
        //             'success' => true,
        //             'message' => 'Barang Berhasil Disimpan!',
        //             'data' => $stuff
        //         ]);
        //     } else {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Barang Gagal Disimpan!',
        //         ], 400);
        //     }
        // }

        try {
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required',
            ]);

            $stuff = Stuff::create([
                'name' => $request->input('name'),
                'category' => $request->input('category'),
            ]);
            return ApiFormatter::sendResponse(201, true, 'Barang Berhasil Disimpan!', $stuff);
            } 
            catch (\Throwable $th) {
                if ($th->validator->errors()) {
                    return ApiFormatter::sendResponse(400, false, 'Terdapat Kesalahan Input Silahkan Coba Lagi!', $th->validator->errors());
                } else {
                    return ApiFormatter::sendResponse(400, false, 'Terdapat Kesalahan Input Silahkan Coba Lagi!', $th->getMessage());
                }
            }
        }

    public function show($id)
    {
        try {
            $stuff = Stuff::with('stock')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, "Lihat barang dengan id $id", $stuff);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Data dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stuff = Stuff::findOrFail($id);
            
            $name = ($request->name) ? $request->name : $stuff->name;
            $category = ($request->category) ? $request->category : $stuff->category;

            $stuff->update([
                'name' => $name,
                'category' => $category
            ]);

            return ApiFormatter::sendResponse(200, true, "Berhasil ubah data dengan id $id");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $stuff = Stuff::findOrFail($id);

            $stuff->delete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus data dengan id $id", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $stuff = Stuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "Lihat data barang yang dihapus", $stuff);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $stuff = Stuff::onlyTrashed()->where('id', $id);
            
            $stuff->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan data yang telah di hapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $stuff = Stuff::onlyTrashed();
            
            $stuff->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan data yang telah di hapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try {
            $stuff = Stuff::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDeleteAll()
    {
        try {
            $stuff = Stuff::onlyTrashed();
            
            $stuff->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen semua data yang telah dihapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }
}