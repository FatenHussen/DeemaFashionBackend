<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $models = [
            'Role',
            'Admin',
            'User',
            'City',
            'Governorate',
            'Area',
            'Vendor',
            'Shop',
            'Coupon',
            'Complaint',
            'Order',
            'Product',
            'Banner',
            'Category',
            'Service',
            'Section',
            'PageSection',
            'Page',
            'Brand',
            'Driver',
            'Language',
            'Recipe',
            'Basket',
            'ScheduleBasket',
            'UserBasketSchedule',
            'Faq',
            'Setting',
            'LegalDocument',
            'Notification',
            'VendorPackage',
            'ServiceOrder',
            'CustomOrderRequest',
            'VendorService',
            'VendorServiceType',
            'ShopVendorService',
            'DeliveryDistanceRange',
            'NavMenuItem',
            'Unit',
            'Warranty',
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        $permissions = [];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $permissions[] = strtolower($model) . '.' . $action;
            }
        }
        $permissions[] = 'stats.index';
        $permissions[] = 'activitylog.index';

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }
        $superAdmin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'admin',
        ]);

        // $admin = Role::firstOrCreate([
        //     'name' => 'vendor',
        //     'guard_name' => 'admin',

        // ]);

        // $employee   = Role::firstOrCreate([
        //     'name' => 'employee',
        //     'guard_name' => 'admin',
        // ]);

        // //assign permissions to super-admin role
        $superAdminPermissions = Permission::all();
        $superAdmin->syncPermissions($superAdminPermissions);


        // //assign permissions to employee role
        // $employeePermissions = Permission::whereIn('name', [
        //     'patient.view',
        //     'patient.create',
        //     'patient.update',
        //     'patient.delete',
        //     'appointment.view',
        //     'appointment.create',
        //     'appointment.update',
        //     'appointment.delete',
        // ])->get();
        // $employee->syncPermissions($employeePermissions);

        // //assign permissions to admin role
        // $adminPermissions = Permission::whereNotIn('name', [
        //     'admin.view',
        //     'admin.create',
        //     'admin.update',
        //     'admin.delete',
        //     'role.view',
        //     'role.create',
        //     'role.update',
        //     'role.delete',
        // ])->get();

        // $admin->syncPermissions($adminPermissions);


        //create Admins
        $Em1 = Admin::firstOrCreate(
            ['email' => 'admin@deemafashion.com'],
            [
                'name' => 'Deema Admin',
                'password' => bcrypt('password'),
            ]
        );
        $Em1->assignRole($superAdmin);


        // $Em2 = Admin::firstOrCreate(
        //     ['email' => 'admin@admin.com'],
        //     [
        //         'name' => 'Admin',
        //         'password' => bcrypt('password'),
        //     ]
        // );
        // $Em2->assignRole($admin);


        // $Em3 = Admin::firstOrCreate(
        //     ['email' => 'employee@admin.com'],
        //     [
        //         'name' => 'Employee',
        //         'password' => bcrypt('password'),
        //     ]
        // );
        // $Em3->assignRole($employee);
    }
}
