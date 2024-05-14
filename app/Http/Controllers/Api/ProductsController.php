<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\ProductImage;
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
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="The page number to retrieve",
     *          @OA\Schema(
     *              type="integer",
     *              default=1
     *          )
     *      ),
     *     @OA\Response(response="200", description="List Products.")
     * )
     */
    public function getList(Request $request)
    {
        $perPage = 4;
        $page = $request->query('page', 1);

        $data = Products::with('product_images')->paginate($perPage, ['*'], 'page', $page);
        return response()->json($data)
            ->header("Content-Type", 'application/json; charset=utf-8');
    }

    /**
     * @OA\Get(
     *     tags={"Product"},
     *     path="/api/products/discounts",
     *     @OA\Response(response="200", description="List Discounted Products.")
     * )
     */
    public function getListDiscounts()
    {
        $query = Products::with('product_images')
            ->whereNotNull('discount_percentage')
            ->whereDate('discount_start_date', '<=', now())
            ->whereDate('discount_end_date', '>=', now());

        $discountedProducts = $query->get();

        $randomProducts = $discountedProducts->random(4);

        return response()->json($randomProducts)
            ->header("Content-Type", 'application/json; charset=utf-8');
    }


    /**
     * @OA\Get(
     *     tags={"Product"},
     *     path="/api/product/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Get Product by ID.")
     * )
     */
    public function getProduct($id)
    {
        $product = Products::with('product_images')->findOrFail($id);

        return response()->json($product)
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
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Response(response="200", description="Get Products by Category ID.")
     * )
     */
    public function getByCategory($id, Request $request)
    {
        $perPage = 4;
        $page = $request->query('page', 1);

        $products = Products::where('category_id', $id)
            ->with('product_images')
            ->paginate($perPage, ['*'], 'page', $page);

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
     *                  ),
     *                  @OA\Property(
     *                     property="product_images[]",
     *                     type="array",
     *                        @OA\Items(
     *                            type="file",
     *                        )
     *                   )
     *              )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Product.")
     * )
     */

    public function create(Request $request): JsonResponse
    {
        $productImages = $request->file('product_images');

        $product = Products::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
        ]);

        if ($productImages) {

            $sizes = [100, 300, 500];
            $priority = 1;

            foreach ($productImages as $productImage) {
                $filename = time();
                $manager = new ImageManager(new Driver());

                foreach ($sizes as $size) {
                    $image = $manager->read($productImage);
                    $image->scale(width: $size, height: $size);
                    $image->toWebp()->save(base_path('public/uploads/' . $size . '_' . $filename . $priority . '.webp'));
                }

                ProductImage::create([
                    'name' => $filename . $priority . '.webp',
                    'priority' => $priority,
                    'product_id' => $product->id,
                ]);

                $priority++;
            }
        }

        return response()->json($product, 201)
            ->header("Content-Type", 'application/json; charset=utf-8');
    }
}
