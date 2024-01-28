<?php

namespace App\Repositories;

use App\Helpers\ImageHelper;
use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\CustomizationRepositoryInterface;
use App\Models\Customization;

define('CUSTOMIZATION_PATH', 'public/customization-files/');

class CustomizationRepository implements CustomizationRepositoryInterface
{
    private ImageHelper $imageHelper;

    public function __construct(
        ImageHelper $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
    }
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $customization = Customization::filter($filter);
        return $customization->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Customization
     * @throws \Exception
     */
    public function getById(int $id): Customization
    {
        $customization = Customization::find($id);
        if (!$customization) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $customization;
    }

    /**
     * @param array $data
     * @return Customization
     */
    public function create(array $data): Customization
    {
        if (isset($data['image'])) {
            $imagePath = $this->imageHelper->storagePutB64Image(CUSTOMIZATION_PATH, $data['image']);
            $data['image'] = asset(str_replace('public', 'storage', $imagePath));
        }

        return Customization::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Customization
     * @throws \Exception
     */
    public function update(int $id, array $data): Customization
    {
        $customization = $this->getById($id);

        if (array_key_exists('image', $data)) {

            if($data['image']) {
                if($customization->image) {
                    // Use parse_url() para obter o caminho da URL
                    $path = parse_url($customization->image, PHP_URL_PATH);

                    // Use pathinfo() para obter o nome do arquivo
                    $filename = pathinfo($path, PATHINFO_BASENAME);
                    $this->imageHelper->deleteStorageFile(CUSTOMIZATION_PATH . '/' .$filename);
                }
                $data['image'] = asset(str_replace('public', 'storage', $this->imageHelper->updateStorageFile(
                    CUSTOMIZATION_PATH,
                    $data,
                    $customization->getAttributeValue('image') ? $customization->getAttributeValue('image') : ""
                )));
            }
        }

        $customization->update($data);
        return $customization;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        $customization = Customization::find($id);
        if($customization->image) {
            // Use parse_url() para obter o caminho da URL
            $path = parse_url($customization->image, PHP_URL_PATH);

            // Use pathinfo() para obter o nome do arquivo
            $filename = pathinfo($path, PATHINFO_BASENAME);
            $this->imageHelper->deleteStorageFile(CUSTOMIZATION_PATH . '/' .$filename);
        }
        return $customization->delete();
    }

}
