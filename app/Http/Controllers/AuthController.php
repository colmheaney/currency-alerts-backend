<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthController extends Controller
{
  public function store(Request $request)
  {
    $this->validate($request, [
      'name' => 'required',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:5',
    ]);

    $name = $request->input('name');
    $email = $request->input('email');
    $password = $request->input('password');

    $user = new User([
      'name' => $name,
      'email' => $email,
      'password' => bcrypt($password)
    ]);

    if ($user->save()) {
      $response = [
        'msg' => 'User created',
        'user' => $user
      ];
      return response()->json($response, 201);
    }

    $response = [
      'msg' => 'An error occurred',
    ];

    return response()->json($response, 404);
  }

  public function signin(Request $request)
  {
    $this->validate($request, [
      'email' => 'required|email',
      'password' => 'required'
    ]);
    
    $credentials = $request->only('email', 'password');

    try {
      if (! $token = JWTAuth::attempt($credentials)) {
        return response()->json(['msg' => 'Invalid credentials'], 401);
      }
    } catch (JWTException $e) {
      return response()->json(['mg' => 'Could not create token'], 500);
    }

    return response()->json(['token' => $token]);
  }
}
