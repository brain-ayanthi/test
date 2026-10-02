<?php $__env->startSection('title', $product->name.' — Stock History'); ?>
<?php $__env->startSection('page-title', 'Product Stock History'); ?>
<?php echo $__env->make('components.inventory-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->startSection('content'); ?>
<div class="iv" data-inventory-version="product-total-v2">
 <div class="iv-head"><div><h2><?php echo e($product->name); ?></h2><p class="iv-muted"><?php echo e($product->sku); ?> · <?php echo e($product->category_name ?? 'Uncategorized'); ?></p></div><div class="iv-tabs"><a class="iv-btn" href="<?php echo e(route('products.index')); ?>">Back to inventory</a><a class="iv-btn" href="<?php echo e(route('reports.inventory',['product_id'=>$product->id])); ?>">Inventory Report</a></div></div>
 <?php if($errors->any()): ?><div class="iv-errors"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($error); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>
 <section class="iv-card"><div class="iv-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
  <div class="iv-stat"><small>Available stock (pcs)</small><strong><?php echo e(\App\Support\InventoryQuantity::display($product->available_pcs)); ?></strong></div>
  <div class="iv-stat"><small>Available stock (Purchase Unit)</small><strong style="font-size:19px"><?php echo e(\App\Support\InventoryQuantity::packs($product->available_pcs,$product->pieces_per_purchase_unit,$product->purchase_unit)); ?></strong></div>
  <div class="iv-stat"><small>Total recorded stock (all batches, pcs)</small><strong><?php echo e(\App\Support\InventoryQuantity::display($product->recorded_pcs)); ?></strong></div>
 </div></section>
 <?php if($product->mismatched_batches>0): ?><div class="iv-info iv-warn">The tracked balance differs from current recorded stock. A stock change outside tracking may have occurred; review it before relying on this history.</div><?php endif; ?>
 <section class="iv-card"><div class="iv-head"><h3>Product stock history</h3><a class="iv-btn" href="<?php echo e(route('products.show',$product->id)); ?>">Reset</a></div>
 <p class="iv-muted">History from <?php echo e($state->started_at); ?> (<?php echo e(config('app.timezone')); ?>). Prescription · Purchase Invoice · Stock Adjustment.</p>
 <form method="GET" action="<?php echo e(route('products.show',$product->id)); ?>"><?php echo $__env->make('components.inventory-movement-filters', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?></form>
 <?php echo $__env->make('components.inventory-movements', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
 </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/inventory/products/show.blade.php ENDPATH**/ ?>