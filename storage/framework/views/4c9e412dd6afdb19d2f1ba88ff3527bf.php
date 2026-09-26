<?php $__env->startSection('title', 'Sales Report'); ?>
<?php $__env->startSection('page-title', 'Sales Report'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-5">
    <form method="GET" class="flex flex-wrap gap-3 mb-4">
        <input type="date" name="from" value="<?php echo e($from); ?>" class="border rounded-lg p-2">
        <input type="date" name="to" value="<?php echo e($to); ?>" class="border rounded-lg p-2">
        <select name="group_by" class="border rounded-lg p-2">
            <option value="day" <?php if($group==='day'): echo 'selected'; endif; ?>>Daily</option>
            <option value="week" <?php if($group==='week'): echo 'selected'; endif; ?>>Weekly</option>
            <option value="month" <?php if($group==='month'): echo 'selected'; endif; ?>>Monthly</option>
        </select>
        <button class="bg-blue-500 text-white px-4 py-2 rounded-lg">Generate</button>
    </form>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Period</th><th class="p-2">Invoices</th><th class="p-2">Subtotal</th>
            <th class="p-2">Discount</th><th class="p-2">Total</th><th class="p-2">Paid</th><th class="p-2">Due</th>
        </tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr class="border-b">
            <td class="p-2 font-mono"><?php echo e($row->period); ?></td>
            <td class="p-2"><?php echo e($row->invoices); ?></td>
            <td class="p-2">Rs <?php echo e(number_format($row->subtotal,2)); ?></td>
            <td class="p-2 text-red-600">Rs <?php echo e(number_format($row->discount,2)); ?></td>
            <td class="p-2 font-bold">Rs <?php echo e(number_format($row->total,2)); ?></td>
            <td class="p-2 text-green-600">Rs <?php echo e(number_format($row->paid,2)); ?></td>
            <td class="p-2 text-red-600">Rs <?php echo e(number_format($row->due,2)); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" class="p-6 text-center text-gray-400">No data.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/reports/sales.blade.php ENDPATH**/ ?>