<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    /**
     * Get list transaction of an account_id
     *
     * @param string $account_id
     * @param string $orderBy
     * @param string $direction
     * @return array transactions
     *
     */
    public function getListTransaction($accountNumber, $orderBy = 'account_transfer', $direction = 'asc')
    {
        try {
            $listTransactions = DB::table('transactions')
                                    ->select(['transaction_id', 'account_transfer', 'account_receiver', 'amount', 'date_transfer'])
                                    ->where('account_transfer', $accountNumber)
                                    ->where('deleted_at', null)
                                    ->orderBy($orderBy, $direction)
                                    ->get();
            return $listTransactions;
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }

    /**
     * Get transaction detail
     *
     * @param $transactionId
     * @return Transaction $transaction
     *
     */
    public function getDetailTransaction($transactionId)
    {
        try {
            $transaction = DB::table('transactions')
                                ->select([
                                    'transactions.transaction_id',
                                    'transactions.account_transfer',
                                    'transactions.account_receiver',
                                    'transactions.amount',
                                    'transactions.date_transfer',
                                    'customers.first_name',
                                    'customers.last_name',
                                ])
                                ->join('accounts', 'accounts.account_id', '=', 'transactions.account_receiver')
                                ->join('customers', 'customers.id', '=', 'accounts.customer_id')
                                ->where('transaction_id', $transactionId)
                                ->where('deleted_at', null)
                                ->first();
            return $transaction;
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }

    /**
     * Save transaction after each transfer.
     *
     * @param string $transferAccountId
     * @param string $receiverAccountId
     * @param double $amount
     * @return Transaction $transactionSaved
     *
     */
    public function saveTransaction($transferAccountId, $receiverAccountId, $amount)
    {
        try {
            DB::beginTransaction();
            $transactionSaved = DB::table('transactions')
                ->insert([
                    'account_transfer' => $transferAccountId,
                    'account_receiver' => $receiverAccountId,
                    'amount'           => $amount,
                    'date_transfer'    => Carbon::now()->format('Y-m-d H:i:s'),
                    'created_at'       => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at'       => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
            DB::commit();
            return $transactionSaved;
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }
}
