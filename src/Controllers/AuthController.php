<?php


use App\Http\Requests\FenoxApi\User\StoreUserRequest;
use App\Http\Requests\FenoxApi\User\UpdateUserRequest;
use App\Models\User;
use Fenox\ApiBase\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    public function register(StoreUserRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $validatedData['password'] = Hash::make($validatedData['password']); // Hash the password

        $user = User::create($validatedData);

        // Return success response using ResponseHelper
        return ResponseHelper::success($user, 'User registered successfully', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate token for user
        $token = $user->createToken('API Token')->plainTextToken;

        return ResponseHelper::success(['token' => $token], 'Login successful', 200);
    }

    public function logout(Request $request): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return ResponseHelper::success([], 'Successfully logged out', 200);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());
        return ResponseHelper::success($user, 'User updated successfully');
    }
}
