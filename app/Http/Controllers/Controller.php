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

    /**
     * How many talent builds to return. Fixed for the site's own pages; the
     * public API lets a caller ask for a different number, as the old API's
     * `total_builds` parameter did.
     */
    public function setBuildsToReturn(int $builds): static
    {
        $this->buildsToReturn = max(1, min($builds, self::MAX_BUILDS_TO_RETURN));

        return $this;
    }
}
