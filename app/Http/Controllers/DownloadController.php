<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Storage;
use Response;


class DownloadController extends Controller
{
  public function downloadReplay(Request $request){

    $validationRules = [
      'esport' => 'nullable|in:NGS,CCL,MastersClash,HeroesInternational',
      'replayID' => 'required:integer',
    ];

    $validator = Validator::make($request->all(), $validationRules);

    if ($validator->fails()) {
        return [
            'data' => [$request->all()],
            'status' => 'failure to validate inputs',
        ];
    }

    $replayID = $request["replayID"];
    $bucket = 'heroesprofile-ccl';

    return Storage::disk('gcs')->download("https://storage.googleapis.com/$bucket/{$replayID}.StormReplay");

  }
}
