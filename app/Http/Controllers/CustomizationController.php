<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\CustomizationRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomizationController extends Controller
{
    private CustomizationRepositoryInterface $CustomizationRepository;
    private UserRepositoryInterface $UserRepository;

    public function __construct(CustomizationRepositoryInterface $CustomizationRepository,
                                UserRepositoryInterface $UserRepository)
    {
        $this->CustomizationRepository = $CustomizationRepository;
        $this->UserRepository = $UserRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->CustomizationRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $customization = $this->CustomizationRepository->getById($id);

            return response()->json([$customization]);
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

            $customization = $this->UserRepository->getCustomizationByUserUuid($uuid);

            return response()->json([$customization]);
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
            $validatedData = $request->validate([
                'image'      => ['b64image', 'nullable'],
                'pixel_meta'        => 'string|max:255|nullable',
                'tag_google'        => 'string|max:255|nullable',
                'custom_domain'        => 'string|max:255|nullable',
                'user_id'  => 'required|int|exists:users,id'
            ]);

            $customization = $this->CustomizationRepository->create($validatedData);

            return response()->json([
                'message' => __('Created Customization successfully'),
                'customization' => $customization
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
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {

            $rules = [
                //'image'      => ['b64image', 'nullable'],
                'pixel_meta'        => 'string|max:255|nullable',
                'tag_google'        => 'string|max:255|nullable',
                'custom_domain'        => 'string|max:255|nullable',
                'user_id'  => 'int|exists:users,id'
            ];

            if (filter_var($request->input('image'), FILTER_VALIDATE_URL)) {
                // Remova 'image' das regras de validação
                unset($rules['image']);
            } else {
                // Adicione a regra 'b64image' para validar a imagem codificada em base64
                $rules['image'] = ['b64image', 'nullable'];
            }

            $validatedData = $request->validate($rules);

            $customization = $this->CustomizationRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated Customization successfully'),
                'customization' => $customization
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
            if ($this->CustomizationRepository->getById($id)) {
                $this->CustomizationRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted Customization successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
