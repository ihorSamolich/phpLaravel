<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Events\Registered;

use Google_Client;
use Google_Service_Oauth2;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/login",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="User login data",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         required={"email", "password"},
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="password", type="string"),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *       mediaType="application/json"
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request"
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found"
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Forbidden"
     *   )
     * )
     */
    public function login(Request $request)
    {
        $recaptchaToken = $request->recaptchaToken;

        $secretKey = env('RECAPTCHA_SECRET_ID');

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $recaptchaToken,
        ]);

        $score = $response['score'];

        if ($response['success'] && $score >= 0.5) {
            $validation = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6'
            ], [
                'email.required' => 'The email is required.',
                'email.email' => 'The email must be a valid email address.',
                'password.required' => 'The password cannot be empty.',
                'password.min' => 'The password must be at least 6 characters long.',
            ]);
            if ($validation->fails()) {
                return response()->json($validation->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if (!$token = auth()->attempt($validation->validated())) {
                return response()->json(['error' => 'Uncorrected login data!'], Response::HTTP_UNAUTHORIZED);
            }
            return response()->json(['token' => $token], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Recaptcha not verified!'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/login/google",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="User login data",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         required={"token"},
     *         @OA\Property(property="token", type="string"),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *       mediaType="application/json"
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request"
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found"
     *   )
     * )
     */
    public function loginGoogle(Request $request)
    {
        $token = $request->token;

        $clientId = env('GOOGLE_CLIENT_ID');
        $client = new Google_Client(['client_id' => $clientId]);

        try {
            $payload = $client->verifyIdToken($token);

            if ($payload) {
                $userEmail = $payload['email'];
                $user = User::where('email', $userEmail)->first();

                if (!$user) {
                    $imageUrl = $payload['picture'];
                    $imageContent = file_get_contents($imageUrl);

                    $folderName = public_path('uploads');
                    if (!file_exists($folderName)) {
                        mkdir($folderName, 0777);
                    }

                    $imageName = uniqid() . ".webp";
                    $sizes = [100, 300, 500];
                    $manager = new ImageManager(new Driver());
                    foreach ($sizes as $size) {
                        $fileSave = $size . "_" . $imageName;
                        $imageRead = $manager->read($imageContent);
                        $imageRead->scale(width: $size);
                        $path = public_path('uploads/' . $fileSave);
                        $imageRead->toWebp()->save($path);
                    }

                    $userNew = User::create([
                        'name' => $payload['name'],
                        'email' => $payload['email'],
                        'sub' => $payload['sub'],
                        'password' => Hash::make(Str::random(100)),
                        'phone' => 'Google account',
                        'image' => $imageName,
                    ]);

                    $userNew->email_verified_at = now();
                    $userNew->save();

                    $token = auth()->login($userNew);
                    return response()->json(['token' => $token], Response::HTTP_OK);

                }

                if ($user->email_verified_at == null) {
                    $user->email_verified_at = now();
                    $user->sub = $payload['sub'];

                    $user->save();
                }

                $token = auth()->login($user);
                return response()->json(['token' => $token], Response::HTTP_OK);

            } else {
                return response()->json(['error' => 'Invalid token'], 401);
            }
        } catch (Exception) {
            return response()->json(['error' => 'Token verification failed'], 500);
        }
    }


    /**
     * @OA\Post(
     *   path="/api/verification",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="User verification data",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         required={"email"},
     *         @OA\Property(property="email", type="string"),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *       mediaType="application/json"
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request"
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found"
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Forbidden"
     *   )
     * )
     */
    public function verificationEmail(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('email', $request->email)->first();
        $user->sendEmailVerificationNotification();

        return response()->json($user, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     tags={"Auth"},
     *     path="/api/users",
     *     @OA\Response(response="200", description="List Users.")
     * )
     */
    public function getList()
    {
        $data = User::all();
        return response()->json($data)
            ->header("Content-Type", 'application/json; charset=utf-8');
    }


    /**
     * @OA\Post(
     *   path="/api/register",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     description="User register data",
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"name","email", "password", "image", "phone"},
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="password", type="string"),
     *         @OA\Property(property="image", type="file"),
     *         @OA\Property(property="phone", type="string"),
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\MediaType(
     *       mediaType="application/json"
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated"
     *   )
     * )
     */
    public function register(Request $request)
    {
        $recaptchaToken = $request->recaptchaToken;

        $secretKey = env('RECAPTCHA_SECRET_ID');

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $recaptchaToken,
        ]);

        $score = $response['score'];

        if ($response['success'] && $score >= 0.5) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'nullable|string|max:255',
                'image' => 'file',
            ]);

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

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'image' => $filename . '.webp',
            ]);

            $user->sendEmailVerificationNotification();

            $token = auth()->login($user);
            return response()->json(['token' => $token], Response::HTTP_CREATED);
        } else {
            return response()->json(['error' => 'Recaptcha not verified!'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}