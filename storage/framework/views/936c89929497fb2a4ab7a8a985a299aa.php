<?php $__env->startSection('title', 'Categories'); ?>
<?php $__env->startSection('page-title', 'Product Categories'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Add Category</h3>
        <form method="POST" action="<?php echo e(route('categories.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="text" name="name" required placeholder="Category name" class="w-full border rounded-lg p-2 mb-2">
            <textarea name="description" placeholder="Description" class="w-full border rounded-lg p-2 mb-2" rows="2"></textarea>
            <button class="w-full bg-green-500 text-white py-2 rounded-lg font-semibold">Add</button>
        </form>
    </div>
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-100 text-left"><th class="p-2">Name</th><th class="p-2">Slug</th><th class="p-2">Products</th></tr></thead>
            <tbody>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="border-b"><td class="p-2 font-semibold"><?php echo e($c->name); ?></td><td class="p-2 text-gray-500 font-mono text-xs"><?php echo e($c->slug); ?></td><td class="p-2"><?php echo e($c->products->count()); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/inventory/categories/index.blade.php ENDPATH**/ ?>