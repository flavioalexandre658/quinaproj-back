<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Filters\RequestFilter;
use App\Interfaces\SocialMediaRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    private SocialMediaRepositoryInterface $SocialMediaRepository;
    private UserRepositoryInterface $UserRepository;

    public function __construct(SocialMediaRepositoryInterface $SocialMediaRepository,
                                UserRepositoryInterface $UserRepository)
    {
        $this->SocialMediaRepository = $SocialMediaRepository;
        $this->UserRepository = $UserRepository;
    }

    public function getAll(RequestFilter $filter): Paginator
    {
        $page = request()->get('page') ?: 1;
        $limit = request()->get('limit') ?: 100;
        return $this->SocialMediaRepository->getAll($limit, $page, $filter);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        try {
            $socialMedia = $this->SocialMediaRepository->getById($id);

            return response()->json([$socialMedia]);
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

            $socialMedia = $this->UserRepository->getSocialMediaByUserUuid($uuid);

            return response()->json([$socialMedia]);
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
                'name'        => 'string|max:125|nullable',
                'url'        => 'string|max:255|nullable',
                'user_id'  => 'required|int|exists:users,id'
            ]);

            $socialMedia = $this->SocialMediaRepository->create($validatedData);

            return response()->json([
                'message' => __('Created SocialMedia successfully'),
                'socialMedia' => $socialMedia
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

            $validatedData = $request->validate([
                'name'        => 'string|max:125|nullable',
                'url'        => 'string|max:255|nullable',
                'user_id'  => 'int|exists:users,id'
            ]);

            $socialMedia = $this->SocialMediaRepository->update($id, $validatedData);

            return response()->json([
                'message' => __('Updated SocialMedia successfully'),
                'socialMedia' => $socialMedia
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
            if ($this->SocialMediaRepository->getById($id)) {
                $this->SocialMediaRepository->delete($id);
            }

            return response()->json([
                'message' => __('Deleted SocialMedia successfully')
            ], 202);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 400;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
