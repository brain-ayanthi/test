<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="patients-store-url" content="<?php echo e(route('patients.store')); ?>">
    <meta name="prescriptions-store-url" content="<?php echo e(route('prescriptions.store')); ?>">
    <meta name="treatments-data-url" content="<?php echo e(url('treatments')); ?>/">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - <?php echo e(config('app.name')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-gray-100">
<div class="flex h-screen overflow-hidden">
    
    <?php echo $__env->make('components.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <header class="bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-6 py-3">
            <div class="flex items-center gap-4">
                <button id="sidebarToggle" class="text-gray-500 hover:text-gray-700 lg:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h1 class="text-xl font-bold text-gray-800"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-sm font-semibold">
                    <i class="fas fa-clock"></i>
                    <span id="liveClock"><?php echo e(now()->format('h:i:s A')); ?></span>
                    <span class="text-blue-400">|</span>
                    <span class="text-xs"><?php echo e(now()->format('D, M d, Y')); ?></span>
                </div>
                <?php
                    $headerTodayExpense = \Illuminate\Support\Facades\Schema::hasTable('expenses')
                        ? (float) \Illuminate\Support\Facades\DB::table('expenses')->whereDate('expense_date', today())->sum('amount')
                        : 0;
                ?>
                <a href="<?php echo e(route('expenses.index')); ?>" class="bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 px-3 py-2 rounded-lg font-semibold text-sm flex items-center gap-2" title="Add expense / view expenses">
                    <i class="fas fa-receipt"></i>
                    <span class="hidden xl:inline">Expense</span>
                    <span class="font-bold">Rs <?php echo e(number_format($headerTodayExpense, 2)); ?></span>
                </a>
                <a href="<?php echo e(route('prescriptions.create')); ?>" class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg font-semibold text-sm flex items-center gap-2">
                    <i class="fas fa-prescription-bottle-medical"></i> Prescription
                </a>
                <button class="w-9 h-9 rounded-lg bg-yellow-400 flex items-center justify-center text-gray-900">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-full bg-yellow-400 flex items-center justify-center font-bold text-gray-900">S</div>
                    <div class="hidden md:block">
                        <div class="text-sm font-semibold text-gray-800"><?php echo e(auth()->user()->name); ?></div>
                        <div class="text-xs text-gray-500"><?php echo e(auth()->user()->roles->first()?->name ?? 'User'); ?></div>
                    </div>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="ml-2">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-gray-400 hover:text-red-500" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        
        <main class="flex-1 overflow-y-auto p-6 bg-gray-100">
            <?php if(session('success')): ?>
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                    <i class="fas fa-check-circle mr-2"></i><?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo e(asset('js/app.js')); ?>"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\clinicms\resources\views/layouts/app.blade.php ENDPATH**/ ?>