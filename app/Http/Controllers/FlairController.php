<?php

namespace App\Http\Controllers;

class FlairController extends Controller
{
    // Flair the frontend resolves itself (so cached page data doesn't need it).
    public function state()
    {
        return response()->json([
            'void_eye' => $this->globalDataService->getVoidEyeHolders(),
            'owner_hidden' => $this->globalDataService->isFlairHidden('owner', 67280, 1),
        ]);
    }
}
