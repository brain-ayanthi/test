<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $period] = $this->resolveRange($request);

        $query = Expense::with(['category:id,name,color', 'creator:id,name'])
            ->whereBetween('expense_date', [$from, $to]);

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->integer('category_id'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $selectedTotal = (clone $query)->sum('amount');
        $expenses = $query->latest('expense_date')->latest('id')->paginate(20)->withQueryString();
        $categories = ExpenseCategory::orderByDesc('is_active')->orderBy('name')->get();
        $activeCategories = $categories->where('is_active', true);

        $categorySummary = Expense::query()
            ->join('expense_categories as ec', 'ec.id', '=', 'expenses.expense_category_id')
            ->whereBetween('expenses.expense_date', [$from, $to])
            ->selectRaw('ec.id, ec.name, ec.color, COUNT(expenses.id) as entries, SUM(expenses.amount) as total')
            ->groupBy('ec.id', 'ec.name', 'ec.color')
            ->orderByDesc('total')->get();

        $todayTotal = Expense::whereDate('expense_date', today())->sum('amount');
        $monthTotal = Expense::whereBetween('expense_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->sum('amount');
        $yearTotal = Expense::whereBetween('expense_date', [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()])->sum('amount');
        $editExpense = $request->filled('edit') ? Expense::find($request->integer('edit')) : null;

        return view('expenses.index', compact(
            'expenses', 'categories', 'activeCategories', 'categorySummary',
            'todayTotal', 'monthTotal', 'yearTotal', 'selectedTotal',
            'from', 'to', 'period', 'editExpense'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateExpense($request);
        $data['created_by'] = auth()->id();
        Expense::create($data);
        $this->clearExpenseCaches();

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validateExpense($request));
        $this->clearExpenseCaches();

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        $this->clearExpenseCaches();
        return back()->with('success', 'Expense deleted.');
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:expense_categories,name',
            'description' => 'nullable|string|max:500',
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        $data['is_active'] = true;
        ExpenseCategory::create($data);
        return back()->with('success', 'Expense category added.');
    }

    public function updateCategory(Request $request, ExpenseCategory $expenseCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string|max:500',
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $expenseCategory->update($data);
        return back()->with('success', 'Expense category updated.');
    }

    public function destroyCategory(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->exists()) {
            return back()->with('error', 'This category has expenses and cannot be deleted. Deactivate it instead.');
        }
        $expenseCategory->delete();
        return back()->with('success', 'Expense category deleted.');
    }

    public function report(Request $request)
    {
        [$from, $to, $period] = $this->resolveRange($request, 'this_month');

        $daily = Expense::whereBetween('expense_date', [$from, $to])
            ->selectRaw('expense_date as date, COUNT(*) as entries, SUM(amount) as total')
            ->groupBy('expense_date')->orderBy('expense_date')->get();

        $categories = Expense::query()
            ->join('expense_categories as ec', 'ec.id', '=', 'expenses.expense_category_id')
            ->whereBetween('expenses.expense_date', [$from, $to])
            ->selectRaw('ec.name, ec.color, COUNT(expenses.id) as entries, SUM(expenses.amount) as total')
            ->groupBy('ec.id', 'ec.name', 'ec.color')->orderByDesc('total')->get();

        $expenses = Expense::with('category:id,name,color')
            ->whereBetween('expense_date', [$from, $to])
            ->latest('expense_date')->get();
        $total = $expenses->sum('amount');

        return view('expenses.report', compact('daily', 'categories', 'expenses', 'total', 'from', 'to', 'period'));
    }

    protected function validateExpense(Request $request): array
    {
        return $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999999999.99',
            'payment_method' => 'required|in:cash,bank,mfs,card,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
    }

    protected function resolveRange(Request $request, string $default = 'today'): array
    {
        if ($request->filled('from') && $request->filled('to')) {
            return [$request->date('from')->toDateString(), $request->date('to')->toDateString(), 'custom'];
        }

        $period = $request->input('period', $default);
        [$from, $to] = match ($period) {
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            '7days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };

        return [$from->toDateString(), $to->toDateString(), $period];
    }

    protected function clearExpenseCaches(): void
    {
        Cache::forget('dashboard_summary');
    }
}
