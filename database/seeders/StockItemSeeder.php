<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Package;
use App\Models\StockItem;
use App\Models\WarehouseProduct;
use App\Models\User;
use Carbon\Carbon;

class StockItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // สร้างหมวดหมู่สินค้า
        $categories = [
            [
                'name' => 'เสาไอ', 
                'code' => 'PILE',
                'description' => 'เสาไอคอนกรีตสำเร็จรูป',
                'color' => '#6c757d',
                'icon' => 'fas fa-columns'
            ],
            [
                'name' => 'แผ่นพื้น', 
                'code' => 'SLAB',
                'description' => 'แผ่นพื้นคอนกรีตสำเร็จรูป',
                'color' => '#17a2b8',
                'icon' => 'fas fa-square'
            ],
            [
                'name' => 'เสาเข็ม', 
                'code' => 'FOUNDATION',
                'description' => 'เสาเข็มคอนกรีตเสริมเหล็ก',
                'color' => '#fd7e14',
                'icon' => 'fas fa-hammer'
            ],
            [
                'name' => 'คานคอนกรีต', 
                'code' => 'BEAM',
                'description' => 'คานคอนกรีตเสริมเหล็ก',
                'color' => '#20c997',
                'icon' => 'fas fa-minus'
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // สร้างผู้จำหน่าย
        $suppliers = [
            [
                'name' => 'บริษัท คอนกรีตไทย จำกัด',
                'code' => 'CONCRETE001',
                'contact_person' => 'นาย สมชาย คอนกรีต',
                'phone' => '02-123-4567',
                'email' => 'sales@concretethai.co.th',
                'address' => '123 ถนนราชดำเนิน เขตดุสิต กรุงเทพฯ 10300'
            ],
            [
                'name' => 'โรงงานเสาเข็ม พัฒนา',
                'code' => 'PILE001',
                'contact_person' => 'นางสาว สมหญิง เสาเข็ม',
                'phone' => '02-234-5678',
                'email' => 'info@pilepattana.com',
                'address' => '456 ถนนพหลโยธิน เขตจตุจักร กรุงเทพฯ 10900'
            ],
            [
                'name' => 'บริษัท เอ็มซี คอนกรีต จำกัด',
                'code' => 'MC001',
                'contact_person' => 'นาย สมศักดิ์ พรีคาสต์',
                'phone' => '02-345-6789',
                'email' => 'contact@mcconcrete.co.th',
                'address' => '789 ถนนรามอินทรา เขตคันนายาว กรุงเทพฯ 10230'
            ],
            [
                'name' => 'โรงงานแผ่นพื้น สยาม',
                'code' => 'SLAB001',
                'contact_person' => 'นางสาว สมใส แผ่นพื้น',
                'phone' => '02-456-7890',
                'email' => 'orders@siamslab.com',
                'address' => '321 ถนนบางนา-ตราด เขตบางนา กรุงเทพฯ 10260'
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        // สร้างคลังสินค้า
        $warehouses = [
            [
                'name' => 'คลังเสาไิ',
                'code' => 'WH001',
                'description' => 'คลังเก็บเสาไอและคานคอนกรีต',
                'address' => 'ลาดกระบัง กรุงเทพฯ',
                'contact_person' => 'นาย สมศักดิ์ คลังดี',
                'phone' => '02-111-2222',
                'max_capacity' => 2000.00,
                'current_usage' => 800.00,
                'is_main' => true
            ],
            [
                'name' => 'คลังแผ่นพื้น',
                'code' => 'WH002',
                'description' => 'คลังเก็บแผ่นพื้นคอนกรีต',
                'address' => 'บางนา กรุงเทพฯ',
                'contact_person' => 'นางสาว สมใส แผ่นพื้น',
                'phone' => '02-222-3333',
                'max_capacity' => 1500.00,
                'current_usage' => 600.00
            ],
            [
                'name' => 'คลังเสาเข็ม',
                'code' => 'WH003',
                'description' => 'คลังเก็บเสาเข็มขนาดใหญ่',
                'address' => 'สำโรง สมุทรปราการ',
                'contact_person' => 'นาย สมหมาย เสาเข็ม',
                'phone' => '02-333-4444',
                'max_capacity' => 3000.00,
                'current_usage' => 1200.00
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }

        // สร้างสินค้า
        $products = [
            [
                'name' => 'เสาไอสี่เหลี่ยม 35x35x600 ซม.',
                'sku' => 'PILE-001',
                'barcode' => '8851234567890',
                'description' => 'เสาไอคอนกรีตสี่เหลี่ยม เสริมเหล็ก',
                'category_id' => 1,
                'supplier_id' => 1,
                'price' => 2800.00,
                'cost' => 2200.00,
                'min_stock' => 10,
                'max_stock' => 100,
                'unit' => 'ต้น',
                'size_type' => 'standard',
                'allow_custom_order' => false,
                'length' => 600.00,
                'thickness' => 35.00,
                'steel_type' => 'wire_6',
                'side_steel_type' => 'show_side_steel',
                'measurement_unit' => 'centimeter'
            ],
            [
                'name' => 'เสาไอกลม เส้นผ่านศูนย์กลาง 40 ซม. ยาว 8 ม.',
                'sku' => 'PILE-002',
                'barcode' => '8851234567891',
                'description' => 'เสาไอคอนกรีตกลม สำหรับอาคารสูง',
                'category_id' => 1,
                'supplier_id' => 1,
                'price' => 4500.00,
                'cost' => 3600.00,
                'min_stock' => 5,
                'max_stock' => 50,
                'unit' => 'ต้น',
                'size_type' => 'custom',
                'custom_size_options' => [
                    'diameters' => ['30 ซม.', '35 ซม.', '40 ซม.', '45 ซม.', '50 ซม.'],
                    'lengths' => ['6 ม.', '8 ม.', '10 ม.', '12 ม.', '15 ม.'],
                    'strengths' => ['280 กก./ตร.ซม.', '350 กก./ตร.ซม.', '400 กก./ตร.ซม.']
                ],
                'allow_custom_order' => true,
                'length' => 800.00,
                'thickness' => 40.00,
                'steel_type' => 'wire_7',
                'side_steel_type' => 'no_side_steel',
                'measurement_unit' => 'centimeter'
            ],
            [
                'name' => 'แผ่นพื้น 120x60x12 ซม.',
                'sku' => 'SLAB-001',
                'barcode' => '8851234567892',
                'description' => 'แผ่นพื้นคอนกรีตสำเร็จรูป',
                'category_id' => 2,
                'supplier_id' => 4,
                'price' => 850.00,
                'cost' => 650.00,
                'min_stock' => 20,
                'max_stock' => 200,
                'unit' => 'แผ่น',
                'size_type' => 'standard',
                'allow_custom_order' => false,
                'length' => 120.00,
                'thickness' => 12.00,
                'steel_type' => 'wire_5',
                'side_steel_type' => 'show_side_steel',
                'measurement_unit' => 'centimeter'
            ],
            [
                'name' => 'แผ่นพื้น 200x100x15 ซม.',
                'sku' => 'SLAB-002',
                'barcode' => '8851234567893',
                'description' => 'แผ่นพื้นคอนกรีตขนาดใหญ่',
                'category_id' => 2,
                'supplier_id' => 4,
                'price' => 1800.00,
                'cost' => 1400.00,
                'min_stock' => 10,
                'max_stock' => 100,
                'unit' => 'แผ่น',
                'size_type' => 'custom',
                'custom_size_options' => [
                    'widths' => ['100 ซม.', '120 ซม.', '150 ซม.', '200 ซม.'],
                    'lengths' => ['200 ซม.', '250 ซม.', '300 ซม.', '400 ซม.'],
                    'thicknesses' => ['12 ซม.', '15 ซม.', '18 ซม.', '20 ซม.'],
                    'finishes' => ['เรียบ', 'ขัดผิว', 'ลายนูน']
                ],
                'allow_custom_order' => true
            ],
            [
                'name' => 'เสาเข็มสี่เหลี่ยม 40x40x1200 ซม.',
                'sku' => 'FOUNDATION-001',
                'barcode' => '8851234567894',
                'description' => 'เสาเข็มคอนกรีตเสริมเหล็กสำหรับฐานราก',
                'category_id' => 3,
                'supplier_id' => 2,
                'price' => 8500.00,
                'cost' => 7000.00,
                'min_stock' => 5,
                'max_stock' => 30,
                'unit' => 'ต้น',
                'size_type' => 'custom',
                'custom_size_options' => [
                    'cross_sections' => ['30x30 ซม.', '35x35 ซม.', '40x40 ซม.', '50x50 ซม.'],
                    'lengths' => ['800 ซม.', '1000 ซม.', '1200 ซม.', '1500 ซม.', '1800 ซม.'],
                    'steel_grades' => ['SD40', 'SD50', 'SD60'],
                    'load_capacities' => ['50 ตัน', '80 ตัน', '100 ตัน', '150 ตัน']
                ],
                'allow_custom_order' => true
            ],
            [
                'name' => 'เสาเข็มกลม เส้นผ่านศูนย์กลาง 60 ซม. ยาว 15 ม.',
                'sku' => 'FOUNDATION-002',
                'barcode' => '8851234567895',
                'description' => 'เสาเข็มคอนกรีตกลมขนาดใหญ่',
                'category_id' => 3,
                'supplier_id' => 2,
                'price' => 15000.00,
                'cost' => 12000.00,
                'min_stock' => 3,
                'max_stock' => 20,
                'unit' => 'ต้น',
                'size_type' => 'custom',
                'custom_size_options' => [
                    'diameters' => ['40 ซม.', '50 ซม.', '60 ซม.', '70 ซม.', '80 ซม.'],
                    'lengths' => ['10 ม.', '12 ม.', '15 ม.', '18 ม.', '20 ม.'],
                    'head_types' => ['แบน', 'มุ่ง', 'สี่เหลี่ยม']
                ],
                'allow_custom_order' => true
            ],
            [
                'name' => 'คานคอนกรีต 20x40x600 ซม.',
                'sku' => 'BEAM-001',
                'barcode' => '8851234567896',
                'description' => 'คานคอนกรีตเสริมเหล็กสำหรับโครงสร้าง',
                'category_id' => 4,
                'supplier_id' => 3,
                'price' => 3200.00,
                'cost' => 2500.00,
                'min_stock' => 8,
                'max_stock' => 80,
                'unit' => 'ท่อน',
                'size_type' => 'standard',
                'allow_custom_order' => false
            ],
            [
                'name' => 'คานคอนกรีต T-Beam 30x50x800 ซม.',
                'sku' => 'BEAM-002',
                'barcode' => '8851234567897',
                'description' => 'คาน T-Beam สำหรับโครงสร้างพิเศษ',
                'category_id' => 4,
                'supplier_id' => 3,
                'price' => 5800.00,
                'cost' => 4600.00,
                'min_stock' => 5,
                'max_stock' => 50,
                'unit' => 'ท่อน',
                'size_type' => 'custom',
                'custom_size_options' => [
                    'web_widths' => ['20 ซม.', '25 ซม.', '30 ซม.', '35 ซม.'],
                    'heights' => ['40 ซม.', '50 ซม.', '60 ซม.', '70 ซม.'],
                    'lengths' => ['600 ซม.', '800 ซม.', '1000 ซม.', '1200 ซม.'],
                    'flange_widths' => ['40 ซม.', '50 ซม.', '60 ซม.', '80 ซม.']
                ],
                'allow_custom_order' => true
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // สร้างแพ็คเกจ
        $packages = [
            [
                'name' => 'แพ็คเกจโครงสร้างอาคาร 3 ชั้น',
                'code' => 'PKG-STRUCT-001',
                'description' => 'แพ็คเกจเสาไอและคานสำหรับอาคาร 3 ชั้น',
                'package_quantity' => 1,
                'length_per_package' => 0,
                'items_per_package' => 35,
                'item_unit' => 'ชิ้น',
                'cost_per_package' => 45000.00,
                'selling_price_per_package' => 58000.00,
                'supplier_id' => 1,
                'category_id' => 1
            ],
            [
                'name' => 'แพ็คเกจพื้นสำเร็จรูป',
                'code' => 'PKG-SLAB-001',
                'description' => 'แพ็คเกจแผ่นพื้นคอนกรีตสำหรับบ้าน 2 ชั้น',
                'package_quantity' => 1,
                'length_per_package' => 0,
                'items_per_package' => 40,
                'item_unit' => 'แผ่น',
                'cost_per_package' => 28000.00,
                'selling_price_per_package' => 36000.00,
                'supplier_id' => 4,
                'category_id' => 2
            ],
            [
                'name' => 'แพ็คเกจฐานรากแข็งแรง',
                'code' => 'PKG-FOUND-001',
                'description' => 'แพ็คเกจเสาเข็มสำหรับอาคารขนาดกลาง',
                'package_quantity' => 1,
                'length_per_package' => 0,
                'items_per_package' => 13,
                'item_unit' => 'ต้น',
                'cost_per_package' => 85000.00,
                'selling_price_per_package' => 110000.00,
                'supplier_id' => 2,
                'category_id' => 3
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }

        // เพิ่มสินค้าในแพ็คเกจ
        $packageProducts = [
            // แพ็คเกจโครงสร้างอาคาร 3 ชั้น
            [
                'package_id' => 1, 
                'product_id' => 1, 
                'quantity_per_package' => 20,
                'unit' => 'ต้น',
                'cost_per_unit' => 2200.00,
                'selling_price_per_unit' => 2800.00,
                'grade' => 'A',
                'size' => '35x35x600 ซม.',
                'is_main_product' => true,
                'sort_order' => 1
            ], // เสาไอสี่เหลี่ยม
            [
                'package_id' => 1, 
                'product_id' => 7, 
                'quantity_per_package' => 15,
                'unit' => 'ท่อน',
                'cost_per_unit' => 2500.00,
                'selling_price_per_unit' => 3200.00,
                'grade' => 'A',
                'size' => '20x40x600 ซม.',
                'sort_order' => 2
            ], // คานคอนกรีต
            
            // แพ็คเกจพื้นสำเร็จรูป
            [
                'package_id' => 2, 
                'product_id' => 3, 
                'quantity_per_package' => 30,
                'unit' => 'แผ่น',
                'cost_per_unit' => 650.00,
                'selling_price_per_unit' => 850.00,
                'grade' => 'A',
                'size' => '120x60x12 ซม.',
                'is_main_product' => true,
                'sort_order' => 1
            ], // แผ่นพื้น 120x60
            [
                'package_id' => 2, 
                'product_id' => 4, 
                'quantity_per_package' => 10,
                'unit' => 'แผ่น',
                'cost_per_unit' => 1400.00,
                'selling_price_per_unit' => 1800.00,
                'grade' => 'A',
                'size' => '200x100x15 ซม.',
                'sort_order' => 2
            ], // แผ่นพื้น 200x100
            
            // แพ็คเกจฐานรากแข็งแรง
            [
                'package_id' => 3, 
                'product_id' => 5, 
                'quantity_per_package' => 8,
                'unit' => 'ต้น',
                'cost_per_unit' => 7000.00,
                'selling_price_per_unit' => 8500.00,
                'grade' => 'A',
                'size' => '40x40x1200 ซม.',
                'is_main_product' => true,
                'sort_order' => 1
            ], // เสาเข็มสี่เหลี่ยม
            [
                'package_id' => 3, 
                'product_id' => 6, 
                'quantity_per_package' => 5,
                'unit' => 'ต้น',
                'cost_per_unit' => 12000.00,
                'selling_price_per_unit' => 15000.00,
                'grade' => 'A',
                'size' => 'Ø60 ซม. x 15 ม.',
                'sort_order' => 2
            ], // เสาเข็มกลม
        ];

        foreach ($packageProducts as $packageProduct) {
            \App\Models\PackageProduct::create($packageProduct);
        }

        // สร้าง Stock Items (รายการสินค้าแต่ละชิ้น)
        $stockItems = [];
        $user = User::first();
        
        // เสาไอสี่เหลี่ยม 35x35x600 ซม. - 15 ต้น
        for ($i = 1; $i <= 15; $i++) {
            $stockItems[] = [
                'product_id' => 1,
                'warehouse_id' => 1, // คลังเสาไอ
                'package_id' => ($i <= 20) ? 1 : null, // 20 ต้นแรกอยู่ในแพ็คเกจ
                'barcode' => 'PI01' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'serial_number' => 'SN-PILE-SQ-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'lot_number' => 'LOT-PI-2024-001',
                'batch_number' => 'BATCH-PI-001',
                'location_code' => 'A-1-' . $i,
                'status' => ($i <= 12) ? 'available' : (($i <= 14) ? 'reserved' : 'sold'),
                'manufacture_date' => Carbon::now()->subDays(15),
                'received_date' => Carbon::now()->subDays(7),
                'cost_price' => 2200.00,
                'selling_price' => 2800.00,
                'grade' => 'A',
                'size' => '35x35x600 ซม.',
                'notes' => 'เสาไอคอนกรีตสี่เหลี่ยม เสริมเหล็ก',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // เสาไอกลม - 8 ต้น
        for ($i = 1; $i <= 8; $i++) {
            $stockItems[] = [
                'product_id' => 2,
                'warehouse_id' => 1,
                'barcode' => 'PI02' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'serial_number' => 'SN-PILE-RD-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'lot_number' => 'LOT-PI-2024-002',
                'batch_number' => 'BATCH-PI-002',
                'location_code' => 'A-2-' . $i,
                'status' => ($i <= 6) ? 'available' : 'reserved',
                'manufacture_date' => Carbon::now()->subDays(20),
                'received_date' => Carbon::now()->subDays(10),
                'cost_price' => 3600.00,
                'selling_price' => 4500.00,
                'grade' => 'A',
                'size' => 'Ø40 ซม. x 8 ม.',
                'notes' => 'เสาไอคอนกรีตกลม สำหรับอาคารสูง',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // แผ่นพื้น 120x60x12 ซม. - 50 แผ่น
        for ($i = 1; $i <= 50; $i++) {
            $stockItems[] = [
                'product_id' => 3,
                'warehouse_id' => 2, // คลังแผ่นพื้น
                'package_id' => ($i <= 30) ? 2 : null,
                'barcode' => 'SL01' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'lot_number' => 'LOT-SL-2024-001',
                'batch_number' => 'BATCH-SL-001',
                'location_code' => 'B-1-' . $i,
                'status' => ($i <= 45) ? 'available' : 'sold',
                'manufacture_date' => Carbon::now()->subDays(10),
                'received_date' => Carbon::now()->subDays(3),
                'cost_price' => 650.00,
                'selling_price' => 850.00,
                'grade' => 'A',
                'size' => '120x60x12 ซม.',
                'notes' => 'แผ่นพื้นคอนกรีตสำเร็จรูป',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // แผ่นพื้น 200x100x15 ซม. - 20 แผ่น
        for ($i = 1; $i <= 20; $i++) {
            $stockItems[] = [
                'product_id' => 4,
                'warehouse_id' => 2,
                'package_id' => ($i <= 10) ? 2 : null,
                'barcode' => 'SL02' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'lot_number' => 'LOT-SL-2024-002',
                'batch_number' => 'BATCH-SL-002',
                'location_code' => 'B-2-' . $i,
                'status' => ($i <= 18) ? 'available' : 'reserved',
                'manufacture_date' => Carbon::now()->subDays(12),
                'received_date' => Carbon::now()->subDays(5),
                'cost_price' => 1400.00,
                'selling_price' => 1800.00,
                'grade' => 'A',
                'size' => '200x100x15 ซม.',
                'notes' => 'แผ่นพื้นคอนกรีตขนาดใหญ่',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // เสาเข็มสี่เหลี่ยม - 12 ต้น
        for ($i = 1; $i <= 12; $i++) {
            $stockItems[] = [
                'product_id' => 5,
                'warehouse_id' => 3, // คลังเสาเข็ม
                'package_id' => ($i <= 8) ? 3 : null,
                'barcode' => 'FN01' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'serial_number' => 'SN-FOUND-SQ-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'lot_number' => 'LOT-FN-2024-001',
                'batch_number' => 'BATCH-FN-001',
                'location_code' => 'C-1-' . $i,
                'status' => ($i <= 10) ? 'available' : 'reserved',
                'manufacture_date' => Carbon::now()->subDays(25),
                'received_date' => Carbon::now()->subDays(12),
                'cost_price' => 7000.00,
                'selling_price' => 8500.00,
                'grade' => 'A',
                'size' => '40x40x1200 ซม.',
                'notes' => 'เสาเข็มคอนกรีตเสริมเหล็กสำหรับฐานราก',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // เสาเข็มกลม - 8 ต้น
        for ($i = 1; $i <= 8; $i++) {
            $stockItems[] = [
                'product_id' => 6,
                'warehouse_id' => 3,
                'package_id' => ($i <= 5) ? 3 : null,
                'barcode' => 'FN02' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'serial_number' => 'SN-FOUND-RD-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'lot_number' => 'LOT-FN-2024-002',
                'batch_number' => 'BATCH-FN-002',
                'location_code' => 'C-2-' . $i,
                'status' => ($i <= 6) ? 'available' : 'reserved',
                'manufacture_date' => Carbon::now()->subDays(30),
                'received_date' => Carbon::now()->subDays(15),
                'cost_price' => 12000.00,
                'selling_price' => 15000.00,
                'grade' => 'A',
                'size' => 'Ø60 ซม. x 15 ม.',
                'notes' => 'เสาเข็มคอนกรีตกลมขนาดใหญ่',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // คานคอนกรีต 20x40x600 ซม. - 25 ท่อน
        for ($i = 1; $i <= 25; $i++) {
            $stockItems[] = [
                'product_id' => 7,
                'warehouse_id' => 1,
                'package_id' => ($i <= 15) ? 1 : null,
                'barcode' => 'BM01' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'lot_number' => 'LOT-BM-2024-001',
                'batch_number' => 'BATCH-BM-001',
                'location_code' => 'A-3-' . $i,
                'status' => ($i <= 22) ? 'available' : 'sold',
                'manufacture_date' => Carbon::now()->subDays(18),
                'received_date' => Carbon::now()->subDays(8),
                'cost_price' => 2500.00,
                'selling_price' => 3200.00,
                'grade' => 'A',
                'size' => '20x40x600 ซม.',
                'notes' => 'คานคอนกรีตเสริมเหล็กสำหรับโครงสร้าง',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // คาน T-Beam - 10 ท่อน
        for ($i = 1; $i <= 10; $i++) {
            $stockItems[] = [
                'product_id' => 8,
                'warehouse_id' => 1,
                'barcode' => 'BM02' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'lot_number' => 'LOT-BM-2024-002',
                'batch_number' => 'BATCH-BM-002',
                'location_code' => 'A-4-' . $i,
                'status' => ($i <= 8) ? 'available' : 'reserved',
                'manufacture_date' => Carbon::now()->subDays(22),
                'received_date' => Carbon::now()->subDays(10),
                'cost_price' => 4600.00,
                'selling_price' => 5800.00,
                'grade' => 'A',
                'size' => '30x50x800 ซม.',
                'notes' => 'คาน T-Beam สำหรับโครงสร้างพิเศษ',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];
        }

        // สร้าง Stock Items ทั้งหมด
        foreach ($stockItems as $item) {
            StockItem::create($item);
        }

        // สร้าง WarehouseProduct สำหรับติดตามจำนวนสินค้าในแต่ละคลัง
        $warehouseProducts = [
            // คลังเสาไอ
            [
                'warehouse_id' => 1, 
                'product_id' => 1, 
                'quantity' => 15,
                'available_quantity' => 12,
                'reserved_quantity' => 3,
                'location_code' => 'A-1'
            ], // เสาไอสี่เหลี่ยม
            [
                'warehouse_id' => 1, 
                'product_id' => 2, 
                'quantity' => 8,
                'available_quantity' => 6,
                'reserved_quantity' => 2,
                'location_code' => 'A-2'
            ],  // เสาไอกลม
            [
                'warehouse_id' => 1, 
                'product_id' => 7, 
                'quantity' => 25,
                'available_quantity' => 22,
                'reserved_quantity' => 3,
                'location_code' => 'A-3'
            ], // คานคอนกรีต
            [
                'warehouse_id' => 1, 
                'product_id' => 8, 
                'quantity' => 10,
                'available_quantity' => 8,
                'reserved_quantity' => 2,
                'location_code' => 'A-4'
            ], // คาน T-Beam
            
            // คลังแผ่นพื้น
            [
                'warehouse_id' => 2, 
                'product_id' => 3, 
                'quantity' => 50,
                'available_quantity' => 45,
                'reserved_quantity' => 5,
                'location_code' => 'B-1'
            ], // แผ่นพื้น 120x60
            [
                'warehouse_id' => 2, 
                'product_id' => 4, 
                'quantity' => 20,
                'available_quantity' => 18,
                'reserved_quantity' => 2,
                'location_code' => 'B-2'
            ], // แผ่นพื้น 200x100
            
            // คลังเสาเข็ม
            [
                'warehouse_id' => 3, 
                'product_id' => 5, 
                'quantity' => 12,
                'available_quantity' => 10,
                'reserved_quantity' => 2,
                'location_code' => 'C-1'
            ], // เสาเข็มสี่เหลี่ยม
            [
                'warehouse_id' => 3, 
                'product_id' => 6, 
                'quantity' => 8,
                'available_quantity' => 6,
                'reserved_quantity' => 2,
                'location_code' => 'C-2'
            ],  // เสาเข็มกลม
        ];

        foreach ($warehouseProducts as $wp) {
            WarehouseProduct::create($wp);
        }

        echo "✅ สร้างข้อมูลตัวอย่างสินค้าคอนกรีตเสร็จสิ้น!\n";
        echo "📊 สถิติข้อมูลที่สร้าง:\n";
        echo "   - หมวดหมู่สินค้า: " . Category::count() . " หมวดหมู่\n";
        echo "   - ผู้จำหน่าย: " . Supplier::count() . " รายการ\n";
        echo "   - คลังสินค้า: " . Warehouse::count() . " คลัง\n";
        echo "   - ผลิตภัณฑ์คอนกรีต: " . Product::count() . " รายการ\n";
        echo "   - แพ็คเกจโครงสร้าง: " . Package::count() . " แพ็คเกจ\n";
        echo "   - รายการสินค้าคอนกรีต: " . StockItem::count() . " ชิ้น\n";
        echo "   - พร้อมจำหน่าย: " . StockItem::where('status', 'available')->count() . " ชิ้น\n";
        echo "   - จองแล้ว: " . StockItem::where('status', 'reserved')->count() . " ชิ้น\n";
        echo "   - ขายแล้ว: " . StockItem::where('status', 'sold')->count() . " ชิ้น\n";
        echo "   - เสียหาย: " . StockItem::where('status', 'damaged')->count() . " ชิ้น\n";
        echo "\n🏗️ ประเภทสินค้าในระบบ:\n";
        echo "   - เสาไอ: " . StockItem::whereIn('product_id', [1, 2])->count() . " ต้น\n";
        echo "   - แผ่นพื้น: " . StockItem::whereIn('product_id', [3, 4])->count() . " แผ่น\n";
        echo "   - เสาเข็ม: " . StockItem::whereIn('product_id', [5, 6])->count() . " ต้น\n";
        echo "   - คานคอนกรีต: " . StockItem::whereIn('product_id', [7, 8])->count() . " ท่อน\n";
        echo "\n🎛️ ประเภทไซส์:\n";
        echo "   - ไซส์มาตรฐาน: " . Product::where('size_type', 'standard')->count() . " รายการ\n";
        echo "   - ไซส์กำหนดเอง: " . Product::where('size_type', 'custom')->count() . " รายการ\n";
        echo "   - รับผลิตตามสั่ง: " . Product::where('allow_custom_order', true)->count() . " รายการ\n";
    }
}
