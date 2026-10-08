# آخر التعديلات والتحديثات — Deema Fashion Backend

> **أرسلوا هذا الملف** — آخر نسخة شاملة (كل الفرق)  
> **تاريخ:** 8 تشرين الأول 2026  
> **النطاق:** من منتصف آب حتى اليوم — باك + داشبورد + ويب + Flutter  
> **الحالة:** الباك جاهز بعد `git pull` + `php artisan config:clear`  
> **مهم:** المشروع **ديما فاشن** منفصل عن تيك مارت — الهوية البصرية [`frontend/DEEMA_BRAND_IDENTITY.md`](./frontend/DEEMA_BRAND_IDENTITY.md)

**آخر ما نزل اليوم (8 تشرين الأول):** صلاحيات الداشبورد — إخفاء السايدبار حسب `permissions` (مثال: بس شريط التنقّل) — [`frontend/DASHBOARD_PERMISSIONS.md`](./frontend/DASHBOARD_PERMISSIONS.md) · **ويب/Flutter الزبون: لا حاجة**

**آخر ما نزل (8 تشرين الأول):** هوية ديما فاشن البصرية (منفصل عن تيك مارت) — ألوان `#c720a4` / `#ff1493` من `GET /api/user/settings` · داش [`DASHBOARD_BRAND_IDENTITY.md`](./frontend/DASHBOARD_BRAND_IDENTITY.md) · ويب [`WEB_BRAND_IDENTITY.md`](./frontend/WEB_BRAND_IDENTITY.md) · Flutter [`FLUTTER_BRAND_IDENTITY.md`](./frontend/FLUTTER_BRAND_IDENTITY.md)

**آخر ما نزل (5 تشرين الأول):** دفع إلكتروني Stripe للطلبات — باك + دليل كامل [`api/STRIPE_PAYMENTS.md`](./api/STRIPE_PAYMENTS.md) (PaymentIntent · webhook · ويب/Flutter)

**آخر ما نزل (25 أيلول):** واجهة جدولة التسليم **مطوية** (سطر + زر) — ويب [`WEB_SCHEDULE_UI_COLLAPSED.md`](./frontend/WEB_SCHEDULE_UI_COLLAPSED.md) · Flutter [`FLUTTER_SCHEDULE_UI_COLLAPSED.md`](./frontend/FLUTTER_SCHEDULE_UI_COLLAPSED.md) — لا تغيير باك

**آخر ما نزل (20 أيلول):** فروع المتاجر واسم المتجر ملغيان — داش [`DASHBOARD_NO_SHOP_BRANCHES.md`](./frontend/DASHBOARD_NO_SHOP_BRANCHES.md) · ويب [`WEB_NO_SHOP_BRANCHES.md`](./frontend/WEB_NO_SHOP_BRANCHES.md) · Flutter [`FLUTTER_NO_SHOP_BRANCHES.md`](./frontend/FLUTTER_NO_SHOP_BRANCHES.md)

**آخر ما نزل (16 أيلول):** `POST /api/user/cart/items` · OTP مؤقت `00000`

| الفريق | الملف |
|--------|--------|
| **ويب + Flutter — OTP مؤقت** | [`REGISTER_FLOW.md`](./frontend/REGISTER_FLOW.md) · [`WEB_LATEST.md`](./frontend/WEB_LATEST.md) · [`FLUTTER_LATEST.md`](./frontend/FLUTTER_LATEST.md) |

**آخر ما نزل (مساء 8 أيلول):** ناف بار «جدولة» من الداشبورد · إنشاء منتج بدون `vendor_id` · بائع المنصة

| الفريق | الملف |
|--------|--------|
| **داشبورد — جدولة في الناف** | [`NAV_MENU_SCHEDULES.md`](./frontend/NAV_MENU_SCHEDULES.md) |
| **داشبورد — إنشاء منتج `vendor_id`** | [`DASHBOARD_PRODUCT_VENDOR_ID.md`](./frontend/DASHBOARD_PRODUCT_VENDOR_ID.md) |
| **داشبورد** (سعر/خصم/كمية) | [`DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./frontend/DASHBOARD_PRODUCT_PRICING_FIELDS.md) |
| **ويب** | [`WEB_LATEST.md`](./frontend/WEB_LATEST.md) · [`WEB_PRODUCT_PRICING_FIELDS.md`](./frontend/WEB_PRODUCT_PRICING_FIELDS.md) |
| **Flutter** | [`FLUTTER_LATEST.md`](./frontend/FLUTTER_LATEST.md) · [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./frontend/FLUTTER_PRODUCT_PRICING_FIELDS.md) |

---

## أين أبدأ؟

| الفريق | أرسلوا هذا | ملاحظة |
|--------|------------|--------|
| **الكل (هذا الملف)** | [`LATEST_UPDATES.md`](./LATEST_UPDATES.md) | آخر نسخة لكل شيء |
| **هوية ديما (الكل)** | [`frontend/DEEMA_BRAND_IDENTITY.md`](./frontend/DEEMA_BRAND_IDENTITY.md) | **8 تشرين الأول — منفصل عن تيك مارت** |
| **داشبورد — صلاحيات السايدبار** | [`frontend/DASHBOARD_PERMISSIONS.md`](./frontend/DASHBOARD_PERMISSIONS.md) | **8 تشرين الأول — أخفوا القوائم حسب permissions** |
| **داشبورد** | [`frontend/dashboard.md`](./frontend/dashboard.md) · [`DASHBOARD_BRAND_IDENTITY.md`](./frontend/DASHBOARD_BRAND_IDENTITY.md) | كل تعديلات الأدمن + هوية |
| **ويب** | [`frontend/WEB_LATEST.md`](./frontend/WEB_LATEST.md) · [`WEB_BRAND_IDENTITY.md`](./frontend/WEB_BRAND_IDENTITY.md) | آخر نسخة الموقع + هوية |
| **Flutter** | [`frontend/FLUTTER_LATEST.md`](./frontend/FLUTTER_LATEST.md) · [`FLUTTER_BRAND_IDENTITY.md`](./frontend/FLUTTER_BRAND_IDENTITY.md) | آخر نسخة التطبيق + هوية |
| **سلة مخصصة — داش** | [`frontend/DASHBOARD_CUSTOM_BASKET.md`](./frontend/DASHBOARD_CUSTOM_BASKET.md) | فئات + سلل جاهزة |
| **سلة مخصصة — ويب** | [`frontend/WEB_CUSTOM_BASKET.md`](./frontend/WEB_CUSTOM_BASKET.md) | كروت + تخصيص + تأكيد |
| **سلة مخصصة — Flutter** | [`frontend/FLUTTER_CUSTOM_BASKET.md`](./frontend/FLUTTER_CUSTOM_BASKET.md) | نفس العقد للشاشات |
| **متغيّرات — داشبورد** | [`frontend/product-variants-dashboard.md`](./frontend/product-variants-dashboard.md) | **آخر تحديث 8 أيلول** |
| **Nav — جدولة من الداشبورد** (`route` مو صفحة) | [`frontend/NAV_MENU_SCHEDULES.md`](./frontend/NAV_MENU_SCHEDULES.md) | **8 أيلول مساءً** |
| **إنشاء منتج — لا ترسلوا `vendor_id`** | [`frontend/DASHBOARD_PRODUCT_VENDOR_ID.md`](./frontend/DASHBOARD_PRODUCT_VENDOR_ID.md) | **8 أيلول** |
| **إنشاء منتج — صفات جزئية + حذف صور** | [`frontend/DASHBOARD_PRODUCT_CREATE_UX.md`](./frontend/DASHBOARD_PRODUCT_CREATE_UX.md) | **31 آب** |
| **حقول السعر · الخصم · الكمية · باركود · SKU — داشبورد** | [`frontend/DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./frontend/DASHBOARD_PRODUCT_PRICING_FIELDS.md) | **8 أيلول — إدخال في تاب المعلومات + كارد المتغيّر** |
| **نفس الحقول — ويب** | [`frontend/WEB_PRODUCT_PRICING_FIELDS.md`](./frontend/WEB_PRODUCT_PRICING_FIELDS.md) | **8 أيلول — كارد من المنتج · التفاصيل من المتغيّر المختار** |
| **نفس الحقول — Flutter** | [`frontend/FLUTTER_PRODUCT_PRICING_FIELDS.md`](./frontend/FLUTTER_PRODUCT_PRICING_FIELDS.md) | **8 أيلول — كارد من المنتج · التفاصيل من المتغيّر المختار** |
| **باگ كمية المنتج (توست موجبة)** | [`frontend/DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md`](./frontend/DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md) | **5 أيلول** — الكمية رجعت لتاب المعلومات: الدليل الجديد فوق |
| **ضمان المنتج — دروب داون + قسم مستقل** | [`frontend/DASHBOARD_PRODUCT_WARRANTY.md`](./frontend/DASHBOARD_PRODUCT_WARRANTY.md) | **5 أيلول** |
| **تسجيل المستخدم — تدفق كامل** | [`frontend/REGISTER_FLOW.md`](./frontend/REGISTER_FLOW.md) | **8 أيلول — ويب + Flutter** |
| **متغيّرات — ويب (تفصيل)** | [`frontend/product-variants-web.md`](./frontend/product-variants-web.md) | |
| **متغيّرات — ويب (دليل شامل)** | [`frontend/WEB_VARIANTS_COMPLETE.md`](./frontend/WEB_VARIANTS_COMPLETE.md) | **31 آب** — API حقيقي · types · gaps · سلة |
| **هوية ديما — Flutter** | [`frontend/FLUTTER_BRAND_IDENTITY.md`](./frontend/FLUTTER_BRAND_IDENTITY.md) | **8 تشرين الأول** |
| **هوية ديما — ويب** | [`frontend/WEB_BRAND_IDENTITY.md`](./frontend/WEB_BRAND_IDENTITY.md) | **8 تشرين الأول** |
| **هوية ديما — داشبورد** | [`frontend/DASHBOARD_BRAND_IDENTITY.md`](./frontend/DASHBOARD_BRAND_IDENTITY.md) | **8 تشرين الأول** |
| **متغيّرات — Flutter (تفصيل)** | [`frontend/product-variants-flutter.md`](./frontend/product-variants-flutter.md) | |
| **طلب سريع** | [`custom-orders/`](./custom-orders/) | dashboard · web · flutter |
| **إظهار/إخفاء أقسام** | [`page-sections/`](./page-sections/) | Eye toggle |
| **مرجع API كامل** | [`api/`](./api/) | Postman · صلاحيات · endpoints |
| **نظرة شاملة على المشروع** | [`FULL_PROJECT_OVERVIEW.md`](./FULL_PROJECT_OVERVIEW.md) | بدون تواريخ — ماذا يوجد وكيف يعمل |

---

## ملخص تنفيذي — أهم التغييرات حتى اليوم

| # | التغيير | من يتأثر |
|---|---------|----------|
| 1 | **السلة المخصصة** — فئات جدولة + تخصيص داخل الفئة + تأكيد نعم/لا | الكل |
| 2 | **ضمان المنتج** — دروب داون من قسم مستقل · المتجر يقرأ `warranty.name` | الكل |
| 3 | **كمية المنتج** — المخزون = `shop_variants[].quantity` (ممكن `null`) | الكل |
| 4 | **متغيّرات المنتج** — سعر + كمية + **خصم** على `product_variants` | الكل |
| 5 | **إنشاء منتج** — متغيّرات في state محلي ثم `POST /products` مرة | داشبورد |
| 6 | **Page Builder موحّد** — صفحات + أقسام + preview + reorder | الكل |
| 7 | **Eye toggle** — إخفاء قسم بدون حذف (`is_active`) | الكل |
| 8 | **Nav Menu** — `GET /nav-menu` + `route_key=schedules` (جدولة) من الداشبورد | ويب + Flutter + داش |
| 9 | **الطلب السريع** — إعدادات + `quick_order_page_ids` + convert/cancel | الكل |
| 10 | **استيراد Excel** + **قناة البيع** `sale_channel` | داشبورد |
| 11 | **حقول السعر/الخصم/الكمية/الباركود/SKU** — تاب المعلومات + كارد المتغيّر | الكل |
| 12 | **إنشاء منتج** — لا `vendor_id` من الداشبورد · بائع المنصة من السيدر | داشبورد |

---

## آخر نسخة (8 أيلول 2026 مساءً) — ناف «جدولة» + إنشاء منتج

### ناف بار — جدولة من الداشبورد

جدولة **ليست صفحة** Page Builder. الأدمن يضيفها يدوياً:

- النوع: **شاشة ثابتة** `type=route` (مو `page`)
- الشاشة: `route_key=schedules`
- دروب داون الشاشات: `GET /api/admin/nav-menu-items/route-keys`

**لا سيدر تلقائي.** ويب: `schedules` → `/schedules`. Flutter: شاشة `GET /api/user/schedules`.

> [`NAV_MENU_SCHEDULES.md`](./frontend/NAV_MENU_SCHEDULES.md)

### إنشاء منتج — `vendor_id`

لا ترسلوا `vendor_id` (ولا `0`). للموقع: `sale_channel=platform`. للمتجر: `shop_variants` والباك يأخذ البائع من الفرع.

إذا ظهر «بائع المنصة غير موجود» على داتا الإنتاج:

```bash
php artisan db:seed --class=PlatformVendorSeeder
```

> [`DASHBOARD_PRODUCT_VENDOR_ID.md`](./frontend/DASHBOARD_PRODUCT_VENDOR_ID.md)

### سلة المستخدم (ويب)

`POST /api/user/cart/items` يحتاج توكن. الجسم: `{ shop_product_variant_id, quantity, note? }`.

- `GET /api/user/cart`
- `PUT /api/user/cart/items/{id}`
- `DELETE /api/user/cart/items/{id}`

الطلب `POST /api/user/orders` كما هو.

---

## آخر نسخة (8 أيلول 2026) — حقول السعر · الخصم · الكمية · الباركود · SKU

نفس البلوك في الداشبورد (إدخال) والمتجر (عرض):

| الواجهة | داشبورد | كارد المتجر | صفحة/شاشة التفاصيل |
|---------|---------|-------------|---------------------|
| سعر المتغير (دولار) | `price` | `price_currencies.USD` من المنتج | من `shop_variants` المختار |
| سعر المتغيّر (ليرة سورية) | `price_syp` | `price_currencies.SYP` | نفس |
| نوع الخصم — **لا يوجد خصم** | `discount_type=none` | مقارنة السعر / بعد الخصم | `discount_type` |
| قيمة الخصم | `discount` | — | `discount_value` |
| السعر بعد الخصم | عرض فقط — لا يُرسل | `price_after_discount_currencies` | نفس من المتغيّر |
| الكمية المتوفرة | `quantity` | `quantity` المنتج | `shop_variants[].quantity` |
| الباركود `6291101234567` | `barcode` | لا على الكرت | `barcode` |
| رمز التخزين التعريفي للمتغير | `sku` | لا على الكرت | `sku` |

بلا متغيّرات: الكارد والتفاصيل من معلومات المنتج. مع متغيّرات: تغيير اللون/المقاس يبدّل كل الحقول.

> داشبورد: [`DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./frontend/DASHBOARD_PRODUCT_PRICING_FIELDS.md)  
> ويب: [`WEB_PRODUCT_PRICING_FIELDS.md`](./frontend/WEB_PRODUCT_PRICING_FIELDS.md)  
> Flutter: [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./frontend/FLUTTER_PRODUCT_PRICING_FIELDS.md)

---

## آخر نسخة (16 أيلول 2026) — OTP مؤقت بدون SMS

**مؤقت حتى اشتراك بوابة SMS جديدة.** ما في رسالة عالهاتف.

على شاشة OTP أدخلوا **`00000`** ثم `POST /auth/verify-otp` `{ phone, code: "00000" }`.

نفس الكود لنسيت كلمة المرور وتحديث الهاتف/الإيميل والسائق.

> [`REGISTER_FLOW.md`](./frontend/REGISTER_FLOW.md) · ويب [`WEB_LATEST.md`](./frontend/WEB_LATEST.md) · Flutter [`FLUTTER_LATEST.md`](./frontend/FLUTTER_LATEST.md)

---

## آخر نسخة (8 أيلول 2026) — تدفق التسجيل (ويب + Flutter)

```
GET /governorates → GET /cities?governorate_id=
POST /auth/register  →  200 بدون توكن
POST /auth/verify-otp { phone, code }  →  data.token
إعادة الإرسال: POST /auth/login (403 = SMS جديد) — ليس /send-otp
```

`phone` مطلوب (أرقام فقط) · `email` اختياري (لا `""`) · كلمة المرور ≥ 8 + صغير + كبير + رقم + رمز.

> الكامل: [`REGISTER_FLOW.md`](./frontend/REGISTER_FLOW.md)  
> ويب: [`WEB_REGISTER_FLOW.md`](./frontend/WEB_REGISTER_FLOW.md)  
> Flutter: [`FLUTTER_REGISTER_FLOW.md`](./frontend/FLUTTER_REGISTER_FLOW.md)

---

## آخر نسخة (5 أيلول 2026 مساءً) — السلة المخصصة (كل الفرق)

الأدمن يعرّف **فئات جدولة** بحرية (أسبوعي، شهري، كل 3 أيام…). كل فئة: اسم + أيام + خصم على **السلة كاملة** + صورة + وصف + بادجز.

المستخدم يفتح الفئة ويختار هو المنتجات والكميات. يقدر يخصّص أكثر من فئة. عند التأكيد: **نعم** = جدولة + تذكير · **لا** = طلب مرة.

**اليوم:** تعديل الأدمن كان يرجّع نجاح بدون كتابة الصورة/البادجز. بعد الرفع + migrate، نفس حقول المستخدم تتعبّى. ما في aliases — الغلاف = `image`، المعرض = `images[]`، الشارات = `top_badges` / `bottom_badges`.

```bash
php artisan migrate
php artisan config:clear
```

> داشبورد: [`DASHBOARD_CUSTOM_BASKET.md`](./frontend/DASHBOARD_CUSTOM_BASKET.md)  
> ويب: [`WEB_CUSTOM_BASKET.md`](./frontend/WEB_CUSTOM_BASKET.md)  
> Flutter: [`FLUTTER_CUSTOM_BASKET.md`](./frontend/FLUTTER_CUSTOM_BASKET.md)

### داشبورد — فئات الجدولة

| Method | Endpoint |
|--------|----------|
| GET/POST | `/api/admin/schedules` |
| GET/PUT/DELETE | `/api/admin/schedules/{id}` |

تعديل الجدول: **POST** + `_method=PUT` (مو PUT خام). لا تضعوا `Content-Type: multipart/form-data` يدوياً. خصم فاضي = **لا ترسلوا** `discount_type` / `discount_value`.

`POST` multipart: `name[ar|en]` · `description[ar|en]` · `interval_days` · `discount_type` (`percentage`\|`fixed`) · `discount_value` · `is_active` · `image` · `images[]` · `badges[][id]` + `badges[][position]` = `top`\|`bottom`.

رد التعديل لازم فيه `image` · `images` · `top_badges` · `bottom_badges` و`updated_at` جديد.

سلة أدمن جاهزة (اختياري): `POST /api/admin/scheduled-baskets` **يتطلب `schedule_id`**. لا ترسلوا `schedules[].number_of_days`. خصم فاضي = يرث الفئة. خصم معبّأ = استثناء لهالسلة.

قسم الصفحة: على `/sections/pages/{id}/sections/create` نوع **فئات الجدولة الزمنية** = `content_type: schedule` / `api_method: schedules` / `display_type_id: 11`. هذا غير `schedule-basket` (سلل جاهزة).

### ويب + Flutter — كروت ثم تخصيص

كروت عامة:

```http
GET /api/user/schedules
GET /api/user/schedules/{id}
```

عقد الكرت الثابت (بدون aliases): `name` (string حسب اللغة) · `description` · `image` (URL كامل — مو `cover_image`/`photo`) · `images[]` (مو `gallery`) · `interval_days` · `discount_type` = `percentage`\|`fixed`\|`null` (مو `"none"`) · `top_badges` · `bottom_badges`.

كرت عمودي، صورة دائرية. الضغط → صفحة `{id}`. على الرئيسية أيضاً من `GET /sections` إذا `display_type_id=11` / `content_type=schedule`. `display_type_id=5` = سلل جاهزة.

تخصيص (Auth) — مسودة لكل `schedule_id`:

| | Endpoint |
|--|----------|
| هيدر + مسودة + `summary` | `GET /api/user/schedules/{id}/custom-basket` |
| إضافة | `POST .../items` `{ shop_product_variant_id, quantity }` |
| كمية | `PUT .../items/{itemId}` `{ quantity }` |
| حذف | `DELETE .../items/{itemId}` |
| تأكيد | `POST .../confirm` |

تصفح: `GET /products?category_id=&search=&brand_id=` · `GET /brands` · `GET /categories`.

عرض السلة من `items` + `summary` (`savings` = وفّرت). لا تحسبوا الخصم على الفرونت.

| الزر | Body | النتيجة |
|------|------|---------|
| نعم | `{ "confirm_schedule": true, "start_date": "2026-09-08" }` | طلباتي المجدولة + تذكير + `cart_items` لأول طلب |
| لا | `{ "confirm_schedule": false }` | `cart_items` مرة — المسودة تُحذف |

`cart_items`: `[{ shop_product_variant_id, quantity }]` → سلة الموقع / `POST /orders`.

---

## آخر تحديث (8 أيلول 2026) — حقول السعر على المنتج والمتغيّر (داشبورد Flutter Web)

نفس البلوك يظهر في **تاب معلومات المنتج** (كل منتج) **و** على **كارد المتغيّر** إذا أُضيفت متغيّرات:

سعر $ · سعر ل.س · نوع الخصم (لا يوجد خصم) · قيمة الخصم · السعر بعد الخصم (readonly) · الكمية المتوفرة · الباركود · رمز التخزين التعريفي للمتغير.

- بلا متغيّرات → الكارد وصفحة التفاصيل من معلومات المنتج
- مع متغيّرات → الكارد من معلومات المنتج · التفاصيل تتبدّل عند اختيار المتغيّر
- لا كارد متغيّر تلقائي · لا `required` على الحقول

> الدليل: [`DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./frontend/DASHBOARD_PRODUCT_PRICING_FIELDS.md)

---

## آخر تحديث (5 أيلول 2026) — ضمان المنتج + كمية + آخر نسخة ويب/Flutter

- الضمان في المتجر: `warranty: { id, name, description }` من `GET /api/user/products/{id}`
- الكمية المعروضة للعميل: `shop_variants[].quantity` (ممكن `null`)
- الأدمن ما عاد يولّد متغيّر فاضي — الباك يبقى fallback `shop_variants[0]`

> ويب: [`WEB_LATEST.md`](./frontend/WEB_LATEST.md)  
> Flutter: [`FLUTTER_LATEST.md`](./frontend/FLUTTER_LATEST.md)  
> داشبورد: [`DASHBOARD_PRODUCT_WARRANTY.md`](./frontend/DASHBOARD_PRODUCT_WARRANTY.md)

---

## آخر تحديث (5 أيلول 2026) — ضمان المنتج + بدون متغيّر افتراضي

الضمان في إنشاء المنتج **دروب داون** من قسم **الضمانات** (`/products/warranties`) — مو حقل أشهر. لا كارد «المتغير رقم 1» تلقائي.

> الدليل: [`DASHBOARD_PRODUCT_WARRANTY.md`](./frontend/DASHBOARD_PRODUCT_WARRANTY.md)

```bash
php artisan migrate
php artisan db:seed --class=AdminRolePermissionSeeder
```

---

## آخر تحديث (5 أيلول 2026) — باگ كمية المنتج في الداشبورد

توست «يجب أن تكون الكمية موجبة» عند إنشاء منتج بمتغيّرات **باگ غير مقصود** — الباك يقبل كمية فاضية. Zod كان يطلب `quantity` المنتج المخفي.

> الدليل: [`DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md`](./frontend/DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md)

---

## آخر تحديث (30–31 آب 2026) — متغيّرات المنتج

### الباك — ما تغيّر

| البند | التفاصيل |
|-------|----------|
| Migration | `2026_08_30_120000_add_discount_to_product_variants_table` |
| حقول جديدة | `discount` (nullable int) · `discount_type` (default `none`) |
| السعر والكمية | على **`product_variants`** — أُزيل `price` من `shop_product_variants` |
| الخصم | per variant — أولوية على خصم المنتج / flash sale |
| API Admin | `variants[].discount` + `variants[].discount_type` في POST/PUT |
| API User | `shop_variants[].discount_value` · `discount_type` · `price_currencies` |
| اسم المتغيّر | **غير موجود** — لا ترسل `name.ar` / `name.en` |

```bash
php artisan migrate   # يضيف discount على product_variants
```

### الداشبورد — مطلوب في UI

| البند | الصح | الخطأ الشائع |
|-------|------|--------------|
| **إنشاء منتج** | متغيّرات في `localVariants[]` → `POST /products` مرة واحدة | toast «احفظ المنتج أولاً» |
| **نموذج الإضافة** | dropdown **single** لكل صفة (لون · مقاس · تصميم) | multi-select مقاسات → 4 كروت دفعة |
| **كل «إضافة»** | متغيّر **واحد** = كارد واحد | `map` على المقاسات |
| **تكرار اللون** | مسموح — يُمنع فقط نفس `attributes_values_ids` | فلترة ألوان مستخدمة من dropdown |
| **حقول الكارد** | SKU · $ · ل.س · خصم · كمية · باركود — **كلها اختيارية** | required على السعر/الكمية |
| **SKU** | **إنجليزي فقط** — `SKU-27T4376` | `PROD-أخضر-XL` |
| **تحديث** (edit) | `PUT product-variants/{id}` أو `PUT products/{id}` | — |

> الدليل الكامل: [`product-variants-dashboard.md`](./frontend/product-variants-dashboard.md)

### الويب + Flutter — مطلوب في UI

| البند | التفاصيل |
|-------|----------|
| Endpoint | `GET /api/user/products/{id}?lat=&lng=` |
| اختيار المتغيّر | من **`shop_variants`** — لا Cartesian كامل |
| السعر | `price_currencies.USD` / `price_currencies.SYP` |
| الخصم | `discount_value` + `discount_type` على المتغيّر |
| الكمية | حقل واحد `quantity` — لا `stock` منفصل |
| `shop_variants` | عنصر واحد على الأقل؛ `id`/`shop_id` ممكن `null` (منصة) |
| `attributes` | `{ attribute, value, type }` — مو `category_attribute` |
| `delivery_time` | على **مستوى المنتج** — ليس per variant |
| `country` | **string** في صفحة المنتج |

> الدليل المشترك: [`product-variants-storefront-update.md`](./frontend/product-variants-storefront-update.md)

---

## الجدول الزمني — أيلول 2026

### 5 أيلول — سلة مخصصة · ضمان · كمية

| الميزة | الوصف |
|--------|--------|
| **فئات الجدولة** | كتالوج `schedules`: اسم · أيام · خصم · صورة · وصف · بادجز |
| **تخصيص المستخدم** | مسودة لكل فئة · إضافة/كمية/حذف · `summary` من الـ API |
| **تأكيد نعم/لا** | نعم = جدولة + تذكير · لا = طلب مرة + حذف المسودة |
| **سلة أدمن جاهزة** | مربوطة بـ `schedule_id` — ترث خصم الفئة أو استثناء |
| **ضمان المنتج** | دروب داون `warranty_id` · المتجر `warranty: { id, name, description }` |
| **كمية المنتج** | المخزون = `shop_variants[].quantity` · لا توست «موجبة» على الكمية المخفية |

**Endpoints جديدة:**

```http
GET/POST /api/admin/schedules
GET/PUT/DELETE /api/admin/schedules/{id}
GET  /api/user/schedules
GET  /api/user/schedules/{id}
GET  /api/user/schedules/{id}/custom-basket
POST /api/user/schedules/{id}/custom-basket/items
PUT  /api/user/schedules/{id}/custom-basket/items/{itemId}
DELETE /api/user/schedules/{id}/custom-basket/items/{itemId}
POST /api/user/schedules/{id}/custom-basket/confirm
```

---

## الجدول الزمني — آب 2026

### 17–19 آب — Page Builder + Nav + فئات

| الميزة | الوصف |
|--------|--------|
| **Page Builder موحّد** | CRUD صفحات · إضافة قسم بـ `section_id` أو إنشاء مباشر · reorder · preview |
| **صفحات فئات تلقائية** | كل فئة → صفحة CMS مرتبطة (`category_id` على `pages`) |
| **Nav Menu** | CRUD + reorder من الداشبoard · `GET /api/user/nav-menu` للعملاء |
| **layout + variant** | `slider` \| `list` \| `grid` + `horizontal` \| `vertical` \| `square` |
| **صفات الفئة** | نُقلت للفئات الجذر · وراثة للفرعية |

**Endpoints رئيسية:**

```http
GET  /api/admin/pages
POST /api/admin/pages/{id}/sections
GET  /api/user/pages/{slug}
GET  /api/user/nav-menu
GET  /api/user/categories/{id}/page
```

---

### 20–22 آب — منتج · متغيّر · فلاتر

| الميزة | الوصف |
|--------|--------|
| **سعر/كمية على variant** | `product_variants.price` · `quantity` — لا على ربط المحل |
| **Soft delete variants** | حذف متغيّر مع تأكيد · 409 عند ارتباط |
| **منتج لأي مستوى فئة** | `category_id` على أي عمق |
| **Boolean query params** | `is_active=1` أو `is_active=true` — الباك يقبل الاثنين |
| **فلاتر منتجات** | تحسينات User API |

---

### 25–26 آب — طلب سريع · قناة بيع · Excel

| الميزة | الوصف |
|--------|--------|
| **Custom Order Requests** | جدول + Admin convert/cancel · User create/list |
| **Quick Order Settings** | `quick_order_enabled` · خلفية · ألوان · خطوات · شكل كارد |
| **`quick_order_page_ids`** | الأدمن يختار صفحات الظهور — افتراضي `home` |
| **`sale_channel`** | `platform` (موقع) أو `shop` (ربط بمتجر) |
| **حذف فئات** | soft delete · `category_id` nullable على المنتج |
| **استيراد Excel** | `GET import-template` · `POST import` · upsert barcode/sku |

**Endpoints طلب سريع:**

```http
GET  /api/admin/custom-order-requests
POST /api/admin/custom-order-requests/{id}/convert
POST /api/admin/custom-order-requests/{id}/cancel
POST /api/user/custom-order-requests
GET  /api/user/settings          → data.quick_order
PUT  /api/admin/settings/quick_order_page_ids
```

---

### 27–28 آب — Eye toggle · أقسام

| الميزة | الوصف |
|--------|--------|
| **Eye toggle** | `is_active` على `page_sections` — إخفاء بدون DELETE |
| **Toggle API** | `POST /api/admin/toggle-status` `{ type: "page_section", id, is_active }` |
| **Preview** | يعرض الأقسام **النشطة فقط** |
| **Soft delete categories** | migration `2026_08_27_143700` |
| **Runtime filters** | فلاتر ديناميكية على أقسام الصفحة |

> الدليل: [`page-sections/dashboard.md`](./page-sections/dashboard.md) · [`page-sections/web.md`](./page-sections/web.md)

---

### 30–31 آب — خصم المتغيّر + توثيق نهائي

| الميزة | الوصف |
|--------|--------|
| **خصم per variant** | `discount` + `discount_type` على `product_variants` |
| **Validation** | كل حقول المتغيّر nullable في Admin |
| **Resources** | `ShopVariantResource` · `ProductVariant/OneResource` محدّثة |
| **Docs** | 7 ملفات frontend محدّثة (dashboard · web · flutter · variants × 4) |

---

## Migrations (ترتيب التشغيل)

```bash
php artisan migrate   # آمن — هيكل فقط، لا يمسح داتا
```

### أيلول 2026

| Migration | الغرض |
|-----------|--------|
| `2026_09_05_100000_normalize_empty_unique_product_strings_to_null` | قيم فريدة فاضية → null |
| `2026_09_05_110000_create_warranties_table` | قسم الضمانات |
| `2026_09_05_124400_add_schedule_id_to_baskets_table` | ربط سلة الأدمن بفئة الكتالوج |
| `2026_09_05_132000_add_card_fields_to_schedules_table` | وصف + صورة على فئة الجدولة |
| `2026_09_05_132100_add_is_draft_to_user_basket_schedules_table` | مسودة تخصيص المستخدم |

صلاحيات الضمان (مرة):

```bash
php artisan db:seed --class=AdminRolePermissionSeeder
```

### آب 2026

| Migration | الغرض |
|-----------|--------|
| `2026_08_16_100000_add_price_to_product_variants_table` | سعر على المتغيّر |
| `2026_08_16_100001_drop_price_from_shop_product_variants_table` | إزالة سعر من ربط المحل |
| `2026_08_16_100002_move_category_attributes_to_root_categories` | صفات على الجذر |
| `2026_08_17_120000_add_soft_deletes_to_product_variants_table` | soft delete variants |
| `2026_08_17_120000_create_nav_menu_items_table` | Nav Menu |
| `2026_08_17_130000_add_category_id_to_pages_table` | صفحات فئات |
| `2026_08_17_180000_add_slider_defaults_to_sections_table` | defaults للسلايدر |
| `2026_08_19_190000_add_is_default_to_page_sections_table` | قسم افتراضي |
| `2026_08_20_120000_add_layout_to_sections_and_page_sections` | layout + variant |
| `2026_08_25_120000_create_custom_order_requests_table` | طلبات مخصصة |
| `2026_08_25_120100_add_custom_order_fields_to_orders_and_items` | ربط بالطلب |
| `2026_08_26_103600_add_quick_order_settings` | إعدادات طلب سريع |
| `2026_08_26_104500_make_product_category_id_nullable_for_category_delete` | حذف فئات |
| `2026_08_26_111600_add_sale_channel_to_products_table` | قناة البيع |
| `2026_08_26_131500_add_quick_order_page_ids_setting` | صفحات ظهور الطلب السريع |
| `2026_08_27_143700_add_soft_deletes_to_categories_table` | soft delete فئات |
| `2026_08_30_120000_add_discount_to_product_variants_table` | **خصم المتغيّر** |

### Seeders (اختياري — ناقص فقط)

```bash
php artisan db:seed --class=RolePermissionSeeder   # صلاحيات CustomOrderRequest
php artisan db:seed --class=CountrySeeder            # دول منشأ
php artisan db:seed --class=SaleCountrySeeder        # بلدان مبيع
```

**لا تشغّل:** `migrate:fresh` · `migrate:refresh` · `db:seed` (كامل)

---

## Base URLs وشكل الرد

| الطرف | Base | Token |
|-------|------|-------|
| Admin | `/api/admin` | Bearer admin |
| User | `/api/user` | Bearer user (اختياري لبعض المسارات) |
| Vendor | `/api/vendor` | Bearer vendor |
| Driver | `/api/driver` | Bearer driver |

```json
{ "success": true, "message": "...", "data": {} }
```

---

## Checklist — حسب الفريق

### داشبورد

- [ ] **سلة مخصصة:** فئات جدولة (اسم · أيام · خصم · صورة · بادجز) — [`DASHBOARD_CUSTOM_BASKET.md`](./frontend/DASHBOARD_CUSTOM_BASKET.md)
- [ ] سلة جاهزة (إن وُجدت) مربوطة بـ `schedule_id` — لا `number_of_days` جوّا السلة
- [ ] ضمان: قسم مستقل + دروب داون `warranty_id` — لا كارد متغيّر افتراضي
- [ ] كمية المنتج اختيارية (لا توست «موجبة» على الحقل المخفي)
- [ ] Page Builder: صفحات + أقسام + reorder + preview + eye toggle
- [ ] Nav Menu CRUD + reorder + دروب داون من `GET /nav-menu-items/route-keys` (جدولة = `schedules` مو صفحة)
- [ ] إنشاء منتج: **لا** `vendor_id` — [`DASHBOARD_PRODUCT_VENDOR_ID.md`](./frontend/DASHBOARD_PRODUCT_VENDOR_ID.md)
- [ ] منتج: أي مستوى فئة · sale_channel · بلد منشأ · بلدان مبيع
- [ ] متغيّرات: single select · state محلي عند الإنشاء · خصم per variant
- [ ] حذف متغيّر/صفة/فئة مع confirm + 409
- [ ] استيراد Excel: قالب + رفع + ملخص
- [ ] طلب سريع: إعدادات + multi-select صفحات + convert/cancel
- [ ] `is_active=1` أو `true` في فلاتر القوائم

### ويب

- [ ] **سلة مخصصة:** كروت `/schedules` + تخصيص + تأكيد نعم/لا — [`WEB_CUSTOM_BASKET.md`](./frontend/WEB_CUSTOM_BASKET.md)
- [ ] ضمان: `warranty.name` / `.description`
- [ ] كمية: `shop_variants[].quantity` (ممكن `null`)
- [ ] Nav من API · خريطة `schedules` → `/schedules` — [`NAV_MENU_SCHEDULES.md`](./frontend/NAV_MENU_SCHEDULES.md)
- [ ] سلة الضيف محلية؛ الطلب يحتاج login
- [ ] منتج: shop_variants picker · price_currencies · discount per variant
- [ ] فلاتر · تسجيل بدون إيميل · أسعار USD/SYP
- [ ] طلب سريع حسب `page_slugs` من settings
- [ ] أقسام مخفية لا تُعرض (الباك يستبعدها)

### Flutter

- [ ] **سلة مخصصة:** قسم `display_type_id=11` + كروت + تخصيص نعم/لا — [`FLUTTER_CUSTOM_BASKET.md`](./frontend/FLUTTER_CUSTOM_BASKET.md)
- [ ] ضمان: `warranty.name` / `.description`
- [ ] كمية: `ShopVariant.quantity` كـ `int?`
- [ ] نفس محاور الويب (Nav · `schedules` · صفحات · فئات · منتج · فلاتر)
- [ ] shop_variants · attributes_map · quantity · discount
- [ ] طلب سريع حسب الصفحة
- [ ] Circular categories · layout/variant للأقسام

---

## محذوف / لا تعتمد عليه

| قديم | البديل |
|------|--------|
| `variant.name` (ar/en) | `attributes` + `sku` |
| `shop_variants[].price` | `product_variants.price` → User API |
| multi-select مقاسات | single لكل صفة |
| toast «احفظ المنتج أولاً» | state محلي + POST واحد |
| DELETE قسم لإخفائه | `is_active` toggle |
| Nav ثابت بالكود | `GET /nav-menu` |
| `price_usd` / `price_syp` flat | `price_currencies.USD` / `.SYP` |
| مستودعاتي / خارجي (sale) | `platform` \| `shop` فقط |
| `warranty_period` (أشهر) | `warranty_id` + كائن `warranty` |
| `schedules[].number_of_days` جوّا سلة الأدمن | `schedule_id` من كتالوج `/schedules` |
| حساب خصم الفئة على الفرونت | `summary.savings` / `summary.final_price_formatted` |

---

## Commits حديثة (مرجع)

| التاريخ | Commit | المحتوى |
|---------|--------|---------|
| 30 آب | `c0f04c1` | خصم variant + ProductService + docs |
| 28 آب | `875480e` | Eye toggle + page sections + tests |
| 26–27 آب | `3895759`… | Quick order · sale_channel · filters |
| 19 آب | `01b020b` | Page Builder موحّد |
| 17 آب | `b721154` | fix product id filter |

---

## روابط سريعة إضافية

- [`README.md`](./README.md) — فهرس الوثائق
- [`frontend/DASHBOARD_CUSTOM_BASKET.md`](./frontend/DASHBOARD_CUSTOM_BASKET.md) — سلة مخصصة داش
- [`frontend/WEB_CUSTOM_BASKET.md`](./frontend/WEB_CUSTOM_BASKET.md) — سلة مخصصة ويب
- [`frontend/FLUTTER_CUSTOM_BASKET.md`](./frontend/FLUTTER_CUSTOM_BASKET.md) — سلة مخصصة Flutter
- [`frontend/DASHBOARD_PRODUCT_WARRANTY.md`](./frontend/DASHBOARD_PRODUCT_WARRANTY.md) — ضمان
- [`api/ADMIN_PRODUCTS_COMPLETE_DOCUMENTATION.md`](./api/ADMIN_PRODUCTS_COMPLETE_DOCUMENTATION.md) — منتجات Admin
- [`api/ADMIN_PRODUCT_VARIANTS_UPDATE_DELETE.md`](./api/ADMIN_PRODUCT_VARIANTS_UPDATE_DELETE.md) — متغيّرات CRUD
- [`api/TOGGLE_STATUS_API.md`](./api/TOGGLE_STATUS_API.md) — toggle عام
- [`api/PRODUCTS_FILTERS_API.md`](./api/PRODUCTS_FILTERS_API.md) — فلاتر
- [`custom-orders/dashboard.md`](./custom-orders/dashboard.md) — طلب سريع Admin

---

> **ملاحظة:** أرشيف الملفات القديمة في `_archive_backup_2026_08_26/` — للمرجع فقط. استخدم الملفات في `frontend/` و `api/` و `custom-orders/` و `page-sections/`.

**الباك جاهز — التعديل المتبقي في واجهات الداشبoard / الويب / Flutter.**
