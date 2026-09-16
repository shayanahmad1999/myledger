<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run():void
    {
        Currency::updateOrCreate(['code'=>'PKR'],['name'=>'Pakistani Rupee','symbol'=>'Rs','decimal_places'=>2,'is_active'=>true]);
        Currency::updateOrCreate(['code'=>'USD'],['name'=>'US Dollar','symbol'=>'$','decimal_places'=>2,'is_active'=>true]);
        Currency::updateOrCreate(['code'=>'EUR'],['name'=>'Euro','symbol'=>'€','decimal_places'=>2,'is_active'=>true]);
        Currency::updateOrCreate(['code'=>'GBP'],['name'=>'Pound Sterling','symbol'=>'£','decimal_places'=>2,'is_active'=>true]);
        Currency::updateOrCreate(['code'=>'AED'],['name'=>'UAE Dirham','symbol'=>'AED','decimal_places'=>2,'is_active'=>true]);
        Currency::updateOrCreate(['code'=>'SAR'],['name'=>'Saudi Riyal','symbol'=>'SAR','decimal_places'=>2,'is_active'=>true]);
    }
}
