<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers\ApiFormatter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
	    $this->validate($request,[
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::attempt($credentials)){
            return ApiFormatter::sendResponse(400, false, 'User not found', 'Silahkan cek kembali email dan password anda!');
        }

        $respondWithToken = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ];
        return ApiFormatter::sendResponse(200,true, 'Logged-in', $respondWithToken);
    }

     /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return ApiFormatter::sendResponse(200,true, 'success', auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return ApiFormatter::sendResponse(200,true, 'success', 'Berhasil Logout!');
    }
}


//     public function authenticate(Request $request)
//     {
//         $this->validate($request, [
//             'email' => 'required',
//             'password' => 'required'
//         ]);


//         $user = User::where('email', $request->input('email'))->first();


//         if (Hash::check($request->input('password'), $user->password)) {
//             $apikey = base64_encode(Str::random(40));


//             $user->update([
//                 'api_key' => $apikey
//             ]);


//             return response()->json([
//                 'success' => true,
//                 'api_key' => $apikey
//             ]);
//         } else {
//             return response()->json([
//                 'success' => false
//             ], 401);
//         }
//     }


//     public function logout(Request $request)
//     {
//         if ($request->header('api_key')) {
//             $user = User::where('api_key', $request->input('api_key'));
//             $user->update([
//                 'api_key' => NULL
//             ]);


//             return response()->json([
//                 'success' => true,
//                 'message' => "Berhasil Logout!"
//             ], 200);
//         } else {
//             return response()->json([
//                 'success' => false,
//             ], 400);
//         }
//     }