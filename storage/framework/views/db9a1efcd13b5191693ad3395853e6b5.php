<?php $__env->startSection('title', $prescription->prescription_number); ?>
<?php $__env->startSection('page-title', 'Prescription Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-wrap justify-between items-start gap-3 border-b pb-4 mb-4">
            <div>
                <h2 class="text-2xl font-bold"><?php echo e($prescription->prescription_number); ?></h2>
                <p class="text-gray-500">Date: <?php echo e($prescription->prescription_date->format('d M Y')); ?></p>
                <p class="text-sm text-gray-500">Patients in this Rx: <strong class="text-blue-600"><?php echo e($prescription->prescriptionPatients->count()); ?></strong></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button"
                        onclick="ClinicMSPrintServer.printExisting(<?php echo e($prescription->id); ?>, <?php echo \Illuminate\Support\Js::from($prescription->prescription_number)->toHtml() ?>, this)"
                        class="bg-green-500 text-white px-4 py-2 rounded-lg disabled:opacity-50">
                    <i class="fas fa-print mr-1"></i> Direct Print
                </button>
                <a href="<?php echo e(route('prescriptions.index')); ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">Back</a>
            </div>
        </div>

        <?php $__currentLoopData = $prescription->prescriptionPatients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $pp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="border rounded-lg p-4 mb-4 <?php echo e($i>0 ? 'mt-6' : ''); ?>">
            <div class="flex flex-wrap justify-between items-start mb-3 bg-blue-50 p-3 rounded">
                <div>
                    <span class="inline-block bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded mr-2">Patient <?php echo e($i+1); ?></span>
                    <strong class="text-lg"><?php echo e($pp->patient_name); ?></strong>
                    <span class="text-sm text-gray-500 ml-2"><?php echo e($pp->patient_age); ?> yrs · <?php echo e($pp->patient_phone); ?></span>
                    <div class="text-xs font-mono text-blue-600 mt-1"><?php echo e($pp->patient_code); ?></div>
                </div>
                <?php if($pp->doctor): ?><div class="text-sm text-gray-600"><i class="fas fa-user-md"></i> <?php echo e($pp->doctor->name); ?></div><?php endif; ?>
            </div>

            <table class="w-full text-sm mb-3">
                <thead><tr class="bg-gray-100 text-left">
                    <th class="p-2">#</th><th class="p-2">Medicine</th><th class="p-2">Timing</th>
                    <th class="p-2">Days</th><th class="p-2">Qty</th><th class="p-2">Price</th>
                    <th class="p-2">Note</th><th class="p-2">Total</th>
                </tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $pp->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b">
                    <td class="p-2"><?php echo e($j+1); ?></td>
                    <td class="p-2 font-semibold"><?php echo e($item->drug_name); ?><div class="text-xs text-gray-500"><?php echo e($item->form_type); ?> <?php echo e($item->strength); ?></div></td>
                    <td class="p-2"><span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-bold"><?php echo e($item->timing); ?></span></td>
                    <td class="p-2"><?php echo e($item->duration_days); ?></td>
                    <td class="p-2 font-bold text-blue-600"><?php echo e((float) $item->quantity); ?></td>
                    <td class="p-2">Rs <?php echo e(number_format($item->unit_price,2)); ?></td>
                    <td class="p-2 text-xs text-gray-500"><?php echo e($item->instruction_note ?: '-'); ?></td>
                    <td class="p-2 font-bold">Rs <?php echo e(number_format($item->total,2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="p-4 text-center text-gray-400">No medicines</td></tr>
                <?php endif; ?>
                </tbody>
            </table>

            <?php if($pp->radiologies->count()): ?>
            <div class="mb-3">
                <h4 class="font-semibold text-sm text-blue-800 mb-1"><i class="fas fa-x-ray mr-1"></i>Radiology / Extra Tests</h4>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $pp->radiologies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold"><?php echo e($rad->test_name); ?> — Rs <?php echo e(number_format($rad->price,2)); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if($pp->diagnosis): ?><p class="text-sm mt-2"><strong>Diagnosis:</strong> <?php echo e($pp->diagnosis); ?></p><?php endif; ?>
            <?php if($pp->precautions): ?><p class="text-sm"><strong>Precautions:</strong> <?php echo e($pp->precautions); ?></p><?php endif; ?>

            <div class="mt-3 flex justify-end">
                <div class="w-64 text-sm space-y-1 border-t pt-2">
                    <div class="flex justify-between"><span>Medicine Cost</span><span>Rs <?php echo e(number_format($pp->medicine_cost,2)); ?></span></div>
                    <div class="flex justify-between text-red-600"><span>Radiology</span><span>Rs <?php echo e(number_format($pp->radiology_cost,2)); ?></span></div>
                    <div class="flex justify-between"><span>Doctor Fee</span><span>Rs <?php echo e(number_format($pp->doctor_fee,2)); ?></span></div>
                    <div class="flex justify-between font-bold text-base border-t pt-1"><span>Subtotal</span><span class="text-green-600">Rs <?php echo e(number_format($pp->total,2)); ?></span></div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold mb-3">Overall Fee Summary</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span>Total Medicine Cost</span><span>Rs <?php echo e(number_format($prescription->medicine_cost,2)); ?></span></div>
            <div class="flex justify-between text-red-600 font-semibold"><span>Radiology (extra)</span><span>Rs <?php echo e(number_format($prescription->radiology_cost,2)); ?></span></div>
            <div class="flex justify-between"><span>Total Doctor Fee</span><span>Rs <?php echo e(number_format($prescription->doctor_fee,2)); ?></span></div>
            <div class="flex justify-between text-lg font-bold border-t pt-2"><span>Grand Total</span><span class="text-green-600">Rs <?php echo e(number_format($prescription->total_fee,2)); ?></span></div>
        </div>
        <p class="text-xs text-gray-400 mt-3">All patients print together on one prescription.</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/clinicms-print-server.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/prescriptions/show.blade.php ENDPATH**/ ?>