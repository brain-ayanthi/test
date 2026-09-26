<?php $__env->startSection('title', 'Expenses'); ?>
<?php $__env->startSection('page-title', 'Expense Management'); ?>

<?php $__env->startSection('content'); ?>
<?php if($errors->any()): ?>
<div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded">
    <ul class="list-disc ml-5 text-sm"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="rounded-xl bg-gradient-to-br from-red-500 to-red-600 text-white p-5 shadow">
        <div class="text-xs uppercase opacity-80">Today Expenses</div>
        <div class="text-2xl font-bold mt-1">Rs <?php echo e(number_format($todayTotal, 2)); ?></div>
        <div class="text-xs opacity-80 mt-1"><?php echo e(now()->format('d M Y')); ?></div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 text-white p-5 shadow">
        <div class="text-xs uppercase opacity-80">This Month</div>
        <div class="text-2xl font-bold mt-1">Rs <?php echo e(number_format($monthTotal, 2)); ?></div>
        <div class="text-xs opacity-80 mt-1"><?php echo e(now()->format('F Y')); ?></div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 text-white p-5 shadow">
        <div class="text-xs uppercase opacity-80">This Year</div>
        <div class="text-2xl font-bold mt-1">Rs <?php echo e(number_format($yearTotal, 2)); ?></div>
        <div class="text-xs opacity-80 mt-1"><?php echo e(now()->format('Y')); ?></div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-gray-700 to-gray-900 text-white p-5 shadow">
        <div class="text-xs uppercase opacity-80">Selected Period</div>
        <div class="text-2xl font-bold mt-1">Rs <?php echo e(number_format($selectedTotal, 2)); ?></div>
        <div class="text-xs opacity-80 mt-1"><?php echo e($from); ?> — <?php echo e($to); ?></div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold text-lg mb-4">
            <i class="fas <?php echo e($editExpense ? 'fa-edit text-blue-500' : 'fa-plus-circle text-red-500'); ?> mr-1"></i>
            <?php echo e($editExpense ? 'Edit Expense' : 'Add Expense'); ?>

        </h3>
        <form method="POST" action="<?php echo e($editExpense ? route('expenses.update', $editExpense) : route('expenses.store')); ?>" class="space-y-3">
            <?php echo csrf_field(); ?> <?php if($editExpense): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
            <div>
                <label class="block text-xs font-semibold mb-1">Date *</label>
                <input type="date" name="expense_date" required value="<?php echo e(old('expense_date', $editExpense?->expense_date?->format('Y-m-d') ?? now()->toDateString())); ?>" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Expense Category *</label>
                <select name="expense_category_id" required class="w-full border rounded-lg p-2">
                    <option value="">Select category</option>
                    <?php $__currentLoopData = $activeCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php if(old('expense_category_id', $editExpense?->expense_category_id)==$category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Title / Description *</label>
                <input type="text" name="title" required value="<?php echo e(old('title', $editExpense?->title)); ?>" placeholder="e.g. Electricity bill" class="w-full border rounded-lg p-2">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold mb-1">Amount (Rs) *</label>
                    <input type="number" name="amount" required min="0.01" step="0.01" value="<?php echo e(old('amount', $editExpense?->amount)); ?>" class="w-full border rounded-lg p-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Payment Method *</label>
                    <select name="payment_method" class="w-full border rounded-lg p-2">
                        <?php $__currentLoopData = ['cash'=>'Cash','bank'=>'Bank','mfs'=>'MFS','card'=>'Card','other'=>'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if(old('payment_method', $editExpense?->payment_method ?? 'cash')===$value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Reference</label>
                <input type="text" name="reference" value="<?php echo e(old('reference', $editExpense?->reference)); ?>" placeholder="Bill / receipt number" class="w-full border rounded-lg p-2">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border rounded-lg p-2"><?php echo e(old('notes', $editExpense?->notes)); ?></textarea>
            </div>
            <div class="flex gap-2">
                <button class="flex-1 <?php echo e($editExpense ? 'bg-blue-600' : 'bg-red-600'); ?> text-white py-2.5 rounded-lg font-semibold">
                    <?php echo e($editExpense ? 'Update Expense' : 'Save Expense'); ?>

                </button>
                <?php if($editExpense): ?><a href="<?php echo e(route('expenses.index')); ?>" class="border px-4 py-2.5 rounded-lg">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h3 class="font-bold text-lg"><i class="fas fa-receipt text-red-500 mr-1"></i>Expenses</h3>
            <div class="flex gap-2">
                <a href="<?php echo e(route('expenses.report')); ?>" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-chart-bar mr-1"></i>Expense Report</a>
                <button type="button" onclick="document.getElementById('categoryPanel').classList.toggle('hidden')" class="bg-gray-800 text-white px-3 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-tags mr-1"></i>Categories</button>
            </div>
        </div>

        <form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
            <div class="flex flex-wrap gap-1">
                <?php $__currentLoopData = ['today'=>'Today','yesterday'=>'Yesterday','7days'=>'7 Days','this_month'=>'Month','last_month'=>'Last Month','this_year'=>'Year']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('expenses.index', ['period'=>$value])); ?>" class="px-3 py-2 rounded-lg border text-xs font-semibold <?php echo e($period===$value ? 'bg-red-600 text-white' : 'bg-white'); ?>"><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="border rounded-lg p-2 text-sm">
            <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="border rounded-lg p-2 text-sm">
            <select name="category_id" class="border rounded-lg p-2 text-sm"><option value="">All Categories</option><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(request('category_id')==$c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search..." class="border rounded-lg p-2 text-sm w-32">
            <button class="bg-blue-600 text-white px-3 py-2 rounded-lg"><i class="fas fa-filter"></i></button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-100 text-left"><th class="p-2">Date</th><th class="p-2">Category</th><th class="p-2">Title</th><th class="p-2">Payment</th><th class="p-2 text-right">Amount</th><th class="p-2"></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2 whitespace-nowrap"><?php echo e($expense->expense_date->format('d M Y')); ?></td>
                        <td class="p-2"><span class="px-2 py-1 rounded-full text-white text-xs" style="background:<?php echo e($expense->category?->color); ?>"><?php echo e($expense->category?->name); ?></span></td>
                        <td class="p-2"><div class="font-semibold"><?php echo e($expense->title); ?></div><div class="text-xs text-gray-400"><?php echo e($expense->reference); ?></div></td>
                        <td class="p-2 uppercase text-xs"><?php echo e($expense->payment_method); ?></td>
                        <td class="p-2 text-right font-bold text-red-600">Rs <?php echo e(number_format($expense->amount, 2)); ?></td>
                        <td class="p-2 whitespace-nowrap text-right">
                            <a href="<?php echo e(route('expenses.index', array_merge(request()->query(), ['edit'=>$expense->id]))); ?>" class="text-blue-600 mr-2"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="<?php echo e(route('expenses.destroy', $expense)); ?>" class="inline" onsubmit="return confirm('Delete this expense?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="text-red-600"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="p-8 text-center text-gray-400">No expenses for this period.</td></tr><?php endif; ?>
                </tbody>
                <tfoot><tr class="bg-red-50 font-bold"><td colspan="4" class="p-3">Selected Total</td><td class="p-3 text-right text-red-700">Rs <?php echo e(number_format($selectedTotal, 2)); ?></td><td></td></tr></tfoot>
            </table>
        </div>
        <div class="mt-4"><?php echo e($expenses->links()); ?></div>
    </div>
</div>

<div id="categoryPanel" class="<?php echo e(request('show_categories') ? '' : 'hidden'); ?> bg-white rounded-xl shadow-sm p-5 mb-5">
    <div class="flex justify-between items-center mb-4"><h3 class="font-bold text-lg"><i class="fas fa-tags mr-1"></i>Expense Categories</h3><button onclick="this.closest('#categoryPanel').classList.add('hidden')" class="text-gray-500"><i class="fas fa-times"></i></button></div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <form method="POST" action="<?php echo e(route('expense-categories.store')); ?>" class="space-y-3 border rounded-lg p-4"><?php echo csrf_field(); ?>
            <h4 class="font-semibold">Add Category</h4>
            <input type="text" name="name" required placeholder="Category name" class="w-full border rounded-lg p-2">
            <textarea name="description" rows="2" placeholder="Description" class="w-full border rounded-lg p-2"></textarea>
            <div><label class="text-xs font-semibold">Color</label><input type="color" name="color" value="#ef4444" class="w-full h-10 border rounded"></div>
            <button class="w-full bg-gray-800 text-white py-2 rounded-lg font-semibold">Add Category</button>
        </form>
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="border rounded-lg p-3">
                <form method="POST" action="<?php echo e(route('expense-categories.update', $category)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <div class="flex gap-2 items-center">
                        <input type="color" name="color" value="<?php echo e($category->color); ?>" class="w-10 h-9 border rounded">
                        <input type="text" name="name" value="<?php echo e($category->name); ?>" required class="flex-1 border rounded p-2 font-semibold">
                        <label class="text-xs flex items-center gap-1"><input type="checkbox" name="is_active" value="1" <?php if($category->is_active): echo 'checked'; endif; ?>> Active</label>
                    </div>
                    <input type="text" name="description" value="<?php echo e($category->description); ?>" placeholder="Description" class="w-full border rounded p-2 text-sm mt-2">
                    <button class="text-blue-600 text-sm font-semibold mt-2">Update</button>
                </form>
                <form method="POST" action="<?php echo e(route('expense-categories.destroy', $category)); ?>" class="text-right -mt-5" onsubmit="return confirm('Delete category?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="text-red-600 text-sm">Delete</button></form>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-5">
    <h3 class="font-bold mb-4">Category Summary — <?php echo e($from); ?> to <?php echo e($to); ?></h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
        <?php $__empty_1 = true; $__currentLoopData = $categorySummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="border-l-4 rounded-lg bg-gray-50 p-3" style="border-color:<?php echo e($row->color); ?>"><div class="text-sm font-semibold"><?php echo e($row->name); ?></div><div class="text-xl font-bold">Rs <?php echo e(number_format($row->total,2)); ?></div><div class="text-xs text-gray-500"><?php echo e($row->entries); ?> entries</div></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="text-gray-400">No category data.</p><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/expenses/index.blade.php ENDPATH**/ ?>