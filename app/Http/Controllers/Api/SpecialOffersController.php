<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\SpecialOffers;
use Illuminate\Http\Request;

class SpecialOffersController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"SpecialOffers"},
     *     path="/api/specialOffers",
     *     @OA\Response(response="200", description="List SpecialOffers.")
     * )
     */
    public function getList()
    {
        $data = SpecialOffers::all();
        return response()->json($data)
            ->header("Content-Type", 'application/json; charset=utf-8');
    }
}
