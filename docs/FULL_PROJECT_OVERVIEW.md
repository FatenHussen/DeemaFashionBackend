# Deema Fashion Backend — من أول استنساخ حتى الوضع الحالي

مرجع واحد يصف **كل ما تم بناؤه وتشغيله** في الـ Backend من بداية المشروع حتى الحالة الحالية.  
بدون تواريخ — تركيز على ماذا يوجد وكيف يعمل.

> للتفاصيل اليومية للفرونت استخدم: `docs/frontend/` و `docs/custom-orders/`.  
> لمرجع الـ API التقني استخدم: `docs/api/`.  
> **الهوية البصرية (منفصل عن تيك مارت):** [`frontend/DEEMA_BRAND_IDENTITY.md`](./frontend/DEEMA_BRAND_IDENTITY.md)

---

## 1) هوية المشروع

| البند | القيمة |
|--------|--------|
| الاسم | **Deema Fashion** / **ديما فاشن** |
| الاسم في الوثائق | **Deema Fashion** (منفصل عن تيك مارت / Tikmool) |
| المستودع | DeemaFashionBackend |
| النطاق | `deemafashion.com` |
| الألوان الأساسية | `#c720a4` (ماجنتا) · `#ff1493` (وردي) — من `GET /api/user/settings` |
| النوع | منصة تجارة إلكترونية / ماركت بليس متعدد الأطراف |
| السوق | سوريا — عملتان (USD / SYP) — محافظات / مدن / مناطق — توصيل |
| التقنيات الأساسية | Laravel 12 · PHP 8.2+ · Sanctum · Spatie Permission · Spatie Translatable · Filament (بائع) · Reverb · Excel · PDF |

**الأطراف المرتبطة بالـ Backend:**

1. **مستخدم** — موقع ويب + تطبيق Flutter  
2. **أدمن / موظف** — داشبورد إداري عبر API  
3. **سائق** — تطبيق سائق  
4. **بائع (Vendor)** — لوحة Filament (+ API محدود)

---

## 2) التشغيل من أول Clone

### المتطلبات

- PHP ^8.2  
- Composer  
- Node.js + npm (لـ Vite)  
- قاعدة بيانات (الافتراضي في `.env.example`: SQLite؛ الإنتاج عادة MySQL/PostgreSQL)

### أوامر الإعداد (من `composer.json` script `setup`)

```bash
composer install
cp .env.example .env          # أو نسخ يدوي على Windows
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

### أوامر مفيدة بعد الإعداد

```bash
# تطوير: سيرفر + طابور + لوجات + Vite
composer run dev

# اختبارات
composer test

# هيكل ناقص فقط (آمن — لا يمسح داتا)
php artisan migrate

# صلاحيات ناقصة فقط (CustomOrderRequest وغيرها)
php artisan db:seed --class=PermissionSeeder   # أو السيدر المعتمد في المشروع للصلاحيات الناقصة
```

### شكل الرد الموحّد لكل الـ APIs

```json
{
  "success": true,
  "message": "...",
  "data": {}
}
```

### Base URLs

| الطرف | المسار | المصادقة |
|--------|--------|----------|
| أدمن | `/api/admin` | Bearer (guard: `admin`) |
| مستخدم | `/api/user` | Bearer (guard: `user`) |
| سائق | `/api/driver` | Bearer (guard: `driver`) |
| بائع | `/api/vendor` | `auth:vendor-user` (محدود) + لوحة Filament |
| مشترك | `/api/notifications`, `/api/popups/*`, `/api/socket` | حسب المسار |

ملفات المسارات: `routes/api.php` → `admin.php` · `user.php` · `driver.php` · `vendor.php` · `socket.php`

---

## 3) المصادقة والصلاحيات

### الحراس (Guards)

| Guard | النوع | النموذج |
|-------|--------|---------|
| `web` | session | User |
| `user` | Sanctum | User |
| `admin` | Sanctum | Admin |
| `driver` | Sanctum | Driver |
| `vendor-user` | session | VendorUser |

### أدمن — Spatie Roles & Permissions

- أدوار أساسية: **`admin`** (كل الصلاحيات) · **`employee`** (كل شيء ما عدا إدارة الأدمن/الأدوار غالباً)
- نمط الصلاحيات: `{model}.{view|create|update|delete}`
- أمثلة إضافية: `statistics.view` · `reports.view` · `customorderrequest.view` · `customorderrequest.update`
- Middleware: `auth:admin` · `crud.permission:*` · `admin.permission:*`

### مستخدم

- تسجيل / OTP / دخول / استعادة كلمة المرور / بروفايل  
- التسجيل **بدون إيميل إلزامي** (هاتف + OTP) حسب وثائق الفرونت  
- تسجيل بائع (seller-register) · طلب انضمام كمسوّق (affiliate/marketer)

### سائق

- دخول + تدفق كلمة مرور/OTP حسب الـ API  
- FCM · مستندات قانونية · طرق تواصل · دورة حياة الطلبات

### بائع

- لوحة **Filament**: طلبات · منتجات · متاجر · مخزون · باقات · اشتراكات · سحوبات · ودجات لوحة  
- API بائع محدود (مثل حذف توكن FCM)

---

## 4) خريطة الوحدات (كل ما اشتغل)

### 4.1 الكتالوج

| المكوّن | ماذا يفعل |
|---------|-----------|
| منتجات | CRUD · موافقة/رفض · مخزون · SEO · صورة مصغّرة · ضمان/انتهاء · مطعم/مقدّم خدمة |
| متغيّرات المنتج | سعر · كمية · ربط فروع (`shop_product_variants`) |
| فئات | شجرة · ترتيب · صفات · وراثة صفات من الجذر · صفحات فئة تلقائية |
| علامات تجارية · وحدات · ألوان · أيقونات · شارات | إدارة كاملة من الأدمن |
| تفاصيل إضافية (Extras) | `ProductExtraDetail` وبنود إضافية على عناصر الطلب |
| قناة البيع `sale_channel` | `platform` = للموقع · `shop` = مربوط بمتجر |
| بلد المنشأ / بلدان المبيع | Select دول العالم · افتراضي سوريا للمبيع · بدون رفع أيقونة إلزامي للمبيع |
| أسعار مزدوجة | USD و SYP مع مزامنة/عرض في الواجهات |
| استيراد Excel | قالب + upsert + تحقق عند الإضافة |
| فلاتر المنتجات (مستخدم) | صفات · نوع · ترتيب · dropdowns جاهزة |

### 4.2 المتاجر والبائعون

- Vendors · Shops · Stores · Vendor users  
- تسجيل بائع · باقات واشتراكات بائع  
- محاسبة بائع · طلبات سحب  
- خدمات بائع وأنواعها وربطها بالمتاجر  
- أعلام مطعم / مقدّم خدمة على فئات ومنتجات ومتاجر وتسجيلات

### 4.3 الطلبات والتوصيل

| المكوّن | التفاصيل |
|---------|----------|
| طلبات عادية | عناصر · extras · تتبع · كوبون · نقاط · سلة · اشتراك · عمولة مسوّق |
| حالات / تعيين سائق | من الأدمن |
| نطاقات مسافة التوصيل | تسعير حسب المسافة |
| طرق الدفع | إدارة وربط بالطلب |
| طلبات الخدمات | مسار منفصل عن طلبات التجزئة (`Service` / `ServiceOrder`) |
| سائق | قبول/رفض · خروج للتوصيل · تسليم · فشل · إرجاع · موقع · إحصائيات · محفظة أرباح |

### 4.4 الطلبات المخصصة / الطلب السريع (Custom Orders)

وحدة كاملة موثّقة في `docs/custom-orders/`.

**الفلو:**

```
إنشاء (نص + صور + عنوان) 
  → pending_pricing 
  → الأدمن يسعّر ويحوّل (convert) إلى Order نظامي (cart_type=custom)
  → waiting_approval 
  → موافقة الزبون → approved (الطلب يدخل التحضير)
  أو إلغاء زبون / إلغاء أدمن
```

**حالات الواجهة:**

| Status | المعنى |
|--------|--------|
| `pending_pricing` | قيد مراجعة/تسعير الإدارة |
| `waiting_approval` | التسعير جاهز — بانتظار موافقة الزبون |
| `approved` | تمت الموافقة وارتبط بطلب نظامي |
| `cancelled` | ألغاه الزبون |
| `cancelled_by_admin` | ألغته الإدارة (+ سبب إن وُجد) |

**قسم الطلب السريع على الصفحات:**

- المحتوى والشكل من **Settings** (ليس Page Builder)
- ظهور القسم حسب صفحات مختارة: `quick_order_page_ids` / `page_slugs`
- الافتراضي: صفحة `home` فقط
- العميل يقرأ من `GET /api/user/settings` → `data.quick_order`

**تحويل الأدمن (convert):** بنود من الكتالوج + بنود خارجية · صور فاتورة · تفاوت سعر محتمل (نسبة أو قيمة).

### 4.5 منشئ الصفحات والتنقّل (CMS)

| المكوّن | الوظيفة |
|---------|---------|
| Pages | CRUD صفحات + slug |
| Sections / PageSections | إضافة أقسام · `layout` ثم `variant` · ترتيب · معاينة |
| صفحات الفئات التلقائية | تُنشأ مع الفئة · قيود UX موثّقة للداشبورد |
| Banners | بانرات ضمن الأقسام |
| Nav Menu | عناصر ديناميكية · `route_key` · إعادة ترتيب |
| Display types | أنواع عرض الأقسام |

### 4.6 السلة والسلات المجدولة

- سلة عادية  
- Baskets جاهزة + عناصر  
- جداول (Schedules) · سلات مستخدم مجدولة · تنبيهات مجدولة  
- يعتمد على Scheduler + Queue (انظر `docs/api/SCHEDULER_AND_QUEUE_SETUP.md`)

### 4.7 التسويق والعروض

- عروض (Promotions) · طلبات ترويج  
- فلاش سيل  
- كوبونات  
- حملات Popup (+ أحداث وربط كيانات)  
- إجراءات سريعة (Quick Actions)

### 4.8 النقاط والمكافآت

- محافظ نقاط · قواعد · أحداث · معاملات · صرف  
- هدايا · هدايا المستخدم  
- وثائق مفصّلة في `docs/api/COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md`

### 4.9 الاشتراكات والباقات

- Packages · Subscriptions · سجلات استخدام · منافع  
- للمستخدم وللبائع حسب السياق

### 4.10 التسويق بالعمولة (Affiliate / Marketer)

- حقول مسوّق على المستخدم  
- زيارات · معاملات محفظة · طلبات سحب  
- أوضاع عمولة إدارية موثّقة في `docs/api/`

### 4.11 الجغرافيا واللغة والعملة

- دول (منشأ) · بلدان مبيع · محافظات · مدن · مناطق · تسعير مناطق  
- عملات · لغات · ترجمات (Spatie Translatable + Translation Manager)

### 4.12 المستخدم والدعم

- عناوين · مفضلات · تقييمات  
- شكاوى · أسئلة شائعة  
- إعدادات مساعدة · مستندات قانونية · طرق تواصل  
- وصفات (Recipes + خطوات + عناصر) مع فلاتر

### 4.13 الإشعارات وال瞬时 (Realtime)

- إشعارات مستخدم / أدمن / بائع  
- FCM (Jobs)  
- Laravel Reverb + تفويض Socket

### 4.14 التشغيل والإحصاء

- إحصائيات · تقارير + تصدير Excel  
- Activity logs  
- إعدادات نظام · Toggle status للكيانات  
- Jobs: OTP/SMS/FCM · انتهاء نقاط · تذكير سلات مجدولة · بانرات · إشعار انتهاء منتج · إشعارات جماعية

---

## 5) ما تم بناؤه على مستوى الطبقات

| الطبقة | الحجم التقريبي / المحتوى |
|--------|---------------------------|
| Models | نحو 100+ نموذج في `app/Models` |
| Controllers | نحو 140+ (Admin / User / Driver / Vendor / Base) |
| Migrations | نحو 200+ هجرة تغطي كل المجالات أعلاه |
| Seeders | عشرات السيدر: أدمن/أدوار · جغرافيا · كتالوج · صفحات · نقاط · عروض · سائقين · طلبات تجريبية… |
| Enums | حالات الطلب · الخدمة · الطلب المخصص · أنواع العروض · تخطيط الأقسام · نوع السلة · موافقة المنتج… |
| Filament | لوحة البائع كاملة نسبياً |

---

## 6) مسار البناء المنطقي (من البداية → الآن)

ترتيب مفاهيمي لما اشتغل عبر عمر المشروع (بدون تواريخ):

1. **أساس المشروع** — Laravel · هيكل أولي  
2. **المصادقة** — مستخدم · كوكيز/توكن · ثم أدمن · بائع مع أدوار · سائق  
3. **Spatie Roles & Permissions** — أدوار وصلاحيات الأدمن  
4. **المتاجر والبائعون** — Vendor · Shop · VendorUser  
5. **الفئات والعلامات** — Categories · Brands · Languages · Base CRUD  
6. **المنتجات** — قاعدة بيانات المنتج · متغيّرات · وسائط · تعديلات متلاحقة  
7. **البانرات والأقسام** — بداية CMS / Sections API  
8. **السائقون** — CRUD ثم دورة طلبات السائق  
9. **CORS والدمج بين الفروع** — استقرار الـ API للفرونت  
10. **الطلبات والدفع** — فلو المستخدم · كوبونات · نقاط · عمولات  
11. **العروض والفلاتر** — Promotions · Flash · فلاتر منتجات ووصفات  
12. **السلات المجدولة والاشتراكات** — جداول · تنبيهات · باقات  
13. **النقاط والمكافآت** — نظام كامل  
14. **Affiliate** — مسوّقين ومحفظة وسحب  
15. **خدمات البائع وطلبات الخدمة**  
16. **Popup campaigns · Quick actions · Badges · Icons · Colors**  
17. **تقارير وتصدير · صلاحيات مسارات · Toggle status**  
18. **Page Builder موحّد** — Pages CRUD · إضافة أقسام · صفحات فئة تلقائية · Nav Menu  
19. **تحسينات المنتج للداشبورد** — إسناد لأي مستوى فئة · وراثة صفات · سعر/كمية متغيّر · ربط فروع · حذف بتأكيد أثر · أسعار USD/SYP  
20. **بلد المنشأ وبلدان المبيع**  
21. **`sale_channel`** — موقع مقابل متجر (+ ربط تلقائي لمتجر المنصة في Flutter عند `platform`)  
22. **الطلبات المخصصة / الطلب السريع** — إنشاء · تسعير · convert · موافقة · إلغاء · إعدادات القسم وصفحات الظهور  
23. **استيراد منتجات Excel**  
24. **تنظيف الوثائق** — ملفات فرونت موحّدة + أرشيف قديم + فهرس API

---

## 7) ملخص واجهات الفرونت (ما يجب أن يشغّله كل فريق)

### داشبورد — `docs/frontend/dashboard.md`

1. Page Builder الموحّد  
2. إدارة الأقسام  
3. صفحات الفئات التلقائية  
4. Nav Menu  
5. إسناد منتج لأي مستوى فئة  
6. وراثة صفات الفئة  
7. سعر وكمية المتغيّر + توفر الفروع  
8. حفظ المتغيّرات وربط الفروع  
9. حذف متغيّرات/صفات/فئات مع تأكيد أثر  
10. أسعار USD/SYP  
11. بلد المنشأ · بلدان المبيع  
12. قناة البيع `sale_channel`  
13. إعدادات الطلب السريع + صفحات الظهور  
14. استيراد Excel  

### ويب — `docs/frontend/web.md`

1. Nav ديناميكي  
2. عرض الصفحات (layout ثم variant)  
3. صفحات الفئات ومنتجات الشجرة  
4. صفحة المنتج (فرع / fallback)  
5. تسجيل بدون إيميل  
6. فلاتر المنتجات التفصيلية  
7. قسم الطلب السريع حسب الصفحة  
8. نص تحميل التطبيق + عرض الأسعار المزدوجة  

### Flutter — `docs/frontend/flutter.md`

نفس محاور الويب تقريباً مع إضافات الموبايل (فئات دائرية · ربط متجر المنصة عند `sale_channel=platform` · …).

### طلبات مخصصة — `docs/custom-orders/`

| ملف | الجمهور |
|-----|---------|
| `dashboard.md` | convert / cancel / إعدادات القسم والصلاحيات |
| `web.md` | إنشاء · قائمة · موافقة · إلغاء · عرض تسعير |
| `flutter.md` | نفس فلو الويب للموبايل |

---

## 8) هيكل قاعدة البيانات (مواضيع فقط)

- **هوية وتوكنات:** users · admins · drivers · vendor_users · personal_access_tokens · verifications · permissions  
- **جغرافيا:** countries · sale_countries · governorates · cities · areas · area_pricings  
- **كتالوج:** products (+ sale_channel وغيرها) · variants · shop_product_variants · media · categories · attributes · brands · units · colors · icons · badges · extras  
- **بائعون:** vendors · shops · stores · packages · subscriptions · services · withdrawals · seller_registrations  
- **طلبات:** orders · order_items · extras · trackings · custom_order_requests · service_orders  
- **CMS:** pages · sections · page_sections · banners · nav_menu_items · display_types  
- **سلات:** baskets · schedules · user_basket_schedules · alerts  
- **تسويق:** promotions · flash_sales · coupons · popups · quick_actions  
- **ولاء:** point_* · gifts · user_gifts  
- **اشتراكات:** packages · subscriptions · usage_logs  
- **مسوّق:** affiliate visits / wallet / withdraw  
- **سائق:** drivers · روابط متجر/بائع · محفظة  
- **دعم ومحتوى:** complaints · faqs · ratings · favorites · recipes · legal · contact · notifications · activity_logs  
- **إعدادات و i18n:** settings · system_settings · languages · translations · currencies · payment_methods  
- **بنية تحتية:** cache · jobs · media  

---

## 9) الحزم الأساسية (Composer)

| الحزمة | الدور |
|--------|--------|
| `laravel/framework` ^12 | الإطار |
| `laravel/sanctum` | توكنات API |
| `spatie/laravel-permission` | أدوار وصلاحيات الأدمن |
| `spatie/laravel-translatable` | حقول متعددة اللغة |
| `filament/filament` | لوحة البائع |
| `laravel/reverb` | بث فوري |
| `maatwebsite/excel` | استيراد/تصدير |
| `barryvdh/laravel-dompdf` · `mpdf/mpdf` | PDF |
| `google/apiclient` | تكامل Google عند الحاجة |
| `barryvdh/laravel-translation-manager` | إدارة الترجمات |

**الفرونت البنائي للمشروع:** Vite 7 · Tailwind 4 · Axios · Echo + Pusher JS.

---

## 10) خريطة الوثائق الحالية

```
docs/
├── FULL_PROJECT_OVERVIEW.md     ← هذا الملف (نظرة شاملة من البداية)
├── README.md                    ← بوابة الفرق + Base URLs
├── frontend/
│   ├── dashboard.md
│   ├── web.md
│   └── flutter.md
├── custom-orders/
│   ├── dashboard.md
│   ├── web.md
│   └── flutter.md
├── api/                         ← مرجع تقني طويل (منتجات، طلبات، نقاط، سلات…)
└── _archive_backup_…/           ← أرشيف قديم (يُحذف بعد التأكد)
```

---

## 11) Checklist تشغيلي سريع «هل كلشي شغال؟»

### بنية

- [ ] `.env` مضبوط (DB · Sanctum · Queue · Reverb/FCM إن لزم)  
- [ ] `migrate` ناجح  
- [ ] صلاحيات Spatie موجودة (خصوصاً CustomOrderRequest)  
- [ ] Queue worker يعمل للـ Jobs  
- [ ] Scheduler مفعّل للسلات المجدولة وانتهاء النقاط  

### أدمن

- [ ] دخول أدمن + أدوار  
- [ ] منتجات/فئات/متغيّرات/فروع  
- [ ] Page Builder + Nav  
- [ ] طلبات عادية + تعيين سائق  
- [ ] طلبات مخصصة: عرض · convert · إلغاء  
- [ ] إعدادات الطلب السريع + صفحات الظهور  
- [ ] استيراد Excel  
- [ ] تقارير / نقاط / عروض حسب الحاجة  

### مستخدم (ويب/موبايل)

- [ ] تسجيل/OTP  
- [ ] تصفح صفحات وأقسام وفلاتر  
- [ ] منتج (platform vs shop)  
- [ ] سلة وطلب  
- [ ] طلب سريع: إنشاء → انتظار تسعير → موافقة  
- [ ] أسعار USD/SYP  

### سائق / بائع

- [ ] سائق: استلام وتحديث حالة الطلب  
- [ ] بائع: دخول Filament وإدارة متجره  

---

## 12) ماذا تقرأ حسب احتياجك

| إذا تريد… | اقرأ |
|-----------|------|
| صورة كاملة للمشروع (هذا الملف) | `docs/FULL_PROJECT_OVERVIEW.md` |
| تنفيذ داشبورد | `docs/frontend/dashboard.md` |
| تنفيذ ويب | `docs/frontend/web.md` |
| تنفيذ Flutter | `docs/frontend/flutter.md` |
| فلو الطلب السريع بالكامل | `docs/custom-orders/*` |
| تفاصيل endpoint معيّن | `docs/api/` + فهرس `docs/api/README.md` |
| بداية سريعة API | `docs/api/QUICK_START_GUIDE.md` |

---

**الخلاصة:** المشروع Backend متكامل لمنصة **Deema Fashion** متعددة الأطراف: كتالوج ومتاجر، طلبات وتوصيل وسائقين، CMS وصفحات، عروض ونقاط واشتراكات ومسوّقين، طلب سريع مخصص، دفع Stripe، وأسعار مزدوجة مع قنوات بيع — جاهز للربط مع الداشبورد والويب وFlutter ولوحة البائع. منفصل عن تيك مارت.
