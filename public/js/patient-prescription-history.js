/* ClinicMS: read-only history for the active patient. No Create form values are rebuilt. */
(function (global) {
    'use strict';
    let config, visible = false, currentId = null, page = 1, meta = null, pending = false;
    let sequence = 0, aborter = null;
    const el = id => document.getElementById(id);
    const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
    const money = value => Number(value || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const number = value => Number(value || 0).toLocaleString('en-US', {maximumFractionDigits: 2});

    function patient() {
        const selected = config.getPatient();
        if (!selected || !/^[1-9]\d*$/.test(String(selected.id))) return null;
        return selected;
    }
    function cancelPending() {
        sequence++;
        if (aborter) aborter.abort();
        aborter = null;
        pending = false;
    }
    function markTabs(active) {
        document.querySelectorAll('.tab-btn').forEach(button => {
            const selected = button === active;
            button.classList.toggle('active', selected);
            button.classList.toggle('text-blue-600', selected);
            button.classList.toggle('border-blue-600', selected);
            button.setAttribute('aria-selected', selected ? 'true' : 'false');
        });
    }
    function setView(show) {
        visible = show;
        el('rxCreateBody').hidden = show;
        el('patientHistoryPanel').hidden = !show;
        el('rxCreateBody').setAttribute('aria-hidden', show ? 'true' : 'false');
        el('patientHistoryPanel').setAttribute('aria-hidden', show ? 'false' : 'true');
    }
    function controls() {
        const selected = patient();
        el('patientHistoryRefresh').disabled = pending || !selected;
        el('patientHistoryPrev').disabled = pending || !meta || Number(meta.current_page) <= 1;
        el('patientHistoryNext').disabled = pending || !meta || Number(meta.current_page) >= Number(meta.last_page);
    }
    function status(text, error = false) {
        const area = el('patientHistoryStatus');
        area.textContent = text;
        area.classList.toggle('text-red-700', error);
        area.classList.toggle('text-gray-500', !error);
    }
    function clearResults() {
        meta = null;
        el('patientHistoryResults').replaceChildren();
        el('patientHistoryPage').textContent = '';
    }
    function show() {
        setView(true);
        markTabs(el('patientPrescriptionHistoryTab'));
        load(1);
    }
    function hide(activeTab) {
        cancelPending();
        setView(false);
        markTabs(activeTab || el('prescriptionCreateTab'));
    }
    function patientChanged() {
        if (!config || !visible) return;
        const id = patient() ? String(patient().id) : null;
        if (id !== currentId) load(1);
    }
    async function load(requestedPage) {
        if (!config || !visible) return;
        cancelPending();
        const selected = patient();
        currentId = selected ? String(selected.id) : null;
        page = Math.max(1, Number(requestedPage) || 1);
        clearResults();
        el('patientHistoryName').textContent = selected
            ? `${selected.name || 'Patient'}${selected.patient_code ? ' · ' + selected.patient_code : ''}`
            : 'No patient selected';
        el('patientHistorySelect').hidden = !!selected;
        if (!selected) {
            status('Select a patient first to view their saved prescription history.');
            controls();
            return;
        }
        const requestedId = currentId;
        const ticket = sequence;
        aborter = typeof AbortController === 'undefined' ? null : new AbortController();
        const controller = aborter;
        pending = true;
        status('Loading prescription history…');
        controls();
        const url = config.endpoint.replace('__PATIENT__', encodeURIComponent(requestedId));
        try {
            const response = await fetch(url + (url.includes('?') ? '&' : '?') + 'page=' + page, {
                method: 'GET', credentials: 'same-origin', cache: 'no-store',
                headers: {'Accept': 'application/json'}, ...(controller ? {signal: controller.signal} : {})
            });
            if (response.status === 401 || response.status === 419 || response.redirected) {
                throw new Error('Your session may have expired. Sign in in another tab, keep this draft open, then select Refresh.');
            }
            if (response.status === 403) throw new Error('You do not have permission to view this patient’s prescription history.');
            if (response.status === 404) throw new Error('Patient history endpoint or patient not found. Check the installed history route.');
            if (!response.ok || !(response.headers.get('content-type') || '').includes('application/json')) {
                throw new Error('Could not load prescription history. Your Create form is unchanged. Please try Refresh.');
            }
            const json = await response.json();
            // A slow response for a previously selected patient must never appear here.
            if (ticket !== sequence || !visible || String(patient()?.id) !== requestedId) return;
            if (String(json.patient?.id) !== requestedId || !Array.isArray(json.data) || !json.meta) {
                throw new Error('Unexpected history response. No history has been displayed; your draft is unchanged.');
            }
            meta = json.meta;
            page = Number(meta.current_page);
            el('patientHistoryName').textContent = `${json.patient.name || selected.name || 'Patient'}${json.patient.patient_code ? ' · ' + json.patient.patient_code : ''}`;
            if (!json.data.length) {
                status(Number(meta.total) > 0 ? 'No records on this page. Select Refresh to return to the latest records.' : 'No saved prescriptions found for this patient.');
            } else {
                render(json.data);
                status(`${Number(meta.total)} prescription(s). Newest prescription dates first. Expand a record to view this patient’s details.`);
            }
            el('patientHistoryPage').textContent = Number(meta.total) > 0
                ? `Page ${meta.current_page} of ${meta.last_page} · ${meta.from ?? 0}–${meta.to ?? 0} of ${meta.total}` : '';
        } catch (error) {
            if (ticket !== sequence || !visible || String(patient()?.id) !== requestedId || error.name === 'AbortError') return;
            clearResults();
            status(error instanceof TypeError ? 'Network error. Your Create form is unchanged. Select Refresh to retry.' : error.message, true);
        } finally {
            if (ticket === sequence) {
                pending = false;
                aborter = null;
                controls();
            }
        }
    }
    function textBlock(label, value) {
        if (value === null || value === undefined || value === '') return '';
        return `<div class="rxh-note"><strong>${escape(label)}</strong><p>${escape(value)}</p></div>`;
    }
    function visitHtml(visit, index, count, legacy) {
        const medicines = Array.isArray(visit.items) ? visit.items : [];
        const tests = Array.isArray(visit.radiologies) ? visit.radiologies : [];
        return `<section class="rxh-visit">
            <div class="rxh-visit-heading"><strong>${count > 1 ? 'Patient entry ' + (index + 1) + ' · ' : ''}${escape(visit.doctor_name || 'Doctor not recorded')}</strong></div>
            <div class="rxh-notes">${textBlock('Diagnosis', visit.diagnosis)}${textBlock('Precautions', visit.precautions)}${textBlock('Next visit', visit.next_visit)}${textBlock('Lab workup', visit.lab_workup)}${textBlock('Physiotherapy', visit.physiotherapy)}${textBlock('Notes', visit.notes)}</div>
            <div class="rxh-table-wrap"><table class="rxh-table"><thead><tr><th>Medicine / dose</th><th>Timing / days</th><th>Qty</th><th>Instructions</th><th>Unit price</th><th>Discount</th><th>Total</th></tr></thead><tbody>
            ${medicines.map(item => `<tr><td><strong>${escape(item.drug_name)}</strong><small>${escape([item.form_type, item.strength].filter(Boolean).join(' · '))}</small></td>
                <td>${escape(item.timing)} · ${escape(item.duration_days)} day(s)<small>${escape(item.meal_relation || '')}</small><small>${escape((Array.isArray(item.times_of_day) ? item.times_of_day : []).join(', '))}</small></td>
                <td>${number(item.quantity)}</td><td class="rxh-instruction">${escape([item.instruction, item.instruction_note].filter(Boolean).join('\n'))}</td>
                <td>${money(item.unit_price)}</td><td>${money(item.discount)}</td><td>${money(item.total)}</td></tr>`).join('') || '<tr><td colspan="7">No medicines recorded.</td></tr>'}
            </tbody></table></div>
            ${tests.length ? `<div class="rxh-tests"><strong>Radiology / Extra Tests</strong>${tests.map(test => `<div><span>${escape(test.test_name)}</span><span>Rs ${money(test.price)}</span>${test.notes ? `<p>${escape(test.notes)}</p>` : ''}</div>`).join('')}</div>` : ''}
            <div class="rxh-fees"><span>Medicine: <b>Rs ${money(visit.medicine_cost)}</b></span><span>Doctor: <b>Rs ${money(visit.doctor_fee)}</b></span><span>Radiology: <b>Rs ${money(visit.radiology_cost)}</b></span><span>Discount: <b>Rs ${money(visit.discount)}</b></span>${legacy ? `<span>Tax: <b>Rs ${money(visit.tax)}</b></span>` : ''}<span>Total: <b>Rs ${money(visit.total)}</b></span></div>
        </section>`;
    }
    function render(records) {
        el('patientHistoryResults').innerHTML = records.map(record => {
            const visits = Array.isArray(record.visits) ? record.visits : [];
            const doctors = [...new Set(visits.map(visit => visit.doctor_name).filter(Boolean))].join(', ');
            const badgeClass = ({active: 'rxh-active', completed: 'rxh-completed', cancelled: 'rxh-cancelled'})[record.status] || '';
            return `<details class="rxh-record"><summary><div class="rxh-card-heading"><div><strong class="rxh-number">${escape(record.prescription_number)}</strong><span class="rxh-date">${escape(record.prescription_date)}</span></div><div><span class="rxh-badge ${badgeClass}">${escape(record.status)}</span><span class="rxh-total">Rs ${money(record.patient_total)}</span></div></div><div class="rxh-card-subtitle"><span>${escape(doctors || 'Doctor not recorded')}</span><span>View details <span aria-hidden="true">▾</span></span></div></summary>
                <div class="rxh-record-body">${visits.map((visit, i) => visitHtml(visit, i, visits.length, !!record.legacy)).join('')}
                <p class="rxh-footnote">${record.legacy ? 'Legacy single-patient prescription.' : 'Only this patient’s entries are shown. Other patients’ details and shared receipt payments/tax are not included.'} History is read-only.</p></div></details>`;
        }).join('');
    }
    function init(options) {
        if (config) return;
        config = options;
        document.querySelectorAll('.tab-btn').forEach(button => button.addEventListener('click', () => {
            if (button.id === 'patientPrescriptionHistoryTab') show();
            else hide(button);
        }));
        el('patientHistoryRefresh').addEventListener('click', () => load(1));
        el('patientHistoryPrev').addEventListener('click', () => load(page - 1));
        el('patientHistoryNext').addEventListener('click', () => load(page + 1));
        el('patientHistoryBack').addEventListener('click', () => hide());
        el('patientHistorySelect').addEventListener('click', () => config.selectPatient());
        controls();
    }
    global.ClinicPatientHistory = {init, show, hide, patientChanged};
})(window);
