<?php $__env->startSection('title', 'Doctor Wise Commission'); ?>
<?php $__env->startSection('page-title', 'Doctor Wise Commission Report'); ?>

<?php $__env->startSection('content'); ?>
<form method="GET" class="mb-4 flex gap-2 items-end">
    <div><label class="block text-xs font-semibold text-gray-600">From</label><input type="date" name="from" value="<?php echo e($from); ?>" class="border rounded p-2"></div>
    <div><label class="block text-xs font-semibold text-gray-600">To</label><input type="date" name="to" value="<?php echo e($to); ?>" class="border rounded p-2"></div>
    <button class="bg-blue-600 text-white px-4 py-2 rounded font-semibold">Apply</button>
    <a href="<?php echo e(route('reports.index')); ?>" class="text-blue-600 text-sm self-center">Back to Dashboard</a>
</form>

<div class="bg-white rounded-xl shadow p-5">
    <h3 class="font-bold text-lg mb-4">Doctor Fees & Patient Counts (<?php echo e($from); ?> to <?php echo e($to); ?>)</h3>
    <table class="w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Doctor</th>
                <th class="p-2">Prescriptions</th>
                <th class="p-2">Patients</th>
                <th class="p-2 text-right">Medicine</th>
                <th class="p-2 text-right">Radiology</th>
                <th class="p-2 text-right bg-yellow-50">Doctor Fee</th>
                <th class="p-2 text-right">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $rx->doctor_wise; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2 font-semibold"><?php echo e($d->doctor); ?></td>
                <td class="p-2 text-center"><?php echo e($d->prescriptions); ?></td>
                <td class="p-2 text-center"><?php echo e($d->patients); ?></td>
                <td class="p-2 text-right">Rs <?php echo e(number_format($d->medicine,2)); ?></td>
                <td class="p-2 text-right">Rs <?php echo e(number_format($d->radiology,2)); ?></td>
                <td class="p-2 text-right font-bold text-rose-600 bg-yellow-50">Rs <?php echo e(number_format($d->doctor_fee,2)); ?></td>
                <td class="p-2 text-right font-bold">Rs <?php echo e(number_format($d->total,2)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" class="p-6 text-center text-gray-400">No data in selected period.</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot class="bg-gray-100 font-bold">
            <tr>
                <td class="p-2">TOTAL</td>
                <td class="p-2 text-center"><?php echo e($rx->rx_count); ?></td>
                <td></td>
                <td class="p-2 text-right">Rs <?php echo e(number_format($rx->medicine,2)); ?></td>
                <td class="p-2 text-right">Rs <?php echo e(number_format($rx->radiology,2)); ?></td>
                <td class="p-2 text-right text-rose-600">Rs <?php echo e(number_format($rx->doctor_fee,2)); ?></td>
                <td class="p-2 text-right">Rs <?php echo e(number_format($rx->total,2)); ?></td>
            </tr>
        </tfoot>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/reports/doctor-wise.blade.php ENDPATH**/ ?>