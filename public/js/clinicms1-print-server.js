/* ClinicMS browser connector for the standalone Windows Print Server EXE. */
(function (window) {
    'use strict';

    const DEFAULT_URL = 'http://127.0.0.1:18181';
    const TOKEN_KEY = 'clinicms_print_server_token';
    const URL_KEY = 'clinicms_print_server_url';

    const serverUrl = () => (localStorage.getItem(URL_KEY) || DEFAULT_URL).replace(/\/$/, '');
    const token = () => localStorage.getItem(TOKEN_KEY) || '';

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function api(path, options) {
        const config = Object.assign({ method: 'GET' }, options || {});
        config.headers = Object.assign({
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }, config.headers || {});
        if (token()) config.headers['X-ClinicMS-Print-Token'] = token();

        let response;
        try {
            response = await fetch(serverUrl() + path, config);
        } catch (error) {
            throw new Error('ClinicMS Print Server සම්බන්ධ කළ නොහැක. EXE application එක run වී තිබේද බලන්න.');
        }

        let json = {};
        try { json = await response.json(); } catch (_) {}
        if (!response.ok) {
            const error = new Error(json.message || `Print Server HTTP ${response.status}`);
            error.status = response.status;
            throw error;
        }
        return json;
    }

    async function pair(force) {
        if (!force && token()) {
            try {
                await api('/api/status');
                return true;
            } catch (error) {
                if (error.status !== 401) throw error;
                localStorage.removeItem(TOKEN_KEY);
            }
        }

        await api('/health'); // Gives a clear "server not running" error first.
        const code = window.prompt(
            'ClinicMS Print Server application එකේ පෙන්වන 6-digit Pairing Code එක ඇතුළත් කරන්න:'
        );
        if (code === null) return false;

        const result = await api('/api/pair', {
            method: 'POST',
            body: JSON.stringify({ code: String(code).trim() })
        });
        localStorage.setItem(TOKEN_KEY, result.token);
        return true;
    }

    function buildPrescriptionHtml(rxNo, prescription) {
        const patients = prescription
            ? (prescription.prescription_patients || prescription.prescriptionPatients || [])
            : [];
        let body = '';

        patients.forEach((patient, patientIndex) => {
            let rows = '';
            (patient.items || []).forEach((item, index) => {
                const product = item.product || {};
                const form = product.form_type || item.form_type || '';
                const strength = product.strength || item.strength || '';
                const details = [form, strength].filter(Boolean).join(' ');
                rows += `
                    <tr>
                        <td class="num">${index + 1}</td>
                        <td>
                            <strong>${escapeHtml(item.drug_name || product.name || '')}</strong>
                            ${details ? `<span class="detail">[${escapeHtml(details)}]</span>` : ''}
                            <div class="dose">${escapeHtml(item.timing || '')} × ${escapeHtml(item.duration_days || '')}d${item.instruction_note ? ' — ' + escapeHtml(item.instruction_note) : ''}</div>
                        </td>
                        <td class="qty">${escapeHtml(item.quantity || '')}</td>
                    </tr>`;
            });

            let radiology = '';
            (patient.radiologies || []).forEach(test => {
                radiology += `<div class="test">☐ ${escapeHtml(test.test_name || '')}</div>`;
            });

            body += `
                <section class="patient">
                    <div class="patient-head">
                        <strong>Patient ${patientIndex + 1}: ${escapeHtml(patient.patient_name || patient.patient?.name || '')}</strong>
                    </div>
                    ${patient.patient_age ? `<div class="meta">Age: ${escapeHtml(patient.patient_age)}</div>` : ''}
                    ${patient.diagnosis ? `<div class="meta">Diagnosis: ${escapeHtml(patient.diagnosis)}</div>` : ''}
                    <table><thead><tr><th>#</th><th>Medicine / Instruction</th><th>Qty</th></tr></thead><tbody>${rows}</tbody></table>
                    ${radiology ? `<div class="tests"><strong>Tests</strong>${radiology}</div>` : ''}
                    ${patient.precautions ? `<div class="notes"><strong>Advice:</strong> ${escapeHtml(patient.precautions)}</div>` : ''}
                </section>`;
        });

        let barcode = '';
        if (typeof window.renderBarcode === 'function') {
            barcode = window.renderBarcode(rxNo);
        }

        return `<!doctype html><html><head><meta charset="utf-8"><title>${escapeHtml(rxNo)}</title>
        <style>
            @page { size: 80mm auto; margin: 2mm; }
            * { box-sizing: border-box; }
            html, body { width: 76mm; margin: 0; padding: 0; background: #fff; color: #000; }
            body { font-family: Arial, 'Segoe UI', sans-serif; font-size: 11px; line-height: 1.3; }
            .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 6px; }
            .title { font-size: 16px; font-weight: 700; }
            .rx { font-size: 14px; font-family: Consolas, monospace; font-weight: 700; margin: 3px 0; }
            .barcode { max-width: 70mm; height: 35px; overflow: hidden; margin: 2px auto; }
            .barcode svg { width: 100%; height: 32px; }
            .date { font-size: 10px; }
            .patient { margin-bottom: 8px; border-bottom: 1px dashed #000; padding-bottom: 6px; page-break-inside: avoid; }
            .patient-head { font-size: 12px; background: #eee; padding: 3px; }
            .meta, .notes { padding: 2px 1px; }
            table { width: 100%; border-collapse: collapse; margin-top: 3px; }
            th { font-size: 9px; text-align: left; border-bottom: 1px solid #000; padding: 2px; }
            td { vertical-align: top; border-bottom: 1px dotted #bbb; padding: 3px 2px; }
            .num { width: 5mm; }
            .qty { width: 9mm; text-align: right; font-weight: 700; }
            .detail { font-size: 9px; margin-left: 2px; }
            .dose { font-size: 10px; margin-top: 1px; }
            .tests { padding-top: 4px; }
            .test { padding-left: 4px; }
            .footer { text-align: center; font-size: 9px; margin: 5px 0 8px; }
        </style></head><body>
            <div class="header">
                <div class="title">PRESCRIPTION</div>
                <div class="rx">${escapeHtml(rxNo)}</div>
                <div class="barcode">${barcode}</div>
                <div class="date">${new Date().toLocaleDateString('en-CA')}</div>
            </div>
            ${body || '<p>No prescription details.</p>'}
            <div class="footer">Please follow the prescribed dosage.</div>
        </body></html>`;
    }

    function arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        const chunkSize = 0x8000;
        let binary = '';
        for (let i = 0; i < bytes.length; i += chunkSize) {
            binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunkSize));
        }
        return btoa(binary);
    }

    async function fetchDirectPrintHtml(prescriptionId) {
        const response = await fetch(`/prescriptions/${encodeURIComponent(prescriptionId)}/direct-print-html`, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Accept': 'text/html' }
        });
        if (!response.ok) {
            throw new Error(`Prescription print layout ලබාගත නොහැක (HTTP ${response.status}).`);
        }
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.toLowerCase().includes('text/html')) {
            throw new Error('Server එක print HTML වෙනුවට වෙනත් response එකක් ලබාදී ඇත. Login session එක පරීක්ෂා කරන්න.');
        }
        return response.text();
    }

    async function markPrinted(prescriptionId) {
        try {
            await fetch(`/prescriptions/${encodeURIComponent(prescriptionId)}/mark-printed`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
        } catch (_) { /* The physical print succeeded; audit update is non-blocking. */ }
    }

    async function printPrescription(prescriptionId, rxNo, prescription, button) {
        const original = button ? button.innerHTML : '';
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...';
        }

        try {
            if (!await pair(false)) return false;

            // Print the same server-rendered Blade layout as native HTML. Text and
            // prices stay sharp because Electron sends vectors/fonts to the 203-DPI
            // Windows driver instead of a grey anti-aliased PDF screenshot.
            const printHtml = await fetchDirectPrintHtml(prescriptionId);
            const result = await api('/api/print', {
                method: 'POST',
                body: JSON.stringify({
                    jobId: `rx-${prescriptionId}-${Date.now()}`,
                    title: `Prescription ${rxNo}`,
                    type: 'html',
                    html: printHtml,
                    pageWidthMicrons: 72000
                })
            });
            await markPrinted(prescriptionId);
            if (button) {
                button.innerHTML = '<i class="fas fa-check mr-1"></i> Printed';
                setTimeout(() => { button.innerHTML = original; button.disabled = false; }, 1800);
            }
            return result.success === true;
        } catch (error) {
            if (button) { button.innerHTML = original; button.disabled = false; }
            alert('Direct print සිදු නොවීය.\n\n' + (error.message || String(error)));
            return false;
        }
    }

    async function openSetup() {
        try {
            await pair(true);
            alert('Print Server pair කරන ලදී. Printer එක Print Server EXE window එකෙන් තෝරන්න.');
        } catch (error) {
            alert(error.message || String(error));
        }
    }

    window.ClinicMSPrintServer = { printPrescription, openSetup, pair };
})(window);
