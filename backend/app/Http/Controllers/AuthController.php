<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Socialite;


class AuthController extends Controller
{
    public function __construct()
    {

    }
    //rediret to google login
    public function redirectToGoogle(){
        return Socialite::driver('google')->redirect();
    }
    //handle google callback
    public function handleGoogleCallback(){
        $user = Socialite::driver('google')->stateless()->user();
        $authUser = $this->findOrCreateUser($user,'google');
        auth()->login($authUser,true);
        return $authUser;
    }
    // find or create user and authenticate him
    public function findOrCreateUser($user){
        $authUser = User::where('provider_id',$user->id)->first();
        if($authUser){
            return $authUser;
        }
        return User::create([
            'name' => $user->name,
            'email' => $user->email,
            'provider' => 'google',
            'provider_id' => $user->id,
            'image' => $user->avatar,
        ]);
    }
    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'email'=> 'required|string|email|unique:users',
            'password' =>'required|string|confirmed|min:6'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password'=>bcrypt($request->password)]
        ));
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ],201);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }


    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' =>auth()->user()->name,
        ]);
    }
}
