<?php $__env->startSection('title', $vendor->name.' - Payments'); ?>
<?php $__env->startSection('page-title', 'Vendor Payment History'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-5 mb-5">
    <div class="flex flex-wrap justify-between items-start gap-4">
        <div>
            <h2 class="text-2xl font-bold"><?php echo e($vendor->name); ?></h2>
            <?php if($vendor->company): ?><p class="text-gray-500"><?php echo e($vendor->company); ?></p><?php endif; ?>
            <p class="text-sm text-gray-500 mt-1">
                <?php if($vendor->phone): ?><i class="fas fa-phone mr-1"></i> <?php echo e($vendor->phone); ?><?php endif; ?>
                <?php if($vendor->email): ?><span class="ml-3"><i class="fas fa-envelope mr-1"></i> <?php echo e($vendor->email); ?></span><?php endif; ?>
            </p>
        </div>
        <a href="<?php echo e(route('vendor-payments.index')); ?>" class="text-blue-600 hover:underline text-sm">&larr; Back to all vendors</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5">
        <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-xs text-blue-700 font-semibold">Total Billed</div>
            <div class="text-2xl font-bold text-blue-900">Rs <?php echo e(number_format($vendor->total_purchased ?? 0, 2)); ?></div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-xs text-green-700 font-semibold">Total Paid</div>
            <div class="text-2xl font-bold text-green-900">Rs <?php echo e(number_format($vendor->total_paid ?? 0, 2)); ?></div>
        </div>
        <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-xs text-red-700 font-semibold">Outstanding</div>
            <div class="text-2xl font-bold text-red-700">Rs <?php echo e(number_format($vendor->outstanding, 2)); ?></div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-xs text-gray-700 font-semibold">Opening Balance</div>
            <div class="text-2xl font-bold text-gray-900">Rs <?php echo e(number_format($vendor->opening_balance, 2)); ?></div>
        </div>
    </div>
</div>


<div class="bg-white rounded-xl shadow-sm p-5 mb-5">
    <h3 class="font-bold text-lg mb-3"><i class="fas fa-plus-circle text-green-600 mr-1"></i> Record Payment</h3>
    <form method="POST" action="<?php echo e(route('vendor-payments.store')); ?>" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="vendor_id" value="<?php echo e($vendor->id); ?>">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Date *</label>
            <input type="date" name="payment_date" value="<?php echo e(now()->toDateString()); ?>" required class="w-full border rounded p-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Amount *</label>
            <input type="number" step="0.01" name="amount" min="0.01" value="<?php echo e(number_format($vendor->outstanding, 2, '.', '')); ?>" required class="w-full border rounded p-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Method</label>
            <select name="payment_method" class="w-full border rounded p-2 text-sm">
                <option value="cash">Cash</option>
                <option value="bank">Bank</option>
                <option value="mfs">MFS</option>
                <option value="cheque">Cheque</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Against Invoice</label>
            <select name="purchase_id" class="w-full border rounded p-2 text-sm">
                <option value="">-- General --</option>
                <?php $__currentLoopData = $purchases->where('due_amount', '>', 0); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($pc->id); ?>"><?php echo e($pc->invoice_number); ?> (Due Rs <?php echo e(number_format($pc->due_amount,2)); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Reference</label>
            <input type="text" name="reference" class="w-full border rounded p-2 text-sm" placeholder="Cheque no / ref">
        </div>
        <div>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded w-full font-semibold text-sm">
                <i class="fas fa-check mr-1"></i> Save
            </button>
        </div>
    </form>
</div>


<div class="bg-white rounded-xl shadow-sm p-5 mb-5">
    <h3 class="font-bold text-lg mb-3">Unpaid Invoices</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Invoice #</th>
                <th class="p-2">Date</th>
                <th class="p-2 text-right">Total</th>
                <th class="p-2 text-right">Paid</th>
                <th class="p-2 text-right">Due</th>
                <th class="p-2">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $purchases->where('due_amount', '>', 0); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-b">
                <td class="p-2 font-semibold text-blue-600"><?php echo e($pc->invoice_number); ?></td>
                <td class="p-2"><?php echo e($pc->purchase_date->format('d M Y')); ?></td>
                <td class="p-2 text-right">Rs <?php echo e(number_format($pc->total,2)); ?></td>
                <td class="p-2 text-right text-green-600">Rs <?php echo e(number_format($pc->paid_amount,2)); ?></td>
                <td class="p-2 text-right font-bold text-red-600">Rs <?php echo e(number_format($pc->due_amount,2)); ?></td>
                <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold bg-yellow-100 text-yellow-800"><?php echo e(ucfirst($pc->payment_status)); ?></span></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="p-4 text-center text-gray-400">No unpaid invoices.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>


<div class="bg-white rounded-xl shadow-sm p-5">
    <h3 class="font-bold text-lg mb-3">Payment History</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Date</th>
                <th class="p-2 text-left">Invoice</th>
                <th class="p-2 text-left">Method</th>
                <th class="p-2 text-left">Reference</th>
                <th class="p-2 text-right">Amount</th>
                <th class="p-2"></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-b">
                <td class="p-2"><?php echo e($pm->payment_date->format('d M Y')); ?></td>
                <td class="p-2"><?php echo e($pm->purchase->invoice_number ?? 'General Payment'); ?></td>
                <td class="p-2 uppercase text-xs"><?php echo e($pm->payment_method); ?></td>
                <td class="p-2 text-gray-500"><?php echo e($pm->reference ?: '-'); ?></td>
                <td class="p-2 text-right font-bold text-green-600">Rs <?php echo e(number_format($pm->amount,2)); ?></td>
                <td class="p-2 text-right">
                    <form method="POST" action="<?php echo e(route('vendor-payments.destroy', $pm)); ?>" onsubmit="return confirm('Delete this payment?')" class="inline">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="p-6 text-center text-gray-400">No payments recorded for this vendor.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <div class="mt-4"><?php echo e($payments->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/purchase/vendor-payments/show.blade.php ENDPATH**/ ?>