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
    public function getList() {
        $data = Products::all();
        return response()->json($data)
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
     *                  required={"name","image","description","price", "category_id"},
     *                  @OA\Property(
     *                       property="image",
     *                       type="file",
     *                   ),
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

    public function create(Request $request) : JsonResponse
    {
        if ($request->hasFile('image')) {
            $takeImage = $request->file('image');
            $manager = new ImageManager(new Driver());

            $filename = time();

            $sizes = [100, 300, 500];

            foreach ($sizes as $size) {
                $image = $manager->read($takeImage);
                $image->scale(width: $size, height: $size);
                $image->toWebp()->save(base_path('public/uploads/'.$size.'_'.$filename.'.webp'));
            }
        }

        $product = Products::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'image' => $filename.'.webp',

        ]);

        return response()->json($product, 201)
            ->header("Content-Type", 'application/json; charset=utf-8');
    }
}
