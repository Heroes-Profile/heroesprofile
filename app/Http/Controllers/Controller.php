<?php

namespace App\Http\Controllers;

use App\Services\GlobalDataService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $globalDataService;

    /**
     * How many talent builds to return. Resolved per request from `total_builds`
     * rather than set on the instance — a global query runs inside a job that
     * builds its own controller, so instance state set by the caller never
     * reaches it.
     */
    protected $buildsToReturn;

    /** What the site's own talent pages show. */
    public const DEFAULT_BUILDS_TO_RETURN = 7;

    /** A ceiling, so a public caller cannot ask for an unbounded result set. */
    public const MAX_BUILDS_TO_RETURN = 25;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
        $this->buildsToReturn = self::DEFAULT_BUILDS_TO_RETURN;
    }
}
