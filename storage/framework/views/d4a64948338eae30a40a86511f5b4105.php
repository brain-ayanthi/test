<?php $showProduct = !isset($product); ?>
<p class="iv-muted" style="margin:12px 0">Before and balance are <strong>product-total ledger stock</strong> across all batches, including expired stock—not a single batch or available-only balance. Rows are shown newest first. Filters do not reset balances. Only Prescription, Purchase Invoice and Stock Adjustment changes are listed; other stock changes still count toward balances.</p>
<div class="iv-scroll"><table data-product-history="v2"><thead><tr><th>Date / time</th><?php if($showProduct): ?><th>Product / SKU</th><?php endif; ?><th>Transaction</th><th>Reference</th><th class="num">Before stock (pcs)</th><th class="num">Added (pcs)</th><th class="num">Reduced (pcs)</th><th class="num">Balance stock (pcs)</th></tr></thead><tbody>
<?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
 <td><?php echo e(\Illuminate\Support\Carbon::parse($m->occurred_at)->format('Y-m-d H:i:s')); ?></td>
 <?php if($showProduct): ?><td><?php if($m->product_deleted_at === null): ?><a class="iv-link" href="<?php echo e(route('products.show',$m->product_id)); ?>"><?php echo e($m->product_name); ?></a><?php else: ?><?php echo e($m->product_name); ?><?php endif; ?><small><?php echo e($m->sku); ?></small></td><?php endif; ?>
 <td><?php echo e(match($m->kind) { 'purchase'=>'Purchase Invoice', 'adjustment'=>'Stock Adjustment', 'prescription_edit_in'=>'Prescription — edit return', 'prescription_edit_out'=>'Prescription — additional issue', default=>'Prescription' }); ?></td>
 <td><?php echo e($m->reference); ?><?php if($m->related_reference): ?><small><?php echo e($m->related_reference); ?></small><?php endif; ?></td>
 <td class="num"><?php echo e(\App\Support\InventoryQuantity::display($m->product_before)); ?></td>
 <td class="num positive"><?php echo e(\App\Support\InventoryQuantity::display($m->quantity_in)); ?></td>
 <td class="num negative"><?php echo e(\App\Support\InventoryQuantity::display($m->quantity_out)); ?></td>
 <td class="num"><strong><?php echo e(\App\Support\InventoryQuantity::display($m->product_after)); ?></strong></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="<?php echo e($showProduct ? 8 : 7); ?>" class="empty">No Prescription, Purchase Invoice or Stock Adjustment changes match these filters.</td></tr><?php endif; ?>
</tbody></table></div>
<?php echo e($movements->onEachSide(2)->links('components.inventory-pagination')); ?>

<?php /**PATH D:\xampp\htdocs\clinicms\resources\views/components/inventory-movements.blade.php ENDPATH**/ ?>