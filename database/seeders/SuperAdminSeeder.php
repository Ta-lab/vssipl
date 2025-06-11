<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creating Super Admin User
        $superAdmin = User::create([
            'name' => 'SuperAdmin',
            'email' => 'edp@venkateswarasteels.com',
            'username' => '100',
            'password' => Hash::make('admin@123'),
            'prepared_by'=>'1'
        ]);
        $superAdmin->assignRole('Super Admin');

        // Creating Admin User
        $admin = User::create([
            'name' => 'Edp',
            'email' => 'edp@venkateswarasteels.com',
            'username' => '101',
            'password' => Hash::make('vssipl@1234'),
            'prepared_by'=>'1'
        ]);
        $admin->assignRole('Admin');

        // Creating Product Manager User
        $productManager2 = User::create([
            'name' => 'Prabhakar',
            'email' => 'pur@venkateswarasteels.com',
            'username' => '102',
            'password' => Hash::make('ppc@12345'),
            'prepared_by'=>'1'
        ]);
        $productManager2->assignRole('Product Manager');

        $purchase = User::create([
            'name' => 'Purchase',
            'email' => 'pur1@venkateswarasteels.com',
            'username' => '103',
            'password' => Hash::make('pur1@12345'),
            'prepared_by'=>'1'
        ]);
        $purchase->assignRole('Purchase');

        $storekeeper = User::create([
            'name' => 'SakthiVel',
            'email' => 'stores@venkateswarasteels.com',
            'username' => '104',
            'password' => Hash::make('stores@12345'),
            'prepared_by'=>'1'
        ]);
        $storekeeper->assignRole('Store Keeper');

        $sfstorekeeper = User::create([
            'name' => 'Venkatesh',
            'email' => 'stores@venkateswarasteels.com',
            'username' => '105',
            'password' => Hash::make('sfstores@12345'),
            'prepared_by'=>'1'
        ]);
        $sfstorekeeper->assignRole('SF Store Keeper');

        $fgstorekeeper = User::create([
            'name' => 'KESAVALU',
            'email' => 'stores@venkateswarasteels.com',
            'username' => '106',
            'password' => Hash::make('fg@12345'),
            'prepared_by'=>'1'
        ]);
        $fgstorekeeper->assignRole('FG Store Keeper');

        $salesManager1 = User::create([
            'name' => 'Saravanan',
            'email' => 'sales@venkateswarasteels.com',
            'username' => '107',
            'password' => Hash::make('sales@12345'),
            'prepared_by'=>'1'
        ]);
        $salesManager1->assignRole('Sales Manager');

        $iqc1 = User::create([
            'name' => 'Dharani',
            'email' => 'qad@venkateswarasteels.com',
            'username' => '108',
            'password' => Hash::make('iqc@12345'),
            'prepared_by'=>'1'
        ]);
        $iqc1->assignRole('Incoming QC');

        $fqc1 = User::create([
            'name' => 'SakthiVel',
            'email' => 'upload@venkateswarasteels.com',
            'username' => '109',
            'password' => Hash::make('fg@12345'),
            'prepared_by'=>'1'
        ]);
        $fqc1->assignRole('Final QC');

        $qc_manager = User::create([
            'name' => 'Thaigarajan',
            'email' => 'qam@venkateswarasteels.com',
            'username' => '110',
            'password' => Hash::make('qam@12345'),
            'prepared_by'=>'1'
        ]);
        $qc_manager->assignRole('Quality Manager');

        $scrap_incharge = User::create([
            'name' => 'Kalimuthu',
            'email' => 'qad@venkateswarasteels.com',
            'username' => '111',
            'password' => Hash::make('Kalimuthu@12345'),
            'prepared_by'=>'1'
        ]);
        $scrap_incharge->assignRole('Scrap Incharge');

        $user=User::create([
            'name' => 'User',
            'email' => 'edp@venkateswarasteels.com',
            'username' => '112',
            'password' => Hash::make('user@12345'),
            'prepared_by'=>'1'
        ]);
        $user->assignRole('User');

        $productManager = User::create([
            'name' => 'Ravi',
            'email' => 'lab@venkateswarasteels.com',
            'username' => '113',
            'password' => Hash::make('ravi@12345'),
            'prepared_by'=>'1'
        ]);
        $productManager->assignRole('Product Manager');

        $supervisor1 = User::create([
            'name' => 'Kiruba',
            'email' => 'lab@venkateswarasteels.com',
            'username' => '114',
            'password' => Hash::make('venkateshcnc@12345'),
            'prepared_by'=>'1'
        ]);
        $supervisor1->assignRole('Supervisor');

        $supervisor2 = User::create([
            'name' => 'Murugesan',
            'email' => 'pur1@venkateswarasteels.com',
            'username' => '115',
            'password' => Hash::make('murugesancnc@12345'),
            'prepared_by'=>'1'
        ]);
        $supervisor2->assignRole('Supervisor');
        $supervisor3 = User::create([
            'name' => 'Saravanan',
            'email' => 'lab@venkateswarasteels.com',
            'username' => '116',
            'password' => Hash::make('saravanancnc@12345'),
            'prepared_by'=>'1'
        ]);
        $supervisor3->assignRole('Supervisor');
        $supervisor4 = User::create([
            'name' => 'Vasanthi',
            'email' => 'pur1@venkateswarasteels.com',
            'username' => '117',
            'password' => Hash::make('vasanthicnc@12345'),
            'prepared_by'=>'1'
        ]);
        $supervisor4->assignRole('Foreman');
        $supervisor5 = User::create([
            'name' => 'Bala',
            'email' => 'pur1@venkateswarasteels.com',
            'username' => '118',
            'password' => Hash::make('balacnc@12345'),
            'prepared_by'=>'1'
        ]);
        $supervisor5->assignRole('Foreman');
        $supervisor6 = User::create([
            'name' => 'Nagaraj',
            'email' => 'lab@venkateswarasteels.com',
            'username' => '119',
            'password' => Hash::make('nagarajcnc@12345'),
            'prepared_by'=>'1'
        ]);
        $supervisor6->assignRole('Foreman');
        $outstorekeeper = User::create([
            'name' => 'Gurumoorthy',
            'email' => 'stores@venkateswarasteels.com',
            'username' => '120',
            'password' => Hash::make('osstores@12345'),
            'prepared_by'=>'1'
        ]);
        $outstorekeeper->assignRole('OS Store Keeper');
        $ptsstorekeeper = User::create([
            'name' => 'Hema',
            'email' => 'pts@venkateswarasteels.com',
            'username' => '121',
            'password' => Hash::make('ptsstores@12345'),
            'prepared_by'=>'1'
        ]);
        $ptsstorekeeper->assignRole('PTS Store Keeper');
        $ptsproduction = User::create([
            'name' => 'Tamilarasan',
            'email' => 'pts@venkateswarasteels.com',
            'username' => '122',
            'password' => Hash::make('ptsproductions@12345'),
            'prepared_by'=>'1'
        ]);
        $ptsproduction->assignRole('PTS Production Manager');
        $clestorekeeper = User::create([
            'name' => 'CLE',
            'email' => 'pts@venkateswarasteels.com',
            'username' => '123',
            'password' => Hash::make('clestores@12345'),
            'prepared_by'=>'1'
        ]);
        $clestorekeeper->assignRole('CLE Store Keeper');

        $iqc2 = User::create([
            'name' => 'Karthi',
            'email' => 'qad@venkateswarasteels.com',
            'username' => '124',
            'password' => Hash::make('iqc2@12345'),
            'prepared_by'=>'1'
        ]);
        $iqc2->assignRole('Incoming QC');

        $fqc2 = User::create([
            'name' => 'Anand',
            'email' => 'upload@venkateswarasteels.com',
            'username' => '125',
            'password' => Hash::make('fg2@12345'),
            'prepared_by'=>'1'
        ]);
        $fqc2->assignRole('Final QC');

        $fqc3 = User::create([
            'name' => 'Tamilarasan',
            'email' => 'upload@venkateswarasteels.com',
            'username' => '126',
            'password' => Hash::make('fg3@12345'),
            'prepared_by'=>'1'
        ]);
        $fqc3->assignRole('Final QC');

        $salesManager2 = User::create([
            'name' => 'Boobesh',
            'email' => 'sales1@venkateswarasteels.com',
            'username' => '127',
            'password' => Hash::make('sales1@12345'),
            'prepared_by'=>'1'
        ]);
        $salesManager2->assignRole('Sales Manager');
    }
}
