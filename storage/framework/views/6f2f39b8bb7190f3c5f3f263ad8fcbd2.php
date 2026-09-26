<?php $__env->startSection('title', $purchase->invoice_number); ?>
<?php $__env->startSection('page-title', 'Purchase Invoice'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between mb-6 border-b pb-4">
        <div>
            <h2 class="text-2xl font-bold"><?php echo e($purchase->invoice_number); ?></h2>
            <p class="text-gray-500">Vendor: <strong><?php echo e($purchase->vendor->name); ?></strong></p>
            <p class="text-gray-500">Date: <?php echo e($purchase->purchase_date->format('d M Y')); ?></p>
        </div>
        <div class="text-right">
            <span class="px-3 py-1 rounded-lg text-sm font-bold
                <?php echo e($purchase->payment_status==='paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                <?php echo e(strtoupper($purchase->payment_status)); ?>

            </span>
        </div>
    </div>

    <table class="w-full text-sm mb-6">
        <thead><tr class="bg-gray-100 text-left">
            <th class="p-2">Product</th><th class="p-2">Batch</th><th class="p-2">Expiry</th>
            <th class="p-2">Qty (boxes)</th><th class="p-2">Total Pcs</th>
            <th class="p-2">Price/Box</th><th class="p-2">Cost/Pc</th><th class="p-2">Total</th>
        </tr></thead>
        <tbody>
        <?php $__currentLoopData = $purchase->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="border-b">
            <td class="p-2 font-semibold"><?php echo e($item->product->name); ?></td>
            <td class="p-2"><?php echo e($item->batch_number); ?></td>
            <td class="p-2"><?php echo e($item->expiry_date?->format('M Y')); ?></td>
            <td class="p-2"><?php echo e($item->purchase_quantity); ?> <?php echo e($item->purchaseUnit?->short_name); ?></td>
            <td class="p-2 font-bold text-blue-600"><?php echo e($item->total_pieces); ?></td>
            <td class="p-2">Rs <?php echo e(number_format($item->purchase_price, 2)); ?></td>
            <td class="p-2 text-gray-500">Rs <?php echo e(number_format($item->unit_cost, 4)); ?></td>
            <td class="p-2 font-bold">Rs <?php echo e(number_format($item->total, 2)); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="flex justify-end">
        <div class="w-72 space-y-1 text-sm">
            <div class="flex justify-between"><span>Subtotal:</span><span>Rs <?php echo e(number_format($purchase->subtotal, 2)); ?></span></div>
            <div class="flex justify-between"><span>Discount:</span><span>- Rs <?php echo e(number_format($purchase->discount, 2)); ?></span></div>
            <div class="flex justify-between"><span>Tax:</span><span>Rs <?php echo e(number_format($purchase->tax, 2)); ?></span></div>
            <div class="flex justify-between"><span>Shipping:</span><span>Rs <?php echo e(number_format($purchase->shipping, 2)); ?></span></div>
            <div class="flex justify-between text-lg font-bold border-t pt-1"><span>Total:</span><span>Rs <?php echo e(number_format($purchase->total, 2)); ?></span></div>
            <div class="flex justify-between text-green-600"><span>Paid:</span><span>Rs <?php echo e(number_format($purchase->paid_amount, 2)); ?></span></div>
            <div class="flex justify-between text-red-600 font-bold"><span>Due:</span><span>Rs <?php echo e(number_format($purchase->due_amount, 2)); ?></span></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/purchase/show.blade.php ENDPATH**/ ?>