<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
   
class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|min:4|max:20||unique:users',
            'email' => 'required|email||unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['pin'] = mt_rand(100000, 999999);
        $input['registered_at'] = date('Y-m-d H:i:s');
        $user = User::create($input);
        //$success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['user_name'] =  $user->user_name;
        $success['email'] =  $user->email;
        $success['pin'] =  $user->pin;
   
        return $this->sendResponse($success, 'User registered successfully!');
    }
   
    /**
     * Pin verfication api
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'pin' => 'required|numeric|min:100000|max:999999',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all(); 
        
        $user = User::where('email', $input['email'])->where('pin', $input['pin'])->first();

        if(!$user){
            return $this->sendError('Record not found!');       
        }

        $user->status = 'active';
        $user->save();

        $success['user_name'] =  $user->user_name;
        $success['email'] =  $user->email;
        //$success['pin'] =  $user->pin;
   
        return $this->sendResponse($success, 'User verfied successfully!');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            $success['name'] =  $user->name;
   
            return $this->sendResponse($success, 'User loggedin successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }
}