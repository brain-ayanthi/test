<?php $__env->startSection('title', 'Vendors'); ?>
<?php $__env->startSection('page-title', 'Vendors / Suppliers'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Add Vendor</h3>
        <form method="POST" action="<?php echo e(route('vendors.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="text" name="name" required placeholder="Vendor name" class="w-full border rounded-lg p-2 mb-2">
            <input type="text" name="company" placeholder="Company" class="w-full border rounded-lg p-2 mb-2">
            <input type="text" name="phone" placeholder="Phone" class="w-full border rounded-lg p-2 mb-2">
            <input type="email" name="email" placeholder="Email" class="w-full border rounded-lg p-2 mb-2">
            <textarea name="address" placeholder="Address" class="w-full border rounded-lg p-2 mb-2" rows="2"></textarea>
            <button class="w-full bg-green-500 text-white py-2 rounded-lg font-semibold">Add Vendor</button>
        </form>
    </div>
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-100 text-left"><th class="p-2">Name</th><th class="p-2">Company</th><th class="p-2">Phone</th><th class="p-2">Purchases</th></tr></thead>
            <tbody>
            <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="border-b"><td class="p-2 font-semibold"><?php echo e($v->name); ?></td><td class="p-2 text-gray-500"><?php echo e($v->company); ?></td><td class="p-2"><?php echo e($v->phone); ?></td><td class="p-2"><?php echo e($v->purchases->count()); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/purchase/vendors/index.blade.php ENDPATH**/ ?>