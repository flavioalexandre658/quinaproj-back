<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\PaymentMethodRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    private PaymentMethodRepositoryInterface $paymentMethodRepository;
    private UserRepositoryInterface $UserRepository;
    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository,
                                UserRepositoryInterface $UserRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->UserRepository = $UserRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->paymentMethodRepository->getAll($limit, $page, $filter);
    }

    public function get(int $id): JsonResponse
    {
        try {
            $paymentMethod = $this->paymentMethodRepository->getById($id);

            return response()->json([$paymentMethod]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getByUserUuid(string $uuid): JsonResponse
    {
        try {

            $paymentMethod = $this->UserRepository->getPaymentMethodByUserUuid($uuid);

            return response()->json([$paymentMethod]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name_method' => 'required|string',
                'name_user' => 'string|nullable',
                'type_key' => 'string|nullable',
                'key' => 'string|nullable',
                'api_token' => 'string|nullable',
                'refresh_token' => 'string|nullable',
                'expire_in' => 'string|nullable',
                'user_connected' => 'string|nullable',
                'email_connected' => 'string|nullable',
                'status' => 'boolean',
                'user_id'  => 'required|int|exists:users,id'
            ]);

            $paymentMethod = $this->paymentMethodRepository->create($validatedData);

            return response()->json([
                'message' => __('Created Payment Method successfully'),
                'payment_method' => $paymentMethod
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name_method' => 'string',
                'name_user' => 'string|nullable',
                'type_key' => 'string|nullable',
                'key' => 'string|nullable',
                'api_token' => 'string|nullable',
                'refresh_token' => 'string|nullable',
                'expire_in' => 'string|nullable',
                'user_connected' => 'string|nullable',
                'email_connected' => 'string|nullable',
                'status' => 'boolean',
                'user_id'  => 'int|exists:users,id'
            ]);

            $paymentMethod = $this->paymentMethodRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Payment Method successfully'),
                'payment_method' => $paymentMethod
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            if ($this->paymentMethodRepository->getById($id)) {
                $this->paymentMethodRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted Payment Method successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
