<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Http\Resources\User as UserResource;
   
class UserController extends BaseController
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function profileUpdate(User $user, Request $request)
    {
        $input = $request->all();
        $userId = $user->id;
        $validator = Validator::make($input, [
            'user_name' => 'sometimes|string|min:4|max:20|unique:users,id,'.$userId,
            'email' => 'sometimes|email|max:255|unique:users,id,'.$userId,
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|dimensions:width=256,height=256',
        ]);

   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        if (!empty($input['user_name'])) {
            $user->user_name = $input['user_name'];
        }
        if (!empty($input['email'])) {
            $user->email = $input['email'];
        }
        if (!empty($input['name'])) {
            $user->name = $input['name'];
        }
        if (!empty($input['user_role'])) {
            $user->user_role = $input['user_role'];
        }
        if (!empty($input['password'])) {
            $user->password = bcrypt($input['password']);
        }
        if ($file = $request->file('avatar')) {
            
            $path = $file->store('public/files');
            $name = $file->getClientOriginalName();

            //Move Uploaded File
            $destinationPath = 'uploads';
            $file->move($destinationPath, $file->getClientOriginalName());

            $user->avatar = $name;
   
        }

        $user->save();
   
        return $this->sendResponse(new UserResource($user), 'Profile updated successfully!');
    }
}