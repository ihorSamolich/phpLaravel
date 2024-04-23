<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CategoriesController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/categories",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Response(response="200", description="List Categories.")
     * )
     */
    public function getList(Request $request)
    {
        $perPage = 4;
        $page = $request->query('page', 1);

        $data = Categories::paginate($perPage, ['*'], 'page', $page);

        return response()->json($data)->header("Content-Type", 'application/json; charset=utf-8');
    }

    //    public function getList()
//    {
//        $perPage = 8;
//
//        $data = Categories::paginate($perPage);
//        return response()->json($data)
//            ->header("Content-Type", 'application/json; charset=utf-8');
//    }

    /**
     * @OA\Post(
     *     tags={"Category"},
     *     path="/api/categories/create",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                  required={"name","image","description"},
     *                  @OA\Property(
     *                       property="image",
     *                       type="file",
     *                   ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                       property="description",
     *                       type="string"
     *                   )
     *              )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Category.")
     * )
     */

    public function create(Request $request): JsonResponse
    {
        if ($request->hasFile('image')) {
            $takeImage = $request->file('image');
            $manager = new ImageManager(new Driver());

            $filename = time();

            $sizes = [100, 300, 500];

            foreach ($sizes as $size) {
                $image = $manager->read($takeImage);
                $image->scale(width: $size, height: $size);
                $image->toWebp()->save(base_path('public/uploads/' . $size . '_' . $filename . '.webp'));
            }
        }

        $category = Categories::create([
            'name' => $request->name,
            'description' => $request->description,
            //'image' => '.webp',
            'image' => $filename . '.webp',

        ]);

        return response()->json($category, 201)
            ->header("Content-Type", 'application/json; charset=utf-8');
    }


    /**
     * @OA\Get(
     *     tags={"Category"},
     *     path="/api/categories/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Get Category by ID.")
     * )
     */
    public function show($id): JsonResponse
    {
        $category = Categories::findOrFail($id);
        return response()->json($category);
    }

    /**
     * @OA\Post  (
     *     tags={"Category"},
     *     path="/api/categories/edit/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Ідентифікатор категорії",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                  @OA\Property(
     *                      property="image",
     *                      type="file"
     *                  ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string"
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Add Category.")
     * )
     */
    public function edit($id, Request $request): JsonResponse
    {
        $category = Categories::findOrFail($id);
        $imageName = $category->image;
        $inputs = $request->all();
        if ($request->hasFile("image")) {
            $image = $request->file("image");
            $imageName = uniqid() . ".webp";
            $sizes = [100, 300, 500];
            // create image manager with desired driver
            $manager = new ImageManager(new Driver());
            foreach ($sizes as $size) {
                $fileSave = $size . "_" . $imageName;
                $imageRead = $manager->read($image);
                $imageRead->scale(width: $size);
                $path = public_path('uploads/' . $fileSave);
                $imageRead->toWebp()->save($path);
                $removeImage = public_path('uploads/' . $size . "_" . $category->image);
                if (file_exists($removeImage))
                    unlink($removeImage);
            }
        }
        $inputs["image"] = $imageName;
        $category->update($inputs);
        return response()->json($category, 200,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }


//    /**
//     * @OA\Post (
//     *     tags={"Category"},
//     *     path="/api/categories/{id}",
//     *     @OA\Parameter(
//     *         name="id",
//     *         in="path",
//     *         description="Category ID",
//     *         required=true,
//     *         @OA\Schema(type="integer")
//     *     ),
//     *     @OA\RequestBody(
//     *         @OA\MediaType(
//     *             mediaType="multipart/form-data",
//     *             @OA\Schema(
//     *                 required={"name"},
//     *                 @OA\Property(
//     *                     property="name",
//     *                     type="string"
//     *                 )
//     *             )
//     *         )
//     *     ),
//     *     @OA\Response(response="200", description="Update Category by ID.")
//     * )
//     */
//    public function update(Request $request, $id) : JsonResponse
//    {
//        $name = $request->input('name');
//
//        $validatedData = $request->validate([
//            'name' => 'required|string|max:255',
//        ]);
//
//        $category = Categories::findOrFail($id);
//        $category->update($validatedData);
//
//        return response()->json($category);
//    }

    /**
     * @OA\Delete(
     *     tags={"Category"},
     *     path="/api/categories/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Delete Category by ID.")
     * )
     */
    public function delete($id): JsonResponse
    {
        $category = Categories::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

}
