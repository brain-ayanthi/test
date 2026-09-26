<?php $__env->startSection('title', 'Profit & Loss'); ?>
<?php $__env->startSection('page-title', 'Profit & Loss Statement'); ?>

<?php $__env->startSection('content'); ?>
<form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">From</label>
        <input type="date" name="from" value="<?php echo e(old('from', request('from', $from))); ?>" class="border rounded-lg p-2">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">To</label>
        <input type="date" name="to" value="<?php echo e(old('to', request('to', $to))); ?>" class="border rounded-lg p-2">
    </div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">Generate</button>
    <a href="<?php echo e(route('reports.index')); ?>" class="text-blue-600 text-sm self-center">Back to Dashboard</a>
</form>

<?php
    $rev = $pnl['revenue'];
    $cost = $pnl['costs'];
    $profit = $pnl['profit'];
    $totalRevenue = $rev['sales'] + $rev['rx_medicine'] + $rev['doctor_fee'] + $rev['radiology'];
    $totalCogs = $cost['sales_cogs'] + $cost['rx_medicine_cogs'];
    $expenseTotal = $pnl['expenses']['total'] ?? 0;
    $netProfit = $pnl['net_profit'];
?>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-4xl">
    <h3 class="font-bold text-lg mb-4">Profit &amp; Loss
        <span class="text-sm text-gray-500 font-normal">(<?php echo e($from); ?> to <?php echo e($to); ?>)</span>
    </h3>

    <table class="w-full text-sm">
        <tbody>
            
            <tr class="border-t bg-gray-50 font-bold">
                <td class="py-2 px-3" colspan="3">REVENUE / INCOME</td>
            </tr>

            
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">POS Sales (Invoices)</td>
                <td class="py-2 text-right">Rs <?php echo e(number_format($rev['sales'], 2)); ?></td>
                <td class="py-2 px-3 text-right text-xs text-emerald-600">
                    Profit: Rs <?php echo e(number_format($profit['sales_profit'], 2)); ?>

                </td>
            </tr>

            
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">
                    Prescription Medicine
                    <div class="text-xs text-gray-500 ml-2">
                        (Sell Rs <?php echo e(number_format($rev['rx_medicine'], 2)); ?> - Cost Rs <?php echo e(number_format($cost['rx_medicine_cogs'], 2)); ?>)
                    </div>
                </td>
                <td class="py-2 text-right">Rs <?php echo e(number_format($rev['rx_medicine'], 2)); ?></td>
                <td class="py-2 px-3 text-right text-xs text-emerald-600 font-semibold">
                    Profit: Rs <?php echo e(number_format($profit['rx_medicine_profit'], 2)); ?>

                </td>
            </tr>

            
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">Doctor Fees (commission)</td>
                <td class="py-2 text-right">Rs <?php echo e(number_format($rev['doctor_fee'], 2)); ?></td>
                <td class="py-2 px-3 text-right text-xs text-emerald-600">
                    (100% income)
                </td>
            </tr>

            
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">Radiology / Extra Tests</td>
                <td class="py-2 text-right">Rs <?php echo e(number_format($rev['radiology'], 2)); ?></td>
                <td class="py-2 px-3 text-right text-xs text-emerald-600">
                    (100% income)
                </td>
            </tr>

            <tr class="border-b bg-blue-50 font-bold">
                <td class="py-2 px-3">Total Revenue</td>
                <td class="py-2 text-right text-blue-700">Rs <?php echo e(number_format($totalRevenue, 2)); ?></td>
                <td class="py-2 px-3 text-right text-blue-700"></td>
            </tr>

            
            <tr class="border-t bg-gray-50 font-bold">
                <td class="py-2 px-3" colspan="3">COST OF GOODS SOLD</td>
            </tr>
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">POS Purchase Cost</td>
                <td class="py-2 text-right text-red-600">- Rs <?php echo e(number_format($cost['sales_cogs'], 2)); ?></td>
                <td></td>
            </tr>
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">Prescription Medicine Purchase Cost</td>
                <td class="py-2 text-right text-red-600">- Rs <?php echo e(number_format($cost['rx_medicine_cogs'], 2)); ?></td>
                <td></td>
            </tr>
            <tr class="border-b bg-red-50 font-bold">
                <td class="py-2 px-3">Total COGS</td>
                <td class="py-2 text-right text-red-700">- Rs <?php echo e(number_format($totalCogs, 2)); ?></td>
                <td></td>
            </tr>

            
            <tr class="border-b">
                <td class="py-2 px-3">Discount Given</td>
                <td class="py-2 text-right text-red-600">- Rs <?php echo e(number_format($pnl['discount'], 2)); ?></td>
                <td></td>
            </tr>

            
            <tr class="border-t bg-red-50 font-bold">
                <td class="py-2 px-3" colspan="3">OPERATING EXPENSES</td>
            </tr>
            <?php $__empty_1 = true; $__currentLoopData = ($pnl['expenses']['breakdown'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">
                    <span class="inline-block w-2 h-2 rounded-full mr-2" style="background:<?php echo e($expense->color); ?>"></span><?php echo e($expense->name); ?>

                    <span class="text-xs text-gray-400">(<?php echo e($expense->entries); ?> entries)</span>
                </td>
                <td class="py-2 text-right text-red-600">- Rs <?php echo e(number_format($expense->total, 2)); ?></td>
                <td></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr class="border-b"><td class="py-2 px-3 pl-6 text-gray-400">No expenses for this period</td><td class="py-2 text-right">Rs 0.00</td><td></td></tr>
            <?php endif; ?>
            <tr class="border-b bg-red-100 font-bold">
                <td class="py-2 px-3">Total Operating Expenses</td>
                <td class="py-2 text-right text-red-700">- Rs <?php echo e(number_format($expenseTotal, 2)); ?></td>
                <td class="py-2 px-3 text-right"><a href="<?php echo e(route('expenses.report', ['from'=>\Carbon\Carbon::parse($from)->toDateString(), 'to'=>\Carbon\Carbon::parse($to)->toDateString()])); ?>" class="text-xs text-blue-600">View Expense Report</a></td>
            </tr>

            
            <tr class="border-t bg-emerald-50 font-bold">
                <td class="py-2 px-3" colspan="3">PROFIT BREAKDOWN</td>
            </tr>
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">POS Sales Profit</td>
                <td class="py-2 text-right">Rs <?php echo e(number_format($profit['sales_profit'], 2)); ?></td>
                <td></td>
            </tr>
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">Prescription Medicine Profit</td>
                <td class="py-2 text-right">Rs <?php echo e(number_format($profit['rx_medicine_profit'], 2)); ?></td>
                <td></td>
            </tr>
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">Doctor Fees</td>
                <td class="py-2 text-right">Rs <?php echo e(number_format($profit['doctor_fee'], 2)); ?></td>
                <td></td>
            </tr>
            <tr class="border-b">
                <td class="py-2 px-3 pl-6">Radiology / Extra Tests</td>
                <td class="py-2 text-right">Rs <?php echo e(number_format($profit['radiology'], 2)); ?></td>
                <td></td>
            </tr>
            <tr class="border-b bg-blue-50 font-bold">
                <td class="py-2 px-3">Profit Before Operating Expenses</td>
                <td class="py-2 text-right">Rs <?php echo e(number_format($pnl['profit_before_expenses'], 2)); ?></td>
                <td></td>
            </tr>
            <tr class="border-b bg-red-50 font-bold">
                <td class="py-2 px-3">Less: Operating Expenses</td>
                <td class="py-2 text-right text-red-700">- Rs <?php echo e(number_format($expenseTotal, 2)); ?></td>
                <td></td>
            </tr>

            
            <tr class="border-t-2 bg-<?php echo e($netProfit >= 0 ? 'green' : 'red'); ?>-50 font-bold text-lg">
                <td class="py-3 px-3"><?php echo e($netProfit >= 0 ? 'NET PROFIT' : 'NET LOSS'); ?></td>
                <td class="py-3 text-right text-<?php echo e($netProfit >= 0 ? 'green' : 'red'); ?>-700" colspan="2">
                    Rs <?php echo e(number_format($netProfit, 2)); ?>

                    <span class="text-xs font-normal ml-2">
                        (<?php echo e($totalRevenue > 0 ? number_format(($netProfit / $totalRevenue) * 100, 1) : 0); ?>% margin)
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/reports/profit-loss.blade.php ENDPATH**/ ?>