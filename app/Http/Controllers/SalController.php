<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Classes\SalApi;
use App\Classes\CredentialsManager;
use App\Models\School;

use Illuminate\Support\Facades\Log;


class SalController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */

    public function getSchools(){
      $schools = School::get();
      return response()->json(["data" => ["schools" => $schools]]);
    }

    public function process($school, $endpoint = null)
    {
      if($school == "schools"){
        $endpoint = "schools";
      }

      $api = new SalApi();

      // Only for testing!
      if(isset($_GET["username"]) && isset($_GET["password"])){
        $username = $_GET["username"];
        $password = $_GET["password"];
      }

      if(isset($_POST["username"]) && isset($_POST["password"])){
        $username = $_POST["username"];
        $password = $_POST["password"];
      }

      if(empty($username) || empty($password)){
        $username = $_SERVER['PHP_AUTH_USER'] ?? null;
        $password = $_SERVER['PHP_AUTH_PW'] ?? null;
      }

      if(empty($username) || empty($password)){
        $authorization = $_SERVER['authorization'] ?? null;
        if(!empty($authorization)){
          $authDecoded = explode(":", base64_decode($authorization));
          $username = $authDecoded[0];
          $password = $authDecoded[1];
        }
      }

      if((empty($username) || empty($password)) && !empty($this->getBearerToken())){
        $cm = new CredentialsManager();
        $c = $cm->getCredentials($this->getBearerToken());

        if(isset($c->username)){
          $username = $c->username;
        }
        if(isset($c->password)){
          $password = $c->password;
        }
      }

      if(!in_array('endpoint', ["subjects", "absence_information", "events", "user", "class", "login"])){

        if(!empty($username) && !empty($password) && !empty($school)){
        if($api->login($username, $password, $school)){
            if($endpoint == "login"){
              $cm = new CredentialsManager();
              $token = $cm->createToken(["username" => $username, "password" => $password]);
              $api->logout();
              return response()->json(["data" => ["token" => $token]]);
            }

            if($endpoint == "subjects"){
              $subjects = $api->getSubjects();
              $api->logout();
              return response()->json(["data" => ["subjects" => $subjects]]);
            }

            if($endpoint == "absence_information"){
              $absences = $api->getAbsenceInformation();
              $api->logout();
              return response()->json(["data" => $absences]);
            }

            if($endpoint == "user"){
              $user = $api->getUser();
              $api->logout();
              return response()->json(["data" => $user]);
            }

            if($endpoint == "events"){
              $events = $api->getEvents();
              $api->logout();
              return response()->json(["data" => ["events" => $events]]);
            }

            if($endpoint == "class"){
              $class = $api->getClass();
              $api->logout();
              return response()->json(["data" => $class]);
            }

            $api->logout();
          }
          else{
            Log::warn("Failed SAL login.", ["username" => $username]);
            return response()->json([
              "error" => [[
                "message" => "Wrong credentials."
              ]]
            ]);
          }
        }
        else{
          return response()->json([
            "error" => [[
              "message" => "No credentials provided."
            ]]
          ]);
        }
      }
      else{
          return response()->json([
            "error" => [[
              "message" => "No endpoint chosen."
            ]]
          ]);
      }
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
