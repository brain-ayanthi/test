(() => {
    'use strict';
    const config = window.StockAdjustmentConfig;
    const $ = id => document.getElementById(id);
    const form = $('saForm'), productSelect = $('saProduct');
    const originalOptions = [...productSelect.options].map(o => ({value:o.value, text:o.textContent}));
    const storageKey = 'clinicms-stock-adjustment-pending:' + config.userId;
    let product = null, batches = [], selectedBatch = null, loading = false, submitting = false;
    let productRecorded = null, productAvailable = null;
    let sequence = 0, aborter = null, pendingPayload = null;
    const MAX = 999999999999;
    function minor(value, signed=false) {
        const s=String(value).trim();
        if (!(signed ? /^-?\d{1,10}(?:\.\d{1,2})?$/ : /^\d{1,10}(?:\.\d{1,2})?$/).test(s)) return null;
        const negative=s.startsWith('-'), parts=s.replace(/^-/, '').split('.');
        const n=Number(parts[0])*100+Number((parts[1]||'').padEnd(2,'0'));
        return n>MAX ? null : (negative?-n:n);
    }
    const display=n => n===null ? '—' : (n/100).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    const mode=()=>form.elements.mode.value;
    function error(messages) { $('saErrors').textContent=messages.join('\n');$('saErrors').hidden=false;$('saErrors').focus(); }
    function pendingView() {
        $('saPending').hidden=!pendingPayload;
        $('saPendingId').textContent=pendingPayload?.request_id || '';
        $('saFields').disabled=!!pendingPayload || submitting;
        $('saRetry').disabled=submitting;
        preview();
    }
    function persistPending(payload) {
        try { sessionStorage.setItem(storageKey,JSON.stringify(payload)); }
        catch (_) {
            error(['This browser cannot retain the request for safe retry. No new request was sent.',
                'Enable browser session storage before saving/retrying. Do not replace an unresolved previous request with a new adjustment.']);
            return false;
        }
        pendingPayload=payload;
        pendingView();
        return true;
    }
    function clearPending() {
        pendingPayload=null;
        try { sessionStorage.removeItem(storageKey); } catch (_) {}
        pendingView();
    }
    function preview() {
        const before=selectedBatch?minor(selectedBatch.quantity,true):null;
        const amount=minor($('saAmount').value);
        const m=mode();
        $('saAmountLabel').textContent=m==='set'?'Counted unallocated stock':m==='increase'?'Quantity to add':'Quantity to subtract';
        let after=null, delta=null;
        if(before!==null && amount!==null){after=m==='set'?amount:m==='increase'?before+amount:before-amount;delta=after-before;}
        $('saBefore').textContent=display(before);$('saAfter').textContent=display(after);
        $('saChange').textContent=delta===null?'—':(delta>0?'+':'')+display(delta);
        $('saChange').classList.toggle('sa-positive',delta>0);$('saChange').classList.toggle('sa-negative',delta<0);
        $('saAfter').classList.toggle('sa-negative',after!==null && after<0);
        const productAfter=productRecorded===null||delta===null?null:productRecorded+delta;
        $('saProductBefore').textContent=display(productRecorded);
        $('saProductAfter').textContent=display(productAfter);
        $('saProductAfter').classList.toggle('sa-negative',productAfter!==null && productAfter<0);
        $('saProductNote').textContent=productRecorded===null?'':
            'All batches of this product, including expired stock. Available now: '+display(productAvailable)+' '+(product?product.unit:'');
        const valid=selectedBatch && amount!==null && after>=0 && after<=MAX && delta!==0 && (m==='set'||amount>0);
        $('saSave').disabled=!!pendingPayload || submitting || loading || !valid;
        $('saRefresh').disabled=loading || submitting || !!pendingPayload || !productSelect.value;
        let message='Preview only. Stock is checked again when you save.';
        if(before!==null && before<0)message='The existing balance is negative. Reconcile it to a verified non-negative quantity.';
        if(after!==null && after<0)message='This would make stock negative. Reduce the amount or use the correct count.';
        else if(after!==null && after>MAX)message='Result exceeds the database quantity limit.';
        else if(delta===0)message='No change: an adjustment is not needed.';
        $('saPreviewStatus').textContent=message;
    }
    function selectBatch() {
        selectedBatch=batches.find(b=>String(b.id)===$('saBatch').value)||null;
        $('saConfirmExpired').checked=false;$('saConfirmAvailable').checked=false;
        $('saExpired').hidden=!selectedBatch?.expired;
        $('saConfirmExpired').required=!!selectedBatch?.expired;
        $('saExpiry').textContent=selectedBatch?'Batch #'+selectedBatch.id+' · '+selectedBatch.batch_number+' · Expiry '+selectedBatch.expiry_date:'';
        preview();
    }
    async function loadBatches(preserveId=null) {
        if(pendingPayload || submitting)return;
        const id=productSelect.value;
        sequence++;const ticket=sequence;if(aborter)aborter.abort();aborter=new AbortController();
        product=null;batches=[];selectedBatch=null;loading=!!id;productRecorded=null;productAvailable=null;
        $('saBatch').replaceChildren(new Option(id?'Loading batches…':'Select a product first',''));
        $('saBatch').disabled=true;$('saUnit').textContent='Select a batch';$('saProductInfo').textContent='Select a product and batch.';
        $('saExpiry').textContent='';$('saExpired').hidden=true;$('saConfirmExpired').required=false;
        $('saConfirmAvailable').checked=false;preview();
        if(!id){$('saBatchStatus').textContent='Select a product to load its existing batches.';return;}
        $('saBatchStatus').textContent='Loading current quantities…';
        try {
            const res=await fetch(config.batchUrl.replace('__PRODUCT__',encodeURIComponent(id)),{
                credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'},signal:aborter.signal
            });
            if(res.redirected || res.status===401 || res.status===419)throw new Error('Session expired. Reload this page after signing in.');
            if(res.status===403)throw new Error('Only active Admin and Doctor users may use Stock Adjustments.');
            if(!res.ok || !(res.headers.get('content-type')||'').includes('application/json'))throw new Error('Could not load batches. Check the routes/database installation, then retry.');
            const json=await res.json();if(ticket!==sequence || productSelect.value!==id)return;
            if(String(json.product?.id)!==id || !Array.isArray(json.batches))throw new Error('Unexpected batch response. Please refresh.');
            product=json.product;batches=json.batches;
            productRecorded=minor(product.recorded_quantity,true);
            productAvailable=minor(product.available_quantity,true);
            $('saBatch').replaceChildren(new Option(batches.length?'Select a batch':'No batches found',''));
            batches.forEach(b=>$('saBatch').add(new Option(`#${b.id} · ${b.batch_number} · ${b.expiry_date} · ${b.quantity} ${product.unit}${b.expired?' · EXPIRED':''}`,String(b.id))));
            $('saBatch').disabled=!batches.length;
            $('saUnit').textContent=product.unit;
            $('saProductInfo').textContent=product.name+' · '+product.sku+(product.is_active?'':' · Inactive product');
            $('saBatchStatus').textContent=batches.length?'Each option is a separate batch record. Refresh after stock movements.':'No existing batches. Receive stock through the normal purchase/opening-stock workflow first.';
            if(preserveId && batches.some(b=>String(b.id)===String(preserveId)))$('saBatch').value=preserveId;
            selectBatch();
        } catch(e) {
            if(ticket!==sequence || e.name==='AbortError')return;
            $('saBatchStatus').textContent=e.message;
        } finally {if(ticket===sequence){loading=false;preview();}}
    }
    async function send(payload) {
        if(submitting)return;
        submitting=true;
        if(!persistPending(payload)){submitting=false;pendingView();return;}
        $('saErrors').hidden=true;
        $('saRetry').textContent='Checking / saving…';
        try {
            const res=await fetch(config.saveUrl,{
                method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':config.csrf},
                body:JSON.stringify(payload)
            });
            const isJson=(res.headers.get('content-type')||'').includes('application/json');
            if(res.redirected || !isJson)throw new Error('Unexpected response or expired session. Reload/sign in, then retry this same pending request.');
            const json=await res.json();
            if(res.ok && json.success && json.redirect_url){
                clearPending();window.location.assign(json.redirect_url);return;
            }
            if(res.status===422){
                // A validation rejection is definitive: the attempted transaction did
                // not save an adjustment. Refresh the snapshot and require confirmation.
                const batchId=payload.batch_id;clearPending();submitting=false;$('saFields').disabled=false;
                const messages=Object.values(json.errors||{}).flat();
                error(messages.length?messages:[json.message||'Please correct the form.']);
                if(json.errors?.revision || json.errors?.batch_id || json.errors?.product_id)await loadBatches(batchId);
                $('saConfirmAvailable').checked=false;
                return;
            }
            const message=res.status===403?'Access denied. Only active Admin and Doctor users may adjust stock.'
                :res.status===419||res.status===401?'Session expired. Reload after signing in; the pending request will be retained for safe retry.'
                :res.status===409?(json.message||'This request conflicts with an existing adjustment. Check history.')
                :'The result could not be confirmed. The server may already have saved it.';
            throw new Error(message);
        } catch(e) {
            error([e.message,'Do not create a new adjustment for the same change. Retry the same request, or verify its request ID in the history.']);
        } finally {
            submitting=false;$('saRetry').textContent='Retry same request';pendingView();
        }
    }
    form.addEventListener('submit',event=>{
        event.preventDefault();if(submitting || pendingPayload || loading || !selectedBatch || !form.reportValidity())return;
        const before=minor(selectedBatch.quantity,true),amount=minor($('saAmount').value),m=mode();
        if(amount===null)return;
        const after=m==='set'?amount:m==='increase'?before+amount:before-amount;
        if(after<0 || after>MAX || after===before || (m!=='set'&&amount===0))return;
        const productAfterLine=productRecorded===null?'':`\nProduct total (all batches): ${display(productRecorded)} → ${display(productRecorded+(after-before))} ${product.unit}`;
        if(!confirm(`Adjust ${product.name} · ${product.sku}\nBatch #${selectedBatch.id}: ${selectedBatch.batch_number}\n${display(before)} → ${display(after)} ${product.unit}${productAfterLine}\n\nThis will be recorded permanently in adjustment history. Continue?`))return;
        const payload={
            request_id:config.requestId,product_id:String(product.id),batch_id:String(selectedBatch.id),mode:m,
            amount:$('saAmount').value.trim(),expected_quantity:selectedBatch.quantity,revision:selectedBatch.revision,
            reason:$('saReason').value,notes:$('saNotes').value.trim(),reference:$('saReference').value.trim(),
            confirm_available:$('saConfirmAvailable').checked?1:0,confirm_expired:$('saConfirmExpired').checked?1:0
        };
        send(payload);
    });
    $('saRetry').addEventListener('click',()=>{if(pendingPayload)send(pendingPayload);});
    productSelect.addEventListener('change',()=>loadBatches());
    $('saBatch').addEventListener('change',selectBatch);
    $('saRefresh').addEventListener('click',()=>loadBatches($('saBatch').value));
    $('saAmount').addEventListener('input',preview);
    form.querySelectorAll('[name="mode"]').forEach(r=>r.addEventListener('change',preview));
    $('saSearch').addEventListener('input',()=>{
        const selected=productSelect.value,q=$('saSearch').value.trim().toLowerCase();
        productSelect.replaceChildren();originalOptions.filter(o=>!o.value||o.value===selected||o.text.toLowerCase().includes(q)).forEach(o=>productSelect.add(new Option(o.text,o.value)));
        productSelect.value=selected;
    });
    window.addEventListener('beforeunload',event=>{if(submitting){event.preventDefault();event.returnValue='';}});
    try {
        const saved=JSON.parse(sessionStorage.getItem(storageKey)||'null');
        if(saved && typeof saved.request_id==='string')pendingPayload=saved;
    } catch (_) {}
    window.addEventListener('pageshow',event=>{if(event.persisted)window.location.reload();});

    // =====================================================================
    // Bulk adjustments: up to StockAdjustmentBulkService::MAX_ITEMS rows,
    // every row validated first, saved in one atomic transaction.
    // =====================================================================
    const escapeHtml = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    if (config.bulk) {
        const bulk = config.bulk;
        const bulkForm = $('saBulkForm'), bulkTbody = $('saBulkRows'), bulkSelect = $('saBulkProduct');
        const bulkOriginalOptions = [...bulkSelect.options].map(o => ({value:o.value, text:o.textContent}));
        const bulkStorageKey = 'clinicms-stock-adjustment-bulk-pending:' + config.userId;
        const cache = new Map();
        let rows = [], bulkPending = null, bulkPendingMeta = [], bulkSubmitting = false, bulkSeq = 0;

        // ---- numbers -------------------------------------------------------
        function computeAll() {
            const running = {};
            const out = [];
            for (const row of rows) {
                const b = row.batches.find(x => String(x.id) === String(row.batchId)) || null;
                const before = b ? minor(b.quantity, true) : null;
                const amount = minor(row.amount || '');
                let after = null, delta = null;
                if (before !== null && amount !== null) {
                    after = row.mode === 'set' ? amount : row.mode === 'increase' ? before + amount : before - amount;
                    delta = after - before;
                }
                // The product total runs forward across the rows of this submission,
                // exactly as the server records it, so the preview matches history.
                let base = Object.prototype.hasOwnProperty.call(running, row.productId)
                    ? running[row.productId]
                    : (row.product ? minor(row.product.recorded_quantity, true) : null);
                if (base === null || base === undefined) base = null;
                const productAfter = (base !== null && delta !== null) ? base + delta : null;
                let why = '';
                if (!b) why = 'Select a batch.';
                else if (amount === null) why = row.mode === 'set' ? 'Enter the counted quantity.' : 'Enter a quantity.';
                else if (row.mode !== 'set' && amount === 0) why = 'Increase and decrease must be greater than zero.';
                else if (after < 0) why = 'This would make stock negative.';
                else if (after > MAX) why = 'Result exceeds the database quantity limit.';
                else if (delta === 0) why = 'No change: this row is not needed.';
                else if (b.expired && !row.confirmExpired) why = 'Confirm the expired batch.';
                else if (!row.reason) why = 'Select a reason.';
                running[row.productId] = (base === null || delta === null) ? base : base + delta;
                out.push({batch: b, before, after, delta, productAfter, valid: why === '', why});
            }
            return out;
        }

        function duplicateBatches() {
            const seen = new Map(), dupes = [];
            rows.forEach((r, i) => {
                if (!r.batchId) return;
                if (seen.has(r.batchId)) dupes.push(`#${r.batchId} (rows ${seen.get(r.batchId)} and ${i + 1})`);
                else seen.set(r.batchId, i + 1);
            });
            return dupes;
        }

        // ---- rendering -----------------------------------------------------
        function rowHtml(row, n, info) {
            const b = info.batch;
            const batchOptions = row.batches.length
                ? row.batches.map(x => `<option value="${x.id}"${String(x.id) === String(row.batchId) ? ' selected' : ''}>#${x.id} &middot; ${escapeHtml(x.batch_number)} &middot; ${x.expiry_date} &middot; ${x.quantity} ${escapeHtml(row.product.unit || '')}${x.expired ? ' &middot; EXPIRED' : ''}</option>`).join('')
                : '<option value="">No batches found</option>';
            const reasonOptions = Object.entries(config.reasons)
                .map(([code, label]) => `<option value="${code}"${row.reason === code ? ' selected' : ''}>${escapeHtml(label)}</option>`).join('');
            const expired = b && b.expired
                ? `<div class="sa-row-expired"><strong>Expired batch.</strong> Correcting it does not make it available for dispensing.<label><input type="checkbox" class="sa-row-confirm-expired"${row.confirmExpired ? ' checked' : ''}> I confirm that I am adjusting an expired batch.</label></div>`
                : '';
            const rowError = row.error ? `<div class="sa-row-expired" style="background:#fef2f2;border-color:#fecaca;color:#991b1b">${escapeHtml(row.error)}</div>` : '';
            return `<tr data-key="${row.key}" class="${info.valid ? 'sa-row-ok' : 'sa-row-bad'}" title="${escapeHtml(info.why)}">
                <td>${n}</td>
                <td class="sa-col-prod"><strong>${escapeHtml(row.product.name)}</strong><small>${escapeHtml(row.product.sku)}${row.product.is_active ? '' : ' &middot; Inactive product'}</small></td>
                <td class="sa-col-batch"><select class="sa-row-batch" aria-label="Batch for row ${n}">${batchOptions}</select>${expired}${rowError}</td>
                <td class="sa-col-mode"><select class="sa-row-mode" aria-label="Method for row ${n}"><option value="set"${row.mode === 'set' ? ' selected' : ''}>Set count</option><option value="increase"${row.mode === 'increase' ? ' selected' : ''}>Increase</option><option value="decrease"${row.mode === 'decrease' ? ' selected' : ''}>Decrease</option></select></td>
                <td class="sa-col-qty"><input class="sa-row-amount" type="text" inputmode="decimal" pattern="[0-9]{1,10}(\.[0-9]{1,2})?" maxlength="13" value="${escapeHtml(row.amount || '')}" placeholder="${row.mode === 'set' ? 'Counted stock' : 'Quantity'}" aria-label="Quantity for row ${n}"></td>
                <td class="sa-col-reason"><select class="sa-row-reason" aria-label="Reason for row ${n}"><option value="">Select a reason</option>${reasonOptions}</select></td>
                <td class="sa-col-ref"><input class="sa-row-reference" type="text" maxlength="100" value="${escapeHtml(row.reference || '')}" placeholder="Optional" aria-label="Reference for row ${n}"></td>
                <td class="sa-col-num">${display(info.before)}</td>
                <td class="sa-col-num${info.after !== null && info.after < 0 ? ' sa-negative' : ''}">${display(info.after)}</td>
                <td class="sa-col-num">${display(info.productAfter)}</td>
                <td><button type="button" class="sa-row-remove" title="Remove row ${n}" aria-label="Remove row ${n}">&times;</button></td>
            </tr>`;
        }

        function paintNumbers(infos) {
            rows.forEach((row, i) => {
                const tr = bulkTbody.querySelector(`tr[data-key="${row.key}"]`);
                if (!tr) return;
                const info = infos[i], cells = tr.children;
                tr.classList.toggle('sa-row-ok', info.valid);
                tr.classList.toggle('sa-row-bad', !info.valid);
                cells[7].textContent = display(info.before);
                cells[8].textContent = display(info.after);
                cells[8].classList.toggle('sa-negative', info.after !== null && info.after < 0);
                cells[9].textContent = display(info.productAfter);
            });
        }

        function renderBulk() {
            const infos = computeAll();
            bulkTbody.replaceChildren();
            $('saBulkEmpty').hidden = rows.length > 0;
            if (rows.length) {
                const frag = document.createDocumentFragment();
                rows.forEach((row, i) => {
                    const holder = document.createElement('tbody');
                    holder.innerHTML = rowHtml(row, i + 1, infos[i]);
                    frag.appendChild(holder.firstElementChild);
                });
                bulkTbody.appendChild(frag);
            }
            paintBulk(infos);
        }

        function paintBulk(infos) {
            infos = infos || computeAll();
            paintNumbers(infos);
            const bad = infos.filter(i => !i.valid).length;
            const dupes = duplicateBatches();
            const products = new Set(rows.map(r => String(r.productId)));
            const expired = rows.some((r, i) => infos[i].batch && infos[i].batch.expired);
            $('saBulkExpiredNote').hidden = !expired;
            $('saBulkSummary').hidden = rows.length === 0;
            $('saBulkSummary').innerHTML =
                `<span><strong>${rows.length}</strong> row${rows.length === 1 ? '' : 's'}</span>` +
                `<span><strong>${products.size}</strong> product${products.size === 1 ? '' : 's'}</span>` +
                (rows.length ? (bad ? `<span style="color:#b91c1c"><strong>${bad}</strong> row${bad === 1 ? '' : 's'} need attention</span>`
                    : `<span style="color:#047857">All rows ready</span>`) : '') +
                (dupes.length ? `<span style="color:#b91c1c">Duplicate batch: ${escapeHtml(dupes.join('; '))}</span>` : '');
            const notesOk = $('saBulkNotes').value.trim().length >= 3;
            const confirmOk = $('saBulkConfirmAvailable').checked;
            const ready = !bulkSubmitting && !bulkPending && rows.length > 0 && rows.length <= bulk.maxItems
                && bad === 0 && dupes.length === 0 && notesOk && confirmOk;
            $('saBulkSave').disabled = !ready;
            $('saBulkAdd').disabled = bulkSubmitting || !!bulkPending || rows.length >= bulk.maxItems || !bulkSelect.value;
            $('saBulkRefresh').disabled = bulkSubmitting || !!bulkPending || rows.length === 0;
            $('saBulkFields').disabled = !!bulkPending || bulkSubmitting;
            $('saBulkRetry').disabled = bulkSubmitting;
            $('saBulkPending').hidden = !bulkPending;
            $('saBulkPendingId').textContent = bulkPending?.request_id || '';
            $('saBulkHint').textContent = rows.length >= bulk.maxItems
                ? `Maximum ${bulk.maxItems} rows reached.`
                : rows.length === 0 ? 'Rows are saved together or not at all.'
                    : bad || dupes.length || !notesOk || !confirmOk ? 'Fix the highlighted rows, the explanation and the confirmation before saving.'
                        : `${rows.length} row(s) will be saved in one transaction.`;
        }

        // ---- data ----------------------------------------------------------
        async function loadProduct(productId, force = false) {
            const key = String(productId);
            if (!force && cache.has(key)) return cache.get(key);
            const ticket = ++bulkSeq;
            $('saBulkStatus').textContent = 'Loading current quantities…';
            try {
                const res = await fetch(config.batchUrl.replace('__PRODUCT__', encodeURIComponent(key)), {
                    credentials: 'same-origin', cache: 'no-store', headers: {Accept: 'application/json'}
                });
                if (res.redirected || res.status === 401 || res.status === 419) throw new Error('Session expired. Reload this page after signing in.');
                if (res.status === 403) throw new Error('Only active Admin and Doctor users may use Stock Adjustments.');
                if (res.status === 404) throw new Error('That product is no longer available.');
                if (!res.ok || !(res.headers.get('content-type') || '').includes('application/json')) throw new Error('Could not load batches. Check the routes/database installation, then retry.');
                const json = await res.json();
                if (String(json.product?.id) !== key || !Array.isArray(json.batches)) throw new Error('Unexpected batch response. Please refresh.');
                const entry = {product: json.product, batches: json.batches};
                cache.set(key, entry);
                if (ticket === bulkSeq) $('saBulkStatus').textContent = json.batches.length
                    ? 'Each row is a separate batch record. Refresh after stock movements.'
                    : 'No existing batches. Receive stock through the normal purchase/opening-stock workflow first.';
                return entry;
            } catch (e) {
                if (ticket === bulkSeq) $('saBulkStatus').textContent = e.message;
                throw e;
            }
        }

        async function addRow(productId) {
            if (!productId || rows.length >= bulk.maxItems || bulkSubmitting || bulkPending) return;
            try {
                const entry = await loadProduct(productId);
                if (!entry.batches.length) { $('saBulkStatus').textContent = 'That product has no batches to adjust.'; return; }
                rows.push({
                    key: 'r' + Date.now().toString(36) + '-' + rows.length, productId: String(productId),
                    product: entry.product, batches: entry.batches.slice(), batchId: '', mode: 'set',
                    amount: '', reason: '', reference: '', confirmExpired: false, error: ''
                });
                renderBulk();
            } catch (_) { /* the status line already explains the failure */ }
        }

        async function reloadRow(row) {
            try {
                const entry = await loadProduct(row.productId, true);
                row.product = entry.product;
                row.batches = entry.batches.slice();
                row.batchId = entry.batches.some(b => String(b.id) === String(row.batchId)) ? row.batchId : '';
                row.confirmExpired = false;
                row.error = '';
                renderBulk();
            } catch (_) {}
        }

        // ---- pending / submit ---------------------------------------------
        function persistBulkPending(payload, meta) {
            try { sessionStorage.setItem(bulkStorageKey, JSON.stringify({payload, meta})); }
            catch (_) {
                error(['This browser cannot retain the submission for safe retry. No new request was sent.',
                    'Enable browser session storage before saving/retrying. Do not replace an unresolved previous submission with a new one.']);
                return false;
            }
            bulkPending = payload;
            bulkPendingMeta = meta;
            paintBulk();
            return true;
        }

        function clearBulkPending() {
            bulkPending = null;
            bulkPendingMeta = [];
            try { sessionStorage.removeItem(bulkStorageKey); } catch (_) {}
            paintBulk();
        }

        function applyBulkErrors(errors, message) {
            const perRow = {}, general = [];
            for (const [key, list] of Object.entries(errors || {})) {
                const m = key.match(/^items\.(\d+)\.(.+)$/);
                const flat = [].concat(list);
                if (m) (perRow[Number(m[1])] = perRow[Number(m[1])] || []).push(...flat);
                else general.push(...flat);
            }
            rows.forEach((r, i) => { r.error = perRow[i] ? perRow[i].join(' ') : ''; });
            renderBulk();
            error(general.length ? general : [message || 'Some rows could not be saved. Nothing was saved. Fix the highlighted rows and try again.']);
            const stale = Object.keys(errors || {}).filter(k => /^items\.\d+\.revision$/.test(k))
                .map(k => Number(k.match(/^items\.(\d+)\./)[1]));
            const reload = async () => { for (const i of stale) if (rows[i]) await reloadRow(rows[i]); };
            reload();
        }

        async function bulkSend(payload, meta) {
            if (bulkSubmitting) return;
            bulkSubmitting = true;
            if (!persistBulkPending(payload, meta)) { bulkSubmitting = false; paintBulk(); return; }
            $('saErrors').hidden = true;
            $('saBulkRetry').textContent = 'Checking / saving…';
            try {
                const res = await fetch(bulk.url, {
                    method: 'POST', credentials: 'same-origin',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrf},
                    body: JSON.stringify(payload)
                });
                const isJson = (res.headers.get('content-type') || '').includes('application/json');
                if (res.redirected || !isJson) throw new Error('Unexpected response or expired session. Reload/sign in, then retry this same pending submission.');
                const json = await res.json();
                if (res.ok && json.success && json.redirect_url) {
                    clearBulkPending();
                    rows = [];
                    renderBulk();
                    window.location.assign(json.redirect_url);
                    return;
                }
                if (res.status === 422) {
                    // A validation rejection is definitive: nothing in this submission saved.
                    clearBulkPending();
                    bulkSubmitting = false;
                    applyBulkErrors(json.errors || {}, json.message);
                    return;
                }
                throw new Error(res.status === 403 ? 'Access denied. Only active Admin and Doctor users may adjust stock.'
                    : res.status === 419 || res.status === 401 ? 'Session expired. Reload after signing in; the pending submission will be retained for safe retry.'
                    : res.status === 409 ? (json.message || 'This submission conflicts with an existing adjustment. Check history.')
                    : 'The result could not be confirmed. The server may already have saved these adjustments.');
            } catch (e) {
                error([e.message, 'Do not create a new submission for the same changes. Retry the same request, or verify its request ID in the history.']);
            } finally {
                bulkSubmitting = false;
                $('saBulkRetry').textContent = 'Retry same submission';
                paintBulk();
            }
        }

        // ---- wiring --------------------------------------------------------
        bulkForm.addEventListener('submit', event => {
            event.preventDefault();
            if (bulkSubmitting || bulkPending || !bulkForm.reportValidity()) return;
            const infos = computeAll();
            if (infos.some(i => !i.valid) || duplicateBatches().length) { renderBulk(); return; }
            const notes = $('saBulkNotes').value.trim();
            if (notes.length < 3) { error(['Enter an explanation of at least 3 characters. It is recorded on every row.']); return; }
            if (!$('saBulkConfirmAvailable').checked) { error(['Confirm that you have checked every product, batch and stock unit.']); return; }
            const items = rows.map((r, i) => ({
                product_id: r.productId, batch_id: String(r.batchId), mode: r.mode,
                amount: (r.amount || '').trim(), expected_quantity: infos[i].batch.quantity,
                revision: infos[i].batch.revision, reason: r.reason, notes,
                reference: (r.reference || '').trim(), confirm_available: 1, confirm_expired: r.confirmExpired ? 1 : 0
            }));
            const preview = rows.slice(0, 5).map((r, i) => `#${i + 1} ${r.product.name} · batch #${items[i].batch_id}: ${display(infos[i].before)} → ${display(infos[i].after)} ${r.product.unit}`).join('\n');
            const more = rows.length > 5 ? `\n…and ${rows.length - 5} more row(s)` : '';
            if (!confirm(`Save ${rows.length} adjustment(s) across ${new Set(rows.map(r => r.productId)).size} product(s)\n\n${preview}${more}\n\nAll rows save together. If any row is rejected, nothing is saved.\nThis will be recorded permanently in adjustment history. Continue?`)) return;
            bulkSend({request_id: bulk.requestId, items}, rows.map(r => ({
                productId: r.productId, batchId: r.batchId, mode: r.mode, amount: r.amount,
                reason: r.reason, reference: r.reference, confirmExpired: r.confirmExpired
            })));
        });

        $('saBulkRetry').addEventListener('click', () => {
            if (!bulkPending) return;
            bulkSend(bulkPending, bulkPendingMeta);
        });

        bulkTbody.addEventListener('change', event => {
            const tr = event.target.closest('tr[data-key]');
            if (!tr) return;
            const row = rows.find(r => r.key === tr.dataset.key);
            if (!row) return;
            row.error = '';
            if (event.target.classList.contains('sa-row-batch')) {
                row.batchId = event.target.value;
                row.confirmExpired = false;
                renderBulk();
            } else if (event.target.classList.contains('sa-row-mode')) {
                row.mode = event.target.value;
                renderBulk();
            } else if (event.target.classList.contains('sa-row-reason')) {
                row.reason = event.target.value;
                paintBulk();
            } else if (event.target.classList.contains('sa-row-confirm-expired')) {
                row.confirmExpired = event.target.checked;
                renderBulk();
            }
        });

        bulkTbody.addEventListener('input', event => {
            const tr = event.target.closest('tr[data-key]');
            if (!tr) return;
            const row = rows.find(r => r.key === tr.dataset.key);
            if (!row) return;
            if (event.target.classList.contains('sa-row-amount')) {
                row.amount = event.target.value;
                paintBulk();
            } else if (event.target.classList.contains('sa-row-reference')) {
                row.reference = event.target.value;
            }
        });

        bulkTbody.addEventListener('click', event => {
            const btn = event.target.closest('.sa-row-remove');
            if (!btn) return;
            const tr = btn.closest('tr[data-key]');
            rows = rows.filter(r => r.key !== tr.dataset.key);
            renderBulk();
        });

        $('saBulkAdd').addEventListener('click', () => addRow(bulkSelect.value));
        bulkSelect.addEventListener('change', () => { $('saBulkAdd').disabled = !bulkSelect.value || rows.length >= bulk.maxItems; });
        $('saBulkNotes').addEventListener('input', () => paintBulk());
        $('saBulkConfirmAvailable').addEventListener('change', () => paintBulk());
        $('saBulkSearch').addEventListener('input', () => {
            const selected = bulkSelect.value, q = $('saBulkSearch').value.trim().toLowerCase();
            bulkSelect.replaceChildren();
            bulkOriginalOptions.filter(o => !o.value || o.value === selected || o.text.toLowerCase().includes(q))
                .forEach(o => bulkSelect.add(new Option(o.text, o.value)));
            bulkSelect.value = selected;
            $('saBulkAdd').disabled = !bulkSelect.value || rows.length >= bulk.maxItems;
        });
        $('saBulkRefresh').addEventListener('click', async () => {
            if (bulkSubmitting || bulkPending) return;
            $('saBulkRefresh').disabled = true;
            const wanted = [...new Set(rows.map(r => r.productId))];
            for (const id of wanted) { try { await loadProduct(id, true); } catch (_) { break; } }
            for (const row of rows) {
                const entry = cache.get(String(row.productId));
                if (!entry) continue;
                row.product = entry.product;
                row.batches = entry.batches.slice();
                if (!entry.batches.some(b => String(b.id) === String(row.batchId))) row.batchId = '';
                row.confirmExpired = false;
                row.error = '';
            }
            renderBulk();
            $('saBulkStatus').textContent = 'Quantities refreshed from the server. Recheck each row before saving.';
        });

        // ---- tabs ----------------------------------------------------------
        function switchTab(which) {
            const single = which !== 'bulk';
            $('saPanelSingle').hidden = !single;
            $('saPanelBulk').hidden = single;
            $('saTabSingle').setAttribute('aria-selected', single ? 'true' : 'false');
            $('saTabBulk').setAttribute('aria-selected', single ? 'false' : 'true');
            if (single) $('saAmount').focus();
            else if (rows.length) $('saBulkSave').focus();
        }
        $('saTabSingle').addEventListener('click', () => switchTab('single'));
        $('saTabBulk').addEventListener('click', () => switchTab('bulk'));

        // ---- restore an unresolved submission so it can be retried ---------
        try {
            const saved = JSON.parse(sessionStorage.getItem(bulkStorageKey) || 'null');
            if (saved && Array.isArray(saved.payload?.items) && saved.payload.items.length) {
                bulkPending = saved.payload;
                bulkPendingMeta = saved.meta || [];
                (saved.meta || []).forEach((m, i) => {
                    if (!m) return;
                    const item = saved.payload.items[i] || {};
                    const batches = [{
                        id: item.batch_id, batch_number: '(restored)', expiry_date: '',
                        quantity: item.expected_quantity, expired: false, revision: item.revision
                    }];
                    rows.push({
                        key: 'restored-' + i, productId: String(m.productId),
                        product: {id: m.productId, name: 'Product #' + m.productId, sku: '', unit: '', is_active: true, recorded_quantity: '0', available_quantity: '0'},
                        batches, batchId: String(item.batch_id), mode: m.mode || item.mode,
                        amount: m.amount || item.amount, reason: m.reason || item.reason,
                        reference: m.reference || '', confirmExpired: !!m.confirmExpired, error: ''
                    });
                });
            }
        } catch (_) {}
        renderBulk();
        // An unresolved submission is the most urgent thing on the page: show it.
        if (bulkPending) switchTab('bulk');
    }

    pendingView();
})();
