<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Services\CashService;
use Illuminate\Http\Request;

class CashController extends Controller
{
    public function __construct(protected CashService $cash) {}

    public function index()
    {
        $accounts = CashAccount::where('is_active', true)->orderBy('id')->get(['id','name','type','balance','color','icon']);
        $transactions = CashTransaction::with('account:id,name')
            ->select(['id','cash_account_id','type','amount','note','transaction_date','created_at'])
            ->latest('id')
            ->paginate(30);
        return view('cash.index', compact('accounts', 'transactions'));
    }

    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:cash,bank,mfs',
            'account_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $data['balance'] = $data['opening_balance'] ?? 0;
        CashAccount::create($data);
        return back()->with('success', 'Account created.');
    }

    public function topUp(Request $request)
    {
        $data = $request->validate([
            'account_id' => 'required|exists:cash_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
        ]);

        $this->cash->topUp($data['account_id'], $data['amount'], $data['note'] ?? 'Top-up');
        return back()->with('success', 'Balance topped up.');
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'from_account_id' => 'required|exists:cash_accounts,id',
            'to_account_id' => 'required|exists:cash_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string',
            'note' => 'nullable|string',
            'transfer_date' => 'nullable|date',
        ]);

        $this->cash->transfer(
            $data['from_account_id'],
            $data['to_account_id'],
            $data['amount'],
            $data
        );

        return back()->with('success', 'Transfer completed.');
    }
}
