<aside id="sidebar" class="w-16 bg-gray-900 text-gray-300 flex flex-col items-center py-4 gap-1 overflow-y-auto flex-shrink-0">
    <div class="w-10 h-10 rounded-lg bg-yellow-400 flex items-center justify-center text-gray-900 mb-3">
        <i class="fas fa-clinic-medical text-lg"></i>
    </div>

    <?php
        $current = request()->route()->getName();
        $nav = [
            ['route' => 'dashboard', 'icon' => 'fa-home', 'label' => 'Dashboard'],
            ['route' => 'patients.index', 'icon' => 'fa-user-injured', 'label' => 'Patients'],
            ['route' => 'doctors.index', 'icon' => 'fa-user-md', 'label' => 'Doctors'],
            ['route' => 'prescriptions.index', 'icon' => 'fa-file-prescription', 'label' => 'Prescriptions'],
            ['route' => 'sales.pos', 'icon' => 'fa-cash-register', 'label' => 'POS / Sales'],
            ['route' => 'sales.index', 'icon' => 'fa-file-invoice-dollar', 'label' => 'Invoices'],
            ['route' => 'products.index', 'icon' => 'fa-pills', 'label' => 'Inventory'],
            ['route' => 'purchases.index', 'icon' => 'fa-truck-loading', 'label' => 'Purchases'],
            ['route' => 'vendors.index', 'icon' => 'fa-truck', 'label' => 'Vendors'],
            ['route' => 'vendor-payments.index', 'icon' => 'fa-money-check-alt', 'label' => 'Vendor Payments'],
            ['route' => 'cash.index', 'icon' => 'fa-wallet', 'label' => 'Cash Store'],
            ['route' => 'expenses.index', 'icon' => 'fa-receipt', 'label' => 'Expenses'],
            ['route' => 'reports.index', 'icon' => 'fa-chart-line', 'label' => 'Reports'],
            ['route' => 'users.index', 'icon' => 'fa-users-cog', 'label' => 'Users'],
        ];
    ?>

    <?php $__currentLoopData = $nav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route($item['route'])); ?>"
           class="w-12 h-12 flex items-center justify-center rounded-lg mb-1 transition group relative
                  <?php echo e(str_starts_with($current, explode('.', $item['route'])[0]) ? 'bg-yellow-400 text-gray-900' : 'hover:bg-gray-800 hover:text-white'); ?>"
           title="<?php echo e($item['label']); ?>">
            <i class="fas <?php echo e($item['icon']); ?>"></i>
            <span class="absolute left-14 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-50 pointer-events-none">
                <?php echo e($item['label']); ?>

            </span>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</aside>
<?php /**PATH D:\xampp\htdocs\clinicms\resources\views/components/sidebar.blade.php ENDPATH**/ ?>