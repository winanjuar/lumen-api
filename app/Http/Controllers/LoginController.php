<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;

class LoginController extends Controller
{
    public function login(Request $request) {
        $rules = [
            'email' => 'required',
            'password' => 'required'
        ];
        $customMessages = [
            'required' => 'Please fill attribute :attribute'
        ];
        $this->validate($request, $rules, $customMessages);

        $email = $request->input('email');
        try {
            $login = User::where('email', $email)->first();
            if ($login) {
                if ($login->count() > 0) {
                    if (Hash::check($request->input('password'), $login->password)) {
                        try {
                            $api_token = sha1($login->id.time());
                            $create_token = User::where('id', $login->id)->update([
                                'api_token' => $api_token
                            ]);
                            $res['status'] = true;
                            $res['message'] = 'Login success!';
                            $res['data'] = $login;
                            $res['api_token'] = $api_token;
                            return response($res, 200);
                        } catch (\Illuminate\Database\QueryException $ex) {
                            $res['status'] = false;
                            $res['message'] = $ex->getMessage();
                            return response($res, 500);
                        }
                    } else {
                        $res['status'] = false;
                        $res['message'] = 'You input wrong password!';
                        return response($res, 500);
                    }
                } else {
                    $res['status'] = false;
                    $res['message'] = 'Email not found!';
                    return response($res, 500);
                }
            } else {
                $res['status'] = false;
                $res['message'] = 'Email not found!';
                return response($res, 500);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $res['status'] = false;
            $res['message'] = $ex->getMessage();
            return response($res, 500);
        }
    }
}
