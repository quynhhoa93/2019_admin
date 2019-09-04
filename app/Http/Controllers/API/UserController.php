<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManagerStatic as Image;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
//        $this->authorize('isAdmin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $this->authorize('isAdmin');
        if(\Gate::allows('isAdmin') || \Gate::allows('isAuthor')) {
            return User::latest()->paginate(2);
        }
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


    public function updateProfile(Request $request){
        $user = auth('api')->user();
        $this->validate($request,[
            'name'=>'required|string|max:100',
            'email'=>'required|string|email|max:200|unique:users,email,'.$user->id,
            'password'=>'sometimes|required|min:6',
        ]);
        $currentPhoto = $user->photo;
        if($request->photo != $currentPhoto){
            $name = time().'.' . explode('/', explode(':', substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];
            \Image::make($request->photo)->save(public_path('img/profile/').$name);
            $request->merge(['photo'=>$name]);

            $userPhoto = public_path('./img/profile/').$currentPhoto;
            if(file_exists($userPhoto)){
                @unlink($userPhoto);
            }
        }
        if(!empty($request->password)){
            $request->merge(['password' => Hash::make($request['password'])]);
        }
        $user->update($request->all());


        return ['message'=>'thanh cong'];
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
        $this->authorize('isAdmin');
        $user = User::findOrFail($id);

        $user->delete();

        return ['message' => 'User da xoa'];
    }
}
