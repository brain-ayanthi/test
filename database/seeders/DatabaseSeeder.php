<?php

namespace Database\Seeders;

use App\Models\CashAccount;
use App\Models\Category;
use App\Models\Doctor;
use App\Models\DrugType;
use App\Models\Patient;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles & Permissions
        $admin = Role::create(['name' => 'Admin']);
        $doctorRole = Role::create(['name' => 'Doctor']);
        $staff = Role::create(['name' => 'Staff']);

        $permissions = [
            'manage users', 'manage doctors', 'manage patients',
            'create prescription', 'view prescription', 'edit prescription',
            'manage inventory', 'manage purchase', 'manage sales',
            'view reports', 'manage cash', 'manage settings',
        ];
        foreach ($permissions as $p) {
            Permission::create(['name' => $p]);
        }
        $admin->givePermissionTo($permissions);
        $doctorRole->givePermissionTo(['create prescription', 'view prescription', 'manage patients']);
        $staff->givePermissionTo(['create prescription', 'view prescription', 'manage sales', 'manage patients']);

        // Admin user  (password: "password")
        $adminUser = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@clinicms.test',
            'password' => '$2y$12$70oISlEdklu3koGmVBwWN.ACOVlBAmDIzA17RH1oDlgWq76s7ps0W',
            'is_active' => true,
        ]);
        $adminUser->assignRole($admin);

        // Cash accounts (Store Cash, Bank, MFS)
        CashAccount::create(['name' => 'Store Cash', 'type' => 'cash', 'balance' => 1000000, 'icon' => 'wallet', 'color' => '#22c55e']);
        CashAccount::create(['name' => 'Bank Balance', 'type' => 'bank', 'balance' => 53354.31, 'icon' => 'bank', 'color' => '#3b82f6']);
        CashAccount::create(['name' => 'MFS Balance', 'type' => 'mfs', 'balance' => 2801.24, 'icon' => 'mobile', 'color' => '#a855f7']);

        // Doctors
        $doctor = Doctor::create([
            'name' => 'Dr. Perera',
            'email' => 'perera@clinic.lk',
            'phone' => '+94770000001',
            'specialization' => 'General Physician',
            'qualification' => 'MBBS, MD',
            'chamber' => 'Colombo Medical Center',
            'visit_fee' => 500,
            'doctor_fee' => 500,
        ]);

        Doctor::create([
            'name' => 'Dr. Silva',
            'email' => 'silva@clinic.lk',
            'phone' => '+94770000002',
            'specialization' => 'Cardiologist',
            'qualification' => 'MBBS, MD Cardiology',
            'chamber' => 'Heart Care Center',
            'visit_fee' => 1500,
            'doctor_fee' => 1000,
        ]);

        // Categories
        $catMed = Category::create(['name' => 'Medicines', 'slug' => 'medicines', 'icon' => 'pill']);
        $catDev = Category::create(['name' => 'Devices', 'slug' => 'devices', 'icon' => 'device']);
        $catVit = Category::create(['name' => 'Vitamins & Supplements', 'slug' => 'vitamins', 'icon' => 'vitamin']);

        // Units
        $piece = Unit::create(['name' => 'Piece', 'short_name' => 'pc', 'conversion' => 1, 'is_base_unit' => true]);
        $strip = Unit::create(['name' => 'Strip', 'short_name' => 'strip', 'conversion' => 10, 'base_unit_id' => $piece->id]);
        $packet = Unit::create(['name' => 'Packet', 'short_name' => 'pkt', 'conversion' => 100, 'base_unit_id' => $piece->id]);
        $box100 = Unit::create(['name' => 'Box (100)', 'short_name' => 'box100', 'conversion' => 100, 'base_unit_id' => $piece->id]);
        $box500 = Unit::create(['name' => 'Box (500)', 'short_name' => 'box500', 'conversion' => 500, 'base_unit_id' => $piece->id]);
        $box1000 = Unit::create(['name' => 'Box (1000)', 'short_name' => 'box1000', 'conversion' => 1000, 'base_unit_id' => $piece->id]);

        // Drug Types
        $types = [
            ['name' => 'Antibiotic', 'color' => '#ef4444', 'icon' => 'shield'],
            ['name' => 'Analgesic', 'color' => '#f59e0b', 'icon' => 'pain'],
            ['name' => 'Antipyretic', 'color' => '#3b82f6', 'icon' => 'thermo'],
            ['name' => 'Antihistamine', 'color' => '#8b5cf6', 'icon' => 'allergy'],
            ['name' => 'Antacid', 'color' => '#10b981', 'icon' => 'stomach'],
            ['name' => 'Vitamin', 'color' => '#f97316', 'icon' => 'vitamin'],
            ['name' => 'Antidiabetic', 'color' => '#06b6d4', 'icon' => 'diabetes'],
            ['name' => 'Cardiac', 'color' => '#ec4899', 'icon' => 'heart'],
        ];
        foreach ($types as $t) {
            DrugType::create($t + ['slug' => \Illuminate\Support\Str::slug($t['name'])]);
        }

        // Vendors
        Vendor::create(['name' => 'ABC Pharmaceuticals', 'company' => 'ABC Pharma (Pvt) Ltd', 'phone' => '+94112222222', 'opening_balance' => 0]);
        Vendor::create(['name' => 'HealthCare Distributors', 'company' => 'HealthCare Ltd', 'phone' => '+94113333333', 'opening_balance' => 0]);

        // Products
        $products = [
            ['name' => 'Paracetamol 500mg', 'sku' => 'MED-PARA-500', 'category_id' => $catMed->id, 'drug_type_id' => 2, 'form_type' => 'tablet', 'strength' => '500mg', 'generic_name' => 'Paracetamol', 'purchase_unit_id' => $box1000->id, 'pieces_per_purchase_unit' => 1000, 'selling_unit_id' => $piece->id, 'purchase_price' => 2500, 'selling_price' => 5, 'mrp' => 5, 'min_stock' => 100, 'rack_number' => 'A-01'],
            ['name' => 'Amoxicillin 250mg', 'sku' => 'MED-AMOX-250', 'category_id' => $catMed->id, 'drug_type_id' => 1, 'form_type' => 'capsule', 'strength' => '250mg', 'generic_name' => 'Amoxicillin', 'purchase_unit_id' => $box1000->id, 'pieces_per_purchase_unit' => 1000, 'selling_unit_id' => $piece->id, 'purchase_price' => 4500, 'selling_price' => 8, 'mrp' => 10, 'min_stock' => 50, 'rack_number' => 'A-02'],
            ['name' => 'Ciprofloxacin 500mg', 'sku' => 'MED-CIPRO-500', 'category_id' => $catMed->id, 'drug_type_id' => 1, 'form_type' => 'tablet', 'strength' => '500mg', 'generic_name' => 'Ciprofloxacin', 'purchase_unit_id' => $box500->id, 'pieces_per_purchase_unit' => 500, 'selling_unit_id' => $piece->id, 'purchase_price' => 6000, 'selling_price' => 15, 'mrp' => 18, 'min_stock' => 30, 'rack_number' => 'A-03'],
            ['name' => 'Azithromycin 500mg', 'sku' => 'MED-AZI-500', 'category_id' => $catMed->id, 'drug_type_id' => 1, 'form_type' => 'tablet', 'strength' => '500mg', 'generic_name' => 'Azithromycin', 'purchase_unit_id' => $box100->id, 'pieces_per_purchase_unit' => 100, 'selling_unit_id' => $piece->id, 'purchase_price' => 3000, 'selling_price' => 35, 'mrp' => 40, 'min_stock' => 20, 'rack_number' => 'A-04'],
            ['name' => 'Metformin 500mg', 'sku' => 'MED-MET-500', 'category_id' => $catMed->id, 'drug_type_id' => 7, 'form_type' => 'tablet', 'strength' => '500mg', 'generic_name' => 'Metformin HCl', 'purchase_unit_id' => $box1000->id, 'pieces_per_purchase_unit' => 1000, 'selling_unit_id' => $piece->id, 'purchase_price' => 3000, 'selling_price' => 6, 'mrp' => 8, 'min_stock' => 100, 'rack_number' => 'B-01'],
            ['name' => 'Omeprazole 20mg', 'sku' => 'MED-OME-20', 'category_id' => $catMed->id, 'drug_type_id' => 5, 'form_type' => 'capsule', 'strength' => '20mg', 'generic_name' => 'Omeprazole', 'purchase_unit_id' => $box1000->id, 'pieces_per_purchase_unit' => 1000, 'selling_unit_id' => $piece->id, 'purchase_price' => 2800, 'selling_price' => 7, 'mrp' => 10, 'min_stock' => 50, 'rack_number' => 'B-02'],
            ['name' => 'Multivitamin', 'sku' => 'VIT-MULTI', 'category_id' => $catVit->id, 'drug_type_id' => 6, 'form_type' => 'tablet', 'strength' => 'Complex', 'generic_name' => 'Multivitamin', 'purchase_unit_id' => $box100->id, 'pieces_per_purchase_unit' => 100, 'selling_unit_id' => $piece->id, 'purchase_price' => 2500, 'selling_price' => 30, 'mrp' => 35, 'min_stock' => 30, 'rack_number' => 'C-01'],
            ['name' => 'Vitamin C 1000mg', 'sku' => 'VIT-C-1000', 'category_id' => $catVit->id, 'drug_type_id' => 6, 'form_type' => 'tablet', 'strength' => '1000mg', 'generic_name' => 'Ascorbic Acid', 'purchase_unit_id' => $packet->id, 'pieces_per_purchase_unit' => 100, 'selling_unit_id' => $piece->id, 'purchase_price' => 1500, 'selling_price' => 20, 'mrp' => 25, 'min_stock' => 20, 'rack_number' => 'C-02'],
            ['name' => 'Losartan 50mg', 'sku' => 'MED-LOS-50', 'category_id' => $catMed->id, 'drug_type_id' => 8, 'form_type' => 'tablet', 'strength' => '50mg', 'generic_name' => 'Losartan Potassium', 'purchase_unit_id' => $box500->id, 'pieces_per_purchase_unit' => 500, 'selling_unit_id' => $piece->id, 'purchase_price' => 4000, 'selling_price' => 10, 'mrp' => 12, 'min_stock' => 50, 'rack_number' => 'D-01'],
            ['name' => 'Ibuprofen 400mg', 'sku' => 'MED-IBU-400', 'category_id' => $catMed->id, 'drug_type_id' => 2, 'form_type' => 'tablet', 'strength' => '400mg', 'generic_name' => 'Ibuprofen', 'purchase_unit_id' => $box1000->id, 'pieces_per_purchase_unit' => 1000, 'selling_unit_id' => $piece->id, 'purchase_price' => 3500, 'selling_price' => 8, 'mrp' => 10, 'min_stock' => 80, 'rack_number' => 'A-05'],
        ];

        foreach ($products as $p) {
            $p['is_active'] = true;
            $p['track_batch'] = true;
            $product = Product::create($p);

            // Create batches with varying expiry
            ProductBatch::create([
                'product_id' => $product->id,
                'batch_number' => 'B' . strtoupper(\Illuminate\Support\Str::random(6)),
                'manufacturing_date' => now()->subMonths(2),
                'expiry_date' => now()->addMonths(rand(6, 36)),
                'purchase_price' => $p['purchase_price'] / $p['pieces_per_purchase_unit'],
                'selling_price' => $p['selling_price'],
                'quantity' => rand(20, 500),
                'initial_quantity' => rand(20, 500),
                'rack_number' => $p['rack_number'],
            ]);
        }

        // Add some low/zero stock products
        Product::where('sku', 'MED-CIPRO-500')->first()->batches()->first()->update(['quantity' => 5]);
        Product::where('sku', 'MED-AZI-500')->first()->batches()->first()->update(['quantity' => 0]);

        // Patients
        $patients = [
            ['name' => 'Elmer Stamm', 'email' => 'brittany52@example.net', 'phone' => '+1-419-428-0787', 'age' => 39, 'gender' => 'other', 'address' => '9702 Wehner Junction Apt. 035 Andersonville, IL'],
            ['name' => 'Patient', 'phone' => '+1 (629) 329-5071', 'age' => 45, 'gender' => 'male'],
            ['name' => 'Raymundo Upton', 'phone' => '+1-929-835-2469', 'age' => 28, 'gender' => 'male'],
            ['name' => 'Lenny Weissnat', 'phone' => '1-484-236-3246', 'age' => 52, 'gender' => 'female'],
            ['name' => 'Jesse Murphy', 'phone' => '580.439.9985', 'age' => 33, 'gender' => 'male'],
            ['name' => 'Grover Weber', 'phone' => '1-930-756-0203', 'age' => 61, 'gender' => 'male'],
            ['name' => 'Miss Marjolaine Rodriguez MD', 'phone' => '(463) 596-1611', 'age' => 47, 'gender' => 'female'],
        ];
        foreach ($patients as $i => $p) {
            $p['created_by'] = $adminUser->id;
            $p['last_visit'] = now()->subDays(rand(1, 30));
            Patient::create($p);
        }

        // Radiology / Extra tests (each has a price that adds to "Extra" in fee summary)
        $radiology = [
            ['name' => 'X-RAY', 'code' => 'XR', 'price' => 1000, 'category' => 'Radiology', 'sort_order' => 1],
            ['name' => 'ECG (Heart Test)', 'code' => 'ECG', 'price' => 500, 'category' => 'Cardiology', 'sort_order' => 2],
            ['name' => 'RLE (Resting 12 lead ECG)', 'code' => 'RLE', 'price' => 500, 'category' => 'Cardiology', 'sort_order' => 3],
            ['name' => 'USG OBS (Ultrasound Pregnancy)', 'code' => 'USG', 'price' => 1500, 'category' => 'Radiology', 'sort_order' => 4],
            ['name' => '2D Echo', 'code' => '2DE', 'price' => 2500, 'category' => 'Cardiology', 'sort_order' => 5],
            ['name' => 'DEXA Bone Densitometry', 'code' => 'DEXA', 'price' => 3000, 'category' => 'Radiology', 'sort_order' => 6],
            ['name' => 'Mammography', 'code' => 'MAM', 'price' => 2000, 'category' => 'Radiology', 'sort_order' => 7],
            ['name' => 'CT Scan', 'code' => 'CT', 'price' => 5000, 'category' => 'Radiology', 'sort_order' => 8],
            ['name' => 'Blood Test - FBC', 'code' => 'FBC', 'price' => 350, 'category' => 'Laboratory', 'sort_order' => 10],
            ['name' => 'Fasting Blood Sugar', 'code' => 'FBS', 'price' => 250, 'category' => 'Laboratory', 'sort_order' => 11],
            ['name' => 'Urine Full Report', 'code' => 'UFR', 'price' => 200, 'category' => 'Laboratory', 'sort_order' => 12],
            ['name' => 'Lipid Profile', 'code' => 'LIPID', 'price' => 800, 'category' => 'Laboratory', 'sort_order' => 13],
        ];
        foreach ($radiology as $r) {
            \App\Models\RadiologyTest::create($r);
        }

        // Sample Ready Treatment template (Common Fever)
        $fever = \App\Models\ReadyTreatment::create([
            'name' => 'Common Fever',
            'disease' => 'Viral Fever',
            'description' => 'Standard medication for viral fever',
            'doctor_id' => $doctor->id,
            'created_by' => $adminUser->id,
        ]);
        $templateItems = [
            ['product_id' => 1, 'timing' => 'TDS', 'duration_days' => 3, 'meal_relation' => 'after', 'instruction' => 'Take after meals'],
            ['product_id' => 11, 'timing' => 'BD', 'duration_days' => 3, 'meal_relation' => 'after', 'instruction' => 'Take at night'],
            ['product_id' => 6, 'timing' => 'OD', 'duration_days' => 3, 'meal_relation' => 'before', 'instruction' => 'Before breakfast'],
        ];
        foreach ($templateItems as $ti) {
            $prod = Product::find($ti['product_id']);
            $fever->items()->create([
                'product_id' => $prod->id,
                'drug_name' => $prod->name,
                'form_type' => $prod->form_type,
                'strength' => $prod->strength,
                'timing' => $ti['timing'],
                'timing_multiplier' => \App\Models\PrescriptionItem::TIMING_MAP[$ti['timing']] ?? 1,
                'meal_relation' => $ti['meal_relation'],
                'duration_days' => $ti['duration_days'],
                'instruction' => $ti['instruction'],
            ]);
        }
    }
}
