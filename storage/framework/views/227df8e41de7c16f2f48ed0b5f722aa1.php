<?php $__env->startSection('title', 'Add User'); ?>
<?php $__env->startSection('page-title', 'Add New User'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">
    <form method="POST" action="<?php echo e(route('users.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="space-y-3">
            <div><label class="block text-sm font-semibold mb-1">Name *</label><input type="text" name="name" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Email *</label><input type="email" name="email" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Phone</label><input type="text" name="phone" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Password *</label><input type="password" name="password" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Confirm Password *</label><input type="password" name="password_confirmation" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Role *</label>
                <select name="role" required class="w-full border rounded-lg p-2">
                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($r->name); ?>"><?php echo e($r->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <button class="mt-4 bg-green-500 text-white px-6 py-2 rounded-lg font-semibold">Create User</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/users/create.blade.php ENDPATH**/ ?>