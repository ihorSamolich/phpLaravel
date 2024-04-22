<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Products;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductsController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Product"},
     *     path="/api/products",
     *     @OA\Response(response="200", description="List Products.")
     * )
     */
    public function getList()
    {
        $data = Products::with('product_images')->get();
        return response()->json($data)
            ->header("Content-Type", 'application/json; charset=utf-8');
    }

    /**
     * @OA\Get(
     *     tags={"Product"},
     *     path="/api/products/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Get Products by Category ID.")
     * )
     */
    public function getByCategory($id)
    {
        $products = Products::where('category_id', $id)->with('product_images')->get();

        return response()->json($products)
            ->header("Content-Type", 'application/json; charset=utf-8');
    }


    /**
     * @OA\Post(
     *     tags={"Product"},
     *     path="/api/products/create",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"name","description","price", "category_id"},
     *                  @OA\Property(
     *                       property="name",
     *                       type="string"
     *                  ),
     *                  @OA\Property(
     *                       property="description",
     *                       type="string"
     *                   ),
     *                 @OA\Property(
     *                      property="price",
     *                      type="number",
     *                      format="float"
     *                  ),
     *                  @OA\Property(
     *                      property="category_id",
     *                      type="integer"
     *                  )
     *              )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Product.")
     * )
     */

    public function create(Request $request): JsonResponse
    {
        $product = Products::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
        ]);

        return response()->json($product, 201)
            ->header("Content-Type", 'application/json; charset=utf-8');
    }
}
