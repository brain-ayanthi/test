<?php $__env->startSection('title', 'Drug Types'); ?>
<?php $__env->startSection('page-title', 'Drug Types (Quick Pick)'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Add Drug Type</h3>
        <form method="POST" action="<?php echo e(route('drug-types.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="text" name="name" required placeholder="e.g. Antibiotic" class="w-full border rounded-lg p-2 mb-2">
            <input type="color" name="color" value="#22c55e" class="w-full border rounded-lg p-1 mb-2 h-10">
            <textarea name="description" placeholder="Description" class="w-full border rounded-lg p-2 mb-2" rows="2"></textarea>
            <button class="w-full bg-green-500 text-white py-2 rounded-lg font-semibold">Add Drug Type</button>
        </form>
    </div>
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <?php $__currentLoopData = $drugTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-lg p-4 text-white font-semibold" style="background:<?php echo e($d->color); ?>">
                <div><?php echo e($d->name); ?></div>
                <div class="text-xs opacity-80"><?php echo e($d->products->count()); ?> medicines</div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/inventory/drug-types/index.blade.php ENDPATH**/ ?>