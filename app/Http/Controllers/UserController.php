<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; 

class UserController extends Controller
{

    public function __construct()
{
    $this->middleware('auth:api');
}

    public function index()
    {
        try {
            $data = User::all()->toArray();

            return ApiFormatter::sendResponse(200, 'succes', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'username' => 'required|min:4|unique:users,username',
                'email' => 'required|unique:users,email',
                'password' => 'required',
                'role' => 'required',
            ]);
    
            $userd = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);
    
            if ($userd ) {
                return ApiFormatter::sendResponse(200, 'success', $userd); 
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal memproses tambah data User! Silahkan coba lagi.');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $data = User::where('id', $id)->first();
            return ApiFormatter::sendResponse(200, 'succes', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'username' => 'required|min:4|unique:users,username,' . $id,
                'email' => 'required|unique:users,email,' . $id,
                'password' => 'required',
                'role' => 'required',
            ]);

            $checkProsess = User::where('id', $id)->update([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            if ($checkProsess) {


                $data = User::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'succes', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $checkProsess = User::where('id', $id)->delete();

            if ($checkProsess) {
                return ApiFormatter::sendResponse(200, 'succes', 'Berhasil hapus data user');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    
    public function trash()
    {
        try {
            // restore : mengembalikan data spesifik yang dihapus/menghapus deleted_at nya

            $data = User::onlyTrashed()->get();
            return ApiFormatter::sendResponse(200, 'succes', $data);
        }catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $checkProsess = User::onlyTrashed()->where('id', $id)->restore();

            if ($checkProsess) {
                $data = User::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'succes', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMassage());
        }
    }

    public function permanentDelete($id)
    {
        try{
            $checkPermanentDelete = User::onlyTrashed()->where('id', $id)->forceDelete();

            if ($checkPermanentDelete) {
                return ApiFormatter::sendResponse(200, 'succes', 'Berhasil menghapus permanent data user');
            } 
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMassage());
       }
    }
}