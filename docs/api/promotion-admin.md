# Promotion — لوحة التحكم (Admin API)

**Base URL:** `/api/admin/promotions`  
**المصادقة:** `auth:admin` + `crud.permission:promotion`

| المكوّن | المسار |
|---------|--------|
| الكنترولر | `app/Http/Controllers/Admin/PromotionController.php` |
| الطلبات | `app/Http/Requests/Admin/Promotion/StoreRequest.php`, `UpdateRequest.php` |
| السيرفس | `app/Services/Admin/PromotionService.php` |
| الموارد | `app/Http/Resources/Promotion/OneResource.php`, `AllResource.php` |
| الموديل | `app/Models/Promotion.php` |

---

## Endpoints

| الطريقة | المسار | الوصف |
|---------|--------|--------|
| `GET` | `/api/admin/promotions` | قائمة مع ترقيم (`page`, `per_page`)، بحث، ترتيب |
| `POST` | `/api/admin/promotions` | إنشاء |
| `GET` | `/api/admin/promotions/{id}` | تفاصيل (`OneResource`) |
| `PUT`/`PATCH` | `/api/admin/promotions/{id}` | تحديث |
| `DELETE` | `/api/admin/promotions/{id}` | حذف |
| `GET` | `/api/admin/promotions/fields-for-type/{type}` | حقول النموذج حسب نوع العرض |

**Query (للقائمة):** `search`, `sort_field`, `sort_order`, `page`, `per_page` — انظر `BaseCRUDController` / `BaseService`.

---

## أنواع العروض (`type`)

| القيمة | الوصف | حقول مطلوبة إضافية |
|--------|--------|---------------------|
| `simple_discount` | خصم مباشر (يختاره المستخدم عند الطلب) | `discount_value`, `discount_type` موصى بهما |
| `spend_x_discount` | خصم عند بلوغ حد إنفاق | `min_spend`, `discount_value`, `discount_type` |
| `spend_x_get_gift` | هدية عند بلوغ حد إنفاق | `min_spend`, `gift_description` (ar/en) |
| `spend_x_get_points` | نقاط مكافأة عند بلوغ حد إنفاق | `min_spend`, `reward_points` |
| `free_shipping` | شحن مجاني تلقائي (بدون حد إنفاق) | — |
| `spend_x_get_free_shipping` | شحن مجاني عند بلوغ حد إنفاق | `min_spend` |
| `first_order_discount` | خصم تلقائي على أول طلب | `discount_value`, `discount_type` |
| `first_order_free_shipping` | توصيل مجاني على أول طلب | — |
| `first_order_gift` | هدية مع أول طلب | `gift_description` (ar/en) |
| `signup_discount` | خصم على أول طلب لحساب أُنشئ أثناء العرض | `discount_value`, `discount_type` |
| `signup_free_shipping` | توصيل مجاني على أول طلب لحساب أُنشئ أثناء العرض | — |
| `signup_gift` | هدية مع أول طلب لحساب أُنشئ أثناء العرض | `gift_description` (ar/en) |

---

## جسم الطلب — إنشاء (`POST`)

### حقول أساسية

| الحقل | القواعد | ملاحظات |
|--------|---------|---------|
| `name` | مطلوب `array` | `name.en`, `name.ar` مطلوبان |
| `description` | مطلوب `array` | `description.en`, `description.ar` مطلوبان |
| `type` | مطلوب، أحد الأنواع أعلاه | — |
| `position` | مطلوب: `top` \| `bottom` | مكان عرض البانر في الواجهة |
| `is_active` | اختياري `boolean` | افتراضي حسب الموديل |
| `starts_at`, `ends_at` | اختياري `date` | `ends_at` ≥ `starts_at` |
| `min_spend` | مطلوب لأنواع Spend X | `numeric`, `min:0` |
| `discount_value` | مطلوب لـ `first_order_discount` و `signup_discount` | `numeric`, `min:0` |
| `discount_type` | مطلوب مع الخصم التلقائي | `percentage` \| `fixed` |
| `gift_description` | مطلوب لـ `spend_x_get_gift` و `first_order_gift` و `signup_gift` | `gift_description.en`, `gift_description.ar` |
| `reward_points` | مطلوب لـ `spend_x_get_points` | `integer`, `min:1` |

### ربط الصفحات (`page_slugs`)

| الحقل | القواعد |
|--------|---------|
| `page_slugs` | اختياري `array`؛ كل عنصر: `exists:pages,slug` |

- تُحفظ عبر pivot **`page_promotion`**.
- عند **الإنشاء:** إن لم تُرسل `page_slugs` لا يُربط العرض بأي صفحة (يظهر في كل الصفحات حسب منطق المستخدم: `scopeForPageSlug`).
- عند **التحديث:** إذا **وُجد** المفتاح في الطلب تُعاد المزامنة (`[]` = إزالة كل الروابط). إذا **لم يُرسل** المفتاح لا تتغير الصفحات.

### الاستهداف (Targeting)

| الحقل | القواعد |
|--------|---------|
| `product_ids` | اختياري `array` من `products.id` |
| `category_ids` | اختياري `array` من `categories.id` |
| `shop_ids` | اختياري `array` من `shops.id` |
| `vendor_ids` | اختياري `array` من `vendors.id` |

**سلوك الاستهداف (عند الطلب / للمستخدم):**

- إذا **لم يُحدَّد** أي من الأربعة (كلها فارغة أو غير مُرسلة عند الإنشاء) → العرض يطبَّق على **كل** المنتجات/عناصر السلة المؤهلة حسب نوع العرض.
- إذا وُجد استهداف واحد أو أكثر → يجب أن **يطابق** عنصر السلة **كل** الأبعاد المفعّلة (منطق AND):
  - إن وُجدت `product_ids` → المنتج ضمن القائمة.
  - إن وُجدت `category_ids` → التصنيف ضمن القائمة.
  - إن وُجدت `shop_ids` → المتجر ضمن القائمة.
  - إن وُجدت `vendor_ids` → البائع ضمن القائمة.
- لأنواع **Spend X** يُحسب `min_spend` على **`eligible_subtotal`** (مجموع أسعار العناصر المؤهلة فقط)، وليس إجمالي السلة.

**مزامنة عند التحديث (مثل `page_slugs`):**

- إذا **حُذف** مفتاح مثل `product_ids` من الطلب → لا تتغير منتجات الربط الحالية.
- إذا **أُرسل** `product_ids: []` → تُفرَّغ روابط المنتجات.

الجداول: `promotion_products`, `promotion_categories`, `promotion_shops`, `promotion_vendors` — انظر `database/migrations/2026_06_02_120000_create_promotion_targeting_tables.php`.

---

## جسم الطلب — تحديث (`PUT`/`PATCH`)

نفس الحقول مع `nullable` حيث ينطبق في `UpdateRequest`.

- عند تغيير `type` تُطبَّق قواعد `required_if` الجديدة على الحقول المرسلة.
- `position` اختياري عند التحديث.

---

## مثال JSON — إنشاء (`spend_x_discount` مع استهداف)

```json
{
  "name": { "en": "Shop A discount", "ar": "خصم متجر أ" },
  "description": { "en": "10% off selected shop", "ar": "خصم 10% على متجر محدد" },
  "type": "spend_x_discount",
  "position": "top",
  "is_active": true,
  "starts_at": "2026-06-01T00:00:00",
  "ends_at": "2026-12-31T23:59:59",
  "min_spend": 250,
  "discount_value": 10,
  "discount_type": "percentage",
  "page_slugs": ["home", "checkout"],
  "shop_ids": [3, 7],
  "product_ids": [],
  "category_ids": [],
  "vendor_ids": []
}
```

---

## استجابة التفاصيل (`OneResource`)

```json
{
  "id": 1,
  "name": { "en": "...", "ar": "..." },
  "description": { "en": "...", "ar": "..." },
  "type": "spend_x_discount",
  "is_active": true,
  "position": "top",
  "starts_at": "2026-06-01T00:00:00.000000Z",
  "ends_at": "2026-12-31T23:59:59.000000Z",
  "min_spend": "250.00",
  "discount_value": "10.00",
  "discount_type": "percentage",
  "gift_description": { "en": "", "ar": "" },
  "reward_points": null,
  "page_slugs": ["home", "checkout"],
  "product_ids": [12, 45],
  "category_ids": [2],
  "shop_ids": [3],
  "vendor_ids": []
}
```

- `page_slugs`, `product_ids`, `category_ids`, `shop_ids`, `vendor_ids` تظهر عند تحميل العلاقات (`pages`, `products`, `categories`, `shops`, `vendors`).

## استجابة القائمة (`AllResource`)

`id`, `name`, `description`, `type`, `is_active`, `position`, `created_at`, وعند التحميل: `page_slugs`, `product_ids`, `category_ids`, `shop_ids`, `vendor_ids`.

---

## `GET /api/admin/promotions/fields-for-type/{type}`

يعيد مصفوفة أسماء الحقول التي يجب إظهارها في نموذج لوحة التحكم حسب `type` (من `PromotionService::fieldsForType`).

**مثال:** `GET /api/admin/promotions/fields-for-type/spend_x_get_gift`

```json
{
  "success": true,
  "data": [
    "name",
    "description",
    "min_spend",
    "gift_description",
    "is_active",
    "starts_at",
    "ends_at",
    "page_slugs",
    "position",
    "product_ids",
    "category_ids",
    "shop_ids",
    "vendor_ids"
  ]
}
```

---

## تأثير العرض على الطلب (مرجع للمطور)

| نوع العرض | سلوك على الطلب |
|-----------|----------------|
| `simple_discount`, `spend_x_discount` | يختارها المستخدم (`promotion_id`)؛ الخصم من `eligible_subtotal` |
| `spend_x_get_gift`, `spend_x_get_points` | تلقائي عند إنشاء الطلب إذا تحقق `min_spend` على المؤهل |
| `free_shipping`, `spend_x_get_free_shipping` | تلقائي؛ يصفّر `delivery_price` عند التأهل |
| `first_order_discount`, `first_order_free_shipping`, `first_order_gift` | تلقائي إذا لم يكن للزبون طلب سابق (الملغى لا يُحسب) |
| `signup_discount`, `signup_free_shipping`, `signup_gift` | تلقائي على أول طلب إذا أُنشئ الحساب داخل فترة العرض |

التنفيذ: `app/Services/User/PromotionService.php` + `app/Services/User/OrderService.php`.

---

## مراجع

| الموضوع | المسار |
|---------|--------|
| صفحات العرض | `database/migrations/2026_05_02_150000_create_page_promotion_table.php` |
| الاستهداف | `database/migrations/2026_06_02_120000_create_promotion_targeting_tables.php` |
| موضع العرض | `database/migrations/2026_05_03_120000_add_position_to_promotions_table.php` |
| API المستخدم | `docs/promotion-user.md`, `docs/USER_PROMOTIONS_API.md` |
| اختبارات الاستهداف | `tests/Feature/PromotionTargetingTest.php` |
