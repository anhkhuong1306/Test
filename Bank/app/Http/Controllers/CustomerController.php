<?php

namespace App\Http\Controllers;

use App\Common\DataConstant;
use App\Http\Requests\CustomerRequest;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /** @var CustomerService */
    private $customerService;

    public function __construct()
    {
        $this->customerService = app()->make(CustomerService::class);
    }

    /**
     * Insert new customer
     * @param Request $request
     * @return json
     */
    public function insert(CustomerRequest $request)
    {
        try {
            $data = $request->input();
            $result = $this->customerService->insertCustomer($data);
            if ($result) {
                return response()->json(['message' => 'Create customer successfully.', 'statusCode' => 201], 201);
            }
            return response()->json(['message' => 'Create customer failed', 'statusCode' => 204], 204);
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            return response()->json(['message' => 'Server error.'], 500);
        }
    }

    /**
     * List customers
     * @return json
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $orderBy = $request->has('orderBy') ? $request->input('orderBy') : 'customer_id' ;
            $direction = $request->has('direction') ? $request->input('direction') : 'asc';
            $listCustomer = $this->customerService->getListCustomer($search, $orderBy, $direction);
            if (!empty($listCustomer)) {
                return response()->json(['message' => 'Get list customers successfully.', 'statusCode' => 200, 'data' => $listCustomer]);
            }
            return response()->json(['message' => 'There are no customers.', 'data' => []]);
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            return response()->json(['message' => 'Server error.'], 500);
        }
    }
}
