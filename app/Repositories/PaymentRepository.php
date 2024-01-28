<?php

namespace App\Repositories;

use App\Http\Filters\RequestFilter;
use Illuminate\Contracts\Pagination\Paginator;
use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Payment;

class PaymentRepository implements PaymentRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @return Paginator
     */
    public function getAll(int $limit, int $page, RequestFilter $filter): Paginator
    {
        $payment = Payment::filter($filter);
        return $payment->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param int $id
     * @return Payment
     * @throws \Exception
     */
    public function getById(int $id): Payment
    {
        $payment = Payment::with(['campaign', 'user', 'collaborator'])->find($id);
        if (!$payment) {
            throw new \Exception(__('Não encontrado.'), 404);
        }
        return $payment;
    }

    /**
     * @param int $id
     * @return Payment
     * @throws \Exception
     */
    public function getByTransactionId(string $transaction_id): Payment
    {
        $payment = Payment::with(['campaign', 'campaign.raffle', 'user', 'collaborator', 'campaign.user', 'campaign.user.paymentMethods', 'campaign.user.socialMedias', 'campaign.user.customizations'])
            ->where('transaction_id', $transaction_id)
            ->first();

        if (!$payment) {
            throw new \Exception(__('Não encontrado.'), 404);
        }

        if($payment->collaborator) {
            $numbers = explode(',', $payment->collaborator->numbers);

            $formattedNumbers = array_map(function ($number) {
                return '[' . $number . ']';
            }, $numbers);

            $payment->collaborator->numbers = implode(' ', $formattedNumbers);
        }

        return $payment;
    }

    /**
     * @param array $data
     * @return Payment
     */
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return Payment
     * @throws \Exception
     */
    public function update(int $id, array $data): Payment
    {
        $payment = $this->getById($id);
        $payment->update($data);
        return $payment;
    }

    /**
     * @param int $id
     * @param array $data
     * @return Payment
     * @throws \Exception
     */
    public function updateByTransaction(int $transaction_id, array $data): Payment
    {
        $payment = $this->getByTransactionId($transaction_id);
        $payment->update($data);
        return $payment;
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return Payment::destroy($id);
    }

    public function getByCollaboratorId(int $collaborator_id, int $limit, int $page, RequestFilter $filter): Paginator
    {
        $payments = Payment::where('collaborator_id', $collaborator_id)->filter($filter);

        return $payments->paginate($limit, ['*'], 'page', $page);
    }

    public function getByCampaignId(int $campaign_id, int $limit, int $page, RequestFilter $filter): Paginator
    {
        $payments = Payment::where('campaign_id', $campaign_id)->filter($filter);

        return $payments->paginate($limit, ['*'], 'page', $page);
    }

    public function getByUserId(int $user_id, int $limit, int $page, RequestFilter $filter): Paginator
    {
        $payments = Payment::where('user_id', $user_id)->filter($filter);

        return $payments->paginate($limit, ['*'], 'page', $page);
    }

    public function getByCampaignAndUserId(int $campaign_id, int $user_id, int $limit, int $page, RequestFilter $filter): Paginator
    {
        $payments = Payment::where('user_id', $user_id)
            ->where('campaign_id', $campaign_id)
            ->filter($filter);

        return $payments->paginate($limit, ['*'], 'page', $page);
    }

    public function getByCampaignAndCollaboratorId(int $campaign_id, int $collaborator_id, int $limit, int $page, RequestFilter $filter): Paginator
    {
        $payments = Payment::where('collaborator_id', $collaborator_id)
            ->where('campaign_id', $campaign_id)
            ->filter($filter);

        return $payments->paginate($limit, ['*'], 'page', $page);
    }
}
