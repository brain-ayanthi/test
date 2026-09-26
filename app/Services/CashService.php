<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\CashTransfer;
use Illuminate\Support\Facades\DB;

/**
 * Multi-account cash management (Store Cash, Bank, MFS).
 * Updates balances automatically on every transaction.
 */
class CashService
{
    public function recordIncome(
        int $accountId,
        float $amount,
        $source = null,
        string $note = '',
        ?string $reference = null,
        ?\DateTimeInterface $date = null
    ): CashTransaction {
        return DB::transaction(function () use ($accountId, $amount, $source, $note, $reference, $date) {
            $account = CashAccount::lockForUpdate()->findOrFail($accountId);
            $account->increment('balance', $amount);

            return CashTransaction::create([
                'cash_account_id' => $account->id,
                'type' => 'income',
                'amount' => $amount,
                'balance_after' => $account->balance,
                'source_type' => $source ? get_class($source) : null,
                'source_id' => $source?->id,
                'reference' => $reference,
                'note' => $note,
                'transaction_date' => $date ?? now(),
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function recordExpense(
        int $accountId,
        float $amount,
        $source = null,
        string $note = '',
        ?string $reference = null,
        ?\DateTimeInterface $date = null
    ): CashTransaction {
        return DB::transaction(function () use ($accountId, $amount, $source, $note, $reference, $date) {
            $account = CashAccount::lockForUpdate()->findOrFail($accountId);
            if ($account->balance < $amount) {
                throw new \Exception("Insufficient balance in {$account->name}");
            }
            $account->decrement('balance', $amount);

            return CashTransaction::create([
                'cash_account_id' => $account->id,
                'type' => 'expense',
                'amount' => $amount,
                'balance_after' => $account->balance,
                'source_type' => $source ? get_class($source) : null,
                'source_id' => $source?->id,
                'reference' => $reference,
                'note' => $note,
                'transaction_date' => $date ?? now(),
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function transfer(int $fromAccountId, int $toAccountId, float $amount, array $data = []): CashTransfer
    {
        return DB::transaction(function () use ($fromAccountId, $toAccountId, $amount, $data) {
            $from = CashAccount::lockForUpdate()->findOrFail($fromAccountId);
            $to = CashAccount::lockForUpdate()->findOrFail($toAccountId);

            if ($from->balance < $amount) {
                throw new \Exception("Insufficient balance in {$from->name}");
            }

            $from->decrement('balance', $amount);
            $to->increment('balance', $amount);

            $transfer = CashTransfer::create([
                'from_account_id' => $fromAccountId,
                'to_account_id' => $toAccountId,
                'amount' => $amount,
                'transfer_date' => $data['transfer_date'] ?? now(),
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            CashTransaction::create([
                'cash_account_id' => $fromAccountId,
                'type' => 'transfer_out',
                'amount' => $amount,
                'balance_after' => $from->balance,
                'reference' => $transfer->reference,
                'note' => "Transfer to {$to->name}",
                'transaction_date' => $transfer->transfer_date,
                'created_by' => auth()->id(),
            ]);

            CashTransaction::create([
                'cash_account_id' => $toAccountId,
                'type' => 'transfer_in',
                'amount' => $amount,
                'balance_after' => $to->balance,
                'reference' => $transfer->reference,
                'note' => "Transfer from {$from->name}",
                'transaction_date' => $transfer->transfer_date,
                'created_by' => auth()->id(),
            ]);

            return $transfer;
        });
    }

    public function topUp(int $accountId, float $amount, string $note = 'Balance top-up'): CashTransaction
    {
        return $this->recordIncome($accountId, $amount, null, $note);
    }
}
