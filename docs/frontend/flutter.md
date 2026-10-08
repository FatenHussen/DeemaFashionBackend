# Flutter — كل التعديلات (نسخة نهائية — 5 أيلول 2026)

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> Base: `/api/user` + `Accept-Language: ar|en`.  
> يجمع **كل** التعديلات: Nav · أقسام · فئات · منتج · فلاتر · أسعار · طلب سريع · متغيّرات · **ضمان** · **كمية**.  
> **آخر نسخة موحّدة (موصى بها للإرسال — 8 تشرين الأول 2026):** [`FLUTTER_LATEST.md`](./FLUTTER_LATEST.md)  
> **هوية ديما فاشن (منفصل عن تيك مارت):** [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md)  
> **سعر · خصم · كمية · باركود · SKU:** [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./FLUTTER_PRODUCT_PRICING_FIELDS.md)  
> **تسجيل:** [`REGISTER_FLOW.md`](./REGISTER_FLOW.md) · [`FLUTTER_REGISTER_FLOW.md`](./FLUTTER_REGISTER_FLOW.md)  
> **آخر تحديث | Last Updated:** 2026-10-08

**اليوم (أحدث دفعة — أرسلوا FLUTTER_LATEST أولاً):** هوية ديما البصرية — [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md) · [`FLUTTER_LATEST.md` §0d](./FLUTTER_LATEST.md#0d-هوية-ديما-فاشن-8-تشرين-الأول-2026)

**اليوم (سابقاً):** خصم ثابت (`fixed`) كسور وأكبر من 100 — `discountValue` نوع `num?` مو `int` — [`FLUTTER_FIXED_DISCOUNT.md`](./FLUTTER_FIXED_DISCOUNT.md)

**اليوم (سابقاً):** لا اسم متجر / Branch على شاشة المنتج — [`FLUTTER_NO_SHOP_BRANCHES.md`](./FLUTTER_NO_SHOP_BRANCHES.md)

**اليوم (سابقاً):** متغيّران على شاشة المنتج — مو `shopVariants.first` فقط — [`FLUTTER_PRODUCT_ALL_VARIANTS.md`](./FLUTTER_PRODUCT_ALL_VARIANTS.md)

**اليوم (سابقاً):** قيم الصفات مربوطة بالـ ID — `attribute_values=31` والاسم من الـ GET — [`FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md)

---

## الفهرس

1. [شريط التنقّل الديناميكي (Nav Menu)](#1-شريط-التنقّل-الديناميكي)
2. [الصفحات والأقسام (Page Builder + layout)](#2-الصفحات-والأقسام)
3. [صفحات الفئات](#3-صفحات-الفئات)
4. [الفئات الدائرية (Circular Categories)](#4-الفئات-الدائرية)
5. [منتجات شجرة الفئة (Category Subtree)](#5-منتجات-شجرة-الفئة)
6. [وراثة صفات الفئة](#6-وراثة-صفات-الفئة)
7. [صفحة المنتج (shop_variants + country)](#7-صفحة-المنتج)
8. [ربط تلقائي بمتجر المنصة (آخر تحديث)](#8-ربط-تلقائي-بمتجر-المنصة)
9. [التسجيل بدون إيميل](#9-التسجيل-بدون-إيميل)
10. [فلاتر المنتجات](#10-فلاتر-المنتجات)
11. [عرض الأسعار دولار + ليرة](#11-عرض-الأسعار)
12. [قسم الطلب السريع (حسب الصفحة) — **آخر تحديث**](#12-قسم-الطلب-السريع-حسب-الصفحة--آخر-تحديث)
13. [متغيّرات المنتج — عرض واختيار](#13-متغيّرات-المنتج--عرض-واختيار)
14. [الضمان + الكمية (5 أيلول 2026)](#14-الضمان--الكمية-5-أيلول-2026)
15. [قيم الصفات بالـ ID](#15-قيم-الصفات-بالـ-id)

---

## 1) شريط التنقّل الديناميكي

> المرجع: `FRONTEND_FLUTTER_NAV_MENU.md`

### التغيير

الشريط العلوي **لم يعد ثابتًا**. يُجلب من API.

### Endpoint

```http
GET /api/user/nav-menu
Accept-Language: ar
```

عام (بدون توكن). يرجّع العناصر **المفعّلة فقط** مرتّبة حسب `order`.

### Dart Model

```dart
class NavMenuItem {
  final int id;
  final String title;
  final String type; // route | category | brand | page | url
  final String? icon;
  final int order;
  final bool openInNewTab;
  final Map<String, dynamic> target;

  factory NavMenuItem.fromJson(Map<String, dynamic> json) => NavMenuItem(
    id: json['id'], title: json['title'], type: json['type'],
    icon: json['icon'], order: json['order'],
    openInNewTab: json['open_in_new_tab'] ?? false,
    target: (json['target'] as Map?)?.cast<String, dynamic>() ?? {},
  );
}
```

### التنقّل

| `type` | الوجهة |
|--------|--------|
| `route` | `target.route_key` → `home` \| `categories` \| `brands` \| `shops` \| `baskets` \| `schedules` \| `points` \| `help` \| `subscriptions` |
| `category` | `CategoryPageScreen(categoryId: target.category_id)` |
| `brand` | شاشة الماركة |
| `page` | شاشة Page Builder (`slug` أو `page_id`) |
| `url` | فتح خارجي (`open_in_new_tab`) |

### Checklist

- [ ] احذف القائمة الثابتة
- [ ] `GET /api/user/nav-menu` واعرض بالترتيب
- [ ] خريطة `route_key` → شاشات
- [ ] fallback آمن لأي `route_key` غير معروف
- [ ] أعد الجلب عند تغيير اللغة
- [ ] إذا `data` فاضية → أخفِ الشريط

---

## 2) الصفحات والأقسام

> المراجع: `FRONTEND_FLUTTER_PAGE_BUILDER.md` · `FRONTEND_FLUTTER_SECTIONS_TODAY.md` · `FRONTEND_FLUTTER_SECTIONS_COMPLETE.md`

### Endpoints

| الغرض | Endpoint |
|-------|----------|
| صفحة عامة (رئيسية / عروض...) | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة (أي مستوى) | `GET /api/user/categories/{categoryId}/page` |

**قاعدة ذهبية:** ويدجت **واحد** لعرض الأقسام — استخدمه في الرئيسية وصفحة الفئة وكل `page_slug`.

### آخر تحديث: فصل 3 مفاهيم (مهم)

| الحقل | المعنى | القيم |
|-------|--------|-------|
| **`layout`** | طريقة عرض **القسم كامل** | `slider` \| `list` \| `grid` |
| **`variant`** | شكل **الكارد داخل** القسم | `horizontal` \| `vertical` \| `square` |
| **`display_type_id`** | نوع **المحتوى** | 1=banner, 2=product, 3=shop, 4=basket, 5=schedule-basket, 6=brand, 7=recipe, 8=category, **11=schedule** (فئات الجدولة) |

**غلط قديم (احذفوه):** `variant == horizontal` → سلايدر. **صار خطأ.**  
السلايدر = `layout: slider`، شكل الكارد = `variant`.

### Fallback

```dart
final layout = section.layout ?? 'slider';
final cardVariant = section.variant ?? 'horizontal';
```

### شكل القسم الكامل

```json
{
  "id": 10,
  "name": { "ar": "...", "en": "..." },
  "type": "manual",
  "content_type": "product",
  "manual_model": "product",
  "api_method": null,
  "position": "before",
  "order": 1,
  "layout": "slider",
  "variant": "vertical",
  "display_type_id": 2,
  "is_default": false,
  "background_color": "#F7F7F7",
  "background_card_color": "#FFFFFF",
  "end_date": null,
  "discount": null,
  "discount_type": null,
  "see_more": { "page_slug": "products", "params": { "category_id": 5 } },
  "show_when": {},
  "action": { "page_slug": "product_details" },
  "items": [...]
}
```

- `end_date` / `discount` / `discount_type`: لأقسام flash sale فقط — غير ذلك `null`.
- `show_when`: فاضي/`{}` = اعرض دائمًا.
- لا تعرض قسمًا `items` فارغة.

### `layout` → ويدجت القسم

| `layout` | Widget |
|----------|--------|
| `slider` | `ListView(scrollDirection: Axis.horizontal)` — **افتراضي** |
| `list` | `ListView` / `Column` عمودي |
| `grid` | `GridView` |

### `variant` → شكل الكارد فقط

| `variant` | الشكل |
|-----------|-------|
| `horizontal` | كارد عرضي عريض (بانرات) |
| `vertical` | كارد رأسي (منتجات) |
| `square` | كارد مربع (فئات) |

### مثال ويدجت موحّد

```dart
Widget buildSection(Section section) {
  if (section.items.isEmpty) return const SizedBox.shrink();

  final layout = section.layout ?? 'slider';
  final cardVariant = section.variant ?? 'horizontal';

  return switch (layout) {
    'list' => SectionListView(items: section.items, cardVariant: cardVariant),
    'grid' => SectionGridView(items: section.items, cardVariant: cardVariant),
    _ => SectionSliderView(items: section.items, cardVariant: cardVariant),
  };
}
```

### البانرات

- غالباً `layout: slider` + `variant: horizontal`
- صورة **عرضية** → `AspectRatio` ≈ 16/6
- عنصر واحد = بانر ثابت، عدة = `PageView`/carousel
- الضغط → `item.link` على مستوى عنصر القسم (`items[i].link`)
- اعرضوا أيضاً `item.title` · `item.desc` · `item.button_text` — [`FLUTTER_BANNER_REQUIRED_FIELDS.md`](./FLUTTER_BANNER_REQUIRED_FIELDS.md)

### Checklist

- [ ] الموديل فيه `layout` + `variant` + `display_type_id`
- [ ] فرّع على `layout` أولاً، ثم مرّر `variant` للكارد
- [ ] احذف المنطق القديم (variant = تخطيط)
- [ ] fallback: بدون `layout` → `slider`
- [ ] نفس ويدجت الأقسام لـ `page_slug` و`categories/{id}/page`
- [ ] `see_more` → شاشة قائمة مع `params`
- [ ] `action.page_slug` → شاشة التفاصيل

---

## 3) صفحات الفئات

> المرجع: `FRONTEND_FLUTTER_CATEGORY_PAGES.md`

### Endpoint

```http
GET /api/user/categories/{categoryId}/page
```

```json
{
  "data": {
    "category": { "id": 12, "name": { "ar": "...", "en": "..." }, "children": [...] },
    "sections": [...]
  }
}
```

### الشاشة

1. نادِ `categories/{id}/page`
2. AppBar: `category.name`
3. `ListView`/`CustomScrollView` يبني `sections` عبر **مُعرّض الأقسام الموحّد** (قسم 2)

### التنقّل

| من | إلى |
|----|-----|
| كارد فئة | `CategoryPageScreen(categoryId: item.id)` |
| كارد منتج | `ProductDetailScreen` |
| `see_more` | شاشة القائمة مع `see_more.params` |

### Checklist

- [ ] لا تفترض عددًا ثابتًا للأقسام
- [ ] رتّب حسب `order`
- [ ] كاش per `categoryId` + `RefreshIndicator`

---

## 4) الفئات الدائرية

> المرجع: `FRONTEND_FLUTTER_CATEGORIES_CIRCULAR.md`

### التغيير

| قبل | بعد |
|-----|-----|
| منتجات على الأوراق فقط | منتج على أي مستوى |
| دوائر للجذور فقط | **نفس الدائرة لكل مستوى** |
| الأب بدون منتجات مباشرة | `category_id` يرجع المنتجات + كل الأحفاد |

### APIs

| الغرض | Endpoint |
|-------|----------|
| الجذور | `GET /api/user/categories` |
| أبناء فئة | `GET /api/user/categories?parent_id={id}` |
| منتجات | `GET /api/user/products?category_id={id}` |
| صفات | `GET /api/user/categories/{id}/attributes` |

> **ملاحظة:** لا يوجد `GET /api/user/categories/{id}` لفئة واحدة.  
> للأبناء استخدم `?parent_id=`. بيانات الفئة (اسم/أيقونة) تأتي من الكارد اللي ضغطته.

### حقول جديدة على الفئة

| الحقل | النوع |
|-------|-------|
| `parent_id` | int / null |
| `children_count` | int |
| `has_children` | bool |
| `children[].icon` | string (URL) |

### UI

```
┌─────────────────────────────┐
│  ← Food                     │
│  (○) Grains  (○) Veg        │ ← دوائر (أبناء)
│  ── Products ──              │
│  [card] [card] [card]        │ ← منتجات
└─────────────────────────────┘
```

- `has_children = true` → نفس الشاشة مع `?parent_id=`
- منتجات للفئة الحالية **تحت الدوائر دائمًا**
- نفس `CategoryCircleTile` لكل مستوى (حتى عمق 6)

### Checklist

- [ ] نفس الدائرة للجذور والفرعيات
- [ ] `has_children` → `?parent_id=`
- [ ] منتجات + دوائر معًا (لا تخفي المنتجات إذا في أبناء)
- [ ] الصفات من `/categories/{id}/attributes` لأي مستوى

---

## 5) منتجات شجرة الفئة

> المرجع: `FRONTEND_FLUTTER_CATEGORY_PRODUCT_SUBTREE.md`

### السلوك

```http
GET /api/user/products?category_id={categoryId}&page=1&per_page=10
```

يرجع منتجات **الفئة الحالية + كل الفروع بأي عمق** — طلب واحد، بدون endpoint جديد.

### ممنوع

```dart
// خطأ: يحذف منتجات الفروع
final visible = products
    .where((p) => p.categoryId == selectedCategoryId)
    .toList();
```

الـ Backend طبّق فلتر الشجرة مسبقًا — **لا تفلتر محلياً**.

### القواعد

- طلب **واحد** بـ `category_id` للفئة المفتوحة
- لا تشترط `has_children == false` لجلب المنتجات
- عند تغيير الفئة: امسح القائمة، `page = 1`، أعد الطلب
- load more: نفس `category_id` + الصفحة التالية
- empty state فقط عند: `!isLoading && products.isEmpty && pagination.total == 0`

### Checklist

- [ ] جلب المنتجات لكل مستوى (مو leaf فقط)
- [ ] لا requests منفصلة لكل child
- [ ] pagination من السيرفر كما هي

---

## 6) وراثة صفات الفئة

> المرجع: `FRONTEND_FLUTTER_CATEGORY_ATTRIBUTE_INHERITANCE.md`

### التغيير

| قبل | بعد |
|-----|-----|
| `category_id` حرفي فقط | أي id بالشجرة → صفات **الجذر** |
| فرعية بدون صفات = `[]` | الفرعية ترجع صفات الرئيسية |

### Endpoint

```http
GET /api/user/categories/{categoryId}/attributes
```

حقول مضافة: `category_id`, `root_category_id`.

### القواعد

- اجلب الصفات عند فتح **أي** فئة (حتى الجذر)
- **لا** تعيد الطلب عند drill-down بنفس الشجرة — استخدم `root_category_id` ككاش
- فلترة المنتجات: `GET /api/user/products?category_id={id}&attribute_values=31,40` — **IDs** مو أسماء
- صفحة المنتج: بعدها `attributes_map` / `shop_variants` — الاسم يتحدّث إذا الأدمن غيّر القيمة؛ الاختيار يبقى بـ `shop_variants[].id`

### Checklist

- [ ] chips حتى لو `has_children == true`
- [ ] إعادة استخدام القائمة عند النزول لأبناء بنفس الجذر
- [ ] قائمة فاضية = الجذر ما عنده صفات (مو خطأ)

---

## 7) صفحة المنتج

> المرجع: `FRONTEND_FLUTTER_PRODUCT_DETAIL_SHOP_VARIANTS.md`

### تغييرات الـ API

```http
GET /api/user/products/{id}
```

| الحقل | قبل | بعد |
|-------|-----|-----|
| `shop_variants` | ممكن `[]` | **دائمًا عنصر واحد على الأقل** |
| `country` | object `{ id, name: {ar,en} }` | **string** أو `null` |
| `shop_variants[].shop_id` | موجود دائمًا | ممكن **`null`** |
| `shop_variants[].id` | موجود دائمًا | ممكن **`null`** |

نفس تغيير `country` على `GET /api/user/products` (قائمة).

**لا تعرضوا اسم المتجر.** الدليل: [`FLUTTER_NO_SHOP_BRANCHES.md`](./FLUTTER_NO_SHOP_BRANCHES.md)

### Models

```dart
class ProductDetail {
  final String? country;
  final List<ShopVariant> shopVariants;
}

class ShopVariant {
  final int? id;
  final int? shopId;
  final num price;
  final int? quantity; // stock — nullable
}
```

### قواعد

```dart
final selected = variants.isNotEmpty ? variants.first : null;
final price = selected?.price ?? product.price;

bool canAddToCart(ShopVariant? v) =>
    v?.shopId != null && v?.id != null && (v?.quantity ?? 0) > 0;

// country
product.country // "تركيا" أو null — مو country.name.ar
```

- `shop_id` أو `id` = `null` → عطّل السلة، **الصفحة تضل تعرض**
- `attributes_map` فاضي → إخفاء picker (مو خطأ)

### Checklist

- [ ] `country` كـ `String?` على detail **و** list
- [ ] لا `shopVariants[0]` بدون حماية
- [ ] fallback الصور: variant → product → thumbnail
- [ ] Error/retry UI بدل red screen

---

## 8) ربط تلقائي بمتجر المنصة (آخر تحديث — مهم)

عند **إنشاء / تحديث منتج من الأدمن** (`sale_channel=platform` أو متجر البائع):

1. متغيّر افتراضي إن لزم
2. ربط بمتجر البائع (متجر واحد — **ما في فروع**)

**لا endpoints جديدة** — نفس `GET /api/user/products/{id}`.

| قبل | بعد (بعد حفظ الأدمن) |
|-----|----------------------|
| `shop_id: null` | متجر المنصة/البائع |
| `id: null` | `shop_product_variant_id` حقيقي |
| السلة معطّلة | السلة تشتغل |

**خلّوا حماية null** — منتجات قديمة أو بائع بلا متجر:

```dart
bool canAddToCart(ShopVariant? v) =>
    v?.shopId != null && v?.id != null && (v?.quantity ?? 0) > 0;

// إضافة للسلة
{ "shop_product_variant_id": selected.id, "quantity": 1 }
```

- لا ترسلوا سعراً من التطبيق
- بعد ربط الأدمن → السلة تشتغل **بدون تحديث للتطبيق**

---

## 9) التسجيل بدون إيميل

> التدفق الكامل: [`REGISTER_FLOW.md`](./REGISTER_FLOW.md) · مختصر: [`FLUTTER_REGISTER_FLOW.md`](./FLUTTER_REGISTER_FLOW.md)

### التغيير

| الحقل | قبل | بعد |
|-------|-----|-----|
| `phone` | مطلوب إذا ما في إيميل | **مطلوب دائمًا** |
| `email` | مطلوب إذا ما في هاتف | **اختياري** |
| OTP | SMS أو إيميل | **SMS دائمًا** على `phone` |

### Request

```http
POST /api/user/auth/register
```

```json
{
  "name": "أحمد",
  "phone": "0501234567",
  "password": "Passw0rd!",
  "city_id": 1,
  "governorate_id": 1
}
```

- **لا ترسل `email: ""`** — احذف المفتاح إذا فاضي
- `phone` أرقام فقط (`digitsOnly`)

### التحقق

```http
POST /api/user/auth/verify-otp
{ "phone": "0501234567", "code": "12345" }
```

### Checklist

- [ ] حقل الهاتف مطلوب + `digitsOnly`
- [ ] الإيميل "(اختياري)"
- [ ] حذف tabs/toggle "إيميل أو هاتف"
- [ ] OTP بالهاتف
- [ ] `email` nullable بكل الموديلات
- [ ] الملف الشخصي يتعامل مع `email == null`

---

## 10) فلاتر المنتجات

> المرجع الكامل: **`FRONTEND_FLUTTER_FILTERS.md`**

### Endpoints

| الغرض | Endpoint |
|-------|----------|
| منتجات + فلاتر | `GET /api/user/products` |
| chips (لون/وزن/...) | `GET /api/user/categories/{id}/attributes` |

### باراميترات رئيسية

`category_id` · `brand_id` · `shop_id` · `price_min`/`price_max` · `search` · `country` (نص) · `is_free_delivery` · `is_instant_delivery` · `on_sale` · `in_stock_only` · `attribute_values` · `type` · `sort_by` · `page` · `per_page`

### قواعد

- `category_id` = الفئة الحالية + **كل الأحفاد** — لا تفلتر محلياً
- `/attributes` على **أي** مستوى → صفات الجذر — كاش بـ `root_category_id`
- أي تغيير فلتر → `page = 1`
- `country` على البطاقة = string (مو object)

### Checklist

- [ ] chips من `/attributes` + منتجات من `/products` بنفس الطلب
- [ ] toggles + سعر + ترتيب
- [ ] empty state من `pagination.total === 0`

---

## 11) عرض الأسعار دولار + ليرة

- اعرض `*_formatted` حسب عملة المستخدم
- للعرض الثنائي استخدم `price_currencies` / `price_after_discount_currencies` (USD + SYP)
- **لا تحسب** سعر الصرف داخل التطبيق
- في صفحة المنتج: السعر بعد الخصم هو الأساسي؛ الأصلي مشطوب عند الخصم
- السلة/الطلب: مبالغ الـ API فقط — أرسل ids + كمية

> حذف الفئات وتسعير الأدمن بالليرة = **داشبورد فقط**.

---

## 12) قسم الطلب السريع (حسب الصفحة) — **آخر تحديث**

> **تحديث 26 آب 2026:** المحتوى والشكل من **Settings** (ليس Page Builder). الأدمن يختار **صفحات الظهور** عبر `quick_order_page_ids` — العميل يقرأ `page_ids` + `page_slugs`.  
> فلو الإنشاء / الموافقة / الإلغاء بالكامل: [`../custom-orders/flutter.md`](../custom-orders/flutter.md)

### الفكرة

| قبل | بعد |
|-----|-----|
| القسم ثابت على الهوم فقط | يظهر على **الصفحات التي يختارها الأدمن** (افتراضي: `home`) |
| سويتش واحد يخفي الزر والقسم معاً | **`show_header` و `show_section` منفصلان** |
| — | القسم نفسه **حسب الصفحة** (`page_slugs`) |

> تفصيل الفصل: [`QUICK_ORDER_HEADER_VS_SECTION.md`](./QUICK_ORDER_HEADER_VS_SECTION.md)

### Endpoint

```http
GET /api/user/settings
Accept-Language: ar
```

### شكل `data.quick_order`

```json
{
  "show_header": true,
  "show_section": true,
  "is_enabled": true,
  "page_ids": [1],
  "page_slugs": ["home"],
  "background_image": "https://.../storage/settings/quick-order-bg.jpg",
  "background_color": "#FFE8D6",
  "card_background_color": "#FFFFFF",
  "card_variant": "horizontal",
  "badge": "طلب عاجل",
  "title": "تحتاجه الآن؟",
  "subtitle": "اكتب ما تريده مثل قائمة السوق...",
  "cta": "اطلب الآن",
  "steps": [
    { "number": 1, "icon": "edit", "title": "اكتبه", "description": "قائمتك، بكلماتك" },
    { "number": 2, "icon": "price", "title": "نسعّره", "description": "أسعار واضحة قبل الدفع" },
    { "number": 3, "icon": "delivery", "title": "نوصّل", "description": "للباب بسرعة" }
  ],
  "action": {
    "page_slug": "custom_order_request",
    "route": "/api/user/custom-order-requests"
  }
}
```

| حقل | استخدام |
|-----|---------|
| `show_header` | زر الهيدر فقط |
| `show_section` | القسم مفعّل |
| `is_enabled` | = `show_section` (توافق خلفي — لا تستخدمه للزر) |
| `page_ids` | IDs الصفحات المعتمدة |
| `page_slugs` | نفس الصفحات كـ slug — **موصى للمطابقة** مع الصفحة الحالية |
| `background_image` | URL صورة خلفية — `BoxFit.cover` |
| `background_color` | لون احتياطي تحت/بدل الصورة |
| `card_background_color` | خلفية كل كارد خطوة |
| `card_variant` | `horizontal` \| `vertical` \| `square` |
| `badge` / `title` / `subtitle` / `cta` | نصوص (حسب `Accept-Language`) |
| `steps[]` | `{number, icon, title, description}` — حتى 6 |
| `action.page_slug` | `custom_order_request` → شاشة الإنشاء |

### Dart Model

```dart
class QuickOrderSettings {
  final bool showHeader;
  final bool showSection;
  final bool isEnabled; // = showSection (BC)
  final List<int> pageIds;
  final List<String> pageSlugs;
  final String? backgroundImage;
  final String backgroundColor;
  final String cardBackgroundColor;
  final String cardVariant;
  final String badge;
  final String title;
  final String subtitle;
  final String cta;
  final List<QuickOrderStep> steps;

  factory QuickOrderSettings.fromJson(Map<String, dynamic> json) =>
      QuickOrderSettings(
        showHeader: json['show_header'] == true,
        showSection: json['show_section'] == true ||
            (json['show_section'] == null && json['is_enabled'] == true),
        isEnabled: json['is_enabled'] == true,
        pageIds: (json['page_ids'] as List?)?.cast<int>() ?? [],
        pageSlugs: (json['page_slugs'] as List?)?.cast<String>() ?? [],
        backgroundImage: json['background_image'] as String?,
        backgroundColor: json['background_color'] as String? ?? '#FFE8D6',
        cardBackgroundColor:
            json['card_background_color'] as String? ?? '#FFFFFF',
        cardVariant: json['card_variant'] as String? ?? 'horizontal',
        badge: json['badge'] as String? ?? '',
        title: json['title'] as String? ?? '',
        subtitle: json['subtitle'] as String? ?? '',
        cta: json['cta'] as String? ?? '',
        steps: (json['steps'] as List? ?? [])
            .map((e) => QuickOrderStep.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
}

class QuickOrderStep {
  final int number;
  final String? icon;
  final String title;
  final String description;
  // fromJson ...
}
```

### قاعدة الإظهار / الإخفاء

```dart
final qo = settings.quickOrder;
final showHeader = qo.showHeader;
final showSection = qo.showSection &&
    (qo.pageSlugs.contains(currentPageSlug) ||
        qo.pageIds.contains(currentPageId));

// AppBar / Scaffold
if (showHeader) QuickOrderHeaderButton(label: qo.badge);

// داخل صفحة Page Builder (sections list)
if (showSection) QuickOrderSection(config: qo);
```

1. `show_header` يتحكم بزر الهيدر فقط (عام على كل الشاشات)
2. `show_section` يتحكم بالقسم + مطابقة `page_slugs` / `page_ids`
3. الافتراضي من الباك = `home` فقط؛ `page_ids: []` → لا قسم (الزر يبقى حسب `show_header`)
4. يمكن إخفاء القسم وإبقاء الزر، أو العكس

### خلفية القسم

```dart
BoxDecoration buildQuickOrderDecoration(QuickOrderSettings qo) {
  if (qo.backgroundImage != null) {
    return BoxDecoration(
      image: DecorationImage(
        image: NetworkImage(qo.backgroundImage!),
        fit: BoxFit.cover,
      ),
    );
  }
  return BoxDecoration(color: _colorFromHex(qo.backgroundColor));
}
```

### شكل الكارد (`card_variant`)

| قيمة | تخطيط |
|------|--------|
| `horizontal` | أيقونة يسار + نص يمين (افتراضي) |
| `vertical` | أيقونة فوق + نص تحت |
| `square` | كارد مربّع (مناسب لـ `GridView`) |

طبّق `card_background_color` على خلفية كل كارد. `borderRadius` ≈ 16، padding أفقي ≥ 16.

### ريسبونسيف

| عرض | تخطيط |
|-----|--------|
| ≥ 768 (تابلت) | صف: نص \| كروت الخطوات \| زر CTA |
| < 768 (موبايل) | عمود: عنوان → كروت عمودياً أو `ListView` أفقي → CTA `width: double.infinity` |

```dart
LayoutBuilder(
  builder: (context, constraints) {
    final isWide = constraints.maxWidth >= 768;
    return isWide
        ? Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Expanded(child: _QuickOrderCopy(qo)),
              Expanded(flex: 2, child: _QuickOrderSteps(qo)),
              _QuickOrderCta(qo),
            ],
          )
        : Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              _QuickOrderCopy(qo),
              _QuickOrderSteps(qo),
              SizedBox(
                width: double.infinity,
                child: _QuickOrderCta(qo),
              ),
            ],
          );
  },
);
```

### الأيقونات

`steps[].icon`: `edit` | `price` | `delivery` — اربطها بـ Material/SVG محلي. قيمة غير معروفة → أيقونة افتراضية.

### CTA → إنشاء الطلب

```http
POST /api/user/custom-order-requests
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

| حقل | مطلوب |
|-----|--------|
| `description` | نعم (≥ 10 أحرف) |
| `address_id` | نعم |
| `payment_method_id` | لا |
| `expected_at` | لا |
| `images[]` | لا (حتى 5) |

```dart
Navigator.pushNamed(context, Routes.customOrderCreate);
// أو context.push('/custom-order-requests/new');
```

بعد الإرسال: `pending_pricing` — لا تسعير تلقائي. التفاصيل الكاملة في [`../custom-orders/flutter.md`](../custom-orders/flutter.md).

### Checklist — طلب سريع

- [ ] `GET /settings` → parse `quick_order` (كاش مع invalidation عند تغيير اللغة)
- [ ] الزر ← `show_header` · القسم ← `show_section` + مطابقة الصفحة
- [ ] القسم يظهر فقط إن `page_slugs` / `page_ids` تطابق الصفحة الحالية
- [ ] خلفية صورة (`NetworkImage` + `cover`) أو لون
- [ ] كروت بـ `card_background_color` + `card_variant`
- [ ] ريسبونسيف موبايل / تابلت
- [ ] CTA → شاشة إنشاء + `POST /custom-order-requests`
- [ ] إشعار `custom_order_request` + `waiting_approval` → شاشة الموافقة

---

## أوامر التشغيل (Backend)

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=CategoryPagesBackfillSeeder
php artisan db:seed --class=NavMenuSeeder
```

> طلب سريع (تفصيل + فلو الإنشاء): [`../custom-orders/flutter.md`](../custom-orders/flutter.md)

---

## Checklist شامل (كل التطبيق)

### أقسام وصفحات
- [ ] ويدجت أقسام موحّد
- [ ] `layout` → slider/list/grid
- [ ] `variant` → شكل الكارد فقط
- [ ] fallback بدون `layout` → `slider`
- [ ] صفحة فئة = نفس الويدجت

### فئات
- [ ] دوائر لكل مستوى (`?parent_id=` — **مو** `/categories/{id}`)
- [ ] منتجات + أبناء معًا
- [ ] subtree: لا فلترة محلية
- [ ] صفات الجذر لأي مستوى

### منتج + سلة + تسجيل + أسعار
- [ ] `country` string
- [ ] `shop_variants` nullable ids + حماية canAddToCart
- [ ] بعد ربط الأدمن بالمتجر → السلة تشتغل
- [ ] تسجيل: phone مطلوب، email اختياري
- [ ] أسعار من `*_formatted` / `*_currencies`

### Nav + فلاتر + طلب سريع (آخر تحديث)
- [ ] `GET /nav-menu` ديناميكي
- [ ] chips + `/products` + toggles + ترتيب
- [ ] `GET /settings` → `quick_order`
- [ ] `show_header` للزر · `show_section` + `page_slugs` للقسم (منفصلان)
- [ ] `page_slugs` / `page_ids`: القسم حسب الصفحة (افتراضي `home`)
- [ ] خلفية صورة/لون + `card_variant` + ريسبونسيف
- [ ] CTA → `POST /custom-order-requests` (فلو كامل: [`../custom-orders/flutter.md`](../custom-orders/flutter.md))
- [ ] ضمان: `warranty.name` / `.description`
- [ ] كمية المتغيّر `int?` + `canAddToCart` آمن مع `null`

---

## 13) متغيّرات المنتج — عرض واختيار

> **آخر تحديث:** [`product-variants-storefront-update.md`](product-variants-storefront-update.md)  
> **الدليل الكامل:** [`product-variants-flutter.md`](product-variants-flutter.md)

### ملخص

| البند | التفاصيل |
|-------|----------|
| API | `GET /api/user/products/{id}` → `shop_variants[]` |
| الهوية | `attributes` + `sku` — **لا اسم متغيّر** |
| الاختيار | لون → فلتر المقاسات المتاحة → `shop_product_variant_id` |
| السعر | `price_usd` / `price_syp` + `price_after_discount_*` |
| الكمية | `quantity` — «الكمية المتوفرة» |
| الخصم | `discount_value` + `discount_type` → `price_after_discount` |
| التسليم | `product.delivery_time` — ليس per variant |
| عرض التوفير | `discount` = المبلغ المخصوم |

- لا تفترض كل الألوان × كل المقاسات — اعرض فقط ما في API.
- `canAddToCart` يتطلب `quantity > 0` و `id != null` و `shopId != null` — كمية `null` = 0.

---

## 14) الضمان + الكمية (5 أيلول 2026)

> الدليل الكامل للإرسال: [`FLUTTER_LATEST.md`](./FLUTTER_LATEST.md)

### الضمان

```dart
class ProductWarranty {
  final int id;
  final String name;
  final String? description;
}
```

من `GET /api/user/products/{id}`: `warranty` object أو `null`. الحقول localized strings. Fallback: `warranty_period`.

### الكمية

```dart
bool canAddToCart(ShopVariant? v) =>
    v?.id != null &&
    v?.shopId != null &&
    (v?.quantity ?? 0) > 0;
```

`ShopVariant.quantity` و `ProductDetail.quantity` كلاهما `int?`. المخزون من المتغيّر فقط.

الأدمن ما عاد يضيف متغيّر فاضي. الباك يبقى يرجّع fallback `shop_variants[0]`.

---

## 15) قيم الصفات بالـ ID

> **20 أيلول 2026** — الدليل: [`FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md)

إعادة تسمية قيمة من الداش (صغير → XS) **ما تحذف** المتغيّر. الـ ID يبقى. اعرضوا الاسم الجديد من آخر GET.

- chips: `ValueKey(value.id)` وفلتر `attribute_values=31,40`
- السلة / التفاصيل: `shop_variants[].id` — لا تخزّنوا «صغير» في `SharedPreferences`

---

**آخر تحديث | Last Updated:** 2026-09-20
