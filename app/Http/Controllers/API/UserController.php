<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::latest()->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'name'=>'required|string|max:100',
            'email'=>'required|string|email|max:200|unique:users',
            'password'=>'required|string|min:6',
        ]);



        return User::create([
            'name' =>$request['name'],
            'email' =>$request['email'],
            'type' =>$request['type'],
            'bio' =>$request['bio'],
            'photo' =>$request['photo'],
            'password' => Hash::make($request['password']),
        ]);
    }


    public function profile(){
        return auth('api')->user();
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);


        $user->update($request->all());
        $this->validate($request,[
            'name'=>'required|string|max:100',
            'email'=>'required|string|email|max:200|unique:users,email,'.$user->id,
            'password'=>'sometimes|min:6',
        ]);


        return ['message'=>'update success'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return ['message' => 'User da xoa'];
    }
}
