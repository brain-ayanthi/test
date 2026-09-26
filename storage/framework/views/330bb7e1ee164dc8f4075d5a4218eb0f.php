<?php $__env->startSection('title', 'Patients'); ?>
<?php $__env->startSection('page-title', 'Patient Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-xl shadow-sm p-5">
    <div class="flex flex-col md:flex-row justify-between gap-3 mb-4">
        <form method="GET" class="flex gap-2 flex-1">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by name, phone, code..."
                   class="flex-1 border rounded-lg px-4 py-2">
            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg"><i class="fas fa-search"></i></button>
        </form>
        <a href="<?php echo e(route('prescriptions.create')); ?>" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold">
            <i class="fas fa-plus mr-1"></i> New Prescription
        </a>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-100 text-left text-gray-700">
                <th class="p-3">Patient ID</th>
                <th class="p-3">Name</th>
                <th class="p-3">Phone</th>
                <th class="p-3">Age</th>
                <th class="p-3">Gender</th>
                <th class="p-3">Last Visit</th>
                <th class="p-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3 font-mono text-blue-600"><?php echo e($p->patient_code); ?></td>
                <td class="p-3 font-semibold"><?php echo e($p->name); ?></td>
                <td class="p-3"><?php echo e($p->phone); ?></td>
                <td class="p-3"><?php echo e($p->age); ?></td>
                <td class="p-3 capitalize"><?php echo e($p->gender); ?></td>
                <td class="p-3"><?php echo e($p->last_visit?->format('d M Y') ?? '-'); ?></td>
                <td class="p-3 space-x-2">
                    <a href="<?php echo e(route('patients.show', $p)); ?>" class="text-blue-500"><i class="fas fa-eye"></i></a>
                    <a href="<?php echo e(route('prescriptions.create', ['patient_id' => $p->id])); ?>" class="text-green-500" title="New Rx">
                        <i class="fas fa-prescription"></i>
                    </a>
                    <a href="<?php echo e(route('patients.edit', $p)); ?>" class="text-yellow-500"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="<?php echo e(route('patients.destroy', $p)); ?>" class="inline" onsubmit="return confirm('Delete?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="text-red-500"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" class="p-6 text-center text-gray-400">No patients found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="mt-4"><?php echo e($patients->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/patients/index.blade.php ENDPATH**/ ?>