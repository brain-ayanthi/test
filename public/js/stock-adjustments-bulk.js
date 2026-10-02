(() => {
    'use strict';
    const c=window.StockAdjustmentConfig,$=id=>document.getElementById(id),form=$('saForm');
    const storageKey='clinicms-stock-adjustment-pending:'+c.userId; // Also recover v1 single requests.
    const MAX=999999999999, rows=[];
    let pending=null,submitting=false,navigating=false,nextKey=0;
    const field=(r,k)=>r.el.querySelector('[data-field="'+k+'"]');
    const originalOptions=[...$('saRowTemplate').content.querySelector('[data-field=product]').options].map(o=>({value:o.value,text:o.textContent}));
    function minor(v,signed=false){const s=String(v).trim();if(!(signed?/^-?\d{1,10}(?:\.\d{1,2})?$/:/^\d{1,10}(?:\.\d{1,2})?$/).test(s))return null;const p=s.replace(/^-/,'').split('.'),n=Number(p[0])*100+Number((p[1]||'').padEnd(2,'0'));return n>MAX?null:(s.startsWith('-')?-n:n);}
    const display=n=>n===null?'—':(n/100).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    function error(messages){$('saErrors').textContent=messages.join('\n');$('saErrors').hidden=false;$('saErrors').focus();}
    function rowError(r,messages){field(r,'errors').textContent=messages.join('\n');field(r,'errors').hidden=!messages.length;r.el.classList.toggle('sa-invalid',!!messages.length);}
    function changed(r){$('saConfirmAvailable').checked=false;if(r){rowError(r,[]);r.el.querySelectorAll('[aria-invalid]').forEach(e=>e.removeAttribute('aria-invalid'));}previewAll();}
    function calculation(r){
        const before=r.batch?minor(r.batch.quantity,true):null,amount=minor(field(r,'amount').value),mode=field(r,'mode').value;
        const after=before===null||amount===null?null:mode==='set'?amount:mode==='increase'?before+amount:before-amount;
        const delta=after===null?null:after-before;
        return {before,amount,after,delta,valid:before!==null&&amount!==null&&after>=0&&after<=MAX&&delta!==0&&(mode==='set'||amount>0)};
    }
    function previewAll(){
        const counts=new Map();rows.forEach(r=>{if(r.batch)counts.set(r.batch.id,(counts.get(r.batch.id)||0)+1);});
        let good=!!rows.length,loading=false;
        rows.forEach((r,i)=>{
            const q=calculation(r),duplicate=r.batch&&counts.get(r.batch.id)>1;
            field(r,'heading').textContent='Batch row '+(i+1);
            field(r,'amountLabel').textContent=field(r,'mode').value==='set'?'Counted unallocated quantity':field(r,'mode').value==='increase'?'Quantity to add':'Quantity to subtract';
            field(r,'before').textContent=display(q.before);field(r,'change').textContent=q.delta===null?'—':(q.delta>0?'+':'')+display(q.delta);field(r,'after').textContent=display(q.after);
            field(r,'change').className=q.delta>0?'sa-positive':q.delta<0?'sa-negative':'';
            let msg='Stock is checked again when the complete list is saved.';
            if(q.before!==null&&q.before<0)msg='Negative legacy balance: reconcile to a verified non-negative quantity.';
            if(q.after!==null&&(q.after<0||q.after>MAX))msg='Result is outside the allowed stock quantity range.';
            else if(q.delta===0)msg='No change: remove this row or enter a different verified quantity.';
            if(field(r,'amount').value.trim()&&q.amount===null)msg='Use digits and at most two decimal places, with a dot (no commas or exponent notation).';
            if(duplicate)msg='This batch is selected more than once. Use only one row per batch.';
            field(r,'previewStatus').textContent=msg;field(r,'previewStatus').classList.toggle('sa-negative',!!duplicate||q.after<0||q.after>MAX||q.delta===0||!!(field(r,'amount').value.trim()&&q.amount===null));
            r.el.querySelector('[data-action=remove]').disabled=rows.length===1;
            r.el.querySelector('[data-action=refresh]').disabled=r.loading||!field(r,'product').value;
            good=good&&q.valid&&!duplicate&&!r.loading;loading=loading||r.loading;
        });
        const products=new Set(rows.map(r=>field(r,'product').value).filter(Boolean));
        $('saRowCount').textContent=rows.length+' batch row'+(rows.length===1?'':'s')+' · '+products.size+' product'+(products.size===1?'':'s');
        $('saSave').textContent='Save all '+rows.length+' adjustment'+(rows.length===1?'':'s');
        $('saSave').disabled=!!pending||submitting||!good;
        $('saAdd').disabled=$('saAddBottom').disabled=rows.length>=c.maxRows;
        $('saRefreshAll').disabled=loading||!products.size;
        $('saReadyStatus').textContent=good?'Review every row and confirm below.':'Complete valid, non-duplicate rows before saving.';
    }
    function pendingView(){
        $('saPending').hidden=!pending;$('saFields').disabled=!!pending||submitting;$('saRetry').disabled=submitting;
        $('saPendingId').textContent=pending?.request_id||'';
        const n=Array.isArray(pending?.items)?pending.items.length:1;
        $('saPendingSummary').textContent=pending?(Array.isArray(pending.items)?n+' adjustments are awaiting confirmation.':'A single adjustment from the previous version is awaiting confirmation.'):'';
        $('saPendingHistory').href=c.historyUrl+'?group='+encodeURIComponent(pending?.request_id||'')+'#adjustment-history';
        previewAll();
    }
    function remember(payload){try{sessionStorage.setItem(storageKey,JSON.stringify(payload));}catch(_){error(['Browser session storage is unavailable. No new request was sent. Enable storage before saving or retrying.']);return false;}pending=payload;pendingView();return true;}
    function clearPending(){pending=null;try{sessionStorage.removeItem(storageKey);}catch(_){}pendingView();}
    function defaults(r){field(r,'reason').value=$('saDefaultReason').value;field(r,'notes').value=$('saDefaultNotes').value;field(r,'reference').value=$('saDefaultReference').value;}
    function selectBatch(r){
        r.batch=r.batches.find(b=>String(b.id)===field(r,'batch').value)||null;
        field(r,'expiredPanel').hidden=!r.batch?.expired;field(r,'confirm_expired').required=!!r.batch?.expired;field(r,'confirm_expired').checked=false;
        changed(r);
    }
    async function loadBatches(r,batchId=null){
        const id=field(r,'product').value,ticket=++r.sequence;if(r.aborter)r.aborter.abort();r.aborter=new AbortController();
        r.product=null;r.batch=null;r.batches=[];r.loading=!!id;
        field(r,'batch').replaceChildren(new Option(id?'Loading batches…':'Select a product first',''));field(r,'batch').disabled=true;
        field(r,'unit').textContent='Select a batch';field(r,'expiredPanel').hidden=true;field(r,'confirm_expired').required=false;field(r,'confirm_expired').checked=false;
        field(r,'status').textContent=id?'Loading current batch quantities…':'Select a product. Zero-stock and expired batches are included.';changed(r);
        if(!id)return;
        try{
            const res=await fetch(c.batchUrl.replace('__PRODUCT__',encodeURIComponent(id)),{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'},signal:r.aborter.signal});
            if(res.redirected||res.status===401||res.status===419)throw new Error('Session expired. Sign in and reload.');
            if(res.status===403)throw new Error('Only active Admin and Doctor users may access stock adjustments.');
            if(!res.ok||!(res.headers.get('content-type')||'').includes('application/json'))throw new Error('Could not load batches. Check the product and try Refresh batch.');
            const json=await res.json();if(!r.alive||ticket!==r.sequence||field(r,'product').value!==id)return;
            if(String(json.product?.id)!==id||!Array.isArray(json.batches))throw new Error('Unexpected batch response. Refresh this row.');
            r.product=json.product;r.batches=json.batches;
            field(r,'batch').replaceChildren(new Option(r.batches.length?'Select a batch':'No existing batches',''));
            r.batches.forEach(b=>field(r,'batch').add(new Option(`#${b.id} · ${b.batch_number} · ${b.expiry_date} · ${b.quantity} ${r.product.unit}${b.expired?' · EXPIRED':''}`,String(b.id))));
            field(r,'batch').disabled=!r.batches.length;field(r,'unit').textContent=r.product.unit;
            field(r,'status').textContent=r.batches.length?'Each #ID is a separate batch record. Verify the unit and expiry before saving.':'No batches: use the existing stock-receiving workflow first.';
            if(batchId&&r.batches.some(b=>String(b.id)===String(batchId)))field(r,'batch').value=String(batchId);
            selectBatch(r);
        }catch(e){if(!r.alive||ticket!==r.sequence||e.name==='AbortError')return;field(r,'status').textContent=e.message;}
        finally{if(r.alive&&ticket===r.sequence){r.loading=false;previewAll();}}
    }
    function addRow(values=null){
        if(rows.length>=c.maxRows)return null;
        const el=$('saRowTemplate').content.firstElementChild.cloneNode(true),r={el,key:++nextKey,product:null,batch:null,batches:[],loading:false,alive:true,sequence:0,aborter:null};
        el.id='saRow-'+r.key;rows.push(r);$('saRows').append(el);defaults(r);
        // Labels wrap their own controls, and names are unique for native validity.
        el.querySelectorAll('input,select,textarea').forEach(e=>{e.name='row_'+r.key+'_'+e.dataset.field;});
        field(r,'product').addEventListener('change',()=>{field(r,'amount').value='';loadBatches(r);});
        field(r,'batch').addEventListener('change',()=>selectBatch(r));
        field(r,'search').addEventListener('input',()=>{const select=field(r,'product'),current=select.value,q=field(r,'search').value.trim().toLowerCase();select.replaceChildren();originalOptions.filter(o=>!o.value||o.value===current||o.text.toLowerCase().includes(q)).forEach(o=>select.add(new Option(o.text,o.value)));select.value=current;});
        ['amount','mode','reason','notes','reference','confirm_expired'].forEach(k=>field(r,k).addEventListener(k==='amount'||k==='notes'||k==='reference'?'input':'change',()=>changed(r)));
        el.querySelector('[data-action=refresh]').addEventListener('click',()=>loadBatches(r,field(r,'batch').value));
        el.querySelector('[data-action=remove]').addEventListener('click',()=>{if(rows.length===1||pending||submitting)return;if((r.batch||field(r,'amount').value||field(r,'notes').value)&&!confirm('Remove this unsaved row?'))return;r.alive=false;if(r.aborter)r.aborter.abort();rows.splice(rows.indexOf(r),1);el.remove();changed();});
        if(values){
            for(const k of ['mode','amount','reason','notes','reference'])field(r,k).value=values[k]??'';
            const id=String(values.product_id||'');if(id&&!originalOptions.some(o=>o.value===id))field(r,'product').add(new Option('Product #'+id+' (verify availability)',id));field(r,'product').value=id;
        }
        changed();return r;
    }
    async function restoreRows(payload){
        rows.forEach(r=>{r.alive=false;if(r.aborter)r.aborter.abort();});rows.length=0;$('saRows').replaceChildren();
        const items=Array.isArray(payload.items)?payload.items:[payload];
        const jobs=items.slice(0,c.maxRows).map(item=>{const r=addRow(item);return loadBatches(r,item.batch_id);});
        await Promise.all(jobs);$('saConfirmAvailable').checked=false;
    }
    function validationErrors(errors){
        const summary=['Nothing was saved. Correct the affected rows, review all previews and confirm again.'];
        const grouped=new Map();
        for(const [key,value] of Object.entries(errors||{})){
            const messages=Array.isArray(value)?value:[String(value)],match=key.match(/^items\.(\d+)\.(.+)$/);
            if(match){const i=Number(match[1]);summary.push(...messages.map(m=>'Row '+(i+1)+': '+m));grouped.set(i,[...(grouped.get(i)||[]),...messages]);const r=rows[i];if(r){const input=field(r,match[2]);if(input)input.setAttribute('aria-invalid','true');}}
            else summary.push(...messages);
        }
        grouped.forEach((messages,i)=>{if(rows[i])rowError(rows[i],messages);});error(summary);
    }
    async function send(payload){
        if(submitting)return;submitting=true;
        if(!remember(payload)){submitting=false;pendingView();return;}
        $('saErrors').hidden=true;$('saRetry').textContent='Checking / saving…';
        try{
            const res=await fetch(c.saveUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':c.csrf},body:JSON.stringify(payload)});
            if(res.redirected||!(res.headers.get('content-type')||'').includes('application/json'))throw new Error('Unexpected response or expired session. Sign in/reload and retry this same submission.');
            const json=await res.json();
            if(res.ok&&json.success&&json.redirect_url){navigating=true;clearPending();window.location.assign(json.redirect_url);return;}
            if(res.status===422){clearPending();await restoreRows(payload);validationErrors(json.errors||{submission:[json.message||'Please review the form.']});return;}
            throw new Error(res.status===403?'Access denied. Only active Admin/Doctor users may save adjustments.':res.status===401||res.status===419?'Session expired. Reload after signing in, then retry this pending submission.':res.status===409?(json.message||'Submission conflicts with saved history.'):'The server result could not be confirmed. It may already have saved all rows.');
        }catch(e){error([e.message,'Do not create a replacement submission. Retry the exact pending request or check its saved group in history.']);}
        finally{submitting=false;$('saRetry').textContent='Retry same submission';pendingView();}
    }
    form.addEventListener('submit',event=>{
        event.preventDefault();if(pending||submitting||!form.reportValidity())return;
        previewAll();if($('saSave').disabled)return;
        const items=rows.map(r=>({product_id:String(r.product.id),batch_id:String(r.batch.id),mode:field(r,'mode').value,amount:field(r,'amount').value.trim(),expected_quantity:r.batch.quantity,revision:r.batch.revision,reason:field(r,'reason').value,notes:field(r,'notes').value.trim(),reference:field(r,'reference').value.trim(),confirm_available:$('saConfirmAvailable').checked?1:0,confirm_expired:field(r,'confirm_expired').checked?1:0}));
        const preview=rows.slice(0,8).map((r,i)=>{const q=calculation(r);return `${i+1}. ${r.product.name} · ${r.product.sku} · batch #${r.batch.id}: ${display(q.before)} → ${display(q.after)} ${r.product.unit}`;}).join('\n');
        if(!confirm(`Save ALL ${rows.length} adjustments together?\n\n${preview}${rows.length>8?'\n… and '+(rows.length-8)+' more rows reviewed on this page.':''}\n\nOne invalid/stale row cancels the entire submission. Continue?`))return;
        send({request_id:c.requestId,items});
    });
    function addAndFocus(){if(!pending&&!submitting){const r=addRow();if(r){r.el.scrollIntoView({block:'start',behavior:'smooth'});field(r,'search').focus({preventScroll:true});}}}
    $('saAdd').addEventListener('click',addAndFocus);
    $('saAddBottom').addEventListener('click',addAndFocus);
    $('saRefreshAll').addEventListener('click',()=>{if(!pending&&!submitting)rows.forEach(r=>loadBatches(r,field(r,'batch').value));});
    $('saApplyDefaults').addEventListener('click',()=>{if(pending||submitting)return;if(rows.some(r=>field(r,'reason').value||field(r,'notes').value||field(r,'reference').value)&&!confirm('Replace reason, explanation and reference on every row with these defaults?'))return;rows.forEach(defaults);changed();});
    $('saRetry').addEventListener('click',()=>{if(pending)send(pending);});
    window.addEventListener('beforeunload',e=>{const dirty=rows.some(r=>field(r,'product').value||field(r,'amount').value||field(r,'notes').value);if(!navigating&&(submitting||(!pending&&dirty))){e.preventDefault();e.returnValue='';}});
    window.addEventListener('pageshow',e=>{if(e.persisted)window.location.reload();});
    addRow();
    try{const saved=JSON.parse(sessionStorage.getItem(storageKey)||'null');if(saved&&typeof saved.request_id==='string')pending=saved;}catch(_){}
    pendingView();
})();
