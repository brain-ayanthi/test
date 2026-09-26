<?php $__env->startSection('title', 'Edit Product'); ?>
<?php $__env->startSection('page-title', 'Edit Product'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-6 max-w-4xl">
    <form method="POST" action="<?php echo e(route('products.update', $product)); ?>">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div><label class="block text-sm font-semibold mb-1">Name *</label><input type="text" name="name" value="<?php echo e($product->name); ?>" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">SKU *</label><input type="text" name="sku" value="<?php echo e($product->sku); ?>" required class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Form Type</label><input type="text" name="form_type" value="<?php echo e($product->form_type); ?>" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Strength</label><input type="text" name="strength" value="<?php echo e($product->strength); ?>" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Drug Type</label>
                <select name="drug_type_id" class="w-full border rounded-lg p-2">
                    <option value="">--</option>
                    <?php $__currentLoopData = $drugTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->id); ?>" <?php if($product->drug_type_id==$d->id): echo 'selected'; endif; ?>><?php echo e($d->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div><label class="block text-sm font-semibold mb-1">Selling Price</label><input type="number" step="0.01" name="selling_price" value="<?php echo e($product->selling_price); ?>" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Purchase Price</label><input type="number" step="0.01" name="purchase_price" value="<?php echo e($product->purchase_price); ?>" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Rack</label><input type="text" name="rack_number" value="<?php echo e($product->rack_number); ?>" class="w-full border rounded-lg p-2"></div>
            <div><label class="block text-sm font-semibold mb-1">Min Stock</label><input type="number" name="min_stock" value="<?php echo e($product->min_stock); ?>" class="w-full border rounded-lg p-2"></div>
        </div>
        <button class="mt-4 bg-blue-500 text-white px-6 py-2 rounded-lg font-semibold">Update Product</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/inventory/products/edit.blade.php ENDPATH**/ ?>