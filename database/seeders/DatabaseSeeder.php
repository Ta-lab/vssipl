<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SuperAdminSeeder::class,
            DepartmentSeeder::class,
            RawMaterialCategorySeeder::class,
            RawMaterialSeeder::class,
            CurrencySeeder::class,
            ModeOfUnitSeeder::class,
            SupplierSeeder::class,
            SupplierProductSeeder::class,
            RackStockmasterSeeder::class,
            RackmasterSeeder::class,
            ItemProcesmasterSeeder::class,
            ProductMasterSeeder::class,
            ChildProductMasterSeeder::class,
            ProductProcessMasterSeeder::class,
            DcMasterSeeder::class,
            CustomerMasterSeeder::class,
            CustomerPoMasterSeeder::class,
            CustomerPoRateReviseSeeder::class,
            CustomerProductMasterSeeder::class,
            StageQrCodeLockSeeder::class,
            BomMasterSeeder::class
        ]);
    }
}
