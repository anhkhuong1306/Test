<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    /**
     * Get list customer
     * @param string $search
     * @param string $orderBy (default = 'id')
     * @param string $direction (default = 'asc')
     * @return array listTransaction
     *
     */
    public function getListCustomer($search = '', $orderBy = 'id', $direction = 'asc')
    {
        try {
            $listTransaction = DB::table('customers')
                ->when(
                    $search,
                    function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    }
                )
                ->where('deleted_at', null)
                ->orderBy($orderBy, $direction)
                ->paginate();

            return $listTransaction;
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }

    /**
     * Create a new customer.
     *
     * @param array $data
     * @return boolean true|false
     *
     */
    public function insertCustomer($data)
    {
        try {
            DB::beginTransaction();
            $firstName = $data['firstName'];
            $lastName = $data['lastName'];
            $phone = $data['phone'];
            $email = $data['email'];

            $customerInserted = DB::table('customers')
                ->insert([
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'phone'      => $phone,
                    'email'      => $email,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
            DB::commit();
            return $customerInserted;
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("Exception:", [$ex->getTrace()]);
            throw $ex;
        }
    }
}
