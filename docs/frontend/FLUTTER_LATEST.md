# Flutter — آخر نسخة (كل التعديلات) — ديما فاشن

> **أرسلوا هذا الملف لفريق Flutter.**  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **آخر تحديث | Last Updated:** 2026-10-08  
> الملف الشامل السابق يبقى: [`flutter.md`](./flutter.md)  
> **المشروع منفصل عن تيك مارت** — الهوية البصرية: [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md)

**اليوم (8 تشرين الأول):** هوية ديما فاشن البصرية — ألوان ماجنتا/وردي من `GET /settings` · لا تيك مارت في الواجهة — [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md) · المرجع [`DEEMA_BRAND_IDENTITY.md`](./DEEMA_BRAND_IDENTITY.md)

**اليوم (سابقاً — 5 تشرين الأول):** دفع Stripe للطلبات — [`../api/STRIPE_PAYMENTS.md`](../api/STRIPE_PAYMENTS.md) (`PaymentSheet` · `merchantDisplayName: 'Deema'`)

**اليوم (سابقاً — 27 أيلول):** السلة: أقل طلب + أقرب توصيل + سعر التوصيل. المطعم لحاله فوري (سائق المطعم أو عامل ديما) — [`FLUTTER_CART_CHECKOUT_DELIVERY.md`](./FLUTTER_CART_CHECKOUT_DELIVERY.md)

**اليوم (27 أيلول):** إخفاء الفئة يخفي منتجاتها من القوائم والبحث والأقسام وصفحة المنتج (`404`) — [`FLUTTER_HIDDEN_CATEGORY_PRODUCTS.md`](./FLUTTER_HIDDEN_CATEGORY_PRODUCTS.md)

**اليوم (27 أيلول):** صورة الأيقونة — بعد استبدالها مسار الملف يتغيّر، والكاش على الرابط كامل مع `?v=` مو على `id` — [`FLUTTER_ICON_IMAGE.md`](./FLUTTER_ICON_IMAGE.md)

**اليوم (27 أيلول):** عروض تلقائية — أول طلب أو إنشاء حساب أو شراء بقيمة، والمكافأة خصم أو توصيل مجاني أو هدية — [`FLUTTER_PROMOTION_TRIGGERS.md`](./FLUTTER_PROMOTION_TRIGGERS.md)

**اليوم (25 أيلول):** جدولة السلة = تاريخ واحد + وقت داخل اليوم، وزر الحفظ يرسل الطلب — [`FLUTTER_BASKET_SCHEDULE_DATE_TIME.md`](./FLUTTER_BASKET_SCHEDULE_DATE_TIME.md)

**اليوم (25 أيلول):** السلة / الدفع / المراجعة بدون تكرار، وكرت الطلب يعرض `scheduled_delivery_at` إذا الإدارة حددته — [`FLUTTER_CHECKOUT_LAYOUT.md`](./FLUTTER_CHECKOUT_LAYOUT.md)

**اليوم (سابقاً):** حالة الطلب — اعرضوا `out_delivery` / `status_label` (مو fallback `pending`) — [`FLUTTER_ORDER_STATUS.md`](./FLUTTER_ORDER_STATUS.md)

**اليوم (سابقاً):** طلب سريع — **زر الهيدر منفصل عن القسم** (`show_header` / `show_section`) — [`QUICK_ORDER_HEADER_VS_SECTION.md`](./QUICK_ORDER_HEADER_VS_SECTION.md)

**اليوم (سابقاً):** بنر — اعرضوا `title` · `desc` · `button_text` · `link` (مو صورة فقط) — [`FLUTTER_BANNER_REQUIRED_FIELDS.md`](./FLUTTER_BANNER_REQUIRED_FIELDS.md)

**اليوم (سابقاً):** مسح حقول البنر يوصل `null` — لا تعرضوا النص القديم — [`FLUTTER_BANNER_CLEAR_FIELDS.md`](./FLUTTER_BANNER_CLEAR_FIELDS.md)

**اليوم (سابقاً):** جدولة التسليم **مطوية** — سطر + زر «عرض تفاصيل الجدولة» — [`FLUTTER_SCHEDULE_UI_COLLAPSED.md`](./FLUTTER_SCHEDULE_UI_COLLAPSED.md)

**اليوم (21 أيلول) — دفعة واحدة أرسلوا:**

| # | الموضوع | الملف |
|---|---------|--------|
| 1 | تكبير صورة المنتج (pinch / lightbox) | [`FLUTTER_PRODUCT_IMAGE_ZOOM.md`](./FLUTTER_PRODUCT_IMAGE_ZOOM.md) |
| 2 | دوائر اللون من `hex` | [`FLUTTER_PRODUCT_COLOR_HEX.md`](./FLUTTER_PRODUCT_COLOR_HEX.md) |
| 3 | جدول التفاصيل بلا ترويسة + فاصل | [`FLUTTER_PRODUCT_CATEGORY_DETAILS_TABLE.md`](./FLUTTER_PRODUCT_CATEGORY_DETAILS_TABLE.md) |
| 4 | السلة بدون مدة التوصيل | [`FLUTTER_CART_NO_DELIVERY_TIME.md`](./FLUTTER_CART_NO_DELIVERY_TIME.md) |
| 5 | لوحة المسوق فقط بعد الموافقة | [`FLUTTER_MARKETER_DASHBOARD_GATE.md`](./FLUTTER_MARKETER_DASHBOARD_GATE.md) |

**إصلاحات باك + تطبيق (نفس اليوم):** فئة 422 من فلاتر boolean · `bought_with` تنقّل بـ `id` · عنوان بعد التسجيل بدون شاشة سوداء — انظر [§0c](#0c-ماذا-تغيّر-في-21-أيلول-2026).

**اليوم (سابقاً):** خصم ثابت (`fixed`) كسور وأكبر من 100 — [`FLUTTER_FIXED_DISCOUNT.md`](./FLUTTER_FIXED_DISCOUNT.md) · لا Branch — [`FLUTTER_NO_SHOP_BRANCHES.md`](./FLUTTER_NO_SHOP_BRANCHES.md) · صفات بالـ ID — [`FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md) · كل المتغيّرات — [`FLUTTER_PRODUCT_ALL_VARIANTS.md`](./FLUTTER_PRODUCT_ALL_VARIANTS.md) · أسعار — [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./FLUTTER_PRODUCT_PRICING_FIELDS.md)

يجمع **كل** ما يحتاجه التطبيق حتى اليوم: **هوية ديما** · Nav · أقسام · فئات · فلاتر · تسجيل · طلب سريع · أسعار · متغيّرات · لون hex · جدول تفاصيل · تكبير صور · سلة بلا delivery_time · بوابة مسوّق · ضمان · كمية · سلة مخصصة · إخفاء الفئة · أيقونات · عروض تلقائية · جدولة بتاريخ ووقت · دفع ومراجعة · حالة الطلب · بنر · Stripe.

---

## الفهرس

0d. [هوية ديما فاشن (8 تشرين الأول 2026)](#0d-هوية-ديما-فاشن-8-تشرين-الأول-2026)
0c. [ماذا تغيّر في 21 أيلول 2026](#0c-ماذا-تغيّر-في-21-أيلول-2026)
0. [ماذا تغيّر في 8 أيلول 2026](#0-ماذا-تغيّر-في-8-أيلول-2026)
0b. [ماذا تغيّر في 5 أيلول 2026](#0b-ماذا-تغيّر-في-5-أيلول-2026)
1. [شريط التنقّل](#1-شريط-التنقّل)
2. [الصفحات والأقسام](#2-الصفحات-والأقسام)
3. [الفئات الدائرية والشجرة](#3-الفئات-الدائرية-والشجرة)
4. [فلاتر المنتجات](#4-فلاتر-المنتجات)
5. [صفحة المنتج](#5-صفحة-المنتج)
6. [الضمان](#6-الضمان)
7. [الكمية والسلة](#7-الكمية-والسلة)
8. [متغيّرات المنتج](#8-متغيّرات-المنتج)
9. [التسجيل](#9-التسجيل)
10. [الطلب السريع](#10-الطلب-السريع)
11. [الأسعار](#11-الأسعار)
12. [Dart — الضمان والكمية](#12-dart--الضمان-والكمية)
13. [Checklist](#13-checklist)
14. [السلة المخصصة](#14-السلة-المخصصة)
15. [لوحة المسوق](#15-لوحة-المسوق)
16. [إخفاء الفئة يخفي منتجاتها](#16-إخفاء-الفئة-يخفي-منتجاتها)
17. [صورة الأيقونة](#17-صورة-الأيقونة)
18. [العروض التلقائية](#18-العروض-التلقائية)
19. [جدولة السلة: تاريخ + وقت](#19-جدولة-السلة-تاريخ--وقت)
20. [السلة والدفع والمراجعة](#20-السلة-والدفع-والمراجعة)
21. [حالة الطلب](#21-حالة-الطلب)
22. [البنر](#22-البنر)
23. [صفحة المنتج — تعديلات العرض](#23-صفحة-المنتج--تعديلات-العرض)
24. [هوية ديما البصرية](#24-هوية-ديما-البصرية)

---

## 0d) هوية ديما فاشن (8 تشرين الأول 2026)

| البند | المطلوب |
|-------|---------|
| الاسم | **ديما فاشن** / **Deema Fashion** — مو تيك مارت / Tikmool |
| الألوان | من `GET /api/user/settings` → `color` / `dark_color` |
| أساسي / ثانوي | `#c720a4` / `#ff1493` |
| ممنوع | `#00aed1` · `#FFA000` · شعار تيك مارت · كلمة «تيك مارت» في UI |
| API قديم | `source: "tikmart"` = منصة ديما — اعرضوا «المنصة» / «ديما فاشن» |
| Stripe | `merchantDisplayName: 'Deema'` |

الدليل الكامل: [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md).

---

## 0c) ماذا تغيّر في 21 أيلول 2026

| البند | المطلوب في التطبيق |
|-------|---------------------|
| **تكبير الصورة** | ضغط صورة المنتج → fullscreen + pinch zoom + pan — [`FLUTTER_PRODUCT_IMAGE_ZOOM.md`](./FLUTTER_PRODUCT_IMAGE_ZOOM.md) |
| **دوائر اللون** | من `hex` (`options[].hex` / `attributes[].hex` / `values[].hex`) — مو اسم «أسود» — [`FLUTTER_PRODUCT_COLOR_HEX.md`](./FLUTTER_PRODUCT_COLOR_HEX.md) |
| **جدول التفاصيل** | بدون صف «الاسم/القيمة» + فاصل عمودي بين الاسم والقيمة — [`FLUTTER_PRODUCT_CATEGORY_DETAILS_TABLE.md`](./FLUTTER_PRODUCT_CATEGORY_DETAILS_TABLE.md) |
| **منتجات مشابهة** | `onTap` → `ProductDetail(bought_with[i].id)` — **لا** تشترطوا `shop_product_variant_id` |
| **فلاتر فئة / منتجات** | لا ترسلوا params فاضي؛ booleans كـ `1`/`0` أو احذفوا المفتاح؛ `attribute_values` = IDs |
| **السلة** | **لا** `delivery_time` داخل السلة/الدفع — فقط على صفحة المنتج — [`FLUTTER_CART_NO_DELIVERY_TIME.md`](./FLUTTER_CART_NO_DELIVERY_TIME.md) |
| **التسجيل + عنوان** | بعد OTP → الرئيسية دائماً؛ عنوان اختياري: `GET /areas?city_id=` ثم `POST /addresses`؛ `area.name` = **String**؛ فشل العنوان ما يوقف الدخول |
| **لوحة المسوق** | فقط إذا مترقّى + موافقة أدمن — [`FLUTTER_MARKETER_DASHBOARD_GATE.md`](./FLUTTER_MARKETER_DASHBOARD_GATE.md) |

**الباك بعد `git pull`:** `on_sale`/`in_stock_only=true` ما عادوا 422 · `hex` على صفات الفئة والمنتج · رد العنوان `area.name` نص.

---

## 0) ماذا تغيّر في 8 أيلول 2026

**مساء — ناف جدولة:** إذا وصل `route_key=schedules` افتحوا شاشة قائمة `GET /api/user/schedules`. بدون خريطة العنصر يُتجاهل. الأدمن يضيف العنصر من الداشبورد (`type=route` مو صفحة). [`NAV_MENU_SCHEDULES.md`](./NAV_MENU_SCHEDULES.md)

**صباحاً —** نفس حقول الداشبورد على الكارد وشاشة المنتج:

| تسمية الداشبورد | كارد القائمة | شاشة التفاصيل |
|-----------------|--------------|----------------|
| سعر المتغير (دولار) / ليرة | `price_currencies` من **المنتج** | من `ShopVariant` المختار |
| نوع الخصم · لا يوجد خصم · قيمة الخصم · السعر بعد الخصم | مقارنة السعر / بعد الخصم | `discountType` · `discountValue` · `priceAfterDiscountCurrencies` |
| الكمية المتوفرة | `quantity` المنتج (`int?`) | `ShopVariant.quantity` |
| الباركود · رمز التخزين التعريفي للمتغير | لا على الكرت | `barcode` · `sku` |

تغيير اللون/المقاس يبدّل كل الحقول. الدليل: [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./FLUTTER_PRODUCT_PRICING_FIELDS.md).

---

## 0b) ماذا تغيّر في 5 أيلول 2026

| البند | قبل | بعد (التطبيق) |
|-------|------|----------------|
| **الضمان** | `warranty_period` (أشهر) | `warranty: { id, name, description }` حسب `Accept-Language` |
| **كمية المنتج** | رقم على المنتج | المخزون = `shop_variants[].quantity` (ممكن `null`) |
| **منتج بلا متغيّرات** | افتراض صف | الأدمن ما عاد يضيف متغيّر فاضي — الباك يبقى يرجّع `shop_variants[0]` fallback حتى ما يكسر الشاشة |
| **canAddToCart** | `quantity > 0` | + `id != null` + `shopId != null` — كمية `null` = 0 |
| **السلة المخصصة** | — | كروت `/schedules` + قسم `display_type_id=11` + تخصيص نعم/لا — [`FLUTTER_CUSTOM_BASKET.md`](./FLUTTER_CUSTOM_BASKET.md) |
| **حقول الكرت** | شكّ إن الصورة اسمها ثاني | `image` · `images` · `top_badges` / `bottom_badges` — ما في `cover_image` / `gallery` |
| **اسم / خصم** | كائن ترجمة أو `none` | `name` = `String` · `discountType` = `percentage` \| `fixed` \| `null` |

ضمان/كمية: نفس `GET /api/user/products/{id}`. السلة المخصصة: القسم 14 + [`FLUTTER_CUSTOM_BASKET.md`](./FLUTTER_CUSTOM_BASKET.md). إذا الكرت بلا صورة: الأدمن ما حفظها بعد.

---

## 1) شريط التنقّل

```http
GET /api/user/nav-menu
Accept-Language: ar
```

| `type` | الوجهة |
|--------|--------|
| `route` | `target.route_key` → شاشات ثابتة — **أضيفوا `schedules`** (قائمة `GET /api/user/schedules`) |
| `category` | `CategoryPageScreen(categoryId:)` |
| `brand` | شاشة الماركة |
| `page` | Page Builder (`slug`) |
| `url` | فتح خارجي |

احذف القائمة الثابتة. أعد الجلب عند تغيير اللغة. `data` فاضية → أخفوا الشريط.

---

## 2) الصفحات والأقسام

| الغرض | Endpoint |
|-------|----------|
| صفحة عامة | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة | `GET /api/user/categories/{id}/page` |

ويدجت **واحد** لكل الصفحات.

| حقل | المعنى |
|-----|--------|
| `layout` | `slider` \| `list` \| `grid` — تخطيط القسم |
| `variant` | `horizontal` \| `vertical` \| `square` — شكل الكارد |
| `display_type_id` | نوع المحتوى: 1=banner … 8=category · **11=schedule** (فئات الجدولة) · 5=schedule-basket (سلل جاهزة) |

**غلط قديم:** `variant == horizontal` يعني سلايدر. **احذفوه.**  
السلايدر = `layout: slider`.

Fallback: `layout ?? 'slider'` · `variant ?? 'horizontal'`.  
قسم `items` فارغ → `SizedBox.shrink()`.  
القسم المخفي من الأدمن لا يصل للـ API.

---

## 3) الفئات الدائرية والشجرة

| الغرض | Endpoint |
|-------|----------|
| الجذور | `GET /api/user/categories` |
| الأبناء | `GET /api/user/categories?parent_id={id}` |
| منتجات | `GET /api/user/products?category_id={id}` |
| صفات | `GET /api/user/categories/{id}/attributes` |

**لا يوجد** `GET /api/user/categories/{id}` لفئة واحدة.

نفس الدائرة لكل مستوى. `has_children` → `?parent_id=`. المنتجات تظهر **تحت الدوائر دائماً**.

`category_id` = الفئة + **كل الأحفاد**. ممنوع:

```dart
products.where((p) => p.categoryId == selectedId); // خطأ
```

صفات أي مستوى = صفات الجذر. كاش `root_category_id`.

---

## 4) فلاتر المنتجات

`GET /api/user/products` — توكن اختياري لـ `is_favorite`.

باراميترات: `category_id` · `brand_id` · `shop_id` · `price_min`/`price_max` · `search` · `country` (نص) · `is_free_delivery` · `is_instant_delivery` · `on_sale` · `in_stock_only` · `attribute_values` · `type` · `sort_by` · `page` · `per_page`

- لا `name` ولا `country_id` على `/products`
- أي فلتر → `page = 1`
- empty state من `pagination.total == 0`
- chips اللون من `/categories/{id}/attributes` → `values[].hex` عند `type=color`
- لا ترسلوا مفاتيح فاضي/`null`؛ booleans: احذفوا المفتاح أو `1`/`0` (الباك يقبل `true`/`false` بعد التحديث)
- `attribute_values=31,40` — **IDs** مو أسماء

---

## 5) صفحة المنتج

```http
GET /api/user/products/{id}?lat=&lng=
Accept-Language: ar
```

| الحقل | النوع |
|-------|--------|
| `country` | `String?` — مو object |
| `warranty` | `{ id, name, description }` أو `null` |
| `warranty_period` | `int?` — قديم |
| `quantity` | `int?` على المنتج — **ليس المخزون** |
| `delivery_time` | `String?` على المنتج — **هنا فقط** (مو بالسلة) |
| `shop_variants` | دائماً عنصر واحد على الأقل |
| `shop_variants[].id` / `shop_id` | `int?` |
| `shop_variants[].quantity` | `int?` — المخزون |
| `shop_variants[].attributes[].hex` | `String?` — للون |
| `attributes_map` | قائمة؛ فاضية → أخفوا الـ picker |
| `attributes_map[].options[].hex` | للون — [`FLUTTER_PRODUCT_COLOR_HEX.md`](./FLUTTER_PRODUCT_COLOR_HEX.md) |
| `category_details` | `{ id, name, value }[]` — جدول بلا ترويسة — [`FLUTTER_PRODUCT_CATEGORY_DETAILS_TABLE.md`](./FLUTTER_PRODUCT_CATEGORY_DETAILS_TABLE.md) |
| `images` / `shop_variants[].images` | معرض؛ ضغط → zoom — [`FLUTTER_PRODUCT_IMAGE_ZOOM.md`](./FLUTTER_PRODUCT_IMAGE_ZOOM.md) |
| `icons` / `top_badges` / `bottom_badges` | كما هي |
| `bought_with` | كروت AllResource — تنقّل بـ **`id`** |

ربط المنصة بمتجر البائع يتم من الأدمن — **لا endpoint جديد**. خلّوا حماية `null`.

**منتجات مشابهة:** `Navigator → ProductDetail(id: item.id)` — لا تعتمدوا على `shop_product_variant_id` (ممكن `null`).

**تكبير الصورة:** onTap على المعرض → fullscreen + pinch + pan بين `images[].path` (مع احترام `has_variant_images`).

**جدول التفاصيل:** صفوف `name | value` فقط + فاصل عمودي — **بدون** «الاسم» / «القيمة».

---

## 6) الضمان

```dart
String? warrantyTitle(ProductDetail p) {
  if (p.warranty?.name != null && p.warranty!.name.isNotEmpty) {
    return p.warranty!.name; // Localized string
  }
  if (p.warrantyPeriod != null) return '${p.warrantyPeriod} شهر';
  return null;
}
```

- `warranty == null` وبدون أشهر → أخفوا الويدجت.
- على `/user` الحقول `name` و `description` = **String** حسب اللغة — مو `{ar, en}`.
- لا تستدعوا Admin warranties API.

---

## 7) الكمية والسلة

```dart
bool canAddToCart(ShopVariant? v) =>
    v?.id != null &&
    v?.shopId != null &&
    (v?.quantity ?? 0) > 0;

int maxBuy(ShopVariant v, ProductDetail p) {
  final qty = v.quantity ?? 0;
  final cap = p.maxPurchaseQuantity;
  if (cap == null) return qty;
  return qty < cap ? qty : cap;
}
```

| `quantity` | العرض |
|------------|--------|
| رقم > 0 | «متوفر: N» |
| `0` أو `null` | غير متوفر — عطّل السلة |

```json
{ "shop_product_variant_id": 55, "quantity": 1 }
```

لا ترسلوا سعراً من التطبيق.

قائمة المنتجات: `quantity` على الكرت ممكن `null` — لا تعملوا `quantity!`.

**مدة التوصيل في السلة:** ❌ ممنوع — [`FLUTTER_CART_NO_DELIVERY_TIME.md`](./FLUTTER_CART_NO_DELIVERY_TIME.md).  
`delivery_time` يظهر على **صفحة المنتج فقط**.

---

## 8) متغيّرات المنتج

تفصيل: [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./FLUTTER_PRODUCT_PRICING_FIELDS.md) · [`FLUTTER_PRODUCT_ALL_VARIANTS.md`](./FLUTTER_PRODUCT_ALL_VARIANTS.md) · [`FLUTTER_PRODUCT_COLOR_HEX.md`](./FLUTTER_PRODUCT_COLOR_HEX.md) · [`product-variants-flutter.md`](./product-variants-flutter.md)

- لا اسم متغيّر — `attributes` + `sku`.
- لون → فلتر المقاسات المتاحة من `shop_variants` فقط.
- دوائر اللون من **`hex`** — [`FLUTTER_PRODUCT_COLOR_HEX.md`](./FLUTTER_PRODUCT_COLOR_HEX.md).
- لا تفترضوا كل لون × كل مقاس.
- السعر/الخصم من المتغيّر المختار (`*_currencies`).
- `delivery_time` مرة واحدة على المنتج.
- `attributes_map` فاضي → استخدموا `shop_variants.first` بدون picker.

---

## 9) التسجيل

تفصيل: [`REGISTER_FLOW.md`](./REGISTER_FLOW.md) · مختصر Flutter: [`FLUTTER_REGISTER_FLOW.md`](./FLUTTER_REGISTER_FLOW.md)

```
Register → POST /auth/register (no token) → OTP → POST /auth/verify-otp → data.token → الرئيسية
```

- `phone` مطلوب (`digitsOnly`).
- `email` اختياري — لا ترسلوا `""`.
- كلمة المرور: ≥ 8 + lower + upper + digit + symbol.
- مدن: `GET /cities?governorate_id=` (الباراميتر إلزامي).
- OTP: `POST /api/user/auth/verify-otp` `{ phone, code }`. **مؤقت:** الكود **`00000`** — ما في SMS.
- إعادة الإرسال: `POST /auth/login` — **ليس** `/send-otp`.
- كل الموديلات: `email` nullable.
- بعد التوكن: `POST /auth/store-token` `{ deviceId, fcmToken }`.

**عنوان توصيل (اختياري بعد OTP):**

1. احفظوا التوكن وادخلوا الرئيسية **دائماً** — حتى لو فشل العنوان.
2. `GET /api/user/areas?city_id={user.city_id}` — `city_id` **مطلوب**.
3. `POST /api/user/addresses` — `label` · `area_id` · `street_name` · `contact_phone`.
4. رد العنوان: `area.name` = **String** (حسب اللغة) — مو `{ar,en}`.

لا تكمّشوا الـ UI على parsing العنوان (كانت تسبب شاشة سوداء).

---

## 10) الطلب السريع

تفصيل: [`../custom-orders/flutter.md`](../custom-orders/flutter.md)

`GET /api/user/settings` → `quick_order`:

- `show_header` → زر الهيدر فقط.
- `show_section` → القسم؛ اعرضوه فقط إذا الصفحة ∈ `page_slugs` (افتراضي `home`).
- `is_enabled` = `show_section` (توافق خلفي — لا تستخدموه للزر).
- CTA → `POST /api/user/custom-order-requests` (`description` ≥ 10، `address_id`).

تفصيل الفصل: [`QUICK_ORDER_HEADER_VS_SECTION.md`](./QUICK_ORDER_HEADER_VS_SECTION.md)

---

## 11) الأسعار

`*_formatted` / `*_currencies` فقط. لا تحويل محلي.

صفحة المنتج: بعد الخصم أساسي؛ الأصلي مشطوب.

---

## 12) Dart — الضمان والكمية

```dart
class ProductWarranty {
  final int id;
  final String name;
  final String? description;

  factory ProductWarranty.fromJson(Map<String, dynamic> json) =>
      ProductWarranty(
        id: json['id'] as int,
        name: json['name'] as String? ?? '',
        description: json['description'] as String?,
      );
}

class ProductDetail {
  final String? country;
  final int? quantity; // product-level — nullable, not stock
  final int? warrantyPeriod;
  final ProductWarranty? warranty;
  final String? deliveryTime;
  final int? maxPurchaseQuantity;
  final List<ShopVariant> shopVariants;
}

class ShopVariant {
  final int? id;
  final int? shopId;
  final num price;
  final int? quantity; // stock — nullable
}
```

Parse آمن:

```dart
int? asInt(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  return int.tryParse('$v');
}
```

---

## 13) Checklist

### أقسام وفئات
- [ ] ويدجت أقسام موحّد (`layout` ثم `variant`)
- [ ] دوائر لكل مستوى + منتجات معها
- [ ] لا فلترة محلية على `category_id`
- [ ] chips اللون من `values[].hex`

### منتج + سلة
- [ ] `country` كـ `String?`
- [ ] لا `shopVariants[0]` بدون حماية
- [ ] **ضمان:** `warranty.name` / `.description`
- [ ] **كمية:** `ShopVariant.quantity` كـ `int?`
- [ ] **سعر/خصم/باركود/SKU:** الكارد من المنتج · التفاصيل من المتغيّر — [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./FLUTTER_PRODUCT_PRICING_FIELDS.md)
- [ ] **لون:** دوائر من `hex` — [`FLUTTER_PRODUCT_COLOR_HEX.md`](./FLUTTER_PRODUCT_COLOR_HEX.md)
- [ ] **جدول التفاصيل:** بلا ترويسة + فاصل — [`FLUTTER_PRODUCT_CATEGORY_DETAILS_TABLE.md`](./FLUTTER_PRODUCT_CATEGORY_DETAILS_TABLE.md)
- [ ] **تكبير الصورة:** pinch lightbox — [`FLUTTER_PRODUCT_IMAGE_ZOOM.md`](./FLUTTER_PRODUCT_IMAGE_ZOOM.md)
- [ ] **مشابهة:** تنقّل بـ `bought_with[].id`
- [ ] **السلة:** بلا `delivery_time` — [`FLUTTER_CART_NO_DELIVERY_TIME.md`](./FLUTTER_CART_NO_DELIVERY_TIME.md)
- [ ] `canAddToCart`: id + shopId + qty > 0
- [ ] لا Cartesian
- [ ] سلة مخصصة: قسم `display_type_id=11` + كروت `image`/`images`/`top_badges` + تخصيص `/schedules/{id}/custom-basket` — [`FLUTTER_CUSTOM_BASKET.md`](./FLUTTER_CUSTOM_BASKET.md)

### باقي التطبيق
- [ ] Nav ديناميكي + `schedules` → شاشة الجداول
- [ ] فلاتر + chips الجذر
- [ ] تسجيل: هاتف مطلوب + بعد OTP للرئيسية دائماً — [`REGISTER_FLOW.md`](./REGISTER_FLOW.md)
- [ ] عنوان: `areas?city_id=` · `area.name` String · فشل ما يجمّد التطبيق
- [ ] أسعار من API
- [ ] طلب سريع من `settings.quick_order`
- [ ] لوحة المسوق فقط بعد الموافقة — [`FLUTTER_MARKETER_DASHBOARD_GATE.md`](./FLUTTER_MARKETER_DASHBOARD_GATE.md)
- [ ] فئة مخفية: امسحوا الكاش، و`404` يفتح «غير موجود» — [§16](#16-إخفاء-الفئة-يخفي-منتجاتها)
- [ ] أيقونة من `icon` أو `image` مع `?v=` ومفتاح الكاش = الرابط كامل — [§17](#17-صورة-الأيقونة)
- [ ] عروض تلقائية من `automatic_promotions` — لا `promotion_id` — [§18](#18-العروض-التلقائية)
- [ ] جدولة السلة: يوم واحد + `delivery_time` `HH:mm` — [§19](#19-جدولة-السلة-تاريخ--وقت)
- [ ] الدفع والمراجعة بدون تكرار، و`scheduled_delivery_at` على كرت الطلب — [§20](#20-السلة-والدفع-والمراجعة)
- [ ] حالة الطلب: `out_delivery` + `status_label` — [§21](#21-حالة-الطلب)
- [ ] بنر: عنوان ووصف وزر ورابط، و`null` بعد المسح — [§22](#22-البنر)
- [ ] **هوية ديما:** ثيم من `settings.color` · لا تيك مارت — [§24](#24-هوية-ديما-البصرية) · [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md)
- [ ] Stripe PaymentSheet باسم `Deema` — [`../api/STRIPE_PAYMENTS.md`](../api/STRIPE_PAYMENTS.md)

---

## 14) السلة المخصصة

> **أرسلوا هذا الملف:** [`FLUTTER_CUSTOM_BASKET.md`](./FLUTTER_CUSTOM_BASKET.md) — **5 أيلول 2026**

على الرئيسية: `GET /api/user/sections?page_slug=home` — إذا `display_type_id=11` أو `content_type=schedule` اعرضوا كروت فئات (صورة دائرية). `onTap` → شاشة `scheduleId`.  
`display_type_id=5` = سلل أدمن جاهزة — مسار مختلف.

كروت / قائمة: `GET /api/user/schedules` — `image` · `images` · `top_badges` / `bottom_badges`. `name` = `String`. `discount_type` = `percentage` \| `fixed` \| `null`.

تخصيص (`Auth`):

| | Endpoint |
|--|----------|
| هيدر + مسودة | `GET /api/user/schedules/{id}/custom-basket` |
| إضافة | `POST .../items` `{ shop_product_variant_id, quantity }` من `shop_variants[].id` |
| كمية | `PUT .../items/{itemId}` `{ quantity }` — `itemId` = `items[].id` |
| حذف | `DELETE .../items/{itemId}` |
| تأكيد | `POST .../confirm` |

نعم: `{ "confirm_schedule": true, "start_date": "2026-09-08" }` → طلباتي المجدولة + `cart_items`  
لا: `{ "confirm_schedule": false }` → `cart_items` مرة — المسودة تُحذف

**واجهة التأكيد مطوية:** سطر + زر «عرض تفاصيل الجدولة» — [`FLUTTER_SCHEDULE_UI_COLLAPSED.md`](./FLUTTER_SCHEDULE_UI_COLLAPSED.md)

`summary.savingsFormatted` = وفّرت. لا تحسبوا الخصم على الجهاز. مسودة لكل `scheduleId`.

---

## 15) لوحة المسوق

تفصيل: [`FLUTTER_MARKETER_DASHBOARD_GATE.md`](./FLUTTER_MARKETER_DASHBOARD_GATE.md)

لوحة المسوق (رصيد · طلبات · أرباح · إنشاء طلب) تظهر **فقط** بعد ترقية الحساب وموافقة الأدمن.  
قبلها: زر/شاشة «كن مسوقاً» فقط — لا تعرضوا لوحة المسوق لليوزر العادي.

```dart
final isApprovedMarketer =
    user.affiliate?.isAffiliate == true && user.affiliate?.approved == true;
```

---

## 16) إخفاء الفئة يخفي منتجاتها

> **27 أيلول 2026** — [`FLUTTER_HIDDEN_CATEGORY_PRODUCTS.md`](./FLUTTER_HIDDEN_CATEGORY_PRODUCTS.md)

ما في حقل جديد ولا فلتر محلي. بعد إخفاء الفئة امسحوا كاش المنتجات والفئات وأعيدوا الجلب.

| المكان | السلوك |
|--------|--------|
| `GET /api/user/categories` | الفئة المخفية ما ترجع، ولا الفئات تحت أب مخفي |
| `GET /api/user/categories/{id}/page` | `404` |
| `GET /api/user/products` و `?category_id=` | منتجات الشجرة ما ترجع |
| `GET /api/user/products/{id}` | `404` → شاشة «غير موجود» |
| البحث والأقسام والمفضلة و`bought_with` | الكرت ما يجي |

إخفاء الفئة الرئيسية يخفي منتجات الفروع. إعادة التفعيل ترجّعها في الطلب التالي. `products_count` على العلامة بدون منتجات الفئات المخفية.

---

## 17) صورة الأيقونة

> **27 أيلول 2026** — [`FLUTTER_ICON_IMAGE.md`](./FLUTTER_ICON_IMAGE.md)

`icons[]` من `GET /products/{id}`. الصورة = `icon` أو `image` كما رجع. بعد الاستبدال من الداشبورد اسم الملف يتغيّر وفيه `?v=`. لا تقصّوا الاستعلام. `cacheKey` = الرابط كامل، مو `id`. `icons` فاضي → أخفوا الصف. لا أصول SVG محلية بديلة.

---

## 18) العروض التلقائية

> **27 أيلول 2026** — [`FLUTTER_PROMOTION_TRIGGERS.md`](./FLUTTER_PROMOTION_TRIGGERS.md)

تنطبق وحدها على المعاينة والطلب. لا ترسلوا `promotion_id`. يبقى لخصم يختاره الزبون: `simple_discount` أو `spend_x_discount`.

| المكافأة | من وين |
|----------|--------|
| خصم | `discounts.promotion_discount` و `automatic_promotions.discounts[]` |
| توصيل مجاني | `free_shipping_applies` = `true` و `delivery_price` = 0 |
| هدية | `automatic_promotions.gifts[]` |

الأنواع: `first_order_*` · `signup_*` · `spend_x_get_free_shipping` · `spend_x_get_gift` · `free_shipping`. المجموع في `total`. لا تعيدوا الحساب على الجهاز.

| الشرط | الزبون |
|--------|--------|
| أول طلب | ما عنده طلب سابق. الملغى ما يُحسب |
| إنشاء حساب | الحساب خلال فترة العرض، وهالطلب أول طلب |
| شراء بقيمة | مجموع الأصناف المؤهلة ≥ `min_spend` |

---

## 19) جدولة السلة: تاريخ + وقت

> **25 أيلول 2026** — [`FLUTTER_BASKET_SCHEDULE_DATE_TIME.md`](./FLUTTER_BASKET_SCHEDULE_DATE_TIME.md)

يوم واحد ≥ اليوم + `showTimePicker`. بلا Start Date. لا تعكسوا أعمدة التقويم في RTL. زر الحفظ `onPressed` شغال ويستدعي `POST /api/user/scheduled-baskets`.

```json
{
  "name": "أسبوعي",
  "schedule_id": 2,
  "start_date": "2026-10-21",
  "delivery_time": "16:30",
  "items": [{ "shop_product_variant_id": 25, "quantity": 2 }]
}
```

`delivery_time` = `HH:mm`. اليوم: ارفضوا وقتاً قبل `TimeOfDay.now()`. لا `category_id`.  
`confirm` المخصصة يبقى `start_date` فقط. العرض: `21 Oct 2026 · 16:30`، وإذا `deliveryTime == null` التاريخ فقط.  
لا تخلطوا مع `product.deliveryTime` («3–5 أيام») ولا مع `scheduled_delivery_at` على الطلب.

---

## 20) السلة والدفع والمراجعة

> **25 أيلول 2026** — [`FLUTTER_CHECKOUT_LAYOUT.md`](./FLUTTER_CHECKOUT_LAYOUT.md)

| المعلومة | السلة | الدفع | المراجعة | كرت الطلب |
|----------|-------|-------|----------|-----------|
| تعديل الكمية / الحذف | ✅ | ❌ | ❌ | ❌ |
| صورة + اسم + كمية + سعر | القائمة | ملخص قصير | مرة واحدة | حسب الكرت |
| `scheduled_delivery_at` | ❌ | ❌ | ❌ | ✅ إذا مو `null` |
| الإجمالي | ✅ | ❌ | مرة واحدة | ✅ |

`product.deliveryTime` على صفحة المنتج فقط. الدفع بلا جدول وبلا سطر توصيل. المراجعة بلا عنوان «مراجعة طلبك» وبلا «عدد العناصر»: طريقة الدفع + القائمة مرة + الإجمالي.

`scheduled_delivery_at`: `"2026-09-28 16:30"` أو `null`. الزبون ما يختاره في الدفع.

---

## 21) حالة الطلب

> [`FLUTTER_ORDER_STATUS.md`](./FLUTTER_ORDER_STATUS.md)

اعرضوا `status_label`. المفتاح `out_delivery` — مو `out_for_delivery`. مفتاح ناقص في الـ enum كان يطلع `pending`.

القيم: `pending` · `waiting_approval` · `preparing` · `out_delivery` · `delivered` · `cancelled` · `cancelled_by_admin` · `rejected_by_delivery` · `faild_deliver` · `returned_by_user`.

---

## 22) البنر

> [`FLUTTER_BANNER_REQUIRED_FIELDS.md`](./FLUTTER_BANNER_REQUIRED_FIELDS.md) · المسح: [`FLUTTER_BANNER_CLEAR_FIELDS.md`](./FLUTTER_BANNER_CLEAR_FIELDS.md)

اعرضوا `title` · `desc` · `button_text` · `link` إن وُجدت، مو الصورة وحدها. بعد المسح: `null` أو `""` — لا عنوان، لا وصف، لا زر، والكارد مو قابل للضغط. لا تبقوا النص القديم في الموديل. الصورة تبقى.

---

## 23) صفحة المنتج — تعديلات العرض

| الموضوع | المطلوب |
|---------|---------|
| تكبير الصورة | pinch + pan — [`FLUTTER_PRODUCT_IMAGE_ZOOM.md`](./FLUTTER_PRODUCT_IMAGE_ZOOM.md) |
| لون | دوائر من `hex` — [`FLUTTER_PRODUCT_COLOR_HEX.md`](./FLUTTER_PRODUCT_COLOR_HEX.md) |
| جدول التفاصيل | بلا ترويسة + فاصل عمودي — [`FLUTTER_PRODUCT_CATEGORY_DETAILS_TABLE.md`](./FLUTTER_PRODUCT_CATEGORY_DETAILS_TABLE.md) |
| خصم ثابت | `discount_value` number، كسور وأكبر من 100 — لا `toInt` — [`FLUTTER_FIXED_DISCOUNT.md`](./FLUTTER_FIXED_DISCOUNT.md) |
| بلا فروع | لا Branch ولا اسم متجر — [`FLUTTER_NO_SHOP_BRANCHES.md`](./FLUTTER_NO_SHOP_BRANCHES.md) |
| صفات بالـ ID | الفلتر والـ picker من `id` — [`FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md) |
| كل المتغيّرات | اختاروا الصف من `shop_variants` مو `[0]` دائماً — [`FLUTTER_PRODUCT_ALL_VARIANTS.md`](./FLUTTER_PRODUCT_ALL_VARIANTS.md) |
| السلة | بلا `delivery_time` — [`FLUTTER_CART_NO_DELIVERY_TIME.md`](./FLUTTER_CART_NO_DELIVERY_TIME.md) |

السعر بعد الخصم من الحقول الجاهزة في الـ API. لا تحسبوه على الجهاز.

---

## 24) هوية ديما البصرية

> **8 تشرين الأول 2026** — [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md)

المشروع **ديما فاشن** منفصل عن تيك مارت. ابنوا الثيم من:

```http
GET /api/user/settings
```

| حقل | استخدام | افتراضي |
|-----|---------|---------|
| `color.main_color` | أزرار · AppBar | `#c720a4` |
| `color.second_color` | CTA ثانوي | `#ff1493` |
| `color.text_color` | نص | `#1F2937` |
| `dark_color.*` | الوضع الداكن | `#1a0a16` / `#a020f0` / `#fce7f3` |
| `login.image` / `welcome.image` | شعار / ترحيب | من السيرفر |
| `contact.*` | تواصل | `info@deemafashion.com` |

نصوص السلة: `source == "tikmart"` → اعرضوا «ديما فاشن» / «المنصة». لا تكتبوا «تيك مارت».

