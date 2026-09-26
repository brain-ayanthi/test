<?php $__env->startSection('title', 'Radiology Report'); ?>
<?php $__env->startSection('page-title', 'Radiology / Extra Tests Report'); ?>

<?php $__env->startSection('content'); ?>
<form method="GET" class="mb-4 flex gap-2 items-end">
    <div><label class="block text-xs font-semibold text-gray-600">From</label><input type="date" name="from" value="<?php echo e($from); ?>" class="border rounded p-2"></div>
    <div><label class="block text-xs font-semibold text-gray-600">To</label><input type="date" name="to" value="<?php echo e($to); ?>" class="border rounded p-2"></div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded font-semibold">Apply</button>
    <a href="<?php echo e(route('reports.index')); ?>" class="text-blue-600 text-sm self-center">Back</a>
</form>

<div class="bg-white rounded-xl shadow p-5">
    <h3 class="font-bold text-lg mb-4">Radiology / Extra Tests Revenue</h3>
    <table class="w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Test Name</th>
                <th class="p-2 text-center">Times Performed</th>
                <th class="p-2 text-right">Revenue</th>
                <th class="p-2 w-40">Share</th>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $rx->test_wise; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $pct = $rx->radiology > 0 ? ($t->total / $rx->radiology * 100) : 0; ?>
            <tr class="border-b">
                <td class="p-2 font-semibold"><?php echo e($t->test_name); ?></td>
                <td class="p-2 text-center"><?php echo e($t->count); ?></td>
                <td class="p-2 text-right font-bold text-amber-600">Rs <?php echo e(number_format($t->total,2)); ?></td>
                <td class="p-2">
                    <div class="bg-gray-200 rounded h-2"><div class="bg-amber-500 h-2 rounded" style="width:<?php echo e($pct); ?>%"></div></div>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="4" class="p-6 text-center text-gray-400">No tests in this period.</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td class="p-2">TOTAL</td>
                <td class="p-2 text-center"><?php echo e($rx->test_wise->sum('count')); ?></td>
                <td class="p-2 text-right text-amber-600">Rs <?php echo e(number_format($rx->radiology,2)); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/reports/radiology.blade.php ENDPATH**/ ?>