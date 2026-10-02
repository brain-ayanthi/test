<?php $__env->startSection('title', 'Stock Adjustments'); ?>
<?php $__env->startSection('page-title', 'Stock Adjustments'); ?>
<?php
    $batchUrl = route('stock-adjustments.batches', ['product' => '__PRODUCT__']);
?>
<?php $__env->startPush('styles'); ?>
<style>
.sa{max-width:1450px;margin:auto;color:#334155}.sa *{box-sizing:border-box}.sa h2{font-size:24px;font-weight:750;color:#0f172a}.sa h3{font-size:17px;font-weight:700;color:#1e293b}.sa p{margin:5px 0}.sa-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px}.sa-badge{font-size:12px;background:#dbeafe;color:#1e40af;padding:7px 12px;border-radius:30px;font-weight:600}.sa-muted{font-size:12px;color:#64748b}.sa-grid{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:20px}.sa-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:22px;margin-bottom:20px}.sa-card h3{margin-bottom:14px}.sa-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.sa label{display:block;font-size:13px;font-weight:600;color:#475569}.sa input:not([type=checkbox]):not([type=radio]),.sa select,.sa textarea{width:100%;padding:10px 11px;border:1px solid #cbd5e1;border-radius:7px;background:#fff;color:#0f172a;font-size:14px;margin-top:5px}.sa input:disabled,.sa select:disabled,.sa textarea:disabled{background:#f1f5f9}.sa input:focus,.sa select:focus,.sa textarea:focus{outline:2px solid #bfdbfe;outline-offset:1px}.sa-full{grid-column:1/-1}.sa-modes{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.sa-mode{border:1px solid #cbd5e1;border-radius:8px;padding:12px;cursor:pointer}.sa-mode:has(input:checked){border-color:#2563eb;background:#eff6ff;color:#1d4ed8}.sa-mode span{display:block;font-size:11px;font-weight:400;margin-top:5px}.sa-mode input{margin-right:5px}.sa-warning{padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;color:#92400e;font-size:12px;margin:14px 0}.sa-info{padding:12px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;color:#1e40af;font-size:12px;margin:14px 0}.sa-check{display:flex!important;align-items:flex-start;gap:9px;font-size:12px!important;font-weight:400!important;line-height:1.6}.sa-check input{margin-top:4px;flex-shrink:0}.sa-btn{display:inline-block;border:1px solid #cbd5e1;border-radius:7px;background:#fff;padding:10px 15px;color:#334155;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none}.sa-btn:hover{background:#f8fafc}.sa-btn:disabled{opacity:.45;cursor:not-allowed}.sa-primary{background:#2563eb!important;color:#fff;border-color:#2563eb}.sa-actions{display:flex;justify-content:flex-end;align-items:center;gap:10px;flex-wrap:wrap;margin-top:18px}.sa-value{font-size:29px;font-weight:750;color:#0f172a;line-height:1.4;overflow-wrap:anywhere}.sa-summary-row{padding:14px 0;border-bottom:1px solid #e2e8f0}.sa-summary-row:last-child{border:0}.sa-summary-row small{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#64748b}.sa-positive{color:#047857}.sa-negative{color:#b91c1c}.sa-table-wrap{overflow-x:auto}.sa table{border-collapse:collapse;width:100%;font-size:12px;min-width:1180px}.sa th{text-align:left;background:#f8fafc;color:#64748b;padding:12px;border-bottom:1px solid #e2e8f0;white-space:nowrap}.sa td{padding:13px 12px;border-bottom:1px solid #e2e8f0;vertical-align:top}.sa td small{display:block;margin-top:3px;color:#64748b}.sa .sa-num{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}.sa details p{white-space:pre-wrap;overflow-wrap:anywhere;max-width:330px}.sa summary{cursor:pointer;color:#2563eb}.sa-filters{display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:10px;align-items:end;margin-bottom:16px}.sa-filters input,.sa-filters select{font-size:12px!important;padding:8px!important}.sa-error{padding:14px;border:1px solid #fecaca;border-radius:8px;background:#fef2f2;color:#991b1b;font-size:13px;margin-bottom:16px;white-space:pre-wrap}.sa [hidden]{display:none!important}.sa-pending{border-color:#f59e0b;background:#fffbeb}.sa-pending p{font-size:13px}.sa-pending code{font-size:11px;word-break:break-all}.sa-tabs{display:flex;gap:8px;margin-bottom:16px;border-bottom:2px solid #e2e8f0}.sa-tab{background:none;border:0;border-bottom:3px solid transparent;margin-bottom:-2px;padding:10px 16px;font-size:14px;font-weight:650;color:#64748b;cursor:pointer}.sa-tab:hover{color:#334155}.sa-tab[aria-selected=true]{color:#1d4ed8;border-bottom-color:#2563eb}.sa-tab-count{display:inline-block;margin-left:6px;padding:2px 7px;border-radius:20px;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:700}.sa-bulk-table td{vertical-align:top}.sa-bulk-table select,.sa-bulk-table input{width:100%;padding:6px 8px;font-size:12px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#0f172a;margin-top:4px}.sa-bulk-table .sa-col-prod{min-width:230px}.sa-bulk-table .sa-col-batch{min-width:190px}.sa-bulk-table .sa-col-mode{min-width:120px}.sa-bulk-table .sa-col-qty{min-width:110px}.sa-bulk-table .sa-col-reason{min-width:150px}.sa-bulk-table .sa-col-ref{min-width:120px}.sa-bulk-table .sa-col-num{min-width:88px;text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}.sa-row-expired{margin-top:6px;padding:6px 8px;border-radius:6px;background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:11px}.sa-row-expired label{display:flex;gap:6px;align-items:flex-start;font-weight:400;margin-top:4px;line-height:1.5}.sa-row-remove{background:none;border:0;color:#b91c1c;cursor:pointer;font-size:15px;padding:4px 8px}.sa-row-bad{background:#fef2f2}.sa-row-ok{background:#f0fdf4}.sa-bulk-sum{display:flex;gap:18px;flex-wrap:wrap;align-items:center;margin:14px 0;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:12px}.sa-bulk-sum strong{color:#0f172a}.sa-legend{display:flex;gap:14px;align-items:center;flex-wrap:wrap}.sa fieldset{min-width:0;border:0;padding:0;margin:0}.sa label em{color:#dc2626;font-style:normal}@media(max-width:1050px){.sa-grid{grid-template-columns:1fr}.sa-filters{grid-template-columns:1fr 1fr}}@media(max-width:600px){.sa-card{padding:16px}.sa-fields{grid-template-columns:1fr}.sa-modes{grid-template-columns:1fr}.sa-filters{grid-template-columns:1fr}.sa h2{font-size:21px}}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<div class="sa">
    <div class="sa-head"><div><h2><i class="fas fa-sliders-h" style="color:#2563eb;margin-right:8px"></i>Stock Adjustments</h2><p class="sa-muted">Correct existing batch quantities, with a recorded reason and adjustment history.</p></div><span class="sa-badge"><i class="fas fa-shield-alt"></i> Admin &amp; Doctor only</span></div>
    <?php if($errors->any()): ?><div class="sa-error"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($error); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>
    <div id="saErrors" class="sa-error" hidden role="alert" tabindex="-1"></div>
    <div id="saPending" class="sa-card sa-pending" hidden role="status">
        <h3>Previous submission needs confirmation</h3><p>The server may already have saved this adjustment. Do not start another one. Retry the same request safely or check the history in another tab.</p><p>Request: <code id="saPendingId"></code></p>
        <div class="sa-actions"><a href="<?php echo e(route('stock-adjustments.index')); ?>#adjustment-history" target="_blank" rel="noopener" class="sa-btn">Check history in another tab</a><button id="saRetry" type="button" class="sa-btn sa-primary">Retry same request</button></div>
    </div>
    <noscript><div class="sa-error">JavaScript is required. Stock cannot be changed from this form without it.</div></noscript>
    <?php if($bulk['enabled']): ?><div class="sa-tabs" role="tablist" aria-label="Adjustment type">
        <button type="button" class="sa-tab" id="saTabSingle" role="tab" aria-controls="saPanelSingle" aria-selected="true">Single adjustment</button>
        <button type="button" class="sa-tab" id="saTabBulk" role="tab" aria-controls="saPanelBulk" aria-selected="false">Bulk adjustment<span class="sa-tab-count">up to <?php echo e($bulk['maxItems']); ?></span></button>
    </div><?php endif; ?>
    <div id="saPanelSingle" role="tabpanel" aria-labelledby="saTabSingle">
    <form id="saForm" action="<?php echo e(route('stock-adjustments.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <fieldset id="saFields">
        <div class="sa-grid">
            <section class="sa-card"><h3>New adjustment</h3>
                <div class="sa-fields">
                    <label class="sa-full">Find a product<input id="saSearch" type="search" placeholder="Search by name, SKU or strength" autocomplete="off"></label>
                    <label class="sa-full">Product <em>*</em><select id="saProduct" name="product_id" required><option value="">Select a product</option><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?> <?php echo e($p->strength); ?> · <?php echo e($p->sku); ?><?php echo e(!$p->is_active ? ' (inactive)' : ''); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                    <label class="sa-full">Batch <em>*</em><select id="saBatch" name="batch_id" required disabled><option value="">Select a product first</option></select><p class="sa-muted" id="saBatchStatus">Zero-stock and expired batches are included for reconciliation.</p><button type="button" id="saRefresh" class="sa-btn" disabled style="margin-top:8px">Refresh batches</button></label>
                    <div id="saExpired" class="sa-warning sa-full" hidden><strong>This batch is expired.</strong> Correcting its quantity does not make it available for dispensing.<label class="sa-check" style="margin-top:8px"><input id="saConfirmExpired" name="confirm_expired" type="checkbox" value="1">I confirm that I am adjusting an expired batch.</label></div>
                    <div class="sa-full"><label style="margin-bottom:8px">Adjustment method <em>*</em></label><div class="sa-modes">
                        <label class="sa-mode"><input type="radio" name="mode" value="set" checked>Set stock count<span>Enter the correct remaining count</span></label>
                        <label class="sa-mode"><input type="radio" name="mode" value="increase">Increase stock<span>Add a specific quantity</span></label>
                        <label class="sa-mode"><input type="radio" name="mode" value="decrease">Decrease stock<span>Subtract a specific quantity</span></label>
                    </div></div>
                    <label><span id="saAmountLabel">Counted unallocated stock</span> <em>*</em><input id="saAmount" name="amount" type="text" inputmode="decimal" pattern="[0-9]{1,10}(\.[0-9]{1,2})?" maxlength="13" required placeholder="e.g. 120.50"><p class="sa-muted">Stock unit: <strong id="saUnit">Select a batch</strong> · Up to 2 decimal places</p></label>
                    <label>Reason <em>*</em><select id="saReason" name="reason" required><option value="">Select a reason</option><?php $__currentLoopData = $reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($code); ?>"><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                    <label class="sa-full">Reference (optional)<input id="saReference" name="reference" maxlength="100" placeholder="Stocktake sheet / incident reference"></label>
                    <label class="sa-full">Explanation <em>*</em><textarea id="saNotes" name="notes" rows="3" required minlength="3" maxlength="2000" placeholder="Explain why the recorded quantity needs to change. Do not enter patient details."></textarea></label>
                    <div class="sa-full sa-info">This changes <strong>batch stock only</strong>. It does not create a purchase, reverse a prescription, change opening quantities/prices, or post cash/profit entries.</div>
                    <label class="sa-check sa-full"><input id="saConfirmAvailable" name="confirm_available" type="checkbox" value="1" required>I have checked the product, batch and stock unit. This is unallocated stock; I have excluded stock already deducted for prescriptions and am not using this as a prescription return/cancellation.</label>
                </div>
                <div class="sa-actions"><span class="sa-muted">Changes and audit history save together.</span><button id="saSave" type="submit" disabled class="sa-btn sa-primary"><i class="fas fa-check"></i> Confirm adjustment</button></div>
            </section>
            <aside><div class="sa-card"><h3>Adjustment preview</h3><p id="saProductInfo" class="sa-muted">Select a product and batch.</p>
                <div class="sa-summary-row"><small>Selected batch &middot; stock before</small><div id="saBefore" class="sa-value">—</div><p id="saExpiry" class="sa-muted"></p></div>
                <div class="sa-summary-row"><small>Change</small><div id="saChange" class="sa-value">—</div></div>
                <div class="sa-summary-row"><small>Selected batch &middot; stock after</small><div id="saAfter" class="sa-value">—</div></div>
                <div class="sa-summary-row" style="border-top:2px solid #e2e8f0"><small>Product total stock &middot; all batches before</small><div id="saProductBefore" class="sa-value">—</div><p id="saProductNote" class="sa-muted"></p></div>
                <div class="sa-summary-row"><small>Product total stock &middot; all batches after</small><div id="saProductAfter" class="sa-value">—</div></div>
                <p id="saPreviewStatus" class="sa-muted">Preview only. Stock is checked again when you save.</p>
            </div><div class="sa-card"><h3>Before saving</h3><p class="sa-muted">1. Recount the correct batch.</p><p class="sa-muted">2. Verify the unit (pieces, ml, etc.).</p><p class="sa-muted">3. Enter a reason and explanation.</p><p class="sa-muted">4. Review batch before / change / after and the product total.</p><p class="sa-muted" style="margin-top:12px">If stock moves while this page is open, the server rejects the stale adjustment and asks you to refresh. History cannot be edited or deleted through this page.</p></div></aside>
        </div>
        </fieldset>
    </form>
    </div>
    <?php if($bulk['enabled']): ?><div id="saPanelBulk" role="tabpanel" aria-labelledby="saTabBulk" hidden>
    <form id="saBulkForm" action="<?php echo e(route('stock-adjustments.bulk')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <fieldset id="saBulkFields">
        <section class="sa-card"><h3>Bulk adjustment &middot; up to <?php echo e($bulk['maxItems']); ?> rows</h3>
            <p class="sa-muted">One row per batch. Every row is checked before anything is saved &mdash; if any row fails, <strong>nothing</strong> is saved and nothing changes in stock.</p>
            <div class="sa-fields">
                <label class="sa-full">Find a product<input id="saBulkSearch" type="search" placeholder="Search by name, SKU or strength" autocomplete="off"></label>
                <label class="sa-full">Add a product <em>*</em>
                    <div style="display:flex;gap:8px;align-items:center;margin-top:5px">
                        <select id="saBulkProduct" style="flex:1"><option value="">Select a product</option><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?> <?php echo e($p->strength); ?> · <?php echo e($p->sku); ?><?php echo e(!$p->is_active ? ' (inactive)' : ''); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                        <button type="button" id="saBulkAdd" class="sa-btn sa-primary" disabled><i class="fas fa-plus"></i> Add row</button>
                        <button type="button" id="saBulkRefresh" class="sa-btn" disabled title="Reload the current quantity and revision for every row">Refresh quantities</button>
                    </div>
                    <p class="sa-muted" id="saBulkStatus">Zero-stock and expired batches are included for reconciliation.</p>
                </label>
            </div>
            <div class="sa-table-wrap"><table class="sa-bulk-table"><thead><tr>
                <th>#</th><th class="sa-col-prod">Product</th><th class="sa-col-batch">Batch</th><th class="sa-col-mode">Method</th>
                <th class="sa-col-qty">Quantity</th><th class="sa-col-reason">Reason</th><th class="sa-col-ref">Reference</th>
                <th class="sa-col-num">Batch before</th><th class="sa-col-num">Batch after</th>
                <th class="sa-col-num">Product total after</th><th></th>
            </tr></thead><tbody id="saBulkRows"></tbody></table></div>
            <p id="saBulkEmpty" class="sa-muted" style="margin-top:12px">No rows yet. Select a product above and press <strong>Add row</strong>.</p>
            <div class="sa-bulk-sum" id="saBulkSummary" hidden></div>
            <label class="sa-full">Explanation <em>*</em><textarea id="saBulkNotes" rows="3" maxlength="2000" placeholder="One explanation recorded on every row of this submission. Explain why the recorded quantities need to change. Do not enter patient details."></textarea><p class="sa-muted">Recorded on every row. Minimum 3 characters.</p></label>
            <div class="sa-full sa-info">This changes <strong>batch stock only</strong>. It does not create a purchase, reverse a prescription, change opening quantities/prices, or post cash/profit entries. All rows and the audit history save in one transaction.</div>
            <label class="sa-check sa-full"><input id="saBulkConfirmAvailable" type="checkbox" value="1">I have checked the product, batch and stock unit for every row. This is unallocated stock; I have excluded stock already deducted for prescriptions and am not using this as a prescription return/cancellation.</label>
            <div id="saBulkExpiredNote" class="sa-warning sa-full" hidden><strong>Expired batches in this submission.</strong> Correcting their quantity does not make them available for dispensing. Each expired row needs its own confirmation below.</div>
            <div class="sa-actions"><span id="saBulkHint" class="sa-muted">Rows are saved together or not at all.</span><button id="saBulkSave" type="submit" disabled class="sa-btn sa-primary"><i class="fas fa-check"></i> Save all adjustments</button></div>
        </section>
        </fieldset>
    </form>
    <div id="saBulkPending" class="sa-card sa-pending" hidden role="status">
        <h3>Previous bulk submission needs confirmation</h3><p>The server may already have saved these adjustments. Do not start another submission. Retry the same request safely or check the history in another tab.</p><p>Request: <code id="saBulkPendingId"></code></p>
        <div class="sa-actions"><a href="<?php echo e(route('stock-adjustments.index')); ?>#adjustment-history" target="_blank" rel="noopener" class="sa-btn">Check history in another tab</a><button id="saBulkRetry" type="button" class="sa-btn sa-primary">Retry same submission</button></div>
    </div>
    </div><?php endif; ?>
    <section class="sa-card" id="adjustment-history">
        <div class="sa-head"><div><h3 style="margin-bottom:3px">Adjustment history</h3><p class="sa-muted"><?php echo e($adjustments->total()); ?> matching entries · Timezone: <?php echo e(config('app.timezone')); ?> &middot; Batch columns are the selected batch; product totals cover all batches of that product, including expired stock.</p></div><div class="sa-legend"><span class="sa-muted sa-positive">+ Increase</span><span class="sa-muted sa-negative">− Decrease</span></div></div>
        <form method="GET" action="<?php echo e(route('stock-adjustments.index')); ?>" class="sa-filters">
            <label>Search<input name="search" value="<?php echo e(request('search')); ?>" maxlength="100" placeholder="Product, SKU, batch, user, reference or ADJ ID"></label>
            <label>Method<select name="mode"><option value="">All methods</option><?php $__currentLoopData = ['set'=>'Set count','increase'=>'Increase','decrease'=>'Decrease']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php echo e(request('mode') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label>From<input name="from" type="date" value="<?php echo e(request('from')); ?>"></label><label>To<input name="to" type="date" value="<?php echo e(request('to')); ?>"></label>
            <div style="display:flex;gap:6px"><button class="sa-btn" type="submit">Filter</button><a href="<?php echo e(route('stock-adjustments.index')); ?>#adjustment-history" class="sa-btn">Reset</a></div>
        </form>
        <div class="sa-table-wrap"><table><thead><tr><th>Adjustment / time</th><th>Product / batch</th><th>Method</th><th class="sa-num">Batch before</th><th class="sa-num">Change</th><th class="sa-num">Batch after</th><th class="sa-num">Product total before</th><th class="sa-num">Product total after</th><th>Reason / details</th><th>Adjusted by</th></tr></thead><tbody>
        <?php $__empty_1 = true; $__currentLoopData = $adjustments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr><td><strong>ADJ-<?php echo e(str_pad((string) $a->id, 6, '0', STR_PAD_LEFT)); ?></strong><small><?php echo e(\Illuminate\Support\Carbon::parse($a->created_at)->format('d M Y H:i:s')); ?></small></td>
            <td><strong><?php echo e($a->product_name); ?></strong><small><?php echo e($a->sku); ?> · #<?php echo e($a->batch_id); ?> <?php echo e($a->batch_number); ?></small><small>Expiry: <?php echo e($a->expiry_date); ?> · <?php echo e($a->stock_unit); ?></small></td>
            <td><?php echo e(['set'=>'Set count','increase'=>'Increase','decrease'=>'Decrease'][$a->mode] ?? $a->mode); ?></td>
            <td class="sa-num"><?php echo e(number_format((float) $a->quantity_before, 2)); ?></td><td class="sa-num <?php echo e((float) $a->quantity_change > 0 ? 'sa-positive' : 'sa-negative'); ?>"><strong><?php echo e((float) $a->quantity_change > 0 ? '+' : ''); ?><?php echo e(number_format((float) $a->quantity_change, 2)); ?></strong></td><td class="sa-num"><strong><?php echo e(number_format((float) $a->quantity_after, 2)); ?></strong></td><td class="sa-num"><?php if($a->product_quantity_before === null): ?>&mdash;<?php else: ?><?php echo e(number_format((float) $a->product_quantity_before, 2)); ?><?php endif; ?></td><td class="sa-num"><?php if($a->product_quantity_after === null): ?>&mdash;<?php else: ?><?php echo e(number_format((float) $a->product_quantity_after, 2)); ?><?php endif; ?></td>
            <td><details><summary><?php echo e($reasons[$a->reason] ?? $a->reason); ?></summary><p><?php echo e($a->notes); ?></p><?php if($a->reference): ?><small>Reference: <?php echo e($a->reference); ?></small><?php endif; ?><small>Entered quantity: <?php echo e($a->entered_quantity); ?></small><?php if($a->expired_confirmed): ?><small>Expired-batch confirmation recorded</small><?php endif; ?><small>Request: <?php echo e($a->request_id); ?></small></details></td>
            <td><?php echo e($a->user_name); ?><small><?php echo e($a->user_roles); ?></small></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="10" style="text-align:center;padding:32px;color:#94a3b8">No stock adjustments match these filters.</td></tr><?php endif; ?>
        </tbody></table></div><div style="margin-top:18px"><?php echo e($adjustments->links()); ?></div>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
window.StockAdjustmentConfig = {
    batchUrl: <?php echo json_encode($batchUrl, 15, 512) ?>, saveUrl: <?php echo json_encode(route('stock-adjustments.store'), 15, 512) ?>,
    requestId: <?php echo json_encode($requestId, 15, 512) ?>, userId: <?php echo json_encode(auth()->id(), 15, 512) ?>, csrf: <?php echo json_encode(csrf_token(), 15, 512) ?>,
    bulk: <?php echo json_encode($bulk['enabled'] ? ['url' => route('stock-adjustments.bulk'), 'maxItems' => $bulk['maxItems'], 'requestId' => $bulk['requestId']] : null) ?>,
    reasons: <?php echo json_encode($reasons, 15, 512) ?>
};
</script>
<script src="<?php echo e(asset('js/stock-adjustments.js')); ?>?v=20260930-4"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/stock-adjustments/index.blade.php ENDPATH**/ ?>