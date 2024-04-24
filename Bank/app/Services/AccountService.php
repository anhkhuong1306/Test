<?php

namespace App\Services;

use App\Common\DataConstant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountService
{
    /**
     * @var TransactionService
     */
    private $transactionService;

    public function __construct()
    {
        $this->transactionService = app()->make(TransactionService::class);
    }

    /**
     * Get list accounts
     */
    public function getListAccount($customerId, $orderBy = 'account_id', $direction = 'asc')
    {
        try {
            $listAccounts = DB::table('accounts')
                                ->select(['account_id', 'customer_id', 'account_number', 'balance'])
                                ->where('customer_id', $customerId)
                                ->where('deleted_at', null)
                                ->orderBy($orderBy, $direction)
                                ->get();
            return $listAccounts;
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }

    /**
     * Get detail account
     * @param int $accountId
     * @return Account $account;
     */
    public function getDetailAccount($accountId)
    {
        try {
            $account = DB::table('accounts')
                        ->select(['account_id', 'account_number', 'balance'])
                        ->where('account_id', $accountId)
                        ->where('deleted_at', null)
                        ->first();
            return $account;
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }

    /**
     * Create new account
     * @param array $data
     */
    public function createNewAccount($data)
    {
        try {
            DB::beginTransaction();
            $customerId = $data['customerId'];
            $balance = $data['balance'];
            $accountNumber = $data['accountNumber'];

            $accountInserted = DB::table('accounts')
                                    ->insert([
                                        'customer_id' => $customerId,
                                        'balance'     => $balance,
                                        'account_number'  => $accountNumber,
                                        'created_at'  => Carbon::now()->format('Y-m-d H:i:s'),
                                        'updated_at'  => Carbon::now()->format('Y-m-d H:i:s'),
                                    ]);

            DB::commit();
            return $accountInserted;
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }

    /**
     * Generate account number
     * @return string $number
     */
    public function generateBarcodeNumber() {
        $number = mt_rand(1000000000, 9999999999);
        if ($this->barcodeNumberExists($number)) {
            return $this->generateBarcodeNumber();
        }

        return $number;
    }

    /**
     * Checking account number exists
     * @param string $number
     */
    public function barcodeNumberExists($number) {
        return DB::table('accounts')->where('account_number', $number)->exists();
    }

    /**
     * Transfer money
     *
     * @param array $data
     * @return boolean true|false
     */
    public function transfer($data)
    {
        try{
            DB::beginTransaction();

            $transferAccountNumber = $data['accountTransfer'];
            $receiverAccountNumber = $data['accountReceiver'];
            $amount = $data['amount'];
            $balance = $data['balance'];

            if ($balance <= DataConstant::MIN_BALANCE) {
                Log::info('Balance is less than 0');
                return false;
            }

            if ($amount > DataConstant::MAX_AMOUNT_TRANSFER) {
                Log::info('Amount transfer is greater than the transfer limit');
                return false;
            }

            if ($amount > $balance) {
                Log::info('Amount transfer is greater than the balance.');
                return false;
            }

            $transferAccount = $this->getAccount($transferAccountNumber);
            $receiverAccount = $this->getAccount($receiverAccountNumber);

            if (empty($transferAccount)) {
                Log::info('Account transfer does not exists.');
                return false;
            }

            if (empty($receiverAccount)) {
                Log::info('Account receiver does not exists.');
                return false;
            }

            if ($transferAccount->balance !== $balance) {
                Log::info('The balance was updated in another session.', ['balance' => $balance]);
                return false;
            }

            $decreased = $this->decreaseBalance($transferAccountNumber, $amount);
            if (!$decreased) {
                DB::rollBack();
                Log::info('Decreasing the balance was not successful.');
                return false;
            }

            $increased = $this->increaseBalance($receiverAccountNumber, $amount);
            if (!$increased) {
                DB::rollBack();
                Log::info('Increasing the balance was not successful.');
                return false;
            }

            $transactionSaved = $this->transactionService->saveTransaction($transferAccountNumber, $receiverAccountNumber, $amount);
            if (empty($transactionSaved)) {
                DB::rollBack();
                Log::info('Creating transaction was not successful.');
                return false;
            }

            DB::commit();
            Log::info('Transferring money was successful.');
            return true;
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }

    /**
     * Get account transfer or receiver
     *
     * @param int $accountId
     * @return Account $account
     *
     */
    public function getAccount($accountNumber)
    {
        try {
            $account = DB::table('accounts')
                ->where('account_number', $accountNumber)
                ->where('deleted_at', null)
                ->first();
            return $account;
        } catch(\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }

    /**
     * Decrease the balance of account transfer
     *
     * @param int $accountId
     * @param double $amount
     * @return boolean true|false
     *
     */
    public function decreaseBalance($accountNumber, $amount)
    {
        try {
            $decreased = DB::table('accounts')
                                ->where('account_number', $accountNumber)
                                ->decrement('balance', $amount, [
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ]);
            return $decreased;
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }

    public function increaseBalance($accountNumber, $amount)
    {
        try {
            $increased = DB::table('accounts')
                                ->where('account_number', $accountNumber)
                                ->increment('balance', $amount, [
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ]);
            return $increased;
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }
}
