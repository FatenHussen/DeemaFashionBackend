<?php

namespace App\Services\Admin;

use App\Http\Resources\SellerRegistration\AllResource;
use App\Http\Resources\SellerRegistration\OneResource;
use App\Jobs\SendApprovalNotificationJob;
use App\Models\SellerRegistration;
use App\Models\Vendor;
use App\Models\Shop;
use App\Models\VendorUser;
use App\Mail\ServiceProviderApprovedMail;
use App\Mail\VendorCredentialsMail;
use App\Models\VendorPackage;
use App\Models\VendorSubscription;
use App\Services\BaseService;
use App\Exceptions\CustomExceptionWithMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SellerRegistrationService extends BaseService
{
    public function __construct(SellerRegistration $model)
    {
        $this->model = $model;
        $this->resource = OneResource::class;
        $this->collection = AllResource::class;
        $this->relations = ['country', 'city', 'governorate'];
        $this->pagination = true;
        $this->searchableFields = ['seller_name', 'email', 'store_name', 'status'];
    }

    /**
     * Approve seller registration and create vendor account
     */
    public function approve($id, array $data = [])
    {
        $registration = $this->model->findOrFail($id);

        if ($registration->status !== 'pending') {
            throw new CustomExceptionWithMessage('custom.seller_registration.not_pending');
        }

        return DB::transaction(function () use ($registration, $data) {
            $isServiceProvider = (bool) $registration->is_service_provider;

            // Create Vendor
            $vendor = Vendor::create([
                'name' => ['en' => $registration->store_name, 'ar' => $registration->store_name],
                'owner_name' => $registration->seller_name,
                'owner_phone' => $registration->email,
                'commercial_register' => $registration->commercial_register_number,
                'contract_date' => $registration->commercial_register_date ?? now(),
                'contract_number' => 'CN-' . time(),
                'contract_duration_months' => $data['contract_duration_months'] ?? 12,
                'commission_rate' => $data['commission_rate'] ?? 0,
                'is_active' => true,
            ]);

            // Create first Shop for the Vendor
            $shop = Shop::create([
                'vendor_id' => $vendor->id,
                'name' => ['en' => $registration->store_name, 'ar' => $registration->store_name],
                'description' => ['en' => '', 'ar' => ''],
                'address' => ['en' => $registration->address ?? '', 'ar' => $registration->address ?? ''],
                'email' => $registration->email,
                'mobile' => $registration->email, // Using email as placeholder, should be phone number
                'area_id' => null,
                'is_active' => true,
                'is_service_provider' => $isServiceProvider,
                'is_restaurant' => (bool) $registration->is_restaurant,
                'min_order_amount' => $registration->min_order_amount,
                'delivery_min_hours' => $registration->delivery_min_hours,
                'delivery_max_hours' => $registration->delivery_max_hours,
            ]);

            // Handle logo if exists
            if ($registration->logo) {
                // Copy logo to vendor/shop media
                // You can implement media handling here
            }

            if ($isServiceProvider) {
                // Update registration status
                $registration->update(['status' => 'approved']);

                if ($registration->phone) {
                    SendApprovalNotificationJob::dispatch(
                        $registration->phone,
                        'emails.service-provider-approved',
                        [
                            'vendorName' => $registration->seller_name,
                            'shopName' => $registration->store_name,
                        ]
                    );
                }

                // Send service provider approval email (no dashboard credentials)
                Mail::to($registration->email)->send(
                    new ServiceProviderApprovedMail(
                        $registration->seller_name,
                        $registration->store_name
                    )
                );

                return [
                    'vendor_id' => $vendor->id,
                    'shop_id' => $shop->id,
                    'user_id' => null,
                    'is_service_provider' => true,
                ];
            }

            // Generate random password
            $password = Str::random(12);

            // Create VendorUser (Shop User)
            $vendorUser = VendorUser::create([
                'name' => $registration->seller_name,
                'email' => $registration->email,
                'password' => Hash::make($password),
                'vendor_id' => $vendor->id,
                'is_active' => true,
            ]);

            // Attach shop to vendor user
            $vendorUser->shops()->attach($shop->id);

            $package = VendorPackage::first();

            if ($package) {
                VendorSubscription::create([
                    'vendor_id' => $vendor->id,
                    'vendor_package_id' => $package->id,
                    'starts_at' => now(),
                    'ends_at' => now()->addDays($package->duration_days ?? 30),
                    'auto_renew' => false,
                    'status' => 'active',
                ]);
            }
            // Update registration status
            $registration->update(['status' => 'approved']);

                if ($registration->phone) {
                    SendApprovalNotificationJob::dispatch(
                        $registration->phone,
                        'emails.vendor-credentials',
                        [
                            'vendorName' => $registration->seller_name,
                            'email' => $registration->email,
                            'password' => $password,
                            'shopName' => $registration->store_name,
                        ]
                    );
                }

            // Send email with credentials
            Mail::to($registration->email)->send(
                new VendorCredentialsMail(
                    $registration->seller_name,
                    $registration->email,
                    $password,
                    $registration->store_name
                )
            );

            return [
                'vendor_id' => $vendor->id,
                'shop_id' => $shop->id,
                'user_id' => $vendorUser->id,
                'is_service_provider' => false,
            ];
        });
    }

    /**
     * Reject seller registration
     */
    public function reject($id)
    {
        $registration = $this->model->findOrFail($id);

        if ($registration->status !== 'pending') {
            throw new CustomExceptionWithMessage('custom.seller_registration.not_pending');
        }

        $registration->update(['status' => 'rejected']);

        return true;
    }
}
