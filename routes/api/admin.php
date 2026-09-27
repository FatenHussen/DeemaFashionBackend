<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Admin\AdminCrudController;
use App\Http\Controllers\Admin\AffiliateWalletTransaction\AffiliateWalletTransactionController;
use App\Http\Controllers\Admin\AffiliateWithdrawRequest\AffiliateWithdrawRequestController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\Banner\BannerCrudController;
use App\Http\Controllers\Admin\Basket\BasketController;
use App\Http\Controllers\Admin\Basket\ScheduledBasketController;
use App\Http\Controllers\Admin\Brand\BrandController;
use App\Http\Controllers\Admin\Category\CategoryAttributeController;
use App\Http\Controllers\Admin\Category\CategoryController;
use App\Http\Controllers\Admin\Category\CategoryDetailController;
use App\Http\Controllers\Admin\Color\ColorController;
use App\Http\Controllers\Admin\Complaint\ComplaintController;
use App\Http\Controllers\Admin\ContactMethodController;
use App\Http\Controllers\Admin\Country\CountryCrudController;
use App\Http\Controllers\Admin\Coupon\CouponCrudController;
use App\Http\Controllers\Admin\Currency\CurrencyController;
use App\Http\Controllers\Admin\Driver\DriverCrudController;
use App\Http\Controllers\Admin\DriverWalletTransaction\DriverWalletTransactionController;
use App\Http\Controllers\Admin\DeliveryDistanceRange\DeliveryDistanceRangeCrudController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\Gift\GiftController;
use App\Http\Controllers\Admin\Governorate\AreaCrudController as GovernorateAreaCrudController;
use App\Http\Controllers\Admin\Governorate\CityCrudController;
use App\Http\Controllers\Admin\Governorate\GovernorateCrudController;
use App\Http\Controllers\Admin\IconController;
use App\Http\Controllers\Admin\Language\LanguageController;
use App\Http\Controllers\Admin\LegalDocumentController;
use App\Http\Controllers\Admin\NavMenuItem\NavMenuItemController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\Order\OrderController;
use App\Http\Controllers\Admin\Package\PackageController;
use App\Http\Controllers\Admin\Page\PageCrudController;
use App\Http\Controllers\Admin\PageSection\PageSectionCrudController;
use App\Http\Controllers\Admin\PointController;
use App\Http\Controllers\Admin\PointExchange\PointExchangeController;
use App\Http\Controllers\Admin\PointRuleController;
use App\Http\Controllers\Admin\PopupCampaignController;
use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\ProductExtraDetail\ProductExtraDetailController;
use App\Http\Controllers\Admin\ProductVariant\ProductVariantController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\PromotionRequest\PromotionRequestCrudController;
use App\Http\Controllers\Admin\QuickActionController;
use App\Http\Controllers\Admin\Recipe\RecipeCrudController;
use App\Http\Controllers\Admin\Reports\ReportsController;
use App\Http\Controllers\Admin\Role_Permission\PermissionIndexController;
use App\Http\Controllers\Admin\Role_Permission\RoleCrudController;
use App\Http\Controllers\Admin\SaleCountry\SaleCountryCrudController;
use App\Http\Controllers\Admin\Schedule\ScheduleCrudController;
use App\Http\Controllers\Admin\Section\SectionController;
use App\Http\Controllers\Admin\Section\SectionCrudController;
use App\Http\Controllers\Admin\SellerRegistration\SellerRegistrationCrudController;
use App\Http\Controllers\Admin\Service\ServiceCrudController;
use App\Http\Controllers\Admin\ServiceOrder\ServiceOrderController;
use App\Http\Controllers\Admin\CustomOrderRequest\CustomOrderRequestController;
use App\Http\Controllers\Admin\Setting\SettingController;
use App\Http\Controllers\Admin\Shop\ShopCrudController;
use App\Http\Controllers\Admin\ShopProductVariant\ShopProductVariantController;
use App\Http\Controllers\Admin\ShopVendorService\ShopVendorServiceCrudController;
use App\Http\Controllers\Admin\Statistics\StatisticsController;
use App\Http\Controllers\Admin\Store\StoreCrudController;
use App\Http\Controllers\Admin\Subscription\SubscriptionController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\ToggleStatusController;
use App\Http\Controllers\Admin\User\UserCrudController;
use App\Http\Controllers\Admin\UserBasketSchedule\UserBasketScheduleController;
use App\Http\Controllers\Admin\UserGift\UserGiftController as AdminUserGiftController;
use App\Http\Controllers\Admin\UserPoint\UserPointController;
use App\Http\Controllers\Admin\Unit\UnitController;
use App\Http\Controllers\Admin\Warranty\WarrantyController;
use App\Http\Controllers\Admin\Vendor\VendorCrudController;
use App\Http\Controllers\Admin\VendorAccounting\VendorAccountingController;
use App\Http\Controllers\Admin\VendorPackage\VendorPackageController;
use App\Http\Controllers\Admin\VendorService\VendorServiceCrudController;
use App\Http\Controllers\Admin\VendorServiceType\VendorServiceTypeCrudController;
use App\Http\Controllers\Admin\VendorSubscription\VendorSubscriptionController;
use App\Http\Controllers\Admin\VendorUser\VendorUserCrudController;
use App\Http\Controllers\Admin\VendorWithdrawRequest\VendorWithdrawRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    // -------------------------------------------------------------------------
    // Authentication (login is public; logout/profile/etc. require auth:admin)
    // -------------------------------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:admin')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('profile', [AuthController::class, 'profile']);
            Route::post('/store-token', [AuthController::class, 'storOrUpdateToken']);
            Route::get('/notifications', [AuthController::class, 'notifications']);
        });
    });

    // -------------------------------------------------------------------------
    // Protected admin area
    // -------------------------------------------------------------------------
    Route::middleware('auth:admin')->group(function () {

        // --- Activity, notifications & shared utilities ---
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->middleware('admin.permission:activitylog.view');
        Route::apiResource('notifications', NotificationController::class)
            ->middleware('crud.permission:notification');
        Route::apiResource('legal-documents', LegalDocumentController::class)
            ->middleware('crud.permission:legaldocument');
        Route::post('/toggle-status', [ToggleStatusController::class, 'toggleStatus']);

        // --- Baskets, schedules & commerce core ---
        Route::apiResource('baskets', BasketController::class)->middleware('crud.permission:basket');
        Route::apiResource('scheduled-baskets', ScheduledBasketController::class)
            ->middleware('crud.permission:schedulebasket');
        Route::apiResource('schedules', ScheduleCrudController::class)->middleware('crud.permission:schedule');
        Route::apiResource('packages', PackageController::class)->middleware('crud.permission:package');
        Route::apiResource('subscriptions', SubscriptionController::class)->middleware('crud.permission:subscription');
        Route::post('gifts/bulk', [GiftController::class, 'bulkStore'])
            ->middleware('admin.permission:gift.create');
        Route::apiResource('gifts', GiftController::class)->middleware('crud.permission:gift');
        Route::apiResource('user-gifts', AdminUserGiftController::class)->middleware('crud.permission:usergift');
        Route::apiResource('point-exchanges', PointExchangeController::class)
            ->only(['index', 'show', 'update'])
            ->middleware('crud.permission:pointexchange');

        // --- Vendor packages & subscriptions ---
        Route::apiResource('vendor-packages', VendorPackageController::class)
            ->middleware('crud.permission:vendorpackage');
        Route::apiResource('vendor-subscriptions', VendorSubscriptionController::class)
            ->middleware('crud.permission:vendorsubscription');

        // --- Currencies ---
        Route::apiResource('currencies', CurrencyController::class)->middleware('crud.permission:currency');

        // --- User points ---
        Route::prefix('user-points')->middleware('admin.permission:pointwallet.view')->group(function () {
            Route::get('/', [UserPointController::class, 'index']);
            Route::get('/{userId}', [UserPointController::class, 'show']);
            Route::get('/{userId}/transactions', [UserPointController::class, 'transactions']);
        });

        // --- Point rules ---
        Route::apiResource('point-rules', PointRuleController::class)->middleware('crud.permission:pointrule');
        Route::apiResource('delivery-distance-ranges', DeliveryDistanceRangeCrudController::class);

        // --- Icons & quick actions ---
        Route::apiResource('icons', IconController::class)->middleware('crud.permission:icon');
        Route::apiResource('quick-actions', QuickActionController::class)->middleware('crud.permission:quickaction');
        Route::apiResource('units', UnitController::class);
        Route::apiResource('warranties', WarrantyController::class);

        // --- Statistics ---
        Route::prefix('statistics')->middleware('admin.permission:statistics.view')->group(function () {
            Route::get('/dashboard', [StatisticsController::class, 'dashboard']);
            Route::get('/counts', [StatisticsController::class, 'counts']);
            Route::get('/monthly-performance', [StatisticsController::class, 'monthlyPerformance']);
            Route::get('/orders-by-status', [StatisticsController::class, 'ordersByStatus']);
            Route::get('/top-shops', [StatisticsController::class, 'topShops']);
            Route::get('/revenue-trend', [StatisticsController::class, 'revenueTrend']);
            Route::get('/orders-by-hour', [StatisticsController::class, 'ordersByHour']);
            Route::get('/orders-by-day', [StatisticsController::class, 'ordersByDayOfWeek']);
            Route::get('/revenue-by-payment', [StatisticsController::class, 'revenueByPaymentMethod']);
            Route::get('/top-categories', [StatisticsController::class, 'topCategoriesByRevenue']);
            Route::get('/user-growth', [StatisticsController::class, 'userGrowth']);
            Route::get('/order-funnel', [StatisticsController::class, 'orderStatusFunnel']);
            Route::get('/avg-order-value-trend', [StatisticsController::class, 'averageOrderValueTrend']);
            Route::get('/driver-comparison', [StatisticsController::class, 'driverPerformanceComparison']);
            Route::get('/stock-levels', [StatisticsController::class, 'productStockLevels']);
            Route::get('/sales-heatmap', [StatisticsController::class, 'salesHeatmap']);
        });

        // --- Reports ---
        Route::prefix('reports')->middleware('admin.permission:reports.view')->group(function () {
            Route::get('/sales', [ReportsController::class, 'sales']);
            Route::get('/product-movement', [ReportsController::class, 'productMovement']);
            Route::get('/vendor-performance/{vendorId}', [ReportsController::class, 'vendorPerformance']);
            Route::get('/driver-performance/{driverId}', [ReportsController::class, 'driverPerformance']);
            Route::get('/sales-by-location', [ReportsController::class, 'salesByLocation']);
            Route::get('/sales-by-category', [ReportsController::class, 'salesByCategory']);
            Route::get('/export/sales', [ReportsController::class, 'exportSales']);
            Route::get('/export/product-movement', [ReportsController::class, 'exportProductMovement']);
            Route::get('/export/vendor-performance/{vendorId}', [ReportsController::class, 'exportVendorPerformance']);
            Route::get('/export/driver-performance/{driverId}', [ReportsController::class, 'exportDriverPerformance']);
        });

        // --- Settings (Setting model) ---
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->middleware('admin.permission:setting.view');
            Route::get('/{key}', [SettingController::class, 'show'])->middleware('admin.permission:setting.view');
            Route::put('/{key}', [SettingController::class, 'update'])->middleware('admin.permission:setting.update');
        });

        // --- System settings ---
        Route::prefix('system-settings')->group(function () {
            Route::middleware('admin.permission:systemsetting.view')->group(function () {
                Route::get('/', [SystemSettingController::class, 'index']);
                Route::get('/group/{group}', [SystemSettingController::class, 'getByGroup']);
                Route::get('/{key}', [SystemSettingController::class, 'show']);
            });
            Route::middleware('admin.permission:systemsetting.update')->group(function () {
                Route::post('/batch', [SystemSettingController::class, 'updateBatch']);
                Route::post('/clear-cache', [SystemSettingController::class, 'clearCache']);
                Route::put('/{key}', [SystemSettingController::class, 'update']);
                Route::post('/', [SystemSettingController::class, 'store']);
            });
            Route::delete('/{key}', [SystemSettingController::class, 'destroy'])
                ->middleware('admin.permission:systemsetting.delete');
        });

        // --- User basket schedules ---
        Route::prefix('user-basket-schedules')->group(function () {
            Route::middleware('admin.permission:userbasketschedule.view')->group(function () {
                Route::get('/', [UserBasketScheduleController::class, 'index']);
                Route::get('/{id}', [UserBasketScheduleController::class, 'get_one']);
            });
            Route::post('/{id}/disable', [UserBasketScheduleController::class, 'disable'])
                ->middleware('admin.permission:userbasketschedule.update');
            Route::post('/{id}/enable', [UserBasketScheduleController::class, 'enable'])
                ->middleware('admin.permission:userbasketschedule.update');
        });

        // --- Section helpers (pages, item types, display types) ---
        Route::prefix('sections')->middleware('admin.permission:section.view')->group(function () {
            Route::get('pages', [SectionController::class, 'pages']);
            Route::get('item-types', [SectionController::class, 'sectionItemTypes']);
            Route::get('display-types', [SectionController::class, 'displayTypes']);
        });

        // Route::get('page-sections/display-types/{manual_model}', [PageSectionCrudController::class, 'displayTypes']);
        Route::post('categories/sort', [CategoryController::class, 'sort']);
        Route::post('brands/sort', [BrandController::class, 'sort']);

        Route::resources([
            'stores'         => StoreCrudController::class,
            // 'shops'          => ShopCrudController::class,
            // 'vendors'        => VendorCrudController::class,
            'languages'      => LanguageController::class,
            'categories' => CategoryController::class,
            'brands' => BrandController::class,
            'colors' => ColorController::class,
            'category-attributes' => CategoryAttributeController::class,
            'category-details' => CategoryDetailController::class,
            'products' => ProductController::class,
            'product-variants' => ProductVariantController::class,
            'shop-product-variants' => ShopProductVariantController::class,
            'sale-countries' => \App\Http\Controllers\Admin\SaleCountry\SaleCountryCrudController::class,
        ]);

        // --- Bulk `Route::resources` (catalog & related) ---
        Route::resource('languages', LanguageController::class)->middleware('crud.permission:language');
        Route::resource('categories', CategoryController::class)->middleware('crud.permission:category');
        Route::resource('brands', BrandController::class)->middleware('crud.permission:brand');
        Route::resource('colors', ColorController::class)->middleware('crud.permission:color');
        Route::resource('category-attributes', CategoryAttributeController::class)
            ->middleware('crud.permission:categoryattribute');
        Route::resource('category-details', CategoryDetailController::class)
            ->middleware('crud.permission:categorydetail');

        // Product Excel import — must be registered before products/{product}
        Route::get('products/import-template', [ProductController::class, 'downloadImportTemplate'])
            ->middleware('admin.permission:product.view');
        Route::post('products/import', [ProductController::class, 'import'])
            ->middleware('admin.permission:product.create');

        Route::resource('products', ProductController::class)->middleware('crud.permission:product');
        Route::apiResource('product-extra-details', ProductExtraDetailController::class)
            ->middleware('crud.permission:productextradetail');
        Route::resource('product-variants', ProductVariantController::class)
            ->middleware('crud.permission:productvariant');
        Route::resource('shop-product-variants', ShopProductVariantController::class)
            ->middleware('crud.permission:shopproductvariant');
        Route::resource('sale-countries', SaleCountryCrudController::class)->middleware('crud.permission:salecountry');

        // --- Product approval & variants by product ---
        Route::post('products/{id}/approve', [ProductController::class, 'approve'])
            ->middleware('admin.permission:product.update');
        Route::post('products/{id}/reject', [ProductController::class, 'reject'])
            ->middleware('admin.permission:product.update');
        Route::get('products/{product}/variants', [ProductVariantController::class, 'byProduct'])
            ->middleware('admin.permission:productvariant.view');

        // --- Delete impact preview (what will be deleted / affected) ---
        Route::get('product-variants/{id}/delete-impact', [ProductVariantController::class, 'deleteImpact'])
            ->middleware('admin.permission:productvariant.delete');
        Route::get('shop-product-variants/{id}/delete-impact', [ShopProductVariantController::class, 'deleteImpact'])
            ->middleware('admin.permission:shopproductvariant.delete');
        Route::get('category-attributes/{id}/delete-impact', [CategoryAttributeController::class, 'deleteImpact'])
            ->middleware('admin.permission:categoryattribute.delete');
        Route::get('category-attributes/{id}/linked-items', [CategoryAttributeController::class, 'linkedItems'])
            ->middleware('admin.permission:categoryattribute.view');
        Route::get('categories/{id}/delete-impact', [CategoryController::class, 'deleteImpact'])
            ->middleware('admin.permission:category.delete');
        Route::get('categories/{id}/linked-items', [CategoryController::class, 'linkedItems'])
            ->middleware('admin.permission:category.view');

        Route::apiResource('shops', ShopCrudController::class)->middleware('crud.permission:shops');
        Route::apiResource('stores', StoreCrudController::class)->middleware('crud.permission:store');
        Route::apiResource('vendors', VendorCrudController::class)->middleware('crud.permission:vendor');
        Route::apiResource('roles', RoleCrudController::class)->middleware('crud.permission:role');
        Route::get('permissions', [PermissionIndexController::class, 'index'])
            ->middleware('admin.permission:role.view');
        Route::apiResource('banners', BannerCrudController::class)->middleware('crud.permission:banner');
        Route::apiResource('admins', AdminCrudController::class)->middleware('crud.permission:admin');
        Route::apiResource('drivers', DriverCrudController::class)->middleware('crud.permission:driver');
        Route::apiResource('governorates', GovernorateCrudController::class)->middleware('crud.permission:governorate');
        Route::apiResource('cities', CityCrudController::class)->middleware('crud.permission:city');
        Route::apiResource('areas', GovernorateAreaCrudController::class)->middleware('crud.permission:area');
        Route::apiResource('countries', CountryCrudController::class)->middleware('crud.permission:country');
        Route::apiResource('services', ServiceCrudController::class)->middleware('crud.permission:service');
        Route::apiResource('vendor-service-types', VendorServiceTypeCrudController::class)
            ->middleware('crud.permission:vendorservicetype');
        Route::apiResource('vendor-services', VendorServiceCrudController::class)
            ->middleware('crud.permission:vendorservice');
        Route::apiResource('shop-vendor-services', ShopVendorServiceCrudController::class)
            ->middleware('crud.permission:shopvendorservice');
        Route::apiResource('sections', SectionCrudController::class)->middleware('crud.permission:section');
        Route::apiResource('pages', PageCrudController::class)->middleware('crud.permission:page');
        Route::post('pages/{page}/sections', [PageCrudController::class, 'addSection'])
            ->middleware('admin.permission:pagesection.create');
        Route::get('pages/{page}/sliders', [PageCrudController::class, 'slidersForPage'])
            ->middleware('admin.permission:pagesection.view');
        Route::get('page-sections/pages/{page}/preview', [PageSectionCrudController::class, 'preview'])
            ->middleware('admin.permission:pagesection.view');
        Route::post('page-sections/pages/{page}/reorder', [PageSectionCrudController::class, 'reorder'])
            ->middleware('admin.permission:pagesection.update');
        Route::apiResource('page-sections', PageSectionCrudController::class)->middleware('crud.permission:pagesection');
        // --- Navigation menu (top bar) ---
        Route::get('nav-menu-items/route-keys', [NavMenuItemController::class, 'routeKeys'])
            ->middleware('admin.permission:navmenuitem.view');
        Route::post('nav-menu-items/reorder', [NavMenuItemController::class, 'sort'])
            ->middleware('admin.permission:navmenuitem.update');
        Route::apiResource('nav-menu-items', NavMenuItemController::class)
            ->middleware('crud.permission:navmenuitem');

        Route::apiResource('coupons', CouponCrudController::class)->middleware('crud.permission:coupon');
        Route::apiResource('complaints', ComplaintController::class)->middleware('crud.permission:complaint');
        Route::apiResource('recipes', RecipeCrudController::class)->middleware('crud.permission:recipe');
        Route::apiResource('faqs', FaqController::class)->middleware('crud.permission:faq');
        Route::apiResource('popup-campaigns', PopupCampaignController::class)->middleware('crud.permission:popupcampaign');
        Route::apiResource('badges', BadgeController::class)->middleware('crud.permission:badge');
        Route::get('promotions/fields-for-type/{type}', [PromotionController::class, 'fieldsForType'])
            ->middleware('crud.permission:promotion');
        Route::apiResource('promotions', PromotionController::class)->middleware('crud.permission:promotion');
        Route::apiResource('contact-methods', ContactMethodController::class);
        Route::apiResource('flash-sales', FlashSaleController::class)
            ->only(['index', 'store', 'show', 'update'])
            ->middleware('crud.permission:flashsale');
        Route::apiResource('affiliate-withdraw-requests', AffiliateWithdrawRequestController::class)
            ->only(['index', 'show', 'update'])
            ->middleware('crud.permission:affiliatewithdrawrequest');
        Route::apiResource('affiliate-wallet-transactions', AffiliateWalletTransactionController::class)
            ->only(['index', 'show'])
            ->middleware('crud.permission:affiliatewallettransaction');
        Route::apiResource('driver-wallet-transactions', DriverWalletTransactionController::class)
            ->only(['index', 'show'])
            ->middleware('crud.permission:driverwallettransaction');
        Route::apiResource('vendor-withdraw-requests', VendorWithdrawRequestController::class)
            ->only(['index', 'show', 'update'])
            ->middleware('crud.permission:vendorwithdrawrequest');

        // --- Vendor accounting ---
        Route::prefix('vendor-accounting')->middleware('admin.permission:vendoraccounting.view')->group(function () {
            Route::get('/summary', [VendorAccountingController::class, 'summary']);
            Route::get('/vendors', [VendorAccountingController::class, 'index']);
            Route::get('/vendors/{vendorId}', [VendorAccountingController::class, 'show']);
        });

        // --- Seller registration ---
        Route::apiResource('seller-registrations', SellerRegistrationCrudController::class)
            ->only(['index', 'show', 'destroy'])
            ->middleware('crud.permission:sellerregistration');
        Route::post('seller-registrations/{id}/approve', [SellerRegistrationCrudController::class, 'approve'])
            ->middleware('admin.permission:sellerregistration.update');
        Route::post('seller-registrations/{id}/reject', [SellerRegistrationCrudController::class, 'reject'])
            ->middleware('admin.permission:sellerregistration.update');

        // --- Promotion requests (`stats/summary` must register before `{id}` routes) ---
        Route::get('promotion-requests/stats/summary', [PromotionRequestCrudController::class, 'stats'])
            ->middleware('admin.permission:promotionrequest.view');
        Route::apiResource('promotion-requests', PromotionRequestCrudController::class)
            ->only(['index', 'show', 'destroy'])
            ->middleware('crud.permission:promotionrequest');
        Route::post('promotion-requests/{id}/approve', [PromotionRequestCrudController::class, 'approve'])
            ->middleware('admin.permission:promotionrequest.update');
        Route::post('promotion-requests/{id}/reject', [PromotionRequestCrudController::class, 'reject'])
            ->middleware('admin.permission:promotionrequest.update');

        // --- Vendor users & end-users ---
        Route::apiResource('vendor-users', VendorUserCrudController::class)->middleware('crud.permission:vendoruser');
        Route::get('users/markters', [UserCrudController::class, 'markters'])
            ->middleware('admin.permission:user.view');
        Route::post('users/{id}/demote-affiliate', [UserCrudController::class, 'demoteAffiliate'])
            ->middleware('admin.permission:user.update');
        Route::post('users/{id}/reactivate-affiliate', [UserCrudController::class, 'reactivateAffiliate'])
            ->middleware('admin.permission:user.update');
        Route::apiResource('users', UserCrudController::class)->middleware('crud.permission:user');
    });

    // -------------------------------------------------------------------------
    // Orders & service orders
    // -------------------------------------------------------------------------
    Route::middleware('auth:admin')->prefix('orders')->group(function () {
        Route::middleware('admin.permission:order.view')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('{id}/get_one', [OrderController::class, 'get_one']);
            Route::get('/to-assign', [OrderController::class, 'ordersToAssign']);
        });
        Route::middleware('admin.permission:order.update')->group(function () {
            Route::patch('{orderId}/change-status', [OrderController::class, 'changeStatus']);
            Route::patch('{orderId}/scheduled-delivery', [OrderController::class, 'setScheduledDelivery']);
            Route::post('{orderId}/assign-driver', [OrderController::class, 'assignDriver']);
            Route::patch('items/{itemId}/change-status', [OrderController::class, 'changeItemStatus']);
        });
    });

    Route::middleware('auth:admin')->prefix('service-orders')->group(function () {
        Route::middleware('admin.permission:serviceorder.view')->group(function () {
            Route::get('/', [ServiceOrderController::class, 'index']);
            Route::get('{id}/get_one', [ServiceOrderController::class, 'get_one']);
        });
        Route::patch('{orderId}/change-status', [ServiceOrderController::class, 'changeStatus'])
            ->middleware('admin.permission:serviceorder.update');
    });

    Route::middleware('auth:admin')->prefix('custom-order-requests')->group(function () {
        Route::middleware('admin.permission:customorderrequest.view')->group(function () {
            Route::get('/', [CustomOrderRequestController::class, 'index']);
            Route::get('{id}/get_one', [CustomOrderRequestController::class, 'get_one']);
        });
        Route::middleware('admin.permission:customorderrequest.update')->group(function () {
            Route::post('{id}/convert', [CustomOrderRequestController::class, 'convert']);
            Route::post('{id}/cancel', [CustomOrderRequestController::class, 'cancel']);
        });
    });
    Route::middleware('auth:admin')->group(function () {
        Route::prefix('points')->group(function () {
            Route::post('add', [PointController::class, 'addPoints']);
            Route::post('deduct', [PointController::class, 'deductPoints']);
            Route::get('user-summary', [PointController::class, 'getUserSummary']);
            Route::get('user-transactions', [PointController::class, 'getUserTransactions']);
        });
    });
});

// Protected routes
// Basket management routes

// // Points management routes

//     Route::apiResource('point-rules', PointRuleController::class);
// });

// System Settings routes
// Route::middleware('auth:admin')->group(function () {
//     Route::prefix('settings')->group(function () {
//         Route::get('/', [\App\Http\Controllers\Admin\SystemSettingController::class, 'index']);
//         Route::get('/group/{group}', [\App\Http\Controllers\Admin\SystemSettingController::class, 'getByGroup']);
//         Route::post('/batch', [\App\Http\Controllers\Admin\SystemSettingController::class, 'updateBatch']);
//         Route::post('/clear-cache', [\App\Http\Controllers\Admin\SystemSettingController::class, 'clearCache']);
//         Route::get('/{key}', [\App\Http\Controllers\Admin\SystemSettingController::class, 'show']);
//         Route::put('/{key}', [\App\Http\Controllers\Admin\SystemSettingController::class, 'update']);
//         Route::post('/', [\App\Http\Controllers\Admin\SystemSettingController::class, 'store']);
//         Route::delete('/{key}', [\App\Http\Controllers\Admin\SystemSettingController::class, 'destroy']);
//     });
// });
