<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Classes\SalApi;

class SalController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
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

      if(!in_array('endpoint', ["schools", "subjects", "absence_information", "events", "user", "class"])){
        //Endpoints without login
        if($endpoint == "schools"){
          return response()->json(["data" => ["schools" => $api->getSchools()]]);
        }

        //Endpoints with login
        else if(!empty($username) && !empty($password) && !empty($school)){
        if($api->login($username, $password, $school)){
            if($endpoint == "subjects"){
              return response()->json(["data" => ["subjects" => $api->getSubjects()]]);
            }

            if($endpoint == "absence_information"){
              return response()->json(["data" => $api->getAbsenceInformation()]);
            }

            if($endpoint == "user"){
              return response()->json(["data" => $api->getUser()]);
            }

            if($endpoint == "events"){
              return response()->json(["data" => ["events" => $api->getEvents()]]);
            }

            if($endpoint == "class"){
              return response()->json(["data" => $api->getClass()]);
            }

            $api->logout();
          }
          else{
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
}
