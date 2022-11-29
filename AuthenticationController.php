<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;

class AuthenticationController extends Controller
{
    public function checkAdminRole(){
        if(Auth::check())
        {
            return response()->json([
                'success'   => true,
            ]);
        }
        else{
            return response()->json([
                'success'   => false,
            ]);
        }
    }
    public function adminLogin(Request $request)
    {
        $attributeNames = [
            'email' => 'Email',
            'password' => 'Password',
        ];

        $messages = [

        ];

        $rules = [
            'email' => 'required',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->setAttributeNames($attributeNames);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }
        else
        {
            $Credentials = [
                'email'     => $request->email,
                'password'  => $request->password,
            ];
            if(Auth::attempt($Credentials)){
                $User = User::where('email', $request->email)->first();
                if($User->status == 'Inactive') {
                    Auth::logout();
                    return response()->json([
                        'status' => 'activation',
                        'message' => 'Your account is not active yet, Kindly check your email',
                    ]);
                }
                else
                {
                    if($request->remember_me == true)
                    {
                        Auth::login($User, true);
                    }
                    Auth::login($User);
                    return response()->json([
                        "message" =>"Login Successfully!",
                        'success' => true,
                        'user' => Auth::user(),
                    ]);
                }
            }
            else
            {
                return response()->json([
                    'success' => 'credentials',
                    'errors' => 'Credentials Not Matched',
                ]);
            }
        }
    }
    public function logout(){
        Auth::logout();
        return response()->json([
            'success'   => true,
        ]);
    }
}
