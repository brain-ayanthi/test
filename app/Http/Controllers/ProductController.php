<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DrugType;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Unit;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Aggregate stock in ONE query instead of loading all batches per product (avoids N+1)
        $query = Product::with(['category:id,name', 'drugType:id,name,color'])
            ->withSum(['batches as stock_quantity' => function ($q) {
                $q->where('quantity', '>', 0)->where('expiry_date', '>=', now());
            }], 'quantity');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($drugTypeId = $request->input('drug_type_id')) {
            $query->where('drug_type_id', $drugTypeId);
        }

        $products = $query->select('products.*')->latest('products.id')->paginate(20);
        $categories = Category::where('is_active', true)->get(['id', 'name']);
        $drugTypes = DrugType::where('is_active', true)->get(['id', 'name']);
        $openingStockImported = $this->openingStockImportCompleted();

        return view('inventory.products.index', compact(
            'products', 'categories', 'drugTypes', 'openingStockImported'
        ));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $drugTypes = DrugType::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        return view('inventory.products.create', compact('categories', 'drugTypes', 'units'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Product::create($data);
        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    /** Show the CSV opening-stock import page. */
    public function openingStockImportForm()
    {
        if (!Schema::hasTable('opening_stock_imports')) {
            return redirect()->route('products.index')->with(
                'error',
                'Opening-stock control table එක නොමැත. database/sql/add_opening_stock_import_control.sql import කරන්න.'
            );
        }

        if ($this->openingStockImportCompleted()) {
            return redirect()->route('products.index')->with(
                'error',
                'Opening Stock import එක මීට පෙර සාර්ථකව භාවිතා කර ඇත. එය නැවත භාවිතා කළ නොහැක.'
            );
        }

        return view('inventory.products.opening-stock-import');
    }

    /** Download a ready-to-fill CSV template (available only before the one-time import). */
    public function openingStockTemplate()
    {
        if (!Schema::hasTable('opening_stock_imports') || $this->openingStockImportCompleted()) {
            return redirect()->route('products.index')->with(
                'error',
                'Opening Stock import එක දැනට ලබාගත නොහැක හෝ මීට පෙර භාවිතා කර ඇත.'
            );
        }

        $headers = [
            'name', 'sku', 'barcode', 'category', 'drug_type', 'form_type',
            'strength', 'generic_name', 'manufacturer', 'purchase_unit',
            'pieces_per_purchase_unit', 'selling_unit', 'selling_price_per_box',
            'percentage', 'mrp', 'rack_number', 'min_stock', 'batch_number',
            'manufacturing_date', 'expiry_date', 'opening_boxes', 'opening_loose_pieces',
        ];

        $example = [
            'Paracetamol 500mg', 'MED-001', '479000000001', 'Medicine', 'Tablet',
            'tablet', '500mg', 'Paracetamol', 'ABC Pharma', 'Box', '100', 'Piece',
            '2000', '10', '25', 'A-01', '20', 'PCM-B001', '2026-01-01',
            '2028-01-01', '5', '0',
        ];

        return response()->streamDownload(function () use ($headers, $example) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM lets Microsoft Excel display text correctly.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            fputcsv($out, $example);
            fclose($out);
        }, 'clinicms-opening-stock-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Create new products and their FEFO opening-stock batches from CSV.
     * The whole file is atomic: one invalid row means nothing is saved.
     */
    public function importOpeningStock(Request $request)
    {
        if (!Schema::hasTable('opening_stock_imports')) {
            return redirect()->route('products.index')->with(
                'error',
                'Opening-stock control table එක නොමැත. database/sql/add_opening_stock_import_control.sql import කරන්න.'
            );
        }

        // Server-side lock: hiding the button alone is not enough. Direct URL/POST is blocked too.
        if ($this->openingStockImportCompleted()) {
            return redirect()->route('products.index')->with(
                'error',
                'Opening Stock import එක එක්වරක් භාවිතා කර ඇති බැවින් නැවත import කළ නොහැක.'
            );
        }

        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        // Column headings must remain unchanged; some values within optional columns may be blank.
        $requiredHeaders = [
            'name', 'sku', 'barcode', 'category', 'drug_type', 'form_type',
            'strength', 'generic_name', 'manufacturer', 'purchase_unit',
            'pieces_per_purchase_unit', 'selling_unit', 'selling_price_per_box',
            'percentage', 'mrp', 'rack_number', 'min_stock', 'batch_number',
            'manufacturing_date', 'expiry_date', 'opening_boxes', 'opening_loose_pieces',
        ];

        $handle = fopen($request->file('import_file')->getRealPath(), 'r');
        if ($handle === false) {
            return back()->with('import_errors', ['CSV file එක විවෘත කළ නොහැක.']);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('import_errors', ['CSV file එක හිස්ය.']);
        }

        $header = array_map(function ($value) {
            return strtolower(trim(str_replace("\xEF\xBB\xBF", '', (string) $value)));
        }, $header);

        $missing = array_values(array_diff($requiredHeaders, $header));
        if ($missing) {
            fclose($handle);
            return back()->with('import_errors', [
                'අවශ්‍ය CSV columns නොමැත: ' . implode(', ', $missing) . '. Template එක download කර භාවිතා කරන්න.',
            ]);
        }

        $rows = [];
        $errors = [];
        $seenSkus = [];
        $line = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($values, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $values = array_pad($values, count($header), '');
            $row = array_combine($header, array_slice($values, 0, count($header)));
            $row = array_map(fn ($value) => trim((string) $value), $row);

            $name = $row['name'] ?? '';
            $sku = $row['sku'] ?? '';
            $pieces = $this->csvNumber($row['pieces_per_purchase_unit'] ?? null);
            $sellingBox = $this->csvNumber($row['selling_price_per_box'] ?? null);
            $percentage = $this->csvNumber($row['percentage'] ?? null);
            $openingBoxes = $this->csvNumber($row['opening_boxes'] ?? null);
            $loosePieces = $this->csvNumber($row['opening_loose_pieces'] ?? 0);
            $expiry = $row['expiry_date'] ?? '';
            $manufacturing = $row['manufacturing_date'] ?? '';

            $rowErrors = [];
            if ($name === '') $rowErrors[] = 'name required';
            if ($sku === '') $rowErrors[] = 'sku required';
            if ($sku !== '' && isset($seenSkus[strtolower($sku)])) $rowErrors[] = 'duplicate SKU in CSV';
            if ($sku !== '' && Product::withTrashed()->where('sku', $sku)->exists()) $rowErrors[] = 'SKU already exists';
            if ($pieces === null || $pieces <= 0) $rowErrors[] = 'pieces_per_purchase_unit must be greater than 0';
            if ($sellingBox === null || $sellingBox < 0) $rowErrors[] = 'selling_price_per_box must be 0 or greater';
            if ($percentage === null || $percentage < 0 || $percentage >= 100) $rowErrors[] = 'percentage must be between 0 and 99.99';
            if (($row['batch_number'] ?? '') === '') $rowErrors[] = 'batch_number required';
            if (!$this->validCsvDate($expiry)) $rowErrors[] = 'expiry_date must be YYYY-MM-DD';
            if ($manufacturing !== '' && !$this->validCsvDate($manufacturing)) $rowErrors[] = 'manufacturing_date must be YYYY-MM-DD';
            if ($manufacturing !== '' && $this->validCsvDate($manufacturing) && $this->validCsvDate($expiry) && $manufacturing > $expiry) {
                $rowErrors[] = 'manufacturing_date cannot be after expiry_date';
            }
            if ($openingBoxes === null || $openingBoxes < 0) $rowErrors[] = 'opening_boxes must be 0 or greater';
            if ($loosePieces === null || $loosePieces < 0) $rowErrors[] = 'opening_loose_pieces must be 0 or greater';
            if (($openingBoxes ?? 0) <= 0 && ($loosePieces ?? 0) <= 0) $rowErrors[] = 'opening stock must be greater than 0';

            if ($sku !== '') $seenSkus[strtolower($sku)] = true;
            if ($rowErrors) {
                $errors[] = "Row {$line} ({$sku}): " . implode(', ', $rowErrors);
            } else {
                $rows[] = $row;
            }
        }
        fclose($handle);

        if (!$rows && !$errors) $errors[] = 'CSV file එකේ data rows නොමැත.';
        if ($errors) return back()->with('import_errors', $errors);

        $originalFileName = $request->file('import_file')->getClientOriginalName();

        try {
            DB::transaction(function () use ($rows, $originalFileName) {
                // A fixed control row (ID 1) guarantees that only one successful import can commit.
                if (DB::table('opening_stock_imports')->where('id', 1)->lockForUpdate()->exists()) {
                    throw new \RuntimeException('Opening Stock import එක මීට පෙර භාවිතා කර ඇත.');
                }

                foreach ($rows as $row) {
                    $pieces = (float) $row['pieces_per_purchase_unit'];
                    $sellingBox = (float) $row['selling_price_per_box'];
                    $percentage = (float) $row['percentage'];
                    $purchaseBox = $sellingBox * (1 - ($percentage / 100));
                    $sellingPiece = $sellingBox / $pieces;
                    $unitCost = $purchaseBox / $pieces;
                    $stockPieces = ((float) $row['opening_boxes'] * $pieces)
                        + (float) ($row['opening_loose_pieces'] ?: 0);

                    $category = $this->findOrCreateCategory($row['category'] ?? '');
                    $drugType = $this->findOrCreateDrugType($row['drug_type'] ?? '');
                    $purchaseUnit = $this->findOrCreateUnit($row['purchase_unit'] ?? '', false);
                    $sellingUnit = $this->findOrCreateUnit($row['selling_unit'] ?? '', true);

                    $product = Product::create([
                        'name' => $row['name'],
                        'sku' => $row['sku'],
                        'barcode' => $row['barcode'] ?: null,
                        'category_id' => $category?->id,
                        'drug_type_id' => $drugType?->id,
                        'form_type' => $row['form_type'] ?: null,
                        'strength' => $row['strength'] ?: null,
                        'generic_name' => $row['generic_name'] ?: null,
                        'manufacturer' => $row['manufacturer'] ?: null,
                        'purchase_unit_id' => $purchaseUnit?->id,
                        'pieces_per_purchase_unit' => $pieces,
                        'selling_unit_id' => $sellingUnit?->id,
                        'purchase_price' => round($purchaseBox, 2),
                        'selling_price' => round($sellingPiece, 2),
                        'mrp' => (float) ($row['mrp'] ?: 0),
                        'rack_number' => $row['rack_number'] ?: null,
                        'min_stock' => (float) ($row['min_stock'] ?: 0),
                        'is_active' => true,
                        'track_batch' => true,
                    ]);

                    ProductBatch::create([
                        'product_id' => $product->id,
                        'batch_number' => $row['batch_number'],
                        'manufacturing_date' => $row['manufacturing_date'] ?: null,
                        'expiry_date' => $row['expiry_date'],
                        // Batch purchase price is per piece, unlike product master purchase price (per box).
                        'purchase_price' => round($unitCost, 2),
                        'selling_price' => round($sellingPiece, 2),
                        'quantity' => $stockPieces,
                        'initial_quantity' => $stockPieces,
                        'rack_number' => $row['rack_number'] ?: null,
                    ]);
                }

                // This record permanently closes the opening-stock importer after success.
                DB::table('opening_stock_imports')->insert([
                    'id' => 1,
                    'imported_at' => now(),
                    'imported_by' => auth()->id(),
                    'products_count' => count($rows),
                    'file_name' => $originalFileName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->with('import_errors', [
                'Import එක save කිරීමේදී database error එකක් ඇතිවිය: ' . $e->getMessage(),
                'කිසිදු row එකක් save කර නොමැත.',
            ]);
        }

        return redirect()->route('products.index')->with(
            'success',
            count($rows) . ' products සහ opening-stock batches සාර්ථකව import කරන ලදී.'
        );
    }

    protected function openingStockImportCompleted(): bool
    {
        return Schema::hasTable('opening_stock_imports')
            && DB::table('opening_stock_imports')->where('id', 1)->exists();
    }

    protected function csvNumber(mixed $value): ?float
    {
        $value = str_replace([',', 'Rs.', 'Rs', '%', ' '], '', trim((string) $value));
        return $value !== '' && is_numeric($value) ? (float) $value : null;
    }

    protected function validCsvDate(string $value): bool
    {
        if ($value === '') return false;
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }

    protected function findOrCreateCategory(string $name): ?Category
    {
        $name = trim($name);
        if ($name === '') return null;
        return Category::firstOrCreate(
            ['name' => $name],
            ['slug' => $this->uniqueSlug(Category::class, $name), 'is_active' => true]
        );
    }

    protected function findOrCreateDrugType(string $name): ?DrugType
    {
        $name = trim($name);
        if ($name === '') return null;
        return DrugType::firstOrCreate(
            ['name' => $name],
            ['slug' => $this->uniqueSlug(DrugType::class, $name), 'color' => '#22c55e', 'is_active' => true]
        );
    }

    protected function findOrCreateUnit(string $name, bool $baseUnit): ?Unit
    {
        $name = trim($name);
        if ($name === '') return null;
        return Unit::firstOrCreate(
            ['name' => $name],
            [
                'short_name' => strtolower(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 10)) ?: 'unit',
                'conversion' => 1,
                'is_base_unit' => $baseUnit,
                'is_active' => true,
            ]
        );
    }

    protected function uniqueSlug(string $modelClass, string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $counter = 2;
        while ($modelClass::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }

    public function show(Product $product, StockService $stock)
    {
        $summary = $stock->getStockSummary($product->id);
        return view('inventory.products.show', compact('product', 'summary'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $drugTypes = DrugType::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        return view('inventory.products.edit', compact('product', 'categories', 'drugTypes', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request);
        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $request->route('product')?->id,
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'drug_type_id' => 'nullable|exists:drug_types,id',
            'form_type' => 'nullable|string|max:100',
            'strength' => 'nullable|string|max:100',
            'generic_name' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'purchase_unit_id' => 'nullable|exists:units,id',
            'pieces_per_purchase_unit' => 'nullable|numeric|min:0',
            'selling_unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0',
            'rack_number' => 'nullable|string|max:50',
            'shelf_number' => 'nullable|string|max:50',
            'min_stock' => 'nullable|numeric|min:0',
            'is_prescription_required' => 'boolean',
            'is_active' => 'boolean',
            'track_batch' => 'boolean',
        ]);
    }
}
