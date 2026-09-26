<?php $__env->startSection('title', 'Doctors'); ?>
<?php $__env->startSection('page-title', 'Doctor Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-5">
    <h3 class="font-bold mb-4">Doctors</h3>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Name</th><th class="p-2">Specialization</th><th class="p-2">Phone</th>
            <th class="p-2">Chamber</th><th class="p-2">Visit Fee</th><th class="p-2">Doctor Fee</th>
        </tr></thead>
        <tbody>
        <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="border-b">
            <td class="p-2 font-semibold"><?php echo e($d->name); ?></td>
            <td class="p-2"><?php echo e($d->specialization); ?></td>
            <td class="p-2"><?php echo e($d->phone); ?></td>
            <td class="p-2"><?php echo e($d->chamber); ?></td>
            <td class="p-2">Rs <?php echo e(number_format($d->visit_fee,2)); ?></td>
            <td class="p-2 font-bold">Rs <?php echo e(number_format($d->doctor_fee,2)); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <div class="mt-4"><?php echo e($doctors->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/doctors/index.blade.php ENDPATH**/ ?>