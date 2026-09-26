<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-600 text-sm font-medium">Today - Rx Medicine</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">Rs <?php echo e(number_format($summary['rx_today']->medicine ?? 0, 2)); ?></p>
            </div>
            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-pills"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-rose-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-600 text-sm font-medium">Today - Doctor Fees</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">Rs <?php echo e(number_format($summary['rx_today']->doctor_fee ?? 0, 2)); ?></p>
            </div>
            <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-md"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-amber-500">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-gray-600 text-sm font-medium">Today - Radiology / Extra</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">Rs <?php echo e(number_format($summary['rx_today']->radiology ?? 0, 2)); ?></p>
            </div>
            <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-x-ray"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-white/80 text-sm font-medium">Today - GRAND TOTAL INCOME</p>
                <p class="text-2xl font-bold mt-2">Rs <?php echo e(number_format(($summary['rx_today']->total ?? 0) + ($summary['sales_today'] ?? 0), 2)); ?></p>
                <p class="text-xs text-white/70 mt-1">Rx + POS Sales</p>
            </div>
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-coins"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    
    <div class="bg-gray-900 text-white rounded-xl p-5 shadow">
        <h3 class="font-bold text-lg mb-4">Medicine Info</h3>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div>
                <div class="w-16 h-16 mx-auto rounded-full bg-green-500 flex items-center justify-center mb-2">
                    <i class="fas fa-check text-2xl"></i>
                </div>
                <div class="text-2xl font-bold"><?php echo e($summary['medicine']['available']); ?></div>
                <div class="text-xs text-gray-400">Available</div>
            </div>
            <div>
                <div class="w-16 h-16 mx-auto rounded-full bg-amber-500 flex items-center justify-center mb-2">
                    <i class="fas fa-exclamation text-2xl"></i>
                </div>
                <div class="text-2xl font-bold"><?php echo e($summary['medicine']['low_stock']); ?></div>
                <div class="text-xs text-gray-400">Low Stock</div>
            </div>
            <div>
                <div class="w-16 h-16 mx-auto rounded-full bg-red-500 flex items-center justify-center mb-2">
                    <i class="fas fa-times text-2xl"></i>
                </div>
                <div class="text-2xl font-bold"><?php echo e($summary['medicine']['out_of_stock']); ?></div>
                <div class="text-xs text-gray-400">Out of Stock</div>
            </div>
        </div>
    </div>

    
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-red-800 text-lg">Total Profit and Loss</h3>
            <a href="<?php echo e(route('reports.profit-loss')); ?>" class="text-blue-600 text-sm hover:underline">View Details</a>
        </div>
        <div class="flex gap-2 mb-3">
            <span class="px-3 py-1 bg-yellow-400 text-gray-900 text-xs font-bold rounded">Today</span>
        </div>
        <div class="text-center py-2">
            <div class="text-3xl font-bold <?php echo e($summary['profit_today'] >= 0 ? 'text-green-700' : 'text-red-700'); ?>">
                Rs <?php echo e(number_format($summary['profit_today'], 2)); ?>

            </div>
            <div class="text-xs text-red-600 mt-1">Net Profit (Today)</div>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-4">
            <div class="bg-white rounded p-3 text-center">
                <div class="text-xs text-gray-500">Revenue</div>
                <div class="font-bold text-gray-800">Rs <?php echo e(number_format($summary['revenue_today'], 2)); ?></div>
            </div>
            <div class="bg-white rounded p-3 text-center">
                <div class="text-xs text-gray-500">Cost</div>
                <div class="font-bold text-red-600">Rs <?php echo e(number_format($summary['cost_today'], 2)); ?></div>
            </div>
        </div>
    </div>

    
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-blue-800 text-lg">Recent Prescription</h3>
            <a href="<?php echo e(route('prescriptions.index')); ?>" class="text-blue-600 text-sm hover:underline">See All</a>
        </div>
        <div class="space-y-2 max-h-52 overflow-y-auto">
            <?php $__empty_1 = true; $__currentLoopData = $recentPrescriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex justify-between items-center bg-white rounded p-2 text-sm">
                <div>
                    <div class="font-medium text-gray-800"><?php echo e($rx->prescription_number); ?></div>
                    <div class="text-xs text-gray-500"><?php echo e($rx->patient_name ?? 'Walk-in'); ?> &middot; <?php echo e(optional($rx->prescription_date)->format('M d, Y')); ?></div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-gray-800">Rs <?php echo e(number_format($rx->total_fee, 2)); ?></div>
                    <span class="text-xs px-2 py-0.5 rounded <?php echo e($rx->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'); ?>">
                        <?php echo e(ucfirst($rx->status)); ?>

                    </span>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-500 text-sm text-center py-4">No prescriptions yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    
    <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-yellow-800">Stock Alert</h3>
            <a href="<?php echo e(route('reports.low-stock')); ?>" class="text-yellow-700 hover:underline text-xs">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 border-b">
                        <th class="py-1">Medicine</th>
                        <th class="py-1">Category</th>
                        <th class="py-1">Stock</th>
                        <th class="py-1">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $summary['low_stock_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b">
                        <td class="py-2"><?php echo e($p->name); ?></td>
                        <td class="text-gray-500 text-xs"><?php echo e(optional($p->category)->name ?? 'Medicine'); ?></td>
                        <td><span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-bold"><?php echo e($p->stock); ?></span></td>
                        <td><span class="px-2 py-0.5 bg-yellow-200 text-yellow-800 rounded text-xs">Order Now</span></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="py-3 text-center text-gray-500 text-xs">All stocks are healthy.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="bg-teal-50 border border-teal-200 rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-teal-800">Prescriptions</h3>
            <a href="<?php echo e(route('prescriptions.index')); ?>" class="text-teal-600 text-xs hover:underline">See All</a>
        </div>
        <div class="space-y-3 mb-4">
            <div class="flex justify-between items-center bg-white/60 rounded p-2">
                <span class="text-gray-600 text-sm">Patient</span>
                <span class="font-bold text-lg text-gray-800"><?php echo e($summary['prescription_count']); ?></span>
            </div>
            <div class="flex justify-between items-center bg-white/60 rounded p-2">
                <span class="text-gray-600 text-sm">Sales (POS)</span>
                <span class="font-bold text-lg text-gray-800"><?php echo e($summary['sales_count']); ?></span>
            </div>
        </div>
        <div class="flex justify-center">
            <a href="<?php echo e(route('prescriptions.create')); ?>" class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-teal-600 text-white text-2xl font-bold hover:bg-teal-700 transition">
                <i class="fas fa-plus"></i>
            </a>
        </div>
        <p class="text-center text-xs text-teal-700 mt-2">New Prescription</p>
    </div>

    
    <div class="bg-red-600 text-white rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-lg">Expired</h3>
            <span class="text-3xl font-bold text-yellow-300"><?php echo e($summary['medicine']['expired']); ?></span>
        </div>
        <div class="space-y-2 max-h-48 overflow-y-auto">
            <?php $__empty_1 = true; $__currentLoopData = $summary['expired_batches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white/10 rounded p-2">
                <div class="font-medium text-sm"><?php echo e($b->product->name ?? 'Unknown'); ?></div>
                <div class="text-xs text-white/70"><?php echo e(optional($b->expiry_date)->format('d M Y')); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-white/70 text-xs text-center py-2">No expired items.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-6">
    <div class="lg:col-span-2 bg-gradient-to-br from-red-800 to-red-900 text-white rounded-xl p-5 shadow">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg">Expiring Soon</h3>
            <span class="text-3xl font-bold text-yellow-300"><?php echo e($summary['medicine']['expiring_soon']); ?></span>
        </div>
        <div class="space-y-2">
            <?php $__empty_1 = true; $__currentLoopData = $summary['expiring']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month => $batches): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex justify-between bg-white/10 rounded px-3 py-2">
                <span><?php echo e($month); ?></span>
                <span class="font-bold">(<?php echo e($batches->count()); ?>)</span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-white/70 text-sm">Nothing expiring soon.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white"><i class="fas fa-file-invoice"></i></div>
            <span class="text-blue-700 font-semibold text-sm">INVOICES TODAY</span>
        </div>
        <div class="text-3xl font-bold text-blue-900 mb-3"><?php echo e($summary['invoice_count']); ?></div>
        <div class="bg-white rounded p-3">
            <div class="text-sm text-gray-500">Paid Today</div>
            <div class="text-xl font-bold text-green-600">Rs <?php echo e(number_format($summary['sales_today_paid'] ?? 0, 2)); ?></div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600"><i class="fas fa-percent"></i></div>
                    <span class="text-gray-600 text-sm">DISCOUNT</span>
                </div>
                <span class="font-bold text-gray-800">Rs <?php echo e(number_format($summary['discount_today'], 2)); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-600"><i class="fas fa-hand-holding-usd"></i></div>
                    <span class="text-gray-600 text-sm">DUES (Customer)</span>
                </div>
                <span class="font-bold text-red-600">Rs <?php echo e(number_format($summary['customer_due'], 2)); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600"><i class="fas fa-truck"></i></div>
                    <span class="text-gray-600 text-sm">DUES (Vendor)</span>
                </div>
                <span class="font-bold text-red-600">Rs <?php echo e(number_format($summary['vendor_due'], 2)); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-600"><i class="fas fa-undo"></i></div>
                    <span class="text-gray-600 text-sm">REFUND</span>
                </div>
                <span class="font-bold text-red-600">Rs <?php echo e(number_format($summary['refund_today'], 2)); ?></span>
            </div>
        </div>
    </div>
</div>


<div class="bg-white rounded-xl p-5 shadow-sm">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-lg text-gray-800">Recent POS Sales</h3>
        <a href="<?php echo e(route('sales.index')); ?>" class="text-blue-600 text-sm hover:underline">See All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2">Invoice #</th>
                    <th class="py-2">Customer</th>
                    <th class="py-2 text-right">Total</th>
                    <th class="py-2 text-center">Status</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $recentSales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b">
                    <td class="py-2 font-mono text-blue-600"><?php echo e($s->invoice_number); ?></td>
                    <td class="text-gray-700"><?php echo e($s->customer_name ?? 'Walking Customer'); ?></td>
                    <td class="py-2 text-right font-bold">Rs <?php echo e(number_format($s->total, 2)); ?></td>
                    <td class="py-2 text-center">
                        <span class="px-2 py-0.5 rounded text-xs font-semibold
                            <?php echo e($s->payment_status === 'paid' ? 'bg-green-100 text-green-700' :
                               ($s->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')); ?>">
                            <?php echo e(ucfirst($s->payment_status)); ?>

                        </span>
                    </td>
                    <td class="py-2 text-right">
                        <a href="<?php echo e(route('sales.show', $s)); ?>" class="text-blue-500"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="py-4 text-center text-gray-400">No sales yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/dashboard/index.blade.php ENDPATH**/ ?>