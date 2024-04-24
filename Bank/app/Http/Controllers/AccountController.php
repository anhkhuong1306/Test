<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    /** @var AccountService */
    private $accountService;

    public function __construct()
    {
        $this->accountService = app()->make(AccountService::class);
    }

    /**
     * Display list account by customer.
     * @param Request $request
     * @return json
     *
     */
    public function index(Request $request)
    {
        try {
            $customerId = $request->input('customerId');
            $orderBy = $request->has('orderBy') ? $request->input('orderBy'): 'account_id';
            $direction = $request->has('direction') ? $request->input('direction') : 'asc';
            $listAccounts = $this->accountService->getListAccount($customerId, $orderBy, $direction);

            if (!empty($listAccounts)) {
                return response()->json(['message' => 'Get list account by customer successfully.', 'statusCode' => 200, 'data' => $listAccounts]);
            }

            return response()->json(['message' => 'There are no accounts.', 'data' => []]);
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            return response()->json(['message' => 'Server error.'], 500);
        }
    }

    /**
     * Get detail account.
     * @param Request $request
     * @return Account $account.
     */
    public function detail(Request $request)
    {
        try {
            $accountId = $request->input('accountId');
            $account = $this->accountService->getDetailAccount($accountId);
            if (!empty($account)) {
                return response()->json(['message' => 'Get detail account successfully.', 'statusCode' => 200, 'data' => $account]);
            }
            return response()->json(['message' => 'There is no account.']);
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            return response()->json(['message' => 'Server error.'], 500);
        }
    }

    /**
     * Transfer money
     * @param Request $request
     * @return json
     */
    public function transfer(Request $request)
    {
        try {
            $data = $request->input();
            $result = $this->accountService->transfer($data);
            if ($result) {
                return response()->json(['message' => 'Transfer money successfully.', 'statusCode' => 200]);
            }
            return response()->json(['message' => 'Transfer monery failed', 'statusCode' => 204]);
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            return response()->json(['message' => 'Server error.'], 500);
        }
    }

    /**
     * Generate account number
     * @return json
     */
    public function generateAccountNumber()
    {
        try {
            $accountNumber = $this->accountService->generateBarcodeNumber();
            return response()->json(['message' => 'Get account number success', 'statusCode' => 200, 'data' => $accountNumber]);
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            return response()->json(['message' => 'Server error.'], 500);
        }
    }

    /**
     * Create new account
     * @param AccountRequest $request
     * @return json
     *
     */
    public function insert(AccountRequest $request)
    {
        try {
            $data = $request->input();
            $result = $this->accountService->createNewAccount($data);
            if ($result) {
                return response()->json(['message' => 'Create account successfully.', 'statusCode' => 201]);
            }
            return response()->json(['message' => 'Create account failed', 'statusCode' => 204]);
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            return response()->json(['message' => 'Server error.'], 500);
        }
    }
}
