<?php if (! $__env->hasRenderedOnce('be8012b3-076c-4016-a97b-73a48ad3d3ba')): $__env->markAsRenderedOnce('be8012b3-076c-4016-a97b-73a48ad3d3ba'); ?>
<style>.inv-pagination{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;margin-top:18px;font-size:13px}.inv-pagination .pages{display:flex;gap:5px;flex-wrap:wrap}.inv-pagination a,.inv-pagination span.page{display:inline-block;padding:7px 11px;border:1px solid #cbd5e1;border-radius:6px;background:white;text-decoration:none;color:#334155}.inv-pagination .current{background:#2563eb!important;color:#fff!important;border-color:#2563eb!important}.inv-pagination .disabled{opacity:.45}.inv-pagination .dots{padding:7px 3px}</style>
<?php endif; ?>
<nav class="inv-pagination" aria-label="Pagination">
 <div><?php echo e($paginator->firstItem() ?? 0); ?>–<?php echo e($paginator->lastItem() ?? 0); ?> of <?php echo e($paginator->total()); ?> records</div>
 <div class="pages">
 <?php if($paginator->onFirstPage()): ?><span class="page disabled" aria-disabled="true">Previous</span><?php else: ?><a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">Previous</a><?php endif; ?>
 <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php if(is_string($element)): ?><span class="dots"><?php echo e($element); ?></span><?php endif; ?>
  <?php if(is_array($element)): ?><?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page=>$url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <?php if($page==$paginator->currentPage()): ?><span class="page current" aria-current="page"><?php echo e($page); ?></span><?php else: ?><a href="<?php echo e($url); ?>" aria-label="Go to page <?php echo e($page); ?>"><?php echo e($page); ?></a><?php endif; ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
 <?php if($paginator->hasMorePages()): ?><a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">Next</a><?php else: ?><span class="page disabled" aria-disabled="true">Next</span><?php endif; ?>
 </div>
</nav>
<?php /**PATH D:\xampp\htdocs\clinicms\resources\views/components/inventory-pagination.blade.php ENDPATH**/ ?>