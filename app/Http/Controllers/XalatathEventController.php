<?php

namespace App\Http\Controllers;

class XalatathEventController extends Controller
{
    public function totals()
    {
        $event = $this->globalDataService->getXalatathEvent();

        if (! $event) {
            return response()->noContent();
        }

        return response()->json($event);
    }
}
