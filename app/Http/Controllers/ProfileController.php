<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
  public function show(){

    return view('profile',
    [
      'title' => 'Profile', // Page title
      'paragraph' => 'Profile Page', // Summary paragraph
    ]);
  }


  public function getData(Request $request){
    switch ($request["page"]) {
    case "profile":
        return $this->profileData($request);
        break;
    default:
        return "Invalid";
        break;
      }
  }

  private function profileData($request){
    return "Hello";
  }
}
