<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Services\GlobalDataService;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    protected $globalDataService;
    protected $buildsToReturn;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
        $this->buildsToReturn = 7;
    }
    
}
