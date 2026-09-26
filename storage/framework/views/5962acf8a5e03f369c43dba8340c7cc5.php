<?php $__env->startSection('title', 'Expense Report'); ?>
<?php $__env->startSection('page-title', 'Expense Report'); ?>

<?php $__env->startSection('content'); ?>
<form method="GET" class="mb-5 flex flex-wrap gap-2 items-end">
    <div class="flex flex-wrap gap-1">
        <?php $__currentLoopData = ['today'=>'Today','yesterday'=>'Yesterday','7days'=>'Last 7 Days','this_month'=>'This Month','last_month'=>'Last Month','this_year'=>'This Year']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('expenses.report',['period'=>$value])); ?>" class="px-3 py-2 rounded-lg border text-sm font-semibold <?php echo e($period===$value ? 'bg-red-600 text-white' : 'bg-white'); ?>"><?php echo e($label); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div><label class="block text-xs font-semibold mb-1">From</label><input type="date" name="from" value="<?php echo e(request('from')); ?>" class="border rounded-lg p-2"></div>
    <div><label class="block text-xs font-semibold mb-1">To</label><input type="date" name="to" value="<?php echo e(request('to')); ?>" class="border rounded-lg p-2"></div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">Generate</button>
    <a href="<?php echo e(route('expenses.index')); ?>" class="border bg-white px-4 py-2 rounded-lg">Back</a>
</form>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <div class="rounded-xl bg-gradient-to-br from-red-500 to-red-700 text-white p-6 shadow">
        <div class="text-xs uppercase opacity-80">Total Expenses</div>
        <div class="text-3xl font-bold mt-1">Rs <?php echo e(number_format($total, 2)); ?></div>
        <div class="text-xs opacity-80 mt-2"><?php echo e($from); ?> — <?php echo e($to); ?></div>
        <div class="text-xs opacity-80"><?php echo e($expenses->count()); ?> expense entries</div>
    </div>
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Category Breakdown</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-lg bg-gray-50 border-l-4 p-3" style="border-color:<?php echo e($category->color); ?>">
                <div class="text-xs text-gray-500"><?php echo e($category->name); ?></div>
                <div class="font-bold text-lg">Rs <?php echo e(number_format($category->total,2)); ?></div>
                <div class="text-xs text-gray-400"><?php echo e($category->entries); ?> entries · <?php echo e($total > 0 ? number_format($category->total/$total*100,1) : 0); ?>%</div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="text-gray-400">No expenses.</p><?php endif; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-4"><i class="fas fa-calendar-day text-red-500 mr-1"></i>Daily Expense Totals</h3>
        <div class="max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-100"><th class="p-2 text-left">Date</th><th class="p-2 text-center">Entries</th><th class="p-2 text-right">Total</th></tr></thead>
                <tbody><?php $__empty_1 = true; $__currentLoopData = $daily; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr class="border-b"><td class="p-2"><?php echo e(\Carbon\Carbon::parse($day->date)->format('d M Y')); ?></td><td class="p-2 text-center"><?php echo e($day->entries); ?></td><td class="p-2 text-right font-bold text-red-600">Rs <?php echo e(number_format($day->total,2)); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="3" class="p-6 text-center text-gray-400">No data.</td></tr><?php endif; ?></tbody>
            </table>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-4"><i class="fas fa-list text-gray-500 mr-1"></i>Expense Details</h3>
        <div class="max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-100"><th class="p-2 text-left">Date / Expense</th><th class="p-2 text-left">Category</th><th class="p-2 text-right">Amount</th></tr></thead>
                <tbody><?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr class="border-b"><td class="p-2"><div class="font-semibold"><?php echo e($expense->title); ?></div><div class="text-xs text-gray-400"><?php echo e($expense->expense_date->format('d M Y')); ?></div></td><td class="p-2"><?php echo e($expense->category?->name); ?></td><td class="p-2 text-right font-bold text-red-600">Rs <?php echo e(number_format($expense->amount,2)); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="3" class="p-6 text-center text-gray-400">No data.</td></tr><?php endif; ?></tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/expenses/report.blade.php ENDPATH**/ ?>