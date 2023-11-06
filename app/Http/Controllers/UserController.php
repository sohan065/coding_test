<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // create new user
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|min:3|max:100',
            'account_type' => 'bail|required|string|in:Individual,Business',
            'balance' => 'bail|required|numeric',
            'email' => 'bail|required|email|unique:users',
            'password' => 'bail|required|string',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }
        $validated = $request->only(['name', 'account_type', 'balance', 'email', 'password']);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'account_type' => $validated['account_type'],
                'balance' => $validated['balance'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'remember_token' => Str::random(10),

            ]);
            return response()->json(['user' => $user], 201);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
    // user login 
    public function userLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'bail|required|email|exists:users,email',
            'password' => 'bail|required|string',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }
        $validated = $request->only(['email', 'password']);

        $user = User::where('email', $validated['email'])->first();

        if ($user && Hash::check($validated['password'], $user['password'])) {
            return response()->json(['login' => 'success'], 200);
        }
        return response()->json(['login' => 'fail'], 406);
    }
    // show all transaction and balance
    public function showAllTransactionAndBalance(Request $request)
    {
        $transactions = Transaction::with('user')->get();

        return response()->json(['transactions' => $transactions], 200);
    }

    // show all deposit transactions
    public function showAllDepositTransaction(Request $request)
    {
        $depositTransactions = Transaction::where('transaction_type', 'deposit')->get();

        return response()->json(['transactions' => $depositTransactions], 200);
    }
    // balance  deposit 
    public function depositBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'bail|required|exists:users,id',
            'amount' => 'bail|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }
        $validated = $request->only(['id', 'amount']);
        $user = User::where('id', $validated['id'])->first();
        try {
            User::where('id', $validated['id'])->update([
                'balance' => $validated['amount'] + $user['balance'],
            ]);
            Transaction::create([
                'user_id' => $user['id'],
                'transaction_type' => 'deposit',
                'amount' => $validated['amount'],
                'fee' => 0,
                'date' => Carbon::now(),
            ]);
            return response()->json(['msg' => 'updated'], 202);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
    // show all withdrawal transaction
    public function showAllWithdrawalTransaction(Request $request)
    {
        $withdrawalTransactions = Transaction::where('transaction_type', 'withdraw')->get();

        return response()->json(['transactions' => $withdrawalTransactions], 200);
    }

    // balance  withdraw 
    public function withdrawBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'bail|required|exists:users,id',
            'amount' => 'bail|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }
        $validated = $request->only(['id', 'amount']);

        $userId = $validated['id'];

        $user = User::with(['transactions' => function ($query) use ($userId) {
            // Get transactions within the last month
            $query->where('user_id', $userId)
                ->where('created_at', '>=', Carbon::now()->subMonth());
        }])->where('id', $userId)->first();

        $date = Carbon::now();

        // monthly  withdraw amount check
        $totalWithdrawalAmount = 0;
        foreach ($user['transactions'] as $transaction) {
            $totalWithdrawalAmount += $transaction['amount'];
        }
        //  fee calculation
        if ($totalWithdrawalAmount < 5000) {
            $chargeFreeAmount = (5000 - $totalWithdrawalAmount);
            // Check if today is Friday 
            if ($date->isFriday()) {
                if ($user['account_type'] == 'Individual') {
                    $fee = (0.015 / 100) * ($validated['amount'] -  $validated['amount'] > $chargeFreeAmount ? $chargeFreeAmount : $validated['amount']);
                } else if ($user['account_type'] == 'Business') {
                    $fee = (0.025 / 100) * ($validated['amount'] - $validated['amount'] > $chargeFreeAmount ? $chargeFreeAmount : $validated['amount']);
                }
            } else {
                if ($user['account_type'] == 'Individual') {
                    $fee = (0.015 / 100) * ($validated['amount'] - $chargeFreeAmount >= 1000 ? 1000 : $chargeFreeAmount);
                } else if ($user['account_type'] == 'Business') {
                    $fee = (0.025 / 100) * ($validated['amount'] - $chargeFreeAmount >= 1000 ? 1000 : $chargeFreeAmount);
                }
            }
        } else {

            // fee calculation
            if ($user['account_type'] == 'Individual') {
                $fee = (0.015 / 100) * ($validated['amount']);
            } else if ($user['account_type'] == 'Business') {
                // above 50k withdrawal
                if ($totalWithdrawalAmount > 50000) {
                    $fee = (0.015 / 100) * ($validated['amount']);
                }
                $fee = (0.025 / 100) * ($validated['amount']);
            }
        }



        // balance check for withdraw
        if ($validated['amount'] <= $user['balance'] - $fee) {
            try {
                User::where('id', $validated['id'])->update([
                    'balance' =>   $user['balance'] - $validated['amount'],
                ]);
                Transaction::create([
                    'user_id' => $user['id'],
                    'transaction_type' => 'withdraw',
                    'amount' => $validated['amount'],
                    'fee' => $fee,
                    'date' => $date,
                ]);
                return response()->json(['msg' => 'updated'], 202);
            } catch (Exception $e) {
                Log::error($e);
                return $e;
            }
        }
        return response()->json(['msg' => 'insufficient balance'], 406);
    }
}