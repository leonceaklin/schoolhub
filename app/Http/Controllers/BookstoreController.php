<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Copy;
use App\Models\Item;
use App\Models\Edition;
use App\Models\File;
use App\Classes\SalApi;
use App\Models\Order;

use ReallySimpleJWT\Token;
use Illuminate\Support\Facades\Log;
use App\Classes\CredentialsManager;

use App\Mail\CopySubmitted;
use App\Mail\OrderConfirmed;
use Illuminate\Support\Facades\Mail;

class BookstoreController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */

     private $secret;

     public function __construct(){
       $this->salApi = new SalApi();
       $this->secret = config("auth.jwt_secret");
     }

     public function process($endpoint){


       if($endpoint == 'users:check'){
         return $this->response(["exists" => $this->checkUser($this->post())]);
       }

       if($endpoint == 'users:me'){
         return $this->response($this->user());
       }

       if($endpoint == 'users:login'){
         return $this->response(["token" => $this->login($this->post())]);
       }

       if($endpoint == 'users:update'){
         return $this->response(["user" => $this->updateUser($this->post())]);
       }


       if($endpoint == 'users:register'){
         return $this->response(["token" => $this->register($this->post())]);
       }

       if($endpoint == 'users:check_session'){
         return $this->response($this->isLoggedIn());
       }

       if($endpoint == 'copies:order'){
         return $this->response(["copy" => $this->orderCopy($this->post())]);
       }

       if($endpoint == 'copies:submit'){
         return $this->response(["copy" => $this->submitCopy($this->post())]);
       }

       if($endpoint == 'copies:cancelorder'){
         return $this->response(["copy" => $this->cancelOrder($this->post())]);
       }

     }

     public function response($data, $error = null){
       $response = ["data" => $data];
       if($error){
         $response["error"] = $error;
       }
       return json_encode($response);
     }

     public function checkUser($properties){
       $credentials = $this->getCredentials($properties);

       return User::where('username', $credentials->username)->exists();
     }

     public function getCredentials($properties){
       if(isset($properties->username)){
         $username = $properties->username;
       }

       if(isset($properties->password)){
         $password = $properties->password;
       }

       if(isset($properties->credentials_token)){
         $cm = new CredentialsManager();
         $c = $cm->getCredentials($properties->credentials_token);

         $username = $c->username;
         $password = $c->password;
       }

       $cred = (object) [];

       if(isset($username)){
         $cred->username = $username;
       }

       if(isset($password)){
         $cred->password = $password;
       }

       return $cred;
     }

     public function isLoggedIn(){
       if(empty($this->getBearerToken())){
         return false;
       }
       return Token::validate($this->getBearerToken(), $this->secret);
     }

     public function user(){
       if(!$this->isLoggedIn()){
         return null;
       }
       $payload = Token::getPayload($this->getBearerToken(), $this->secret);
       $userId = $payload['user_id'];
       return User::find($userId);
     }

     public function login($properties){
       $credentials = $this->getCredentials($properties);

       if($this->salApi->login($credentials->username, $credentials->password, "gymli")){
         $user = User::where('username', $credentials->username)->first();
         $token = Token::create($user->id, $this->secret, time()+3600*24*30, "SchoolHub");
         $this->salApi->logout();
         return $token;
       }
       return null;
     }

     public function register($properties){
       $credentials = $this->getCredentials($properties);

       if(!User::where('username', $credentials->username)->exists()){
         if($this->salApi->login($credentials->username, $credentials->password, "gymli")){
             $user = new User();
             $user->username = $credentials->username;
             $user->email = $properties->email;
             $user->phone = $properties->phone;
             $user->mobile = $properties->mobile;
             $user->first_name = $properties->first_name;
             $user->last_name = $properties->last_name;
             $user->city = $properties->city;
             $user->zip = $properties->zip;
             $user->save();
             $token = Token::create($user->id, $this->secret, time()+3600*24*30, "SchoolHub");
             $this->salApi->logout();
             Log::info("New user created.", ["id" => $user->id, "username" => $user->username, "name" => $user->name]);
             return $token;
         }
       }
     }


     public function post(){
       if(!isset($this->_post)){
         $inputJSON = file_get_contents('php://input');
         $input = json_decode($inputJSON);
         $this->_post = $input;
       }
       return $this->_post;
     }


     public function orderCopy($data){
       if(!$this->user()){
         return null;
       }
       $copy = Copy::find($data->id);

       if($copy->status != 'available' || $copy->ordered_by != null){
         return null;
       }

       $copy->status = 'ordered';
       $copy->ordered_by = $this->user()->id;
       $copy->ordered_on = date("Y-m-d H:i:s");
       $copy->generateOrderHash();

       if($this->user()->activeOrder != null){
         $order = $this->user()->activeOrder;
       }
       else{
         $order = new Order();
         $order->status = "placed";
         $order->placed_by = $this->user()->id;
         $order->save();
       }

       $copy->order = $order->id;
       $copy->save();


       $order->calculate();
       $order->save();

       Log::info("Copy ordered.", ["ordered_by" => $copy->ordered_by, "uid" => $copy->uid, "price" => $copy->price, "id" => $copy->id]);

       Mail::to($copy->orderedBy->activeEmail, $copy->orderedBy->name)->send(new OrderConfirmed($copy));
       return $copy;
     }

     public function cancelOrder($data){
       $copy = Copy::where('order_hash', $data->order_hash)->first();

       if($copy->status != 'ordered' && $copy->status != 'submitted' && $copy->status != 'prepared'){
         return null;
       }

       if($copy->status == 'ordered' || $copy->status == 'prepared'){
         $copy->status = 'available';
         $copy->ordered_by = null;
         $copy->ordered_on = null;
         $copy->order_hash = null;

         if($copy->_order){
           $order = $copy->_order;
           $copy->order = null;
           $copy->save();

           if(sizeof($order->copies) == 0){
             $order->delete();
           }
           else{
             $order->calculate();
             $order->save();
           }
         }

         $copy->save();

         Log::info("Order cancelled.", ["uid" => $copy->uid, "id" => $copy->id]);
         return $copy;
       }

       if($copy->status == 'submitted'){
         $copy->delete();
         Log::info("Submission of copy cancelled.", ["uid" => $copy->uid, "id" => $copy->id]);
         return $copy;
       }
     }

     public function submitCopy($data){
       if(!$this->user()){
         return null;
       }

       $copy = new Copy();
       $copy->status = 'submitted';
       $copy->price = intval($data->price);
       if($copy->price > 200){
         return null;
       }
       $copy->owned_by = $this->user()->id;
       $store =  $this->user()->_school->stores[0];
       $copy->store = $store->id;

       if($store->commission != null && $store->commission > 0 && $store->commission <= 1){
         $copy->commission = $store->commission;
       }

       if($store->charity_commission != null && $store->charity_commission > 0 && $store->charity_commission <= 1){
         $copy->charity_commission = $store->charity_commission;
       }

       if($store->charity != null && $store->_charity != null){
         $copy->charity = $store->charity;
       }

       if($store->allow_donations){
         $copy->donation = $data->donation;
       }

       $copy->generateOrderHash();

       $item = Item::find($data->item);
       if(!isset($item->id)){
         return null;
       }
       $copy->item = $data->item;

       $editions = $item->editions;
       if(sizeof($editions) != 0){
         if(!isset($data->edition)){
           return null;
         }

         if(!$item->hasEdition($data->edition)){
           return false;
         }

         $copy->edition = $data->edition;
       }

       $copy->save();

       Log::info("Copy submitted.", ["id" => $copy->id, "uid" => $copy->uid, "owned_by" => $copy->ownedBy->id]);

       Mail::to($copy->ownedBy->activeEmail, $copy->ownedBy->name)->send(new CopySubmitted($copy));

       return $copy;
     }

     public function updateUser($data){
       $user = $this->user();
       if(!$this->user()){
         return false;
       }

       if(isset($data->email)){
         $email = strtolower($data->email);
         if(preg_match('/.+@(edu\.)?sbl.ch/', $email)){
           return null;
         }

         if(!preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $email)){
           return null;
         }

         $user->email = $email;
       }

       if(isset($data->iban)){
         $user->iban = $data->iban;
       }

       if(isset($data->city)){
         $user->city = $data->city;
       }

       if(isset($data->zip)){
         $user->zip = $data->zip;
       }

       if(isset($data->mobile)){
         $user->mobile = $data->mobile;
       }

       $user->save();
       return $user;
     }

     function getAuthorizationHeader(){
         $headers = null;
         if (isset($_SERVER['Authorization'])) {
             $headers = trim($_SERVER["Authorization"]);
         }
         else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
             $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
         } elseif (function_exists('apache_request_headers')) {
             $requestHeaders = apache_request_headers();
             $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
             if (isset($requestHeaders['Authorization'])) {
                 $headers = trim($requestHeaders['Authorization']);
             }
         }
         return $headers;
     }

     function getBearerToken() {
         $headers = $this->getAuthorizationHeader();
         if (!empty($headers)) {
             if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                 return $matches[1];
             }
         }
         return null;
     }
}
