<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\PaymentMethodRepositoryInterface;
use App\Models\PaymentMethod;

class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $paymentMethod = PaymentMethod::filter($filter);
        return $paymentMethod->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return PaymentMethod
     * @throws \Exception
     */
    public function getById(int $id): PaymentMethod
    {
        $paymentMethod = PaymentMethod::find($id);
        if (!$paymentMethod) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $paymentMethod;
    }

    /**
     * @param array $data
     * @return PaymentMethod
     */
    public function create(array $data): PaymentMethod
    {
        // Verifique se $data['status'] é verdadeiro (true)
        if ($data['status']) {
            // Encontre todos os métodos de pagamento com status verdadeiro (true) para o usuário específico
            $existingMethods = PaymentMethod::where('user_id', $data['user_id'])
                ->where('status', true)
                ->get();

            // Itere pelos métodos de pagamento existentes e atualize o status para false
            foreach ($existingMethods as $existingMethod) {
                $existingMethod->update(['status' => false]);
            }
        }

        // Crie o novo método de pagamento com o status especificado
        return PaymentMethod::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return PaymentMethod
     * @throws \Exception
     */
    public function update(int $id, array $data): PaymentMethod
    {
        $paymentMethod = $this->getById($id);

        // Verifique se o status do método de pagamento a ser atualizado é true
        if ($data['status']) {
            // Encontre todos os métodos de pagamento com status true, excluindo o atual e para o usuário específico
            PaymentMethod::where('status', true)
                ->where('id', '<>', $paymentMethod->id)
                ->where('user_id', $paymentMethod->user_id)
                ->update(['status' => false]);
        }

        // Atualize o método de pagamento existente com os dados fornecidos
        $paymentMethod->update($data);

        return $paymentMethod;
    }


    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return PaymentMethod::destroy($id);
    }

}
