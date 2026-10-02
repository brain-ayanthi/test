<?php $__env->startSection('title', 'Edit Prescription'); ?>
<?php $__env->startSection('page-title', 'Edit Prescription'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.rx-edit{max-width:1400px;margin:auto;color:#1e293b}.rx-edit *{box-sizing:border-box}.rx-edit h2{font-size:22px;font-weight:700}.rx-edit h3{font-size:17px;font-weight:700}.rx-edit p{margin:5px 0}.rx-edit .rx-card{background:#fff;border:1px solid #dbe3ed;border-radius:12px;padding:20px;margin-bottom:18px}.rx-edit .rx-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap}.rx-edit .rx-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px}.rx-edit label{display:block;font-size:12px;font-weight:600;color:#475569}.rx-edit input,.rx-edit select,.rx-edit textarea{display:block;width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px;background:#fff;color:#0f172a;font-size:14px;min-width:0;margin-top:4px}.rx-edit textarea{min-height:65px}.rx-edit input:disabled,.rx-edit select:disabled{background:#f1f5f9;color:#64748b}.rx-edit .rx-btn{display:inline-block;border:1px solid #cbd5e1;border-radius:7px;padding:8px 12px;background:#fff;color:#334155;cursor:pointer;font-size:13px;font-weight:600;text-decoration:none}.rx-edit .rx-primary{background:#0f766e;color:white;border-color:#0f766e}.rx-edit .rx-danger{color:#b91c1c;border-color:#fecaca}.rx-edit .rx-btn:disabled{opacity:.5;cursor:not-allowed}.rx-edit .rx-item{border:1px solid #e2e8f0;border-radius:9px;padding:14px;margin:12px 0;background:#f8fafc}.rx-edit .rx-row-title{margin-bottom:10px}.rx-edit .rx-note{font-size:12px;color:#64748b}.rx-edit .rx-alert{padding:13px 16px;border-radius:8px;margin-bottom:14px;background:#fffbeb;border:1px solid #fcd34d;color:#854d0e}.rx-edit .rx-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}.rx-edit .rx-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}.rx-edit .rx-summary{display:flex;gap:20px;flex-wrap:wrap;padding:14px 0}.rx-edit .rx-summary strong{display:block;font-size:19px}.rx-edit .rx-save{position:sticky;bottom:0;background:#fff;box-shadow:0 -4px 20px #0f172a0d;z-index:10}.rx-edit ul{padding-left:20px;list-style:disc}.rx-edit .rx-section{margin:18px 0 10px}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="rx-edit">
    <div class="rx-head" style="margin-bottom:18px">
        <div><h2>Edit <?php echo e($prescription->prescription_number); ?></h2><p class="rx-note">Keep the original prescription number. Review every change before saving.</p></div>
        <div>
            <a class="rx-btn" href="<?php echo e(route('prescriptions.index')); ?>">All Prescriptions</a>
            <a class="rx-btn" href="<?php echo e(route('prescriptions.show', $prescription)); ?>">View / Print</a>
            <a class="rx-btn" href="<?php echo e(route('prescriptions.edit', $prescription)); ?>">Reload saved data</a>
        </div>
    </div>
    <?php if(session('success')): ?><div class="rx-alert rx-success" role="status"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="rx-alert rx-error" role="alert"><strong>Changes were not saved.</strong><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
    <?php endif; ?>
    <?php if(count($legacyIds)): ?>
        <div class="rx-alert"><strong>Historical stock protection:</strong> marked medicines have no complete batch history. You may edit their instructions, dose text and prices, but cannot change their product/quantity or remove them. An administrator must reconcile historical stock separately; this editor does not guess the original batches.</div>
    <?php endif; ?>
    <noscript><div class="rx-alert rx-error">JavaScript is required for this form. No changes can be saved without it.</div></noscript>
    <form id="rx-edit-form" action="<?php echo e(route('prescriptions.update', $prescription)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <input type="hidden" name="payload" id="rx-payload">
        <div class="rx-card"><h3 class="rx-row-title">Prescription details</h3><div class="rx-grid" id="rx-header"></div>
            <p class="rx-note" style="margin-top:12px">Status: <?php echo e(ucfirst($prescription->status)); ?> · Sale: <?php echo e(ucfirst($prescription->sale_status)); ?> · Paid: LKR <?php echo e(number_format($prescription->paid_amount, 2)); ?>. Payments are read-only here.</p>
        </div>
        <div id="rx-patients"></div>
        <button class="rx-btn" type="button" id="rx-add-patient">+ Add patient</button>
        <div class="rx-card rx-save" style="margin-top:18px">
            <div class="rx-summary" id="rx-summary" aria-live="polite"></div>
            <div class="rx-head"><span class="rx-note">Preview only. The server validates totals and saves stock in one transaction.</span><button class="rx-btn rx-primary" type="submit" id="rx-save" disabled>Save changes</button></div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(() => {
    'use strict';
    const state = <?php echo json_encode($seed, 15, 512) ?>;
    const patients = <?php echo json_encode($patients, 15, 512) ?>;
    const doctors = <?php echo json_encode($doctors, 15, 512) ?>;
    const products = <?php echo json_encode($products, 15, 512) ?>;
    const tests = <?php echo json_encode($radiologyTests, 15, 512) ?>;
    const legacy = new Set(<?php echo json_encode($legacyIds, 15, 512) ?>);
    const paid = Number(<?php echo json_encode($prescription->paid_amount, 15, 512) ?>);
    const form = document.getElementById('rx-edit-form');
    const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const cents = n => Math.round(Number(n || 0) * 100);
    const money = n => (n / 100).toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2});
    const locked = i => legacy.has(Number(i.id));
    let dirty = false, submitting = false;
    state.patients = Array.isArray(state.patients) ? state.patients : [];
    state.patients.forEach(p => { p.items = p.items || []; p.radiologies = p.radiologies || []; });
    function options(rows, current, label = r => r.name, blank = 'Select…') {
        return `<option value="">${escape(blank)}</option>` + rows.map(r => `<option value="${escape(r.id)}" ${String(current) === String(r.id) ? 'selected' : ''}>${escape(label(r))}</option>`).join('');
    }
    function attrs(path) { return `data-path="${escape(path)}"`; }
    function input(label, path, value, type='text', extra='') {
        return `<label>${escape(label)}<input ${attrs(path)} type="${type}" value="${escape(value)}" ${extra}></label>`;
    }
    function area(label, path, value) {
        return `<label>${escape(label)}<textarea ${attrs(path)} maxlength="10000">${escape(value)}</textarea></label>`;
    }
    function select(label, path, html, extra='') {
        return `<label>${escape(label)}<select ${attrs(path)} ${extra}>${html}</select></label>`;
    }
    const amount = 'min="0" max="1000000" step="0.01" required';
    function render() {
        document.getElementById('rx-header').innerHTML =
            input('Prescription date','prescription_date',String(state.prescription_date || '').slice(0,10),'date','required') +
            input('Tax (LKR)','tax',state.tax || 0,'number',amount) +
            input('Next visit / follow-up','next_visit',state.next_visit,'text','maxlength="1000"') +
            ['diagnosis','lab_workup','precautions','physiotherapy','notes'].map(k => area(k.replaceAll('_',' ').replace(/^./, c => c.toUpperCase()), k, state[k])).join('');
        document.getElementById('rx-patients').innerHTML = state.patients.map((p, pi) => {
            const root = `patients.${pi}`;
            return `<section class="rx-card"><div class="rx-head rx-row-title"><h3>Patient ${pi + 1}</h3><button type="button" class="rx-btn rx-danger" data-action="remove-patient" data-p="${pi}" ${p.items.some(locked) ? 'disabled title="Contains historical stock items"' : ''}>Remove patient</button></div>
            <div class="rx-grid">` +
            select('Patient',`${root}.patient_id`,options(patients,p.patient_id,r=>`${r.name} (${r.patient_code || r.id})`),'required') +
            select('Doctor',`${root}.doctor_id`,options(doctors,p.doctor_id,r=>r.name,'No doctor')) +
            input('Doctor fee (LKR)',`${root}.doctor_fee`,p.doctor_fee ?? 0,'number',amount) +
            input('Patient discount (LKR)',`${root}.discount`,p.discount ?? 0,'number',amount) +
            area('Diagnosis',`${root}.diagnosis`,p.diagnosis) + area('Precautions',`${root}.precautions`,p.precautions) +
            input('Next visit / follow-up',`${root}.next_visit`,p.next_visit,'text','maxlength="1000"') + `</div>
            <div class="rx-head rx-section"><h3>Medicines</h3><button type="button" class="rx-btn" data-action="add-item" data-p="${pi}">+ Add medicine</button></div>
            <p class="rx-note">Quantity is explicit, in the product’s stock unit. Changing strength, timing or days does not recalculate it. Verify the prescribed quantity, especially fractional tablets and liquids.</p>` +
            p.items.map((i, ii) => {
                const path = `${root}.items.${ii}`, isLocked = locked(i);
                return `<div class="rx-item"><div class="rx-head rx-row-title"><strong>Medicine ${ii + 1}${isLocked ? ' · Historical stock locked' : ''}</strong><button class="rx-btn rx-danger" type="button" data-action="remove-item" data-p="${pi}" data-i="${ii}" ${isLocked ? 'disabled' : ''}>Remove</button></div><div class="rx-grid">` +
                select('Product',`${path}.product_id`,options(products,i.product_id,r=>`${r.name}${r.strength ? ' — '+r.strength : ''}${!r.is_active || r.deleted_at ? ' (inactive)' : ''}`),isLocked ? 'disabled' : 'required') +
                input('Printed medicine name',`${path}.drug_name`,i.drug_name,'text','required maxlength="255"') +
                input('Strength / dose text',`${path}.strength`,i.strength,'text','maxlength="255"') +
                select('Timing',`${path}.timing`,['OD','BD','BID','TDS','QID'].map(t=>`<option ${i.timing===t?'selected':''}>${t}</option>`).join(''),'required') +
                input('Duration (days)',`${path}.duration_days`,i.duration_days,'number','required min="1" max="3650" step="1"') +
                input('Quantity (stock units)',`${path}.quantity`,i.quantity,'number',`required min="0.01" max="10000" step="0.01" ${isLocked?'disabled':''}`) +
                input('Unit price (LKR)',`${path}.unit_price`,i.unit_price,'number',amount) +
                input('Line discount (LKR)',`${path}.discount`,i.discount ?? 0,'number',amount) +
                input('Meal relation',`${path}.meal_relation`,i.meal_relation,'text','maxlength="255"') +
                input('Instruction',`${path}.instruction`,i.instruction,'text','maxlength="255"') +
                area('Instruction note',`${path}.instruction_note`,i.instruction_note) +
                `</div><p class="rx-note" data-line="${pi}-${ii}"></p></div>`;
            }).join('') +
            `<div class="rx-head rx-section"><h3>Radiology</h3><button type="button" class="rx-btn" data-action="add-rad" data-p="${pi}">+ Add test</button></div>` +
            p.radiologies.map((r, ri) => {
                const path = `${root}.radiologies.${ri}`;
                return `<div class="rx-item"><div class="rx-grid">` +
                select('Catalog test (optional)',`${path}.radiology_test_id`,options(tests,r.radiology_test_id,r=>r.name,'Custom test')) +
                input('Test name',`${path}.test_name`,r.test_name,'text','required maxlength="255"') +
                input('Price (LKR)',`${path}.price`,r.price,'number',amount) +
                area('Notes',`${path}.notes`,r.notes) + `</div><button style="margin-top:10px" type="button" class="rx-btn rx-danger" data-action="remove-rad" data-p="${pi}" data-r="${ri}">Remove test</button></div>`;
            }).join('') + `<p class="rx-note" data-patient-total="${pi}"></p></section>`;
        }).join('');
        updateTotals();
    }
    function lineTotal(i) { return Math.round(cents(i.quantity) * cents(i.unit_price) / 100) - cents(i.discount); }
    function updateTotals() {
        let medicine=0, doctor=0, radiology=0, discount=0;
        state.patients.forEach((p,pi)=>{
            let pm=0, pr=0;
            p.items.forEach((i,ii)=>{
                const total = lineTotal(i); pm += total;
                const el = document.querySelector(`[data-line="${pi}-${ii}"]`);
                if (el) el.textContent = `Line total: LKR ${money(total)}`;
            });
            p.radiologies.forEach(r=>pr+=cents(r.price));
            medicine+=pm; radiology+=pr; doctor+=cents(p.doctor_fee); discount+=cents(p.discount);
            const el=document.querySelector(`[data-patient-total="${pi}"]`);
            if(el) el.textContent=`Patient total: LKR ${money(pm+pr+cents(p.doctor_fee)-cents(p.discount))}`;
        });
        const total=medicine+doctor+radiology-discount+cents(state.tax);
        document.getElementById('rx-summary').innerHTML = [['Medicines',medicine],['Doctor fees',doctor],['Radiology',radiology],['Patient discounts',discount],['Tax',cents(state.tax)],['Total',total],['Due',Math.max(0,total-cents(paid))]].map(([label,v])=>`<div><span class="rx-note">${label}</span><strong>${money(v)}</strong></div>`).join('');
    }
    function setPath(path,value) {
        const parts=path.split('.'); let obj=state;
        for(let i=0;i<parts.length-1;i++) obj=obj[parts[i]];
        obj[parts.at(-1)] = value;
    }
    form.addEventListener('input',e=>{
        const path=e.target.dataset.path; if(!path) return;
        const nullableId=path.endsWith('.doctor_id') || path.endsWith('.radiology_test_id');
        setPath(path,nullableId && e.target.value==='' ? null : e.target.value);
        dirty=true; updateTotals();
    });
    form.addEventListener('change',e=>{
        const path=e.target.dataset.path; if(!path) return;
        const parts=path.split('.');
        // Set explicitly: not all browsers emit input for select changes.
        setPath(path,(path.endsWith('.doctor_id') || path.endsWith('.radiology_test_id')) && e.target.value==='' ? null : e.target.value);
        dirty=true;
        if(path.endsWith('.product_id')) {
            const item=state.patients[Number(parts[1])].items[Number(parts[3])];
            const product=products.find(p=>String(p.id)===e.target.value);
            if(product) {item.drug_name=product.name; item.strength=product.strength; item.unit_price=product.selling_price;}
            render();
        } else if(path.endsWith('.radiology_test_id')) {
            const rad=state.patients[Number(parts[1])].radiologies[Number(parts[3])];
            const test=tests.find(t=>String(t.id)===e.target.value);
            if(test) {rad.test_name=test.name; rad.price=test.price;}
            render();
        }
        updateTotals();
    });
    function newItem() {return {id:null,product_id:'',drug_name:'',strength:'',timing:'OD',duration_days:1,quantity:1,unit_price:0,discount:0,meal_relation:'',instruction:'',instruction_note:''};}
    document.getElementById('rx-add-patient').addEventListener('click',()=>{
        if(state.patients.length>=50) return alert('Maximum 50 patients per prescription.');
        state.patients.push({id:null,patient_id:'',doctor_id:null,doctor_fee:0,discount:0,diagnosis:'',precautions:'',next_visit:'',items:[newItem()],radiologies:[]});
        dirty=true; render();
    });
    form.addEventListener('click',e=>{
        const button=e.target.closest('[data-action]'); if(!button || button.disabled) return;
        const pi=Number(button.dataset.p), p=state.patients[pi];
        switch(button.dataset.action) {
            case 'remove-patient':
                if(state.patients.length===1) return alert('At least one patient is required.');
                if(p.items.some(locked)) return;
                if(!confirm('Remove this patient and all their medicines/tests when Save is pressed?')) return;
                state.patients.splice(pi,1); break;
            case 'add-item': if(p.items.length>=100) return; p.items.push(newItem()); break;
            case 'remove-item':
                if(p.items.length===1) return alert('At least one medicine per patient is required.');
                if(locked(p.items[Number(button.dataset.i)])) return;
                if(!confirm('Remove this medicine when Save is pressed?')) return;
                p.items.splice(Number(button.dataset.i),1); break;
            case 'add-rad': if(p.radiologies.length>=100) return; p.radiologies.push({id:null,radiology_test_id:null,test_name:'',price:0,notes:''}); break;
            case 'remove-rad': p.radiologies.splice(Number(button.dataset.r),1); break;
        }
        dirty=true; render();
    });
    form.addEventListener('submit',e=>{
        if(submitting) {e.preventDefault();return;}
        if(!state.patients.length || state.patients.some(p=>!p.items.length)) {e.preventDefault();alert('Each prescription needs at least one patient and one medicine per patient.');return;}
        document.getElementById('rx-payload').value=JSON.stringify(state);
        submitting=true;
        document.getElementById('rx-save').disabled=true;
        document.getElementById('rx-save').textContent='Saving…';
    });
    window.addEventListener('beforeunload',e=>{if(dirty && !submitting){e.preventDefault();e.returnValue='';}});
    window.addEventListener('pageshow',()=>{submitting=false;document.getElementById('rx-save').disabled=false;document.getElementById('rx-save').textContent='Save changes';});
    render();
    document.getElementById('rx-save').disabled=false;
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\clinicms\resources\views/prescriptions/edit.blade.php ENDPATH**/ ?>