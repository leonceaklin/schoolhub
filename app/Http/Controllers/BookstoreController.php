<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Copy;
use App\Models\Item;
use App\Models\Edition;
use App\Models\File;
use App\Classes\SalApi;

use ReallySimpleJWT\Token;
use Illuminate\Support\Facades\Log;
use App\Classes\CredentialsManager;



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

       if($endpoint == 'copies:updated'){
         return $this->processCopyWebhook('updated');
       }

       if($endpoint == 'copies:created'){
         return $this->processCopyWebhook('created');
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
       $copy->save();

       $this->mailToUser("Deine Bestellung im GymLi Bookstore", view("mail.order_confirmed", ["copy" => $copy]));
       return $copy;
     }

     public function cancelOrder($data){
       $copy = Copy::where('order_hash', $data->order_hash)->first();

       if($copy->status != 'ordered' && $copy->status != 'submitted'){
         return null;
       }

       if($copy->status == 'ordered'){
         $copy->status = 'available';
         $copy->ordered_by = null;
         $copy->ordered_on = null;
         $copy->order_hash = null;
         $copy->save();
         return $copy;
       }

       if($copy->status == 'submitted'){
         $copy->delete();
         return $copy;
       }
     }

     public function processCopyWebhook($event){
       $copies = Copy::where('sold_on', null)->where('status', 'sold')
       ->orWhere(function($query){
         $query->where('available_since', null)->where('status', 'available');
       })
       ->orWhere(function($query){
         $query->where('status', 'available')->where('ordered_by', "!=", null);
       })
       ->orWhere(function($query){
         $query->where('status', 'available')->where('ordered_on', "!=", null);
       })
       ->get();

       foreach($copies as $copy){
         if($copy->status == "available"){
            $copySaved = false;
            $copy->ordered_on = null;
            $copy->ordered_by = null;
            $copy->order_hash = null;

             if(!$copy->available_since){
                 $copy->available_since = date();
                 $copy->save();
                 $copySaved = true;
                 Log::info("Copy available (".$copy->uid.") ".$copy->price." CHF by ".$copy->ownedBy->email);

                 if($event == 'updated'){
                   $this->mailToUser("Dein Buch ist jetzt im GymLi Bookstore verfügbar", view("mail.copy_available", ["copy" => $copy]), $copy->ownedBy);
                 }
             }

             if(!$copySaved){
               $copy->save();
             }
         }

         if($copy->status == "sold"){
             if(!$copy->sold_on){
                 $copy->sold_on = date();
                 $copy->save();
                 Log::info("Copy sold (".$copy->uid.") ".$copy->price." CHF to ".$copy->orderedBy->email);
             }
         }
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

       $this->mailToUser("Dein eingereichtes Exemplar für den GymLi Bookstore", view("mail.copy_submitted", ["copy" => $copy]));

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

     public function mailTo($receiver, $title, $content){
       $sender = "GymLi Bookstore<gymlibookstore@schoolhub.ch>";
       $subject = $title;
       $reply = "GymLi Bookstore Support<buechergymliestal@gmail.com>";
       $header  = "MIME-Version: 1.0\r\n";
       $header .= "Content-type: text/html; charset=utf-8\r\n";

       $header .= "From: $sender\r\n";
       $header .= "Reply-To: $reply\r\n";
       $header .= "X-Mailer: PHP ". phpversion();
       return mail( $receiver,
         '=?utf-8?B?'.base64_encode($subject).'?=',
         $content,
         $header);
     }

     public function mailToUser($title, $content, $user = null){
       if($user == null){
         $user = $this->user();
       }
       if($user == null){
         return false;
       }

       if($user->email){
         $receiver = $user->email;
       }
       else{
         $receiver = $user->username."@sbl.ch";
       }
       return self::mailTo($receiver, $title, $content);
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
