<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        $models = [
            'Role',
            'Admin',
            'User',
            'City',
            'Governorate',
            'Area',
            'Shop',
            'Vendor',
            'Driver',
            'Brand',
            'Category',
            'Product',
            'Banner',
            'Service',
            'CategoryAttribute',
            'CategoryDetail',
            'Language',
            'Section',
            'PageSection',
            'Page',
            'Coupon',
            'Package',
            'Gift',
            'PointExchange',
            'PointWallet',
            'PointTransaction',
            'VendorPackage',
            'VendorSubscription',
            'Currency',
            'VendorUser',
            'SellerRegistration',
            'PointRule',
            'Country',
            'Icon',
            'Setting',
            'Badge',
            'ActivityLog',
            'AffiliateWithdrawRequest',
            'Promotion',
            'PromotionRequest',
            'Subscription',
            'Schedule',
            'SaleCountry',
            'Color',
            'QuickAction',
            'PopupCampaign',
            'FlashSale',
            'Store',
            'ProductVariant',
            'ShopProductVariant',
            'UserGift',
            'VendorWithdrawRequest',
            'SystemSetting',
            'VendorAccounting',
            'Unit',
            'Warranty',
            'ProductExtraDetail'
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        $permissions = [];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $permissions[] = strtolower($model).'.'.$action;
            }
        }

        // Add custom permissions for Statistics and Reports (not model-based)
        $customPermissions = [
            'statistics.view',  // View all statistics endpoints
            'reports.view',
            'affiliatewallettransaction.view',
            'driverwallettransaction.view',  // View and export all reports
        ];

        $permissions = array_merge($permissions, $customPermissions);

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

        $employee = Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => 'admin',
        ]);

        // assign permissions to super-admin role
        $superAdminPermissions = Permission::all();
        $superAdmin->syncPermissions($superAdminPermissions);

        // assign permissions to employee role
        $employeePermissions = Permission::whereNotIn('name', [
            'admin.view',
            'admin.create',
            'admin.update',
            'admin.delete',
            'role.view',
            'role.create',
            'role.update',
            'role.delete',
        ])->get();
        $employee->syncPermissions($employeePermissions);

        // create Admins
        $Em1 = Admin::firstOrCreate(
            ['email' => 'admin@deemafashion.com'],
            [
                'name' => 'Deema Admin',
                'password' => 'password',
            ]
        );
        $Em1->assignRole($superAdmin);

        $Em2 = Admin::firstOrCreate(
            ['email' => 'employee@admin.com'],
            [
                'name' => 'Employee',
                'password' => 'password',
            ]
        );
        $Em2->assignRole($employee);
    }
}
