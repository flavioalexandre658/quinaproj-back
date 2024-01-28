<?php

namespace App\Http\Controllers;

use App\Interfaces\RoleRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Firebase\JWT\JWT;
class AuthController extends Controller
{
    /** @var UserRepositoryInterface  */
    protected UserRepositoryInterface $userRepository;
    protected RoleRepositoryInterface $roleRepository;
    public function __construct(UserRepositoryInterface $userRepository,
                                RoleRepositoryInterface $roleRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {


            $user = $this->userRepository->getByEmail($request['email']);

            if (!$user || !Hash::check($request['password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => __('Dados de login inválidos.')
                ], 401);
            }

            if (Auth::check() && $user->delete_account == 1) {
                return response()->json([
                    'status' => false,
                    'message' => __('Dados de login inválidos.')
                ], 401);
            }

            $token = JWTAuth::fromUser($user);

            $role = null;

            if ($user->role_id) {
                $role = $this->roleRepository->getById($user->role_id);
            }

            return response()->json([
                'status' => true,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user_id' => $user->uuid,
                'role' => $role ? $role->name : $role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('Erro ao processar a solicitação.')
            ], 500);
        }
    }

    public function renewToken(Request $request): JsonResponse
    {

        /** @var User $user */
        $user = $this->userRepository->getByUuid($request['user_id']);

        $token = JWTAuth::fromUser($user);

        $role = null;

        if($user->role_id) {
            $role = $this->roleRepository->getById($user->role_id);
        }

        return response()->json([
            'status' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_id' => $user->uuid,
            'role' => $role ? $role->name : $role
        ]);
    }
}
