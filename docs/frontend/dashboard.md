# داشبورد — كل التعديلات (نسخة نهائية)

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> Base: `/api/admin` + Admin token.  
> **آخر تحديث:** 27 أيلول 2026  
> يجمع **كل** تعديلات الباك التي تحتاج تنفيذ في الداشبورد (مو بس المنتج).

**اليوم:** قائمة البانرات على الشاشة الصغيرة تسكرول وتعرض كل البطاقات — [`DASHBOARD_BANNER_LIST_SCROLL.md`](./DASHBOARD_BANNER_LIST_SCROLL.md)

**اليوم:** إخفاء الفئة (`is_active: false`) يخفي منتجاتها من الموقع والتطبيق، بدون تعطيل المنتجات في الداشبورد — [`DASHBOARD_HIDDEN_CATEGORY_PRODUCTS.md`](./DASHBOARD_HIDDEN_CATEGORY_PRODUCTS.md)

**اليوم:** تغيير صورة الأيقونة ينحفظ، والمعاينة من `image` / `icon` بعد الرد — [`DASHBOARD_ICON_IMAGE.md`](./DASHBOARD_ICON_IMAGE.md)

**اليوم:** العروض = شرط (أول طلب / شراء بقيمة / إنشاء حساب) + مكافأة (خصم / توصيل مجاني / هدية) — [`DASHBOARD_PROMOTION_TRIGGERS.md`](./DASHBOARD_PROMOTION_TRIGGERS.md)

**اليوم:** جدولة سلة الزبون = تاريخ توصيل واحد + `delivery_time` (عرض فقط) — [`DASHBOARD_BASKET_SCHEDULE_DATE_TIME.md`](./DASHBOARD_BASKET_SCHEDULE_DATE_TIME.md)

**اليوم:** يوم ووقت التوصيل خيار ثاني على الطلب (`scheduled_delivery_at`) — [`DASHBOARD_ORDER_DELIVERY_SCHEDULE.md`](./DASHBOARD_ORDER_DELIVERY_SCHEDULE.md)

**اليوم (سابقاً):** حالة الطلب — `out_delivery` (مو `out_for_delivery`) + `status_label` — [`DASHBOARD_ORDER_STATUS.md`](./DASHBOARD_ORDER_STATUS.md)

**اليوم (سابقاً):** حقول البنر اختيارية ما عدا الصورة عند الإنشاء، ومسحها ينحفظ `null` — [`DASHBOARD_BANNER_REQUIRED_FIELDS.md`](./DASHBOARD_BANNER_REQUIRED_FIELDS.md) · [`DASHBOARD_BANNER_CLEAR_FIELDS.md`](./DASHBOARD_BANNER_CLEAR_FIELDS.md)

**اليوم (سابقاً):** بطاقة البنر في الصفحة: صورة + اسم فقط — [`DASHBOARD_SECTION_BANNER_CARD.md`](./DASHBOARD_SECTION_BANNER_CARD.md)

**اليوم (سابقاً):** `delivery_time` على فورم المنتج فقط — مو داخل سلة الزبون — [`DASHBOARD_CART_NO_DELIVERY_TIME.md`](./DASHBOARD_CART_NO_DELIVERY_TIME.md)

**اليوم (سابقاً):** إضافات المنتجات = اسم + سعر (مو عنوان/قيمة قطن) — [`DASHBOARD_PRODUCT_EXTRA_DETAILS.md`](./DASHBOARD_PRODUCT_EXTRA_DETAILS.md)

**اليوم (سابقاً):** خصم ثابت يقبل كسور وأكبر من 100 — [`DASHBOARD_FIXED_DISCOUNT.md`](./DASHBOARD_FIXED_DISCOUNT.md)

**اليوم (سابقاً):** فروع المتاجر واسم الفرع ملغيان من فورم المنتج — [`DASHBOARD_NO_SHOP_BRANCHES.md`](./DASHBOARD_NO_SHOP_BRANCHES.md)

**اليوم (سابقاً):** قيم الصفات على المنتج بالـ ID — select لكل صفة، الحفظ ما يمسح القياس — [`DASHBOARD_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./DASHBOARD_CATEGORY_ATTRIBUTE_VALUE_IDS.md)

**اليوم (سابقاً):** كل متغيّر لازم ينحفظ بـ `attributes_values_ids` — مو بس الأول يشتغل على الموقع — [`DASHBOARD_PRODUCT_ALL_VARIANTS.md`](./DASHBOARD_PRODUCT_ALL_VARIANTS.md)

**اليوم (سابقاً):** أيقونات المنتج تظهر على الموقع من `icons[]` — الداشبورد تربط فقط `icon_ids` — [`PRODUCT_ICONS_WEB_DASHBOARD.md`](./PRODUCT_ICONS_WEB_DASHBOARD.md)

**اليوم (مساء):** ناف «جدولة» من الداشبورد (`type=route` + `schedules`) · إنشاء منتج بدون `vendor_id` — [`NAV_MENU_SCHEDULES.md`](./NAV_MENU_SCHEDULES.md) · [`DASHBOARD_PRODUCT_VENDOR_ID.md`](./DASHBOARD_PRODUCT_VENDOR_ID.md)

**اليوم (صباحاً):** حقول السعر/الخصم/الكمية/الباركود/SKU — داشبورد [`DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./DASHBOARD_PRODUCT_PRICING_FIELDS.md) · ويب [`WEB_PRODUCT_PRICING_FIELDS.md`](./WEB_PRODUCT_PRICING_FIELDS.md) · Flutter [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./FLUTTER_PRODUCT_PRICING_FIELDS.md).

---

## الفهرس

1. [Page Builder الموحّد](#1-page-builder-الموحّد)
2. [إدارة الأقسام (Sections)](#2-إدارة-الأقسام)
3. [صفحات الفئات التلقائية](#3-صفحات-الفئات-التلقائية)
4. [شريط التنقّل (Nav Menu)](#4-شريط-التنقّل-nav-menu)
5. [إسناد المنتج لأي مستوى فئة](#5-إسناد-المنتج-لأي-مستوى-فئة)
6. [وراثة صفات الفئة من الرئيسية](#6-وراثة-صفات-الفئة-من-الرئيسية)
7. [سعر وكمية المتغيّر (Variant)](#7-سعر-وكمية-المتغيّر)
8. [حفظ المتغيّرات وربط المتجر](#8-حفظ-المتغيّرات-وربط-المتجر)
9. [حذف المتغيّرات مع تأكيد](#9-حذف-المتغيّرات-مع-تأكيد)
10. [حذف صفات الفئة مع تأكيد](#10-حذف-صفات-الفئة-مع-تأكيد)
11. [أسعار المنتج (USD/SYP) + حذف الفئات بتأكيد](#11-أسعار-المنتج-وحذف-الفئات)
12. [بلد المنشأ — Select من كل دول العالم](#12-بلد-المنشأ--select-من-كل-دول-العالم)
13. [بلدان المبيع — بدون رفع أيقونة + افتراضي سوريا](#13-بلدان-المبيع--بدون-رفع-أيقونة--افتراضي-سوريا)
14. [قناة البيع — للموقع أو ربط بمتجر](#14-قناة-البيع--للموقع-أو-ربط-بمتجر)
15. [الطلب السريع — إعدادات + صفحات الظهور](#15-الطلب-السريع--إعدادات--صفحات-الظهور)
16. [استيراد منتجات من Excel](#16-استيراد-منتجات-من-excel)
17. [متغيّرات المنتج — Single select + كارد حقول](#17-متغيّرات-المنتج--single-select--كارد-حقول)
18. [باگ: كمية المنتج إلزامية والمخفية](#18-باگ-كمية-المنتج-إلزامية-والمخفية)
19. [الضمان دروب داون](#19-الضمان-دروب-داون--لا-متغيّر-افتراضي)
20. [السلل المجدولة + السلة المخصصة](#20-السلل-المجدولة--كتالوج-الجدولات)
21. [حقول السعر · الخصم · الكمية · باركود · SKU](#21-حقول-السعر--الخصم--الكمية--باركود--sku)
22. [ناف بار — جدولة + إنشاء منتج بدون vendor_id](#22-ناف-بار--جدولة--إنشاء-منتج)
23. [تعديل قيم الصفات بالـ ID](#23-تعديل-قيم-الصفات-بالـ-id)
24. [كل المتغيّرات تُحفظ وتُربط بالمتجر](#24-كل-المتغيّرات-تحفظ-وتربط-بالمتجر)
25. [إلغاء فروع المتاجر](#25-إلغاء-فروع-المتاجر)
26. [إضافات المنتجات — اسم + سعر](#26-إضافات-المنتجات--اسم--سعر)
27. [إخفاء الفئة يخفي منتجاتها](#27-إخفاء-الفئة-يخفي-منتجاتها)
28. [تغيير صورة الأيقونة](#28-تغيير-صورة-الأيقونة)
29. [عروض: شرط + مكافأة](#29-عروض-شرط--مكافأة)
30. [تاريخ ووقت جدولة سلة الزبون](#30-تاريخ-ووقت-جدولة-سلة-الزبون)
31. [يوم ووقت التوصيل على الطلب](#31-يوم-ووقت-التوصيل-على-الطلب)
32. [حالة الطلب](#32-حالة-الطلب)
33. [البنر: حقول اختيارية ومسحها](#33-البنر-حقول-اختيارية-ومسحها)

---

## 1) Page Builder الموحّد

> المرجع: `FRONTEND_DASHBOARD_PAGE_BUILDER.md`

### الفكرة

الأدمن يتعامل مع مفهوم واحد — **"صفحة فيها أقسام"**. لا يحتاج إنشاء Section ثم ربطه يدويًا.

### Endpoints

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `/api/admin/pages` | قائمة الصفحات (بحث + ترقيم) |
| POST | `/api/admin/pages` | إنشاء صفحة (`title` مطلوب، `slug` اختياري) |
| GET | `/api/admin/pages/{page}` | صفحة مع أقسامها |
| PUT/PATCH | `/api/admin/pages/{page}` | تعديل صفحة |
| DELETE | `/api/admin/pages/{page}` | حذف صفحة |
| GET | `/api/admin/pages/{id}/sliders` | كل الأقسام للاختيار |
| POST | `/api/admin/pages/{id}/sections` | إضافة قسم (بـ `section_id` أو إنشاء مباشر) |

### فلاتر قائمة الصفحات

| Param | القيم | الوصف |
|-------|-------|--------|
| `type` | `content` \| `category` | فلترة نوع الصفحة |
| `category_id` | رقم | صفحة فئة محددة |

كل صفحة ترجّع: `sections_count`, `is_category_page`, `category_id`.

### إضافة قسم لصفحة

**الطريقة الموصى بها:** اختيار قسم موجود:

```json
{ "section_id": 12 }
```

**أو إنشاء مباشر** بنداء واحد:

```json
{
  "type": "manual",
  "name": { "ar": "...", "en": "..." },
  "manual_model": "banner",
  "item_ids": [{ "item_id": 12, "order": 0 }],
  "layout": "slider",
  "variant": "horizontal",
  "background_color": "#F5F5F5"
}
```

### حقول القسم

| الحقل | القيم |
|-------|-------|
| `type` | `manual` \| `api` |
| `manual_model` | `banner` \| `product` \| `shop` \| `restaurant` \| `brand` \| `recipe` \| `basket` \| `category` \| `schedule` \| `schedule-basket` |
| `api_method` | `products` \| `categories` \| `shops` \| `restaurants` \| `brands` \| `recipes` \| `baskets` \| `schedule-basket` \| `schedules` \| `suggested_products` \| `suggested_shops` \| `suggested_baskets` |
| `layout` | `slider` \| `list` \| `grid` — طريقة عرض القسم |
| `variant` | `horizontal` \| `vertical` \| `square` — شكل الكارد |
| `content_type` | بديل أبسط يُحوَّل تلقائيًا |

> انظر **`FRONTEND_SECTION_LAYOUT_AND_CARD.md`** لترتيب خطوات الفورم.

### إعادة الترتيب والمعاينة

- `POST /api/admin/page-sections/pages/{page}/reorder`
- `GET /api/admin/page-sections/pages/{page}/preview`
- `PUT/DELETE /api/admin/page-sections/{id}`

### إظهار / إخفاء قسم (Eye toggle)

> **الدليل الكامل:** [`../page-sections/dashboard.md`](../page-sections/dashboard.md)

> **لا تحذف القسم** إذا تريد إخفاءه مؤقتًا — استخدم `is_active`.

كل قسم في `GET /api/admin/pages/{id}` يرجع حقل **`is_active`**:

| `is_active` | السلوك |
|-------------|--------|
| `true` | يظهر في الموقع/التطبيق |
| `false` | مخفي عن المستخدمين — يبقى في الصفحة ويمكن إعادة إظهاره |

**في شاشة تفاصيل الصفحة** (`/sections/pages/details/{id}`):

- أيقونة **عين** بجانب كل قسم
- `is_active: true` → عين مفتوحة (مرئي)
- `is_active: false` → عين مغلقة / خط (مخفي) + تمييز بصري خفيف (opacity أو badge "Hidden")

**طريقة 1 — Toggle API (موصى بها للأيقونة):**

```http
POST /api/admin/toggle-status
Content-Type: application/json

{
  "type": "page_section",
  "id": 12,
  "is_active": 0
}
```

**طريقة 2 — PATCH مباشر:**

```http
PATCH /api/admin/page-sections/12
Content-Type: application/json

{ "is_active": false }
```

> **Preview:** `GET .../preview` يعرض الأقسام **النشطة فقط** (كما يراها المستخدم). قائمة الأقسام في تفاصيل الصفحة تعرض **الكل** مع `is_active` لتمكين التوغل.

### البانرات

قسم البانرات **يدوي** ويختار من البانرات الموجودة (`GET /api/admin/banners`). الصورة **عرضية** (≈ 16:6). بانر واحد = إعلان ثابت، عدة بانرات = سلايدر.

> **إنشاء/تعديل البنر:** كل الحقول **اختيارية** ما عدا `image` عند الإنشاء. بدون `expires_at` = دائم — [`DASHBOARD_BANNER_REQUIRED_FIELDS.md`](./DASHBOARD_BANNER_REQUIRED_FIELDS.md).  
> **بطاقة البنر** في إضافة قسم وصفحة التفاصيل: صورة + اسم فقط، وبدون اسم تبقى الصورة — [`DASHBOARD_SECTION_BANNER_CARD.md`](./DASHBOARD_SECTION_BANNER_CARD.md).  
> على الشاشة الصغيرة القائمة تسكرول وتعرض كل البانرات، مو بس اللي بينشاف بالصف الأول — [`DASHBOARD_BANNER_LIST_SCROLL.md`](./DASHBOARD_BANNER_LIST_SCROLL.md).

---

## 2) إدارة الأقسام

> المرجع: `FRONTEND_DASHBOARD_SLIDERS.md`

> **ملاحظة:** لا يوجد كيان "سلايدر" مستقل. **القسم (Section)** هو الكيان الوحيد.
> - `layout: slider` = سلايدر أفقي
> - `variant` = شكل الكارد داخل القسم
> التفاصيل: `FRONTEND_SECTION_LAYOUT_AND_CARD.md`.

### الفكرة

القسم يُنشأ **مرة واحدة** (بدون اختيار صفحة)، ثم يُضاف لأي صفحة. يمكن أن يحتوي منتجات، متاجر، مزودين خدمة، سلل، بانرات، فئات، أو أي نوع محتوى.

### Endpoints

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `/api/admin/sections` | قائمة كل الأقسام |
| POST | `/api/admin/sections` | إنشاء قسم |
| GET | `/api/admin/sections/{id}` | تفاصيل + عناصر |
| PUT/PATCH | `/api/admin/sections/{id}` | تعديل |
| DELETE | `/api/admin/sections/{id}` | حذف |

### حقول الإنشاء

| الحقل | مطلوب | الوصف |
|-------|-------|--------|
| `name.ar` / `name.en` | نعم | اسم القسم |
| `content_type` | لا | بديل مُبسّط يُحوَّل تلقائيًا (`banner` \| `product` \| `shop` \| `restaurant` \| `brand` \| `category` \| `recipe` \| `basket` \| `schedule` \| `schedule-basket` \| `suggested_products` \| `suggested_shops` \| `suggested_baskets`) |
| `type` | **نعم** | `manual` أو `api` |
| `variant` | لا | `horizontal` (سلايدر) \| `vertical` (شبكة) \| `square` (مربعات). افتراضي `horizontal` |
| `background_color` | لا | لون خلفية |
| `background_card_color` | لا | لون خلفية الكارد |
| `item_ids` | إذا manual | العناصر المختارة |
| `filters` | إذا api | فلاتر |
| `page_id` | **ممنوع** | لا تُرسل |

### إضافة قسم لصفحة

من داخل الصفحة → `GET /api/admin/pages/{id}/sliders` → اختيار → `POST /api/admin/pages/{id}/sections` مع `section_id`.

### فلترة العناصر اليدوية

عند اختيار منتجات يدويًا في نموذج القسم، أضف فلاتر `search` + `category_id` + `brand_id` + `shop_id` فوق قائمة المنتجات (debounce 300ms على البحث). التفاصيل في `FRONTEND_DASHBOARD_SLIDERS.md` §5.

---

## 3) صفحات الفئات التلقائية

> المرجع: `FRONTEND_DASHBOARD_CATEGORY_PAGES.md`

### الفكرة

كل فئة (رئيسية أو فرعية بأي مستوى) = **صفحة تلقائية**. لا يُنشئها ولا يحذفها الأدمن يدويًا.

### دورة الحياة

```
إنشاء فئة → تُنشأ صفحتها + قسمان (فرعية + منتجات)
تعديل اسم الفئة → يتحدّث عنوان الصفحة
حذف الفئة → تُحذف الصفحة وأقسامها
```

### الوصول

- قائمة الصفحات: `GET /api/admin/pages?type=category`
- قائمة الفئات: كل فئة ترجّع `page_id`

### القيود

- `DELETE /pages/{id}` على صفحة فئة → **422 مرفوض**
- `PUT /pages/{id}` لتعديل title/slug → **422 مرفوض**
- الحذف فقط عبر `DELETE /categories/{id}`

### UX

- **لا** زر "إنشاء صفحة" أو "حذف صفحة" في تبويب صفحات الفئات
- استخدم `can_delete_page` و `can_edit_metadata` من API
- زر "بناء الصفحة" → Page Builder عادي

---

## 4) شريط التنقّل (Nav Menu)

> المرجع: `FRONTEND_DASHBOARD_NAV_MENU.md`

### الفكرة

الأدمن يتحكّم بالكامل بالشريط العلوي — إضافة/تعديل/حذف/ترتيب/تفعيل.

### Endpoints

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `/api/admin/nav-menu-items` | قائمة العناصر |
| GET | `/api/admin/nav-menu-items/route-keys` | شاشات ثابتة للدروب داون (`schedules` = جدولة) |
| POST | `/api/admin/nav-menu-items` | إنشاء عنصر (`multipart/form-data`) |
| GET | `/api/admin/nav-menu-items/{id}` | عنصر واحد |
| PUT/PATCH | `/api/admin/nav-menu-items/{id}` | تعديل |
| DELETE | `/api/admin/nav-menu-items/{id}` | حذف |
| POST | `/api/admin/nav-menu-items/reorder` | إعادة ترتيب |

### أنواع العناصر

| `type` | الحقل المطلوب |
|--------|---------------|
| `page` | `page_id` — صفحة من Page Builder |
| `category` | `category_id` — فئة |
| `brand` | `brand_id` — ماركة |
| `url` | `url` — رابط خارجي |
| `route` | `route_key` — شاشة ثابتة |

### قيم `route_key`

```
home | categories | brands | shops | baskets | schedules | points | help | subscriptions
```

**جدولة** = `type=route` + `route_key=schedules` — ليست صفحة Page Builder.  
القائمة من `GET /api/admin/nav-menu-items/route-keys`. التفاصيل: [`NAV_MENU_SCHEDULES.md`](NAV_MENU_SCHEDULES.md)

### حقول أخرى

- `title` → `{ ar, en }` مطلوبان
- `icon` → ملف صورة اختياري (≤ 2MB)
- `order` → ترتيب الظهور
- `is_active` → إظهار/إخفاء
- `open_in_new_tab` → للويب مع `type=url`

### إعادة الترتيب

```json
{ "ordered_ids": [3, 1, 5, 2, 4] }
```

---

## 5) إسناد المنتج لأي مستوى فئة

> المرجع: `FRONTEND_DASHBOARD_PRODUCT_CATEGORY_ANY_LEVEL.md`

### التغيير

| قبل | بعد |
|-----|-----|
| لازم تختار لآخر فئة فرعية (ورقة) | المستوى 1 يكفي. 2–6 اختيارية |
| `has_children === true` يمنع الحفظ | `has_children` يظهر select التالي **كخيار** فقط |

### قواعد الفورم

1. المستوى 1 (الرئيسية) **مطلوب**.
2. إذا للفئة أبناء → أظهر select التالي كـ **اختياري**.
3. `category_id` المرسل = آخر فئة مختارة (أعمق select مش فاضي).
4. تغيير مستوى أعلى → امسح المستويات الأعمق.
5. **لا تمنع Submit** إذا `has_children === true`.

### الصفات

تُجلب عند اختيار المستوى 1 فقط:

```http
GET /api/admin/category-attributes?category_id={rootCategoryId}
```

النزول للمستويات 2–6 **لا** يعيد طلب الصفات.

---

## 6) وراثة صفات الفئة من الرئيسية

> المرجع: `FRONTEND_DASHBOARD_CATEGORY_ATTRIBUTE_INHERITANCE.md`

### التغيير

| قبل | بعد |
|-----|-----|
| صفة مربوطة بأي فئة | صفة مربوطة **بالجذر فقط** |
| فورم المنتج ينتظر الورقة | بمجرد اختيار المستوى 1 تظهر الصفات |

### شاشة إدارة الصفات

- `category_id` لازم فئة رئيسية → إذا فرعية → **422**
- Select الفئة: اعرض الرئيسية فقط (`is_root === true`)
- `GET /api/admin/category-attributes?category_id={id}` — أي id بالشجرة يرجع صفات الجذر
- **تعديل اسم قيمة:** أرسلوا `values[i][id]` — انظر §23 و [`DASHBOARD_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./DASHBOARD_CATEGORY_ATTRIBUTE_VALUE_IDS.md)

### فورم المنتج

- طلب الصفات عند اختيار المستوى 1
- عرض الصفات والقيم فورًا
- عدم إعادة الطلب عند النزول للمستويات 2–6
- إعادة الطلب فقط إذا تغيّرت الرئيسية

---

## 7) سعر وكمية المتغيّر

> المرجع: `FRONTEND_DASHBOARD_VARIANT_PRICE_QUANTITY.md`

### التغيير الكاسر

**السعر والكمية صاروا على المتغيّر (`product_variants`)**، مو على ربط المحل (`shop_product_variants`).

| قبل | بعد |
|-----|-----|
| `product.variants[].shops[].price` | `product.variants[].price` |
| `product.variants[].shops[].quantity` | `product.variants[].quantity` |

### تاب المتغيّرات

- Input سعر على كل variant
- Input كمية (integer) على كل variant

### توفر المتجر

**لا قسم فروع.** الباك يربط كل المتغيّرات بمتجر البائع.

- `shop_id` (اختياري على مستوى المنتج) عند `sale_channel=shop`
- `shop_variants` **غير مطلوب** — للتوافق فقط (`cost_price`)
- `shop_variants.*.price` / `quantity` → **محذوف — لا ترسلوهما**

---

## 8) حفظ المتغيّرات وربط المتجر

> المرجع: `FRONTEND_DASHBOARD_PRODUCT_VARIANTS_SAVE.md`

### التغيير

`variants` و `shop_variants` **صاروا يُحفظون فعليًا** (كانوا يُتجاهلون).

### قواعد مهمة

0. **إنشاء منتج (`/products/create`):** المتغيّrات **state محلي** — **لا** تطلب `product.id` ولا toast «احفظ المنتج أولاً». عند «إنشاء المنتج» → `POST /products` + `variants[]` **دفعة واحدة**. التفاصيل: [`product-variants-dashboard.md` §2](product-variants-dashboard.md#2-إنشاء-منتج-جديد--حفظ-محلي-بدون-product-id)

1. **`variants` بديل كامل (replace):**
   - صف فيه `id` → تحديث
   - صف بدون `id` → إنشاء جديد
   - متغيّر موجود بالـ DB وما أُرسل → **soft delete**

2. **`shop_variants` اختياري:**
   - إذا ما أرسلتها → الباك يربط بمتجر البائع (متجر واحد)
   - إذا أرسلتها → يُستخدم أول `shop_id` فقط (باقي الفروع تُتجاهل)
   - `shop_variants: []` → تُحذف الروابط ثم يُعاد الربط بمتجر البائع

3. **لا ترسل `variants` إذا الفورم ما فيه تاب متغيّرات**

4. **احفظ `variant.id`** من GET عند التحميل وأرجعه عند الحفظ

5. **`existing_images_ids`** مطلوب دائمًا — أي صورة غير مذكورة تُحذف

### Payload

```text
variants[0][id]=15
variants[0][sku]=JEANS-RED
variants[0][price]=25
variants[0][quantity]=12
variants[0][is_active]=1
variants[0][attributes_values_ids][0]=6

shop_variants[0][shop_id]=1
shop_variants[0][variant_index]=0
shop_variants[0][cost_price]=10000
```

- `variant_index` = ترتيب المتغيّر (0، 1، …) — **مو `variant_id`**
- لا ترسل `price`/`quantity` داخل `shop_variants`

### المنتج بدون متغيّرات

يُنشأ **متغيّر افتراضي** تلقائيًا من بيانات المنتج.

> قناة البيع (موقع / متجر) صارت خيار صريح — انظر **§14**.

---

## 9) حذف المتغيّرات مع تأكيد

> المرجع: `FRONTEND_DASHBOARD_VARIANT_DELETE_CONFIRM.md`

### التغيير

الحذف **مسموح دائمًا** (كان ممنوعًا إذا مرتبط). الحذف **soft delete**.

### التدفق

1. `DELETE /api/admin/product-variants/{id}` بدون `confirm`
   - إذا مرتبط → **409** مع `warnings` و `counts`
   - إذا مو مرتبط → **200** حذف مباشر
2. عرض نافذة التأكيد مع `warnings[].message`
3. إعادة الطلب مع `?confirm=true`

### Endpoints

| الغرض | Method | Endpoint |
|-------|--------|----------|
| معاينة أثر الحذف | GET | `/api/admin/product-variants/{id}/delete-impact` |
| حذف متغيّر | DELETE | `/api/admin/product-variants/{id}?confirm=true` |
| معاينة أثر حذف ربط متجر | GET | `/api/admin/shop-product-variants/{id}/delete-impact` |
| حذف ربط متجر | DELETE | `/api/admin/shop-product-variants/{id}?confirm=true` |

### ضمانات

- سجلات الطلبات **لا تُحذف أبدًا**
- صور المتغيّر تبقى محفوظة
- تحديث المخزون للطلبات الجارية يستمر

---

## 10) حذف صفات الفئة مع تأكيد

> المرجع: `FRONTEND_DASHBOARD_CATEGORY_ATTRIBUTE_DELETE_CONFIRM.md`

### التغيير

الحذف **مسموح دائمًا** (كان يرجع 422 ويتوقف).

### التدفق

1. `GET /api/admin/category-attributes/{id}/delete-impact` → ملخص الأثر
2. `GET /api/admin/category-attributes/{id}/linked-items?page=1&per_page=10` → تبويب العناصر المرتبطة
3. `DELETE /api/admin/category-attributes/{id}?confirm=true` → تنفيذ

### ماذا يحدث عند الحذف

- قيم الخاصية (أحمر، أزرق...) **تُحذف**
- المتغيّرات **تبقى** وتُنظّف منها قيم هذه الخاصية
- المنتجات **تبقى**
- الطلبات **لا تتأثر**

---

## 11) أسعار المنتج (USD/SYP) + حذف الفئات بتأكيد

### فورم المنتج

**سطر 1:** سعر `$` · سعر `ل.س` · نوع الخصم · قيمة الخصم · سعر بعد الخصم (**عرض فقط — يُحسب حيّاً**) · التكلفة  
**سطر 2:** الكمية · الوحدة

- لا تعرض `price` و `price_after_discount` كحقلين قابلين للتعديل معاً
- **لا ترسل** `price_after_discount` ولا `*_currencies`

#### حساب سعر بعد الخصم في الواجهة

```js
function priceAfterDiscount(price, discountType, discount) {
  const p = Number(price) || 0;
  const d = Number(discount) || 0;
  if (!p || !discountType || discountType === 'none' || d <= 0) return p;
  if (discountType === 'percentage') return Math.round((p - p * (d / 100)) * 100) / 100;
  if (discountType === 'fixed') return Math.max(0, Math.round((p - d) * 100) / 100);
  return p;
}
```

| `discount_type` | الحساب |
|-----------------|--------|
| `none` | = السعر |
| `percentage` | `price - price * (discount/100)` |
| `fixed` | `max(0, price - discount)` — المبلغ بالدولار |

#### مزامنة USD ↔ SYP

```js
// GET /api/admin/currencies → SYP.exchange_rate
const sypRate = currencies.find(c => c.code === 'SYP')?.exchange_rate ?? 1;
// كتب دولار → عبّي ليرة؛ كتب ليرة → عبّي دولار
// نفس المنطق لـ cost_price / cost_price_syp
```

إذا أُرسل `price` و `price_syp` معاً → الباك يعتمد **الدولار**.

```js
{
  price: usd || undefined,
  price_syp: !usd && syp ? syp : undefined,
  cost_price: costUsd || undefined,
  cost_price_syp: !costUsd && costSyp ? costSyp : undefined,
  discount_type, discount, quantity, unit_id,
}
```

- **`media`** و **`country_id`** اختياريان — لا تضع `required`

### حذف الفئات

| الغرض | Endpoint |
|-------|----------|
| ملخص الأثر | `GET /api/admin/categories/{id}/delete-impact` |
| العناصر المرتبطة | `GET /api/admin/categories/{id}/linked-items?page=1&per_page=10` |
| تنفيذ | `DELETE /api/admin/categories/{id}?confirm=true` |

1. حذف → `delete-impact`
2. نافذة: تبويب تنبيه + تبويب عناصر مرتبطة
3. موافقة → `DELETE ?confirm=true`
4. **`409` + `requires_confirmation` = تأكيد** — مو توست أحمر

| مفتاح | عند التأكيد |
|-------|-------------|
| `child_categories` | حذف الفرعية |
| `products` | حذف ناعم + فك ارتباط (الطلبات تبقى) |
| `baskets` | تُحذف |
| `pages` | تُحذف صفحة الفئة وأقسامها |
| `recipe_links` | فك ارتباط فقط |

---

## 12) بلد المنشأ — Select من كل دول العالم

- بلد المنشأ **قائمة منسدلة** (`country_id`) وليست حقل نص
- الخيارات: `GET /api/admin/countries` (كل الدول، بدون pagination)
- أرسل `country_id` فقط؛ املأ من `country_id` / `origin_country.id`
- **لا تستخدم** `country.ar` / `country.en`

```bash
php artisan db:seed --class=CountrySeeder
```

---

## 13) بلدان المبيع — بدون رفع أيقونة + افتراضي سوريا

**المشكلة:** فورم بلد المبيع يعرض رفع ملف بعنوان خاطئ.

| الحقل | في الفورم؟ |
|-------|------------|
| `name.ar` / `name.en` | نعم |
| `is_active` | نعم |
| `icon` | **لا — احذفوه** (من السيدر، عرض فقط) |

- بدون `sale_country_id` عند إنشاء منتج → الباك يضع **سوريا**
- في select المنتج: افتراضي **سوريا**

```bash
php artisan db:seed --class=SaleCountrySeeder
```

---

## 14) قناة البيع — للموقع أو ربط بمتجر

> **احذفوا** صف الراديو «مستودعاتي / خارجي» — مو موجود بالباك ويتداخل مع `sale_channel`.

| واجهة | `sale_channel` | السلوك |
|-------|----------------|--------|
| **للموقع** (افتراضي) | `platform` | لا متجر/بائع؛ ربط تلقائي بمتجر المنصة |
| **ربط بمتجر** | `shop` | اختاروا المتجر (`shop_id`) أو البائع — **بدون** قائمة فروع |

| القناة | وقت التسليم |
|--------|-------------|
| للموقع | نص ثابت للمنصة — لا حقل إدخال |
| ربط بمتجر | أظهروا `delivery_time` |

### قواعد

- للموقع: `sale_channel=platform` و**لا ترسل** `shop_variants` ولا `vendor_id`
- لمتجر: `sale_channel=shop` + `shop_id` (أو بائع له متجر) وإلا **422** — **لا ترسل** `vendor_id` إلا إذا هو `vendors.id`
- توست «حقل vendor id غير موجود»: لا ترسلوا `0` / `""` / id مستخدم بائع — [`DASHBOARD_PRODUCT_VENDOR_ID.md`](DASHBOARD_PRODUCT_VENDOR_ID.md)
- GET يرجع `sale_channel` للفورم والقائمة (badge: للموقع / متجر)
- تحويل لموقع: أرسل `sale_channel=platform` فقط
- تعديل اسم فقط: **لا ترسل** `sale_channel` ولا `shop_variants`
- شرط التشغيل: متجر لبائع المنصة
- **لا** تحذير «غير مرتبط بفرع» — الفروع ملغاة

```text
# موقع
sale_channel=platform
price=25
quantity=10

# متجر
sale_channel=shop
shop_id=5
```

---

## 15) الطلب السريع — إعدادات + صفحات الظهور

> التفصيل الكامل (تحويل الطلبات + convert): [`../custom-orders/dashboard.md`](../custom-orders/dashboard.md)

### الفكرة

المحتوى والشكل **مركزي من Settings** (ليس قسم Page Builder).  
الأدمن يختار:
1. **إظهار/إخفاء زر الهيدر** (`quick_order_header_enabled`) — مستقل
2. **إظهار/إخفاء القسم** (`quick_order_enabled`) — مستقل
3. **أي صفحات يظهر عليها القسم** (`quick_order_page_ids`)
4. خلفية، ألوان، شكل الكروت، نصوص AR/EN، خطوات

> **تحديث 25 أيلول 2026:** الزر والقسم مفصولان — [`QUICK_ORDER_HEADER_VS_SECTION.md`](./QUICK_ORDER_HEADER_VS_SECTION.md)

### Endpoints

**Base:** `/api/admin/settings`  
**صلاحيات:** `setting.view` / `setting.update`  
قائمة الصفحات للـ multi-select: `GET /api/admin/pages`

| Key | Type | وصف |
|-----|------|-----|
| `quick_order_header_enabled` | boolean | **إظهار/إخفاء زر الهيدر فقط** |
| `quick_order_enabled` | boolean | **إظهار/إخفاء القسم فقط** |
| `quick_order_page_ids` | json | مصفوفة `pages.id` — الصفحات التي يظهر عليها القسم. الافتراضي = صفحة `home` فقط. `[]` = لا يظهر على أي صفحة |
| `quick_order_background_image` | file | صورة خلفية (`multipart` حقل `value`) |
| `quick_order_background_color` | string | لون احتياطي (مثال `#FFE8D6`) |
| `quick_order_card_background_color` | string | خلفية كروت الخطوات |
| `quick_order_card_variant` | string | `horizontal` \| `vertical` \| `square` |
| `quick_order_badge` / `title` / `subtitle` / `cta` | json | `{ "ar": "...", "en": "..." }` |
| `quick_order_steps` | json | مصفوفة خطوات (حتى 6) |

```http
PUT /api/admin/settings/quick_order_header_enabled
{ "value": false }
```

```http
PUT /api/admin/settings/quick_order_enabled
{ "value": false }
```

```http
PUT /api/admin/settings/quick_order_page_ids
{ "value": [1, 5, 12] }
```

```http
PUT /api/admin/settings/quick_order_background_image
Content-Type: multipart/form-data
value: <image file>
```

### ما يقرأه التطبيق/الويب

```http
GET /api/user/settings → data.quick_order
```

| حقل | معنى |
|-----|------|
| `show_header` | زر الهيدر |
| `show_section` | القسم مفعّل |
| `is_enabled` | = `show_section` (توافق خلفي) |
| `page_ids` | IDs الصفحات المعتمدة |
| `page_slugs` | نفس الصفحات كـ slug |

**قاعدة العرض:** الزر ← `show_header` · القسم ← `show_section` و (الصفحة ∈ `page_slugs` أو ∈ `page_ids`).

### UI مقترح

تبويب «طلب سريع» ضمن الإعدادات:
- سويتش **إظهار زر الهيدر**
- سويتش **إظهار القسم**
- **multi-select صفحات** من `GET /api/admin/pages` (للقسم فقط)
- صورة خلفية + ألوان
- شكل الكارد
- نصوص AR/EN
- محرر الخطوات

**مهم:** استلام الطلبات وتحويلها (`convert` / `cancel`) مسار منفصل تحت `/api/admin/custom-order-requests` — انظر [`../custom-orders/dashboard.md`](../custom-orders/dashboard.md).

---

## 16) استيراد منتجات من Excel

استيراد جماعي من قالب SPBS (أعمدة عربية ثابتة). الكود يقرأ **هذه الأعمدة فقط** ويتجاهل أي عمود زيادة.

### Endpoints

| Method | Path | صلاحية | وصف |
|--------|------|--------|-----|
| `GET` | `/api/admin/products/import-template` | `product.view` | تنزيل قالب `.xlsx` |
| `POST` | `/api/admin/products/import` | `product.create` | رفع الملف والاستيراد |

```http
POST /api/admin/products/import
Content-Type: multipart/form-data
file: <products.xlsx>
```

### أعمدة القالب (Mapping)

| عمود الإكسل | حقل النظام |
|-------------|------------|
| كود المنتج | `sku` |
| الفئة الرئيسية | اسم فئة → `category_id` (مطابقة دقيقة ar/en) |
| اسم المنتج عربي | `name.ar` |
| اسم المنتج انكليزي | `name.en` |
| الوصف المختصر | `description.ar` |
| العلامة التجارية | اسم براند → `brand_id` (اختياري) |
| الباركود | `barcode` |
| السعر دولار | `price` (USD) |
| السعر سوري | يتحوّل لـ USD إذا ما في دولار |
| نوع الخصم | `discount_type` (`none` / `percentage` / `fixed` أو بدون / نسبة / ثابت) |
| الخصم | `discount` |
| الكمية | `quantity` |
| تاريخ انتهاء الصلاحية | `expiry_date` |

### Upsert

1. إن وُجد **باركود** مطابق → تحديث  
2. وإلا إن وُجد **كود منتج (sku)** مطابق → تحديث  
3. وإلا → إضافة جديدة  

عند التحديث: الخلايا الفارغة **لا تمسح** القيم الموجودة.  
المنتجات بسيطة (`sale_channel=platform`)؛ الباك ينشئ/يزامن الـ variant الافتراضي (سعر + كمية + باركود + sku).

### تحقق عند الإضافة

- اسم عربي + إنكليزي مطلوبان  
- الفئة مطلوبة وموجودة (اسم فريد)  
- سعر دولار أو سوري (≥ 0)  
- كمية مطلوبة (≥ 0 عدد صحيح)  

الصفوف الخاطئة تُتخطّى؛ الصحيحة تُستورد.

### استجابة

```json
{
  "status": true,
  "message": "تم استيراد المنتجات",
  "data": {
    "created": 10,
    "updated": 3,
    "failed": [
      { "row": 5, "errors": ["الفئة الرئيسية غير موجودة: ألبان"] }
    ]
  }
}
```

### UI مطلوب

- زر **تنزيل القالب** → `GET import-template`
- زر **استيراد من Excel** → رفع ملف → عرض ملخص: أُضيف / حُدّث / فشل (رقم الصف + السبب)
- لا Mapping يدوي من الواجهة — الأعمدة ثابتة من القالب

---

## Checklist شامل

### صفحات وأقسام
- [ ] Page Builder: صفحات + أقسام + reorder + preview
- [ ] أقسام مستقلة (بدون `page_id` عند الإنشاء)
- [ ] صفحات الفئات تلقائية — لا إنشاء/حذف يدوي

### Nav
- [ ] CRUD + reorder لـ nav-menu-items
- [ ] دروب داون الشاشات من `GET /nav-menu-items/route-keys` — جدولة = `schedules` (مو صفحة)
- [ ] إنشاء منتج: **لا** `vendor_id`

### منتج / متغيّرات / فئات
- [ ] منتج لأي مستوى فئة؛ صفات من الجذر
- [ ] سعر وكمية على الـ variant؛ لا price/qty داخل shop_variants
- [ ] variants = replace؛ احفظ variant.id — **لا** UI فروع
- [ ] حذف متغيّر/صفة/فئة مع confirm + 409
- [ ] أسعار: بعد الخصم حيّ؛ USD↔SYP؛ لا ترسل currencies
- [ ] بلد المنشأ Select؛ media اختياري
- [ ] بلد مبيع بدون icon؛ افتراضي سوريا
- [ ] sale_channel فقط (موقع/متجر) — احذف مستودعاتي/خارجي
- [ ] استيراد Excel: تنزيل قالب + رفع + ملخص created/updated/failed

### اليوم (25–27 أيلول)
- [ ] إيقاف الفئة `is_active: false` فقط — لا تعطّلوا منتجاتها — [§27](#27-إخفاء-الفئة-يخفي-منتجاتها)
- [ ] تغيير صورة الأيقونة في `image`، والمعاينة من الرد مع `?v=` — [§28](#28-تغيير-صورة-الأيقونة)
- [ ] العروض: شرط + مكافأة (خصم / توصيل مجاني / هدية) — [§29](#29-عروض-شرط--مكافأة)
- [ ] جداول سلال الزبائن: `start_date` + `delivery_time` للعرض فقط — [§30](#30-تاريخ-ووقت-جدولة-سلة-الزبون)
- [ ] الطلب: `PATCH .../scheduled-delivery` بصيغة `Y-m-d H:i` — [§31](#31-يوم-ووقت-التوصيل-على-الطلب)
- [ ] الحالة `out_delivery` مو `out_for_delivery` — [§32](#32-حالة-الطلب)
- [ ] مسح حقول البنر يرسل فاضي وينحفظ `null` — [§33](#33-البنر-حقول-اختيارية-ومسحها)

### طلب سريع
- [ ] إعدادات: سويتش `quick_order_header_enabled` + سويتش `quick_order_enabled` + multi-select `quick_order_page_ids` + خلفية + كروت + نصوص
- [ ] قائمة الصفحات من `GET /api/admin/pages`
- [ ] حفظ `PUT /api/admin/settings/quick_order_header_enabled` و `quick_order_enabled` بشكل منفصل
- [ ] حفظ `PUT /api/admin/settings/quick_order_page_ids` بمصفوفة IDs
- [ ] شاشة قائمة/تفاصيل custom-order-requests + convert/cancel

---

## أوامر التشغيل (آمنة — ما تمسح ولا تعيد كتابة داتا قديمة)

```bash
# 1) هيكل فقط: أعمدة + إعدادات quick_order الناقصة + sale_channel
php artisan migrate

# 2) صلاحيات ناقصة فقط (CustomOrderRequest …) — firstOrCreate
php artisan db:seed --class=RolePermissionSeeder

# 3) دول منشأ ناقصة فقط — ما يغيّر is_active/أسماء موجودة
php artisan db:seed --class=CountrySeeder

# 4) بلدان مبيع ناقصة + يعبّي icon إن فاضي فقط
php artisan db:seed --class=SaleCountrySeeder

# 5) بائع المنصة + متجره — مطلوب لإنشاء منتج «للموقع»
php artisan db:seed --class=PlatformVendorSeeder
```

**لا تشغّلوا:**

```bash
php artisan migrate:fresh
php artisan migrate:refresh
php artisan db:seed          # كامل — خطر
php artisan db:seed --class=SettingSeeder   # غير لازم؛ الـ migrate يضيف quick_order
```

Migrations آب 2026:

- `2026_08_26_103600_add_quick_order_settings` — مفاتيح ناقصة فقط
- `2026_08_26_131500_add_quick_order_page_ids_setting` — صفحات الظهور (`quick_order_page_ids`)
- `2026_08_26_104500_make_product_category_id_nullable_for_category_delete`
- `2026_08_26_111600_add_sale_channel_to_products_table` — عمود جديد + تعبئة من `vendor_id`

---

## 17) متغيّرات المنتج — Single select + كارد حقول

> **الدليل الكامل:** [`product-variants-dashboard.md`](product-variants-dashboard.md)

### ملخص سريع

| البند | التفاصيل |
|-------|----------|
| **نموذج الإضافة** | **dropdown واحد لكل صفة** (لون · مقاس · تصميم) — **single فقط** — بدون حقول سعر |
| **كل «إضافة»** | **متغيّر واحد** (تركيبة واحدة) → كارد واحد بالقائمة |
| **كارد المتغيّر** | SKU + ($ · ل.س · خصم · بعد الخصم · كمية · باركود) — **لا** تفرد كروت بالمقاس |
| **القائمة** | كارد (أو tab) لكل متغيّر — عدة مقاسات = **عدة إضافات** |
| **اسم المتغيّر** | **محذوف** — الصفات + SKU |
| صفات الفئة | `GET /api/admin/category-attributes?category_id={id}` عند المستوى 1 |
| **الباك** | **لا تعديل** — `variants[]` جاهز في `POST` و `PUT` |
| **إنشاء منتج** | متغيّrات **محلياً** — حفظ مع `POST /products` (§2 في الدليل) |
| السعر | **`variants[].price` ($)** + **`variants[].price_syp` (ل.س)** — مزامنة تلقائية |
| سعر بعد الخصم | **عرض فقط** — يُحسب حيّاً في الواجهة |
| الكمية | **`variants[].quantity`** — **اختياري** (`nullable`) — مو required. **لا** تطلبوا `quantity` المنتج إذا الحقل مخفي — انظر §18 |
| الباركود | **`variants[].barcode`** لكل متغيّر |
| الخصم | **`variants[].discount` + `discount_type`** لكل متغيّر |
| موعد التسليم | **`delivery_time`** على المنتج — ليس per variant |
| SKU | توليد من الواجهة (الباك لا يولّد تلقائيًا) |

### فلاتر القوائم (`is_active`)

عند جلب الألوان/الوحدات/الضمانات/الفئات... استخدم:

```http
GET /api/admin/colors?page=1&per_page=500&is_active=1
GET /api/admin/warranties?page=1&per_page=500&is_active=1
```

أو `is_active=true` — **الباك يقبل الاثنين** بعد التحديث.

> إذا ظهرت «قيمة التفعيل غير صحيحة» — حدّث الباك أو أرسل `1` بدل `true`.

### Migration

```bash
php artisan migrate   # 2026_08_30_120000_add_discount_to_product_variants_table
```

---

## 18) باگ: كمية المنتج إلزامية والمخفية

> **تحديث 8 أيلول:** الكمية **رجعت** لتاب معلومات المنتج مع السعر والخصم — [`DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./DASHBOARD_PRODUCT_PRICING_FIELDS.md)  
> توست «موجبة» يبقى باگ: الحقول `.optional()` — [`DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md`](./DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md)

توست «يجب أن تكون الكمية موجبة» مع كمية المتغيّر ظاهرة (`5`) = **باگ**. Zod/validators يطلبون `quantity` إلزامي.

- الباك: `quantity` و `variants.*.quantity` كلاهما `nullable|min:0`
- الحل: كل الحقول `.optional()` — فارغ = لا ترسلوا
- الكمية موجودة في تاب المعلومات **و** على كارد المتغيّر

---

## 19) الضمان دروب داون + لا متغيّر افتراضي

> **أرسلوا هذا الملف:** [`DASHBOARD_PRODUCT_WARRANTY.md`](./DASHBOARD_PRODUCT_WARRANTY.md) — **5 أيلول 2026**

فيه: سايدبار `/products/warranties` · صلاحيات `warranty.*` (مفرد) · CRUD · دروب داون `warranty_id` · لا `warranty_period` · لا كارد متغيّر تلقائي.

---

## 20) السلل المجدولة — كتالوج الجدولات

> **أرسلوا هذا الملف:** [`DASHBOARD_CUSTOM_BASKET.md`](./DASHBOARD_CUSTOM_BASKET.md) — **5 أيلول 2026 مساءً**

فيه: فئات الجدولة · قسم الصفحة (`schedule` vs `schedule-basket`) · سلة جاهزة · **كيف تختارون الأصناف** (`shop-product-variants` + فلاتر).

الجدولة مصدر واحد: **كتالوج `schedules`**. سلة الأدمن الجاهزة تختار `schedule_id`. كارد «العنصر 1» يحمّل `GET /api/admin/shop-product-variants?category_id=&brand_id=&price_min=&search=` — القيمة `id` = `shop_product_variant_id`. لا تستخدموا صفات المنتج على هالكارد.

**تعديل الجدول (مهم):** أرسلوا **POST** + `_method=PUT` (مو PUT خام). **لا** تضعوا `Content-Type: multipart/form-data` يدوياً. خصم فاضي = احذفوا `discount_type` و`discount_value` (لا `''`). الرد لازم فيه `image` · `images` · `top_badges` · `bottom_badges` و`updated_at` جديد — إذا `updated_at === created_at` فالصورة ما انحفظت. لا ترسلوا `item_type` في قسم الصفحة.

### كتالوج الجدولات

| Method | Endpoint |
|--------|----------|
| GET/POST | `/api/admin/schedules` |
| POST + `_method=PUT` / DELETE | `/api/admin/schedules/{id}` |

حقول الإنشاء: `name[ar|en]`، `description[ar|en]`، `interval_days`، `discount_type` (`percentage` \| `fixed` — احذفوا الحقل إذا فاضي)، `discount_value`، `is_active`، `image`، `images[]`، `badges[][id]` + `badges[][position]` = `top` \| `bottom`.

خصم الجدولة ينطبق على **كل** سلة المستخدم بهالفئة.

كرت الويب/التطبيق: `GET /api/user/schedules` — صورة + وصف + بادجز.

### قسم فئات الجدولة على الصفحة (`/sections/pages/{id}/sections/create`)

أضيفوا نوع محتوى **فئات الجدولة الزمنية** — هذا **ليس** `schedule-basket` (سلل أدمن جاهزة).

| الحقل | القيمة |
|--------|--------|
| التسمية في الفورم | فئات الجدولة الزمنية |
| `content_type` / `manual_model` | `schedule` |
| `api_method` | `schedules` |
| `display_type_id` | `11` (الباك يعيّنه تلقائيًا) |
| `variant` الموصى به | `vertical` |
| عناصر يدوية | `GET /api/admin/schedules` |

`GET /api/admin/sections/item-types` صار يرجع مفتاح `schedule` مع `url: admin/schedules`.

إنشاء مباشر على الصفحة:

```json
{
  "type": "api",
  "content_type": "schedule",
  "name": { "ar": "فئات الجدولة الزمنية", "en": "Schedule categories" },
  "layout": "slider",
  "variant": "vertical"
}
```

أو اختيار فئات محددة:

```json
{
  "type": "manual",
  "manual_model": "schedule",
  "name": { "ar": "فئات الجدولة الزمنية", "en": "Schedule categories" },
  "item_ids": [{ "item_id": 2, "order": 0 }],
  "layout": "slider",
  "variant": "vertical"
}
```

### إنشاء سلة مجدولة

```http
POST /api/admin/scheduled-baskets
```

- **مطلوب:** `schedule_id` من الكتالوج (جدولة مفعّلة).
- **لا** ترسلوا مصفوفة `schedules[].number_of_days` — الأيام والخصم من الجدولة.
- سلة واحدة = جدولة واحدة.
- الأصناف: `GET /api/admin/shop-product-variants?category_id=` ثم `items[][shop_product_variant_id]` = `id`. التفاصيل في الدليل الكامل §4.

استثناء خصم سلة واحدة (عيد 40% بدل 20% الأسبوعي): أرسلوا `discount` + `discount_type`. إذا ما أرسلتوهم، السلة ترث خصم الجدولة. لمسح الاستثناء عند التعديل: أرسلوا `discount` فاضي/`null`.

الاستجابة فيها `schedule_id`، `schedule` (من الكتالوج)، `has_custom_discount`، و`discount` / `discount_type` = الخصم الفعلي المعروض.

فلتر القائمة: `GET /api/admin/scheduled-baskets?schedule_id={id}`.

---

## 21) حقول السعر · الخصم · الكمية · باركود · SKU

> **أرسلوا هذا الملف:** [`DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./DASHBOARD_PRODUCT_PRICING_FIELDS.md)  
> **خصم ثابت (كسور و>100):** [`DASHBOARD_FIXED_DISCOUNT.md`](./DASHBOARD_FIXED_DISCOUNT.md) — **20 أيلول 2026**

نفس البلوك في تاب **معلومات المنتج** (دائماً) وعلى **كارد المتغيّر** (إذا أُضيفت متغيّرات).

| الواجهة | API المنتج | API المتغيّر |
|---------|------------|--------------|
| سعر المتغير (دولار) | `price` | `variants[].price` |
| سعر المتغيّر (ليرة سورية) | `price_syp` | `variants[].price_syp` |
| نوع الخصم — لا يوجد خصم | `discount_type` = `none` | نفس |
| قيمة الخصم | `discount` — نسبة `0…100` · ثابت كسور و>100 | `variants[].discount` |
| السعر بعد الخصم | — عرض فقط | — عرض فقط |
| الكمية المتوفرة | `quantity` | `variants[].quantity` |
| الباركود | `barcode` | `variants[].barcode` |
| رمز التخزين التعريفي للمتغير | `sku` | `variants[].sku` |

بلا متغيّرات: الكارد والتفاصيل من معلومات المنتج. مع متغيّرات: الكارد من المنتج · صفحة التفاصيل تتبدّل عند اختيار المتغيّر.

المتجر: [`WEB_PRODUCT_PRICING_FIELDS.md`](./WEB_PRODUCT_PRICING_FIELDS.md) · [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./FLUTTER_PRODUCT_PRICING_FIELDS.md)

---

## 22) ناف بار — جدولة + إنشاء منتج

> **8 أيلول مساءً** — [`NAV_MENU_SCHEDULES.md`](./NAV_MENU_SCHEDULES.md) · [`DASHBOARD_PRODUCT_VENDOR_ID.md`](./DASHBOARD_PRODUCT_VENDOR_ID.md)

**جدولة في الناف:** ليست صفحة Page Builder. نوع `route` + `route_key=schedules`. القائمة: `GET /api/admin/nav-menu-items/route-keys`. لا سيدر تلقائي.

**إنشاء منتج:** لا ترسلوا `vendor_id`. للموقع `sale_channel=platform`. للمتجر `shop_id`. إذا «بائع المنصة غير موجود»:

```bash
php artisan db:seed --class=PlatformVendorSeeder
```

---

## 23) تعديل قيم الصفات بالـ ID

> **20 أيلول 2026** — الدليل: [`DASHBOARD_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./DASHBOARD_CATEGORY_ATTRIBUTE_VALUE_IDS.md)

المنتج مربوط بـ `attributes_values_ids` (IDs)، مو بالاسم. إعادة تسمية «صغير → XS» لازم تبقي نفس `values[].id`.

```text
PUT /api/admin/category-attributes/{id}
values[0][id]=31
values[0][name][ar]=XS
values[0][name][en]=XS
```

قيمة جديدة = بدون `id`. حذف قيمة = لا ترسلوا صفها (تتشال من المتغيّرات، المنتج يبقى).  
إذا ما انرسل `id`، القيم كانت تنمسح وتُنشأ من جديد والمنتج يفقد القياس.

---

## 24) كل المتغيّرات تُحفظ وتُربط بالمتجر

> **20 أيلول 2026** — الدليل: [`DASHBOARD_PRODUCT_ALL_VARIANTS.md`](./DASHBOARD_PRODUCT_ALL_VARIANTS.md)

كل صف = تركيبة (أزرق S ≠ أسود L). أرسلوا `attributes_values_ids` و `is_active=1` **لكل** صف.  
الباك يربط **كل** المتغيّرات بمتجر البائع — لا ترسلوا قائمة فروع.

---

## 25) إلغاء فروع المتاجر

> **20 أيلول 2026** — الدليل: [`DASHBOARD_NO_SHOP_BRANCHES.md`](./DASHBOARD_NO_SHOP_BRANCHES.md)

بائع واحد = متجر واحد. احذفوا قسم الفروع من فورم المنتج والمتجر.  
`sale_channel=shop` + `shop_id` (أو البائع). إنشاء متجر ثانٍ لنفس البائع → **422**.

---

## 26) إضافات المنتجات — اسم + سعر

> **21 أيلول 2026** — الدليل: [`DASHBOARD_PRODUCT_EXTRA_DETAILS.md`](./DASHBOARD_PRODUCT_EXTRA_DETAILS.md) · ويب: [`WEB_PRODUCT_EXTRA_DETAILS.md`](./WEB_PRODUCT_EXTRA_DETAILS.md)

فورم `/categories/extra-details` صار: **اسم الإضافة** (`detail_key`) + **سعر** (`price`).  
`detail_value` وصف اختياري فقط — مو بديل عن السعر. لا تستخدموا placeholder «قطن».

---

## 27) إخفاء الفئة يخفي منتجاتها

> **27 أيلول 2026** — [`DASHBOARD_HIDDEN_CATEGORY_PRODUCTS.md`](./DASHBOARD_HIDDEN_CATEGORY_PRODUCTS.md)

مفتاح `is_active` الموجود يكفي. لا زر إخفاء على كل منتج، ولا تعطّلوا المنتجات لما تنطفئ الفئة.

| `is_active` | الموقع والتطبيق |
|-------------|-----------------|
| `true` | الفئة ظاهرة ومنتجاتها ترجع |
| `false` | الفئة تختفي مع منتجات الشجرة تحتها |

إخفاء الرئيسية يخفي منتجات الفروع حتى لو الفرعية بقيت مفعّلة. إعادة `true` تنشر نفس المنتجات. قائمة منتجات الداشبورد تبقى تعرض منتجات الفئة المخفية.

---

## 28) تغيير صورة الأيقونة

> **27 أيلول 2026** — [`DASHBOARD_ICON_IMAGE.md`](./DASHBOARD_ICON_IMAGE.md)

`POST /api/admin/icons` و `POST /api/admin/icons/{id}` مع `_method=PUT`. `multipart/form-data`. اسم الملف **`image`**. بدون ملف جديد لا ترسلوا `image` ولا رابط قديم كنص.

| الحقل | القاعدة |
|--------|---------|
| `image` | اختياري عند التعديل · فاضي = الصورة القديمة |
| الصيغ | `jpg` `jpeg` `png` `gif` `svg` `webp` · 2MB |
| `name[ar]` / `name[en]` | مطلوبان إذا انرسل `name` |

بعد 200: المعاينة من `data.image` أو `data.icon` ومعه `?v=`. ربط المنتج ما تغيّر: `icon_ids`.

---

## 29) عروض: شرط + مكافأة

> **27 أيلول 2026** — [`DASHBOARD_PROMOTION_TRIGGERS.md`](./DASHBOARD_PROMOTION_TRIGGERS.md)

العرض = شرط + مكافأة. الحقول حسب النوع: `GET /api/admin/promotions/fields-for-type/{type}`.

| الشرط | المكافأة | `type` | حقول إضافية |
|--------|----------|--------|-------------|
| أول طلب | خصم | `first_order_discount` | `discount_value` + `discount_type` |
| أول طلب | توصيل مجاني | `first_order_free_shipping` | — |
| أول طلب | هدية | `first_order_gift` | `gift_description.ar` + `.en` |
| شراء بقيمة | خصم | `spend_x_discount` | `min_spend` + الخصم |
| شراء بقيمة | توصيل مجاني | `spend_x_get_free_shipping` | `min_spend` |
| شراء بقيمة | هدية | `spend_x_get_gift` | `min_spend` + `gift_description` |
| أنشأ حساب | خصم | `signup_discount` | `discount_value` + `discount_type` |
| أنشأ حساب | توصيل مجاني | `signup_free_shipping` | — |
| أنشأ حساب | هدية | `signup_gift` | `gift_description` |

`discount_type`: `percentage` أو `fixed`. النقاط تبقى خياراً منفصلاً: `spend_x_get_points` + `reward_points`.

أول طلب تلقائي إذا ما عنده طلب سابق (الملغى ما يُحسب). شراء بقيمة عند بلوغ `min_spend`. إنشاء حساب على أول طلب إذا الحساب ضمن `starts_at` / `ends_at`. التطبيق والموقع يطبّقونه وحدهم.

---

## 30) تاريخ ووقت جدولة سلة الزبون

> **25 أيلول 2026** — [`DASHBOARD_BASKET_SCHEDULE_DATE_TIME.md`](./DASHBOARD_BASKET_SCHEDULE_DATE_TIME.md)

عرض فقط. الزبون يختار اليوم والساعة. الأدمن ما يعدّل الساعة من هذه الشاشة.

```http
GET /api/admin/user-basket-schedules
GET /api/admin/user-basket-schedules/{id}
```

| الحقل | العرض |
|--------|--------|
| `start_date` | تاريخ التوصيل · لا تسموه Start Date |
| `delivery_time` | `HH:mm` · `null` = «—» مو `00:00` |
| `next_run_date` | التوصيل التالي + نفس الساعة إن وُجدت |
| `schedule.name` | التكرار |

تفعيل / إيقاف كما هو (`enable` / `disable`). لا خلط مع `delivery_time` نص المنتج ولا مع `scheduled_delivery_at` على الطلب. صلاحية العرض: `userbasketschedule.view`.

---

## 31) يوم ووقت التوصيل على الطلب

> **25 أيلول 2026** — [`DASHBOARD_ORDER_DELIVERY_SCHEDULE.md`](./DASHBOARD_ORDER_DELIVERY_SCHEDULE.md)

بجانب توصيل فوري (`is_instant_delivery`): خيار **تحديد يوم ووقت**.

```http
PATCH /api/admin/orders/{orderId}/scheduled-delivery
```

```json
{ "scheduled_delivery_at": "2026-09-28 16:30" }
```

مسح الموعد: `"scheduled_delivery_at": null`. الصيغة `Y-m-d H:i` بدون ثواني. نفس الحقل من `GET /api/admin/orders` و `GET /api/admin/orders/{id}/get_one`.

الطلب `delivered` · `cancelled` · `cancelled_by_admin` · `returned_by_user` ما ينحفظ له موعد. `null` مو باگ.

---

## 32) حالة الطلب

> [`DASHBOARD_ORDER_STATUS.md`](./DASHBOARD_ORDER_STATUS.md)

`value` في الـ Select = المفتاح الإنجليزي. **مو** `out_for_delivery`.

```http
PATCH /api/admin/orders/{orderId}/change-status
{ "status": "out_delivery" }
```

إلغاء من الإدارة يحتاج `rejection_reason` مع `cancelled_by_admin`.

| من | إلى |
|----|-----|
| `pending` / `waiting_approval` | `preparing` · `cancelled` · `cancelled_by_admin` |
| `preparing` | `out_delivery` · `cancelled` · `cancelled_by_admin` |
| `out_delivery` | `delivered` · `cancelled` · `cancelled_by_admin` |
| `delivered` | لا تغيير |

`preparing` → `pending` مرفوض. عنصر واحد: `PATCH /api/admin/orders/items/{itemId}/change-status` بقيم `pending` · `preparing` · `out_delivery` · `delivered`.

القيم: `pending` · `waiting_approval` · `preparing` · `out_delivery` · `delivered` · `cancelled` · `cancelled_by_admin` · `rejected_by_delivery` · `faild_deliver` · `returned_by_user`.

---

## 33) البنر: حقول اختيارية ومسحها

> [`DASHBOARD_BANNER_REQUIRED_FIELDS.md`](./DASHBOARD_BANNER_REQUIRED_FIELDS.md) · المسح: [`DASHBOARD_BANNER_CLEAR_FIELDS.md`](./DASHBOARD_BANNER_CLEAR_FIELDS.md) · البطاقة: [`DASHBOARD_SECTION_BANNER_CARD.md`](./DASHBOARD_SECTION_BANNER_CARD.md)

كل الحقول اختيارية ما عدا `image` عند **الإنشاء**. بدون `expires_at` = بنر دائم.

عند التعديل، إرسال الحقل فاضي يمسحه:

| الحقل | فاضي |
|--------|------|
| `title` / `description` / `button_text` | اللغة المرسلة تُمسح · `null` على الكائن يمسح الكل |
| `link` | `null` |
| `expires_at` | `null` = دائم |
| `image` | إذا ما انرسل، الصورة القديمة تبقى |

الرد بعد المسح `{ "ar": null, "en": null }` — مو `[]`. اعرضوا الفورم من الرد الجديد.

بطاقة البنر في إضافة القسم وصفحة التفاصيل: **صورة + اسم فقط**. بدون اسم تبقى الصورة. لا رقم ولا رابط ولا وصف على البطاقة. جدول `/sections/banners` يبقى كاملاً. على الشاشة الصغيرة قائمة البطاقات تسكرول وتعرض الكل — [`DASHBOARD_BANNER_LIST_SCROLL.md`](./DASHBOARD_BANNER_LIST_SCROLL.md).

`delivery_time` على فورم المنتج يبقى نصاً («3–5 أيام») ويظهر على صفحة المنتج فقط — [`DASHBOARD_CART_NO_DELIVERY_TIME.md`](./DASHBOARD_CART_NO_DELIVERY_TIME.md).

