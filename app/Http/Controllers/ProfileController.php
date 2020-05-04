<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
  public function show(){

    return view('Profile.home',
    [
      'title' => 'Profile', // Page title
      'paragraph' => 'Profile Page', // Summary paragraph
      'page' => 'profile',
      'inputUrl' => 'getProfileData'
    ]);
  }


  public function getData(Request $request){
    switch ($request["page"]) {
    case "profile":
        return $this->getProfileData($request);
        break;
    default:
        return "Invalid";
        break;
      }
  }

  private function getProfileData($request){
    return "Hello";
  }
}
