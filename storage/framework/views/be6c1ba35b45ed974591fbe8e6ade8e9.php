<?php $__env->startSection('title', 'Inventory'); ?>
<?php $__env->startSection('page-title', 'Medicine / Product Inventory'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="flex flex-wrap gap-3 justify-between mb-4">
        <form method="GET" class="flex gap-2 flex-1 flex-wrap">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search medicine..." class="border rounded-lg px-3 py-2 flex-1 min-w-[200px]">
            <select name="category_id" class="border rounded-lg px-3 py-2">
                <option value="">All Categories</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>" <?php if(request('category_id')==$c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="drug_type_id" class="border rounded-lg px-3 py-2">
                <option value="">All Drug Types</option>
                <?php $__currentLoopData = $drugTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($d->id); ?>" <?php if(request('drug_type_id')==$d->id): echo 'selected'; endif; ?>><?php echo e($d->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg"><i class="fas fa-search"></i></button>
        </form>
        <div class="flex gap-2">
            <a href="<?php echo e(route('categories.index')); ?>" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm"><i class="fas fa-tags"></i> Categories</a>
            <a href="<?php echo e(route('drug-types.index')); ?>" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm"><i class="fas fa-capsules"></i> Drug Types</a>
            <a href="<?php echo e(route('units.index')); ?>" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm"><i class="fas fa-balance-scale"></i> Units</a>
            <?php if(!$openingStockImported): ?>
                <a href="<?php echo e(route('products.opening-stock.form')); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-file-import mr-1"></i> Import Opening Stock (One Time)</a>
            <?php else: ?>
                <span class="bg-gray-200 text-gray-500 px-4 py-2 rounded-lg font-semibold cursor-not-allowed" title="Opening stock has already been imported">
                    <i class="fas fa-lock mr-1"></i> Opening Stock Imported
                </span>
            <?php endif; ?>
            <a href="<?php echo e(route('products.create')); ?>" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold"><i class="fas fa-plus mr-1"></i> Add Product</a>
        </div>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-100 text-left text-gray-700">
                <th class="p-2">SKU</th>
                <th class="p-2">Name</th>
                <th class="p-2">Form</th>
                <th class="p-2">Strength</th>
                <th class="p-2">Drug Type</th>
                <th class="p-2">Stock (pcs)</th>
                <th class="p-2">Purchase Unit</th>
                <th class="p-2">Sell Price</th>
                <th class="p-2">Rack</th>
                <th class="p-2"></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $stock = $p->stock_quantity; ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2 font-mono text-xs text-gray-500"><?php echo e($p->sku); ?></td>
                <td class="p-2 font-semibold"><?php echo e($p->name); ?></td>
                <td class="p-2"><?php echo e($p->form_type); ?></td>
                <td class="p-2"><?php echo e($p->strength); ?></td>
                <td class="p-2">
                    <?php if($p->drugType): ?>
                    <span class="px-2 py-0.5 rounded text-xs text-white" style="background:<?php echo e($p->drugType->color); ?>"><?php echo e($p->drugType->name); ?></span>
                    <?php endif; ?>
                </td>
                <td class="p-2">
                    <span class="font-bold <?php echo e($stock <= 0 ? 'text-red-600' : ($stock <= $p->min_stock ? 'text-amber-600' : 'text-green-600')); ?>">
                        <?php echo e($stock); ?>

                    </span>
                </td>
                <td class="p-2 text-xs"><?php echo e($p->pieces_per_purchase_unit); ?> pcs / <?php echo e($p->purchaseUnit?->short_name); ?></td>
                <td class="p-2 font-bold">Rs <?php echo e(number_format($p->selling_price, 2)); ?></td>
                <td class="p-2 text-xs"><?php echo e($p->rack_number); ?></td>
                <td class="p-2">
                    <a href="<?php echo e(route('products.edit', $p)); ?>" class="text-yellow-500"><i class="fas fa-edit"></i></a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="10" class="p-6 text-center text-gray-400">No products.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <div class="mt-4"><?php echo e($products->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/inventory/products/index.blade.php ENDPATH**/ ?>