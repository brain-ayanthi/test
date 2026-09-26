<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Prescription <?php echo e($prescription->prescription_number); ?></title>
<style>
/*
 * XP-80T uses 80mm paper but its reliable printable width is about 72mm.
 * Keeping the receipt content inside 64mm gives equal 4mm printable margins
 * and prevents the Qty/Amt column from being clipped on the right.
 */
@page { size: 72mm auto; margin: 1mm; }
* { box-sizing: border-box; }
html, body { background: #fff; color: #000; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.25;
    width: 64mm;
    margin: 0;
    padding: 0;
    color: #000;
}
.center { text-align: center; }
.bold { font-weight: 700; }
.line { border-top: 1px dashed #000; margin: 5px 0; height: 1px; }
table { width: 100%; border-collapse: collapse; font-size: 11px; table-layout: fixed; }
td, th { padding: 2px 1px; vertical-align: top; color: #000; }
th { font-weight: 700; border-bottom: 1px solid #000; }
.right { text-align: right; white-space: nowrap; }
.small { font-size: 10.5px; }
.patient-block { margin-bottom: 7px; }
.medicine-name { font-weight: 600; overflow-wrap: anywhere; word-break: normal; }
.dose { font-size: 10.5px; font-weight: 600; margin-top: 1px; }
.amount-table td { padding-top: 3px; padding-bottom: 3px; }
.barcode-wrap { text-align: center; margin: 5px 0; }
.barcode-svg { max-width: 100%; height: 48px; }
.company-name { font-size: 15px; line-height: 1.15; font-weight: 700; }
.rx-title { font-size: 13px; font-weight: 700; }
.rx-number-bottom { font-size: 14px; letter-spacing: .8px; font-weight: 700; }
.total-row { font-size: 14px; font-weight: 700; border-top: 1px solid #000; }
@media print {
    html, body { width: 64mm !important; margin: 0 !important; padding: 0 !important; }
}
</style>
</head>
<body>

<div class="center company-name"><?php echo e(config('app.company_name', 'Clinic Pharmacy')); ?></div>
<div class="center small">123 Medical Road, Colombo</div>
<div class="center small">Tel: +94 11 222 3333</div>
<div class="line"></div>

<div class="center rx-title">PRESCRIPTION</div>
<table>
    <colgroup><col style="width:25mm"><col style="width:39mm"></colgroup>
    <tr>
        <td>Rx #:</td>
        <td class="right bold"><?php echo e($prescription->prescription_number); ?></td>
    </tr>
    <tr>
        <td>Date:</td>
        <td class="right"><?php echo e($prescription->prescription_date->format('d M Y')); ?></td>
    </tr>
    <tr>
        <td>Patients:</td>
        <td class="right"><?php echo e($prescription->prescriptionPatients->count()); ?></td>
    </tr>
</table>
<div class="line"></div>

<?php $__currentLoopData = $prescription->prescriptionPatients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $pp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="patient-block">
    <div class="bold" style="font-size:12.5px"><?php echo e($i + 1); ?>. <?php echo e($pp->patient_name); ?></div>
    <div class="small"><?php echo e($pp->patient_age); ?> yrs &middot; <?php echo e($pp->patient_phone); ?></div>

    <?php if(!is_null($pp->doctor)): ?>
    <div class="small">Dr. <?php echo e($pp->doctor->name); ?></div>
    <?php endif; ?>

    <table>
        <colgroup>
            <col style="width:4mm">
            <col style="width:39mm">
            <col style="width:7mm">
            <col style="width:14mm">
        </colgroup>
        <thead>
        <tr>
            <th style="text-align:left">#</th>
            <th style="text-align:left">Medicine</th>
            <th class="right">Qty</th>
            <th class="right">Amt</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $pp->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($j + 1); ?></td>
            <td class="medicine-name">
                <?php echo e($item->drug_name); ?>

                <?php if(!empty($item->form_type) || !empty($item->strength)): ?>
                <span class="small">[<?php echo e(trim(($item->form_type ?? '').' '.($item->strength ?? ''))); ?>]</span>
                <?php endif; ?>
                <div class="dose">
                    <?php if(!empty($item->instruction_note)): ?>
                    - <?php echo e($item->instruction_note); ?>

                    <?php endif; ?>
                </div>
            </td>
            <td class="right bold"><?php echo e((float) $item->quantity); ?><?php echo e(str_contains(strtolower((string)$item->strength), 'ml') ? 'ml' : ''); ?></td>
            <td class="right"><div class="dose">
                    <?php echo e($item->timing); ?> x <?php echo e($item->duration_days); ?>d
                </div></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php $__currentLoopData = $pp->radiologies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td colspan="2"><span class="small">[R] <?php echo e($rad->test_name); ?></span></td>
            <td></td>
            <td class="right"><?php echo e(number_format((float)$rad->price, 2)); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <?php if(!empty($pp->diagnosis)): ?>
    <div class="small"><b>Dx:</b> <?php echo e($pp->diagnosis); ?></div>
    <?php endif; ?>
</div>
<div class="line"></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<table class="amount-table">
    <colgroup><col style="width:35mm"><col style="width:29mm"></colgroup>
    <tr>
        <td>Medicine</td>
        <td class="right bold">Rs <?php echo e(number_format((float)$prescription->medicine_cost, 2)); ?></td>
    </tr>
    <tr>
        <td>Radiology</td>
        <td class="right bold">Rs <?php echo e(number_format((float)$prescription->radiology_cost, 2)); ?></td>
    </tr>
    <tr>
        <td>Doctor Fee</td>
        <td class="right bold">Rs <?php echo e(number_format((float)$prescription->doctor_fee, 2)); ?></td>
    </tr>
    <tr class="total-row">
        <td>TOTAL</td>
        <td class="right">Rs <?php echo e(number_format((float)$prescription->total_fee, 2)); ?></td>
    </tr>
</table>

<div class="line"></div>
<div class="barcode-wrap">
    <?php echo $barcodeSvg ?? ''; ?>

    <div class="rx-number-bottom"><?php echo e($prescription->prescription_number); ?></div>
</div>
<div class="line"></div>
<div class="center small">Thank you! Get well soon.</div>
<div class="center small"><?php echo e(now()->format('d M Y h:i A')); ?></div>

<?php if(empty($directPrintServer)): ?>
<script>
window.onload = function () {
    setTimeout(function () { window.print(); }, 200);
};
</script>
<?php endif; ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\clinicms\resources\views/prescriptions/print.blade.php ENDPATH**/ ?>