<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /** @var TransactionService */
    private $transactionService;

    public function __construct()
    {
        $this->transactionService = app()->make(TransactionService::class);
    }

    /**
     * Display list transactions
     */
    public function index(Request $request)
    {
        try {
            $accountNumber = $request->input('accountNumber');
            $orderBy = $request->has('orderBy') ? $request->input('orderBy') : 'account_transfer';
            $direction = $request->has('direction') ? $request->input('direction') : 'asc';
            $listTransactions = $this->transactionService->getListTransaction($accountNumber, $orderBy, $direction);

            if (!empty($listTransactions)) {
                return response()->json(['message' => 'Get list transactions successfully.', 'statusCode' => 200, 'data' => $listTransactions]);
            }

            return response()->json(['message' => 'Get list transactions failed.']);
        } catch (\Exception $ex) {
            Log::error("Exception:", [$ex->getTrace()]);
            return response()->json(['message' => 'Server error.'], 500);
        }
    }
}
