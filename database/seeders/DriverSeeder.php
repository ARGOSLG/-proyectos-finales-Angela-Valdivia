<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Driver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        Driver::create([
            'company_id'              => $company->id,
            'name'                    => 'Juan Conductor',
            'employee_id'             => 'EMP001',
            'phone'                   => '+5214436123733',
            'license_number'          => 'LIC-12345',
            'emergency_contact_name'  => 'Maria Conductor',
            'emergency_contact_phone' => '+5214436123734',
            'status'                  => 'on_route',
            'password'                => Hash::make('password123'),
        ]);
    }
}