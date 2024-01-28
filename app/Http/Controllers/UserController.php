<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\UserRepositoryInterface;
use App\Mail\ResetPassword;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use App\Mail\AccountCreated;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    private UserRepositoryInterface $UserRepository;

    public function __construct(UserRepositoryInterface $UserRepository)
    {
        $this->UserRepository = $UserRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;

        $start_date = request()->get('start_date') ?: null;
        $end_date = request()->get('end_date') ?: null;

        return $this->UserRepository->getAll($limit, $page, $filter, $start_date, $end_date);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $user = $this->UserRepository->getById($id);

            return response()->json([$user]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getByUuid(string $uuid): JsonResponse
    {
        try {
            $user = $this->UserRepository->getByUuid($uuid);

            return response()->json([$user]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function active(string $token): JsonResponse
    {
        try {

            $response = $this->UserRepository->activeAccount($token);

            return response()->json($response, 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function resetPassword(Request $request, string $uuid): JsonResponse
    {
        try {

            $rules = [
                'password' => Password::min(8)->mixedCase()->numbers(),
            ];

            $validatedData = $request->validate($rules);

            $user = $this->UserRepository->resetPassword($uuid, $validatedData);

            return response()->json([
                'message' => __('Password Reset Successfuly!'),
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {

        try {
            $password = ['required', Password::min(8)->mixedCase()->numbers()];
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'nickname' => 'string|max:255|nullable',
                'image'    => ['b64image', 'nullable'],
                'email' => 'required|string|max:255|unique:users,email',
                'phone' => 'required|string|max:125',
                'password' => $password,
                'delete_account' => 'boolean',
                'text_delete_account' => 'string|max:255|nullable',
                'payment_id'  => 'int|exists:payments,id|nullable',
            ]);

            $user = $this->UserRepository->create($validatedData);

            // Crie um token de ativação (pode ser um hash único)
            $token = md5(uniqid());

            // Salve o token no banco de dados associado ao usuário
            $user->activation_token = $token;
            $user->save();

            //Mail::to($user['email'])->send(new AccountCreated($token));

            $token = JWTAuth::fromUser($user);
            
            return response()->json([
                'message' => __('Created User successfully'),
                'user' => $user,
                'access_token' => $token,
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function sendMailActivation($email, $token): JsonResponse
    {
        Mail::to($email)->send(new AccountCreated($token));

        return response()->json([
            'status' => true,
        ], 201);
    }

    public function sendMailResetPassword($email): JsonResponse
    {
        $user = $this->UserRepository->getByEmail($email);
        Mail::to($email)->send(new ResetPassword($user->uuid));

        return response()->json([
            'status' => true,
        ], 201);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {

            $rules = [
                'name' => 'string|max:255',
                'nickname' => 'string|max:255|nullable',
                //'image'    => ['b64image', 'nullable'],
                'email' => 'string|max:255',
                'phone' => 'string|max:125',
                'password' => Password::min(8)->mixedCase()->numbers(),
                'delete_account' => 'boolean',
                'text_delete_account' => 'string|max:255|nullable',
                'payment_id'  => 'int|exists:payments,id|nullable',
                'role_id'  => 'int|exists:roles,id|nullable',
            ];

            if (filter_var($request->input('image'), FILTER_VALIDATE_URL)) {
                // Remova 'image' das regras de validação
                unset($rules['image']);
            } else {
                // Adicione a regra 'b64image' para validar a imagem codificada em base64
                $rules['image'] = ['b64image', 'nullable'];
            }

            $validatedData = $request->validate($rules);

            $user = $this->UserRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated User successfully'),
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
    public function setDiscount(Request $request, int $id): JsonResponse
    {
        try {

            $validatedData = $request->validate([
                'discount'  => ['numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            ]);

            $user = $this->UserRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Discount added with success.'),
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        try {
            if ($this->UserRepository->getById($id)) {
                $this->UserRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted User successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
