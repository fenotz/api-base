<?php
namespace App\Http\Controllers\FenoxApiControllers;

use App\Http\Requests\FenoxApiRequests\User\LoginRequest;
use App\Http\Requests\FenoxApiRequests\User\StoreUserRequest;
use App\Http\Requests\FenoxApiRequests\User\UpdateUserRequest;
use App\Models\User;
use Fenox\ApiBase\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function register(StoreUserRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $validatedData['password'] = Hash::make($validatedData['password']); // Hash the password

        $user = User::create($validatedData);

        // Return success response using ResponseHelper
        return ResponseHelper::success($user, 'User registered successfully', 201);
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $user = User::where('email', $validatedData["email"])->first();

        if (!$user || !Hash::check($validatedData["password"], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate token for user
        $token = $user->createToken('API Token')->plainTextToken;

        return ResponseHelper::success(['token' => $token], 'Login successful', 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return ResponseHelper::success([], 'Successfully logged out', 200);
    }

    /**
     * @param UpdateUserRequest $request
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {

        //dd($request);
        $user = Auth::user();
        $user->update($request->validated());
        return ResponseHelper::success($user, 'User updated successfully');
    }
}
