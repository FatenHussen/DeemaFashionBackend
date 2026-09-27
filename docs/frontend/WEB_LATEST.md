# ويب — آخر نسخة (كل التعديلات)

> **أرسلوا هذا الملف لفريق الويب.**  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **آخر تحديث | Last Updated:** 2026-09-25  
> الملف الشامل السابق يبقى: [`web.md`](./web.md)

**اليوم (27 أيلول):** عروض تلقائية — أول طلب أو إنشاء حساب أو شراء بقيمة، والمكافأة خصم أو توصيل مجاني أو هدية — [`WEB_PROMOTION_TRIGGERS.md`](./WEB_PROMOTION_TRIGGERS.md)

**اليوم (25 أيلول):** جدولة السلة = تاريخ واحد + وقت داخل اليوم، وزر الحفظ يرسل الطلب — [`WEB_BASKET_SCHEDULE_DATE_TIME.md`](./WEB_BASKET_SCHEDULE_DATE_TIME.md)

**اليوم (25 أيلول):** السلة / الدفع / المراجعة بدون تكرار، وكرت الطلب يعرض `scheduled_delivery_at` إذا الإدارة حددته — [`WEB_CHECKOUT_LAYOUT.md`](./WEB_CHECKOUT_LAYOUT.md)

**اليوم (سابقاً):** حالة الطلب — اعرضوا `out_delivery` / `status_label` (مو fallback `pending`) — [`WEB_ORDER_STATUS.md`](./WEB_ORDER_STATUS.md)

**اليوم (سابقاً):** طلب سريع — **زر الهيدر منفصل عن القسم** (`show_header` / `show_section`) — [`QUICK_ORDER_HEADER_VS_SECTION.md`](./QUICK_ORDER_HEADER_VS_SECTION.md)

**اليوم (سابقاً):** على `/home` — **الهيدر الإعلاني (بنر) فوق** كرت تتبع الطلب — [`WEB_HOME_BANNER_BEFORE_TRACK.md`](./WEB_HOME_BANNER_BEFORE_TRACK.md)

**اليوم (سابقاً):** بنر — اعرضوا `title` · `desc` · `button_text` · `link` (مو صورة فقط) — [`WEB_BANNER_REQUIRED_FIELDS.md`](./WEB_BANNER_REQUIRED_FIELDS.md)

**اليوم (سابقاً):** جدولة التسليم **مطوية** — سطر + زر «عرض تفاصيل الجدولة» (مو بلوك مفتوح) — [`WEB_SCHEDULE_UI_COLLAPSED.md`](./WEB_SCHEDULE_UI_COLLAPSED.md)

**اليوم (سابقاً):** تكبير صورة المنتج (lightbox / zoom) — [`WEB_PRODUCT_IMAGE_ZOOM.md`](./WEB_PRODUCT_IMAGE_ZOOM.md)

**اليوم (سابقاً):** جدول تفاصيل المنتج — بلا ترويسة «الاسم/القيمة» + فاصل عمودي — [`WEB_PRODUCT_CATEGORY_DETAILS_TABLE.md`](./WEB_PRODUCT_CATEGORY_DETAILS_TABLE.md)

**اليوم (سابقاً):** إضافات المنتج = اسم (`key`) + سعر (`price_currencies`) — [`WEB_PRODUCT_EXTRA_DETAILS.md`](./WEB_PRODUCT_EXTRA_DETAILS.md)

**اليوم (سابقاً):** خصم ثابت (`fixed`) كسور وأكبر من 100 — `discount_value` = `number` — [`WEB_FIXED_DISCOUNT.md`](./WEB_FIXED_DISCOUNT.md)

**اليوم (سابقاً):** لا دروب داون Branch / اسم متجر على صفحة المنتج — [`WEB_NO_SHOP_BRANCHES.md`](./WEB_NO_SHOP_BRANCHES.md)

**اليوم (سابقاً):** قيم الصفات بالـ ID — فلتر و picker من `options[].id`، الاسم يتبدّل بعد إعادة التسمية والمتغيّر يبقى — [`WEB_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./WEB_CATEGORY_ATTRIBUTE_VALUE_IDS.md)

**اليوم (سابقاً):** متغيّران على صفحة المنتج (أزرق S و أسود L) — اختاروا الصف من `shop_variants` مو `[0]`، وغيّروا المقاس مع اللون — [`WEB_PRODUCT_ALL_VARIANTS.md`](./WEB_PRODUCT_ALL_VARIANTS.md)

**اليوم (سابقاً):** صور المتغيّر — المعرض من `shop_variants[].images[].path` عند تبديل اللون، وإلا صور المنتج — [`PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md`](./PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md)

**اليوم (سابقاً):** أيقونات المنتج على صفحة `/product/{id}` من `icons[]` — الموقع يعرض · الداشبورد تربط `icon_ids` — [`PRODUCT_ICONS_WEB_DASHBOARD.md`](./PRODUCT_ICONS_WEB_DASHBOARD.md)

**اليوم (سابقاً):** `POST /api/user/cart/items` صار موجود (توكن مطلوب) · OTP مؤقت **`00000`**.

**اليوم (صباحاً):** سعر $ · ل.س · نوع الخصم (لا يوجد خصم) · قيمة الخصم · السعر بعد الخصم · الكمية المتوفرة · الباركود · SKU — [`WEB_PRODUCT_PRICING_FIELDS.md`](./WEB_PRODUCT_PRICING_FIELDS.md)

يجمع **كل** ما يحتاجه الموقع حتى اليوم: Nav · أقسام · فئات · فلاتر · تسجيل · طلب سريع · أسعار · متغيّرات · **ضمان** · **كمية** · **سلل مجدولة** · **سلة مخصصة**.

---

## الفهرس

0. [ماذا تغيّر في 8 أيلول 2026](#0-ماذا-تغيّر-في-8-أيلول-2026)
0b. [ماذا تغيّر في 5 أيلول 2026](#0b-ماذا-تغيّر-في-5-أيلول-2026)
1. [شريط التنقّل](#1-شريط-التنقّل)
2. [الصفحات والأقسام](#2-الصفحات-والأقسام)
3. [الفئات ومنتجاتها](#3-الفئات-ومنتجاتها)
4. [فلاتر المنتجات](#4-فلاتر-المنتجات)
5. [صفحة المنتج — الشكل الكامل](#5-صفحة-المنتج--الشكل-الكامل)
6. [الضمان](#6-الضمان)
7. [الكمية والمخزون والسلة](#7-الكمية-والمخزون-والسلة)
8. [متغيّرات المنتج](#8-متغيّرات-المنتج)
9. [التسجيل](#9-التسجيل)
10. [الطلب السريع](#10-الطلب-السريع)
11. [الأسعار](#11-الأسعار)
12. [Checklist](#12-checklist)
13. [السلل المجدولة + السلة المخصصة](#13-السلل-المجدولة--السلة-المخصصة)

---

## 0) ماذا تغيّر في 8 أيلول 2026

**مساء — ناف جدولة + سلة الضيف**

| البند | المطلوب |
|-------|---------|
| `route_key=schedules` | أضيفوا للخريطة: `schedules: "/schedules"` — يظهر فقط إذا الأدمن أضاف العنصر من الداشبورد (`type=route`) |
| سلة المستخدم | `POST /api/user/cart/items` يحتاج توكن. الضيف بدون login: سلة محلية ثم نفس الـ API بعد تسجيل الدخول |

[`NAV_MENU_SCHEDULES.md`](./NAV_MENU_SCHEDULES.md)

**صباحاً — حقول السعر والخصم والكمية والباركود وSKU** — نفس اللي الأدمن بيملأها:

| تسمية الداشبورد | كارد القائمة | صفحة التفاصيل |
|-----------------|--------------|----------------|
| سعر المتغير (دولار) / ليرة | `price_currencies` من **المنتج** | من `shop_variants` المختار |
| نوع الخصم · لا يوجد خصم · قيمة الخصم · السعر بعد الخصم | بعد الخصم vs الأصلي على الكرت | `discount_type` · `discount_value` · `price_after_discount_currencies` |
| الكمية المتوفرة | `quantity` المنتج (قد `null`) | `shop_variants[].quantity` |
| الباركود · رمز التخزين التعريفي للمتغير | لا على الكرت | `barcode` · `sku` |

اختيار لون/مقاس يبدّل **كل** الحقول دفعة واحدة. الدليل: [`WEB_PRODUCT_PRICING_FIELDS.md`](./WEB_PRODUCT_PRICING_FIELDS.md).

---

## 0b) ماذا تغيّر في 5 أيلول 2026

| البند | قبل | بعد (الموقع) |
|-------|------|----------------|
| **الضمان** | رقم أشهر `warranty_period` فقط | كائن `warranty: { id, name, description }` — الاسم والوصف حسب اللغة |
| **كمية المنتج** | رقم على المنتج | المخزون الفعلي = `shop_variants[].quantity` (ممكن `null`) |
| **منتج بلا متغيّرات** | افتراض صف موجود | الأدمن ما عاد يولّد متغيّر فارغ — الباك يبقى يرجّع `shop_variants[0]` **fallback** حتى ما تكسر الصفحة |
| **إضافة السلة** | `quantity > 0` | نفس الشرط + `id` و `shop_id` غير `null` — `null` كمية = غير متوفر |
| **السلل المجدولة** | جدولة مكتوبة جوّا كل سلة | تبويبات من `GET /schedules` — السلل حسب `schedule_id` |
| **السلة المخصصة** | — | كروت فئات + تخصيص داخل الفئة + تأكيد نعم/لا — [`WEB_CUSTOM_BASKET.md`](./WEB_CUSTOM_BASKET.md) |
| **حقول الكرت** | شكّ إن الصورة اسمها ثاني | الغلاف = `image` · المعرض = `images[]` · الشارات = `top_badges` / `bottom_badges` — ما في aliases |
| **اسم / خصم** | كائن ترجمة أو `"none"` | `name` string حسب اللغة · `discount_type` = `percentage` \| `fixed` \| `null` |

ضمان/كمية: نفس `GET /api/user/products/{id}`. السلة المخصصة: [`WEB_CUSTOM_BASKET.md`](./WEB_CUSTOM_BASKET.md). إذا الكرت بلا صورة: الأدمن ما حفظها بعد — نفس الحقول تتعبّى بعد رفع الباك.

---

## 1) شريط التنقّل

```http
GET /api/user/nav-menu
Accept-Language: ar
```

عام. عناصر مفعّلة فقط، مرتّبة.

| `type` | الوجهة |
|--------|--------|
| `route` | `target.route_key` → `home` \| `categories` \| `brands` \| `shops` \| `baskets` \| `schedules` \| `points` \| `help` \| `subscriptions` |
| `category` | `/categories/{target.category_id}` |
| `brand` | `/brands/{target.brand_id}` |
| `page` | `/pages/{target.slug}` |
| `url` | رابط خارجي + `open_in_new_tab` |

احذف القائمة الثابتة. أعد الجلب عند تغيير اللغة. `rel="noopener noreferrer"` للروابط الخارجية.

خريطة ناقصة بدون `schedules` → العنصر يُتجاهل. [`NAV_MENU_SCHEDULES.md`](./NAV_MENU_SCHEDULES.md)

---

## 2) الصفحات والأقسام

| الغرض | Endpoint |
|-------|----------|
| صفحة عامة | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة | `GET /api/user/categories/{id}/page` |

**اعرض:** `layout` أولاً (`slider` \| `list` \| `grid`) ثم `variant` للكارد (`horizontal` \| `vertical` \| `square`).

القسم المخفي من الأدمن (`is_active: false`) **لا يصل** للـ API — لا فلترة إضافية.

تجاهل قسماً `items` فارغة. بانر: صورة عرضية ≈ 16:6.

---

## 3) الفئات ومنتجاتها

```http
GET /api/user/products?category_id={id}
```

يرجّع الفئة **+ كل الفروع بأي عمق**. **ممنوع** فلترة محلية `product.category_id === selectedId`.

صفات أي مستوى (صفات الجذر):

```http
GET /api/user/categories/{id}/attributes
```

كاش بـ `root_category_id`. قائمة فاضية = لا صفات (مو خطأ).

---

## 4) فلاتر المنتجات

نفس `GET /api/user/products` (توكن اختياري لـ `is_favorite`).

| Param | مثال |
|-------|------|
| `category_id` | شجرة كاملة |
| `brand_id` / `shop_id` | ماركة / متجر |
| `price_min` / `price_max` | بعملة العرض |
| `search` | اسم + وصف — **ليس** `name` |
| `country` | نص البلد — **ليس** `country_id` |
| `is_free_delivery` / `is_instant_delivery` / `on_sale` / `in_stock_only` | `1` أو `true` |
| `attribute_values` | `31,40` منطق OR |
| `type` | `new` \| `trend` \| `top_rated` \| `offers` \| `latest_flash_sale` |
| `sort_by` | `price_asc` \| `price_desc` \| `newest` \| `oldest` \| `rating` |

أرسل المفاتيح المفعّلة فقط. أي تغيير → `page = 1`.

---

## 5) صفحة المنتج — الشكل الكامل

```http
GET /api/user/products/{id}?lat={lat}&lng={lng}
Accept-Language: ar
```

```json
{
  "id": 28,
  "name": "جينز",
  "country": "تركيا",
  "warranty": {
    "id": 1,
    "name": "إرجاع مجاني",
    "description": "يمكنك إرجاع المنتج مجاناً ضمن المدة…"
  },
  "warranty_period": null,
  "quantity": null,
  "unit": "قطعة",
  "delivery_time": "3-5 أيام",
  "is_instant_delivery": false,
  "max_purchase_quantity": 5,
  "icons": [{ "id": 1, "name": "الدفع عند الاستلام", "image": "https://..." }],
  "top_badges": [],
  "bottom_badges": [],
  "bought_with": [{ "id": 29, "name": "حزام" }],
  "attributes_map": [
    { "attribute": "لون", "type": "color", "values": ["أحمر", "أسود"] }
  ],
  "shop_variants": [
    {
      "id": 55,
      "variant_id": 15,
      "sku": "JEANS-RED-M",
      "attributes": [
        { "attribute": "لون", "value": "أحمر", "type": "color" }
      ],
      "price": 25,
      "price_formatted": "$ 25",
      "price_currencies": {
        "USD": { "amount": 25, "formatted": "$ 25" },
        "SYP": { "amount": 325000, "formatted": "ل.س 325,000" }
      },
      "discount_value": 10,
      "discount_type": "percentage",
      "price_after_discount": 22.5,
      "quantity": 8,
      "shop_id": 1,
      "images": [{ "id": 1, "path": "https://..." }]
    }
  ]
}
```

| حقل | النوع | ملاحظات |
|-----|--------|---------|
| `country` | `string \| null` | مو object |
| `warranty` | object أو `null` | اعرضه في صفحة المنتج |
| `warranty_period` | `int \| null` | قديم — fallback فقط إذا `warranty === null` |
| `quantity` (المنتج) | `int \| null` | **لا تعتمد عليه للمخزون** |
| `shop_variants` | مصفوفة ≥ 1 | دائماً عنصر واحد على الأقل (fallback) |
| `shop_variants[].id` / `shop_id` | `int \| null` | `null` = السلة معطّلة |
| `shop_variants[].quantity` | `int \| null` | المخزون |
| `attributes_map` | مصفوفة | فاضية → أخفِ الـ picker |

```js
const variants = product.shop_variants ?? [];
const variant = variants[0] ?? null;
const price = variant?.price_after_discount ?? variant?.price ?? product.price;
```

---

## 6) الضمان

الأدمن يختار الضمان من قائمة جاهزة (مو رقم أشهر). الموقع يعرض النص الجاهز.

```js
function warrantyTitle(product) {
  if (product.warranty?.name) return product.warranty.name; // حسب Accept-Language
  if (product.warranty_period) return `${product.warranty_period} شهر`;
  return null;
}

function warrantyBody(product) {
  return product.warranty?.description || null;
}
```

- `warranty === null` وبدون `warranty_period` → أخفِ بلوك الضمان.
- لا تفترض أن `name` object `{ar,en}` على `/user` — الباك يرجّع **string** حسب اللغة.
- لا تستدعوا `/admin/warranties` من الموقع.

---

## 7) الكمية والمخزون والسلة

المخزون = **`shop_variants[].quantity`**. كمية المنتج قد تكون `null`.

```js
const qty = selected?.quantity ?? 0;

const canAddToCart =
  Boolean(selected?.id) &&
  Boolean(selected?.shop_id) &&
  qty > 0;

const maxBuy = Math.min(
  qty,
  product.max_purchase_quantity ?? qty
);
```

| حالة | العرض | السلة |
|------|--------|--------|
| `quantity: 8` | «متوفر: 8» | مفعّلة |
| `quantity: 0` أو `null` | غير متوفر | معطّلة |
| `id` أو `shop_id` = `null` | الصفحة تعرض | معطّلة |

إضافة للسلة (توكن مستخدم):

```http
POST /api/user/cart/items
Authorization: Bearer {token}

{ "shop_product_variant_id": selected.id, "quantity": 1, "note": "optional" }
```

| Method | Path |
|--------|------|
| GET | `/api/user/cart` |
| POST | `/api/user/cart/items` |
| PUT | `/api/user/cart/items/{id}` `{ quantity, note? }` |
| DELETE | `/api/user/cart/items/{id}` |

بدون توكن = 401. الطلب النهائي يبقى `POST /api/user/orders`.

قائمة المنتجات (`GET /products`): `quantity` على الكرت قد تكون `null` — لا تكسروا الـ UI. التوفر الحقيقي في صفحة التفاصيل.

---

## 8) متغيّرات المنتج

تفصيل: [`WEB_PRODUCT_PRICING_FIELDS.md`](./WEB_PRODUCT_PRICING_FIELDS.md) · [`product-variants-web.md`](./product-variants-web.md) · [`product-variants-storefront-update.md`](./product-variants-storefront-update.md)

- الهوية = `attributes` + `sku` — **لا اسم متغيّر**.
- اعرضوا التركيبات الموجودة في API فقط (لا Cartesian).
- لون: swatches. مقاس: أزرار للقيم المتاحة بعد اختيار اللون.
- السعر من المتغيّر المختار: `price_currencies` / `price_after_discount_currencies`.
- خصم المتغيّر أولاً ثم خصم المنتج / flash sale (محسوب في الباك).
- `delivery_time` على **المنتج** مرة واحدة.
- `attributes_map` فاضي → منتج بلا صفات — استخدموا `shop_variants[0]` مباشرة.

---

## 9) التسجيل

تفصيل: [`REGISTER_FLOW.md`](./REGISTER_FLOW.md) · مختصر ويب: [`WEB_REGISTER_FLOW.md`](./WEB_REGISTER_FLOW.md)

```
فورم → POST /auth/register (بدون توكن) → OTP → POST /auth/verify-otp → data.token
```

```http
POST /api/user/auth/register
```

- `phone` **مطلوب دائماً** (أرقام فقط).
- `email` اختياري — **لا ترسلوا** `email: ""`.
- `password`: ≥ 8 + صغير + كبير + رقم + رمز.
- `city_id` + `governorate_id` مطلوبان — المدن: `GET /cities?governorate_id=`.
- OTP: `POST /api/user/auth/verify-otp` `{ phone, code }`. **مؤقت (16 أيلول):** الكود **`00000`** — ما في SMS.
- إعادة الإرسال: `POST /auth/login` (403 متوقع) — **ليس** `/send-otp`.

---

## 10) الطلب السريع

تفصيل: [`../custom-orders/web.md`](../custom-orders/web.md)

```http
GET /api/user/settings
```

`data.quick_order`:

- `show_header` → زر الهيدر فقط.
- `show_section` → القسم مفعّل؛ اعرضوه فقط إذا `page_slugs` تضم الصفحة الحالية (افتراضي `home`).
- `is_enabled` = `show_section` (توافق خلفي — لا تستخدموه للزر).
- CTA → `POST /api/user/custom-order-requests`.

تفصيل الفصل: [`QUICK_ORDER_HEADER_VS_SECTION.md`](./QUICK_ORDER_HEADER_VS_SECTION.md)

---

## 11) الأسعار

اعرضوا `*_formatted` أو `*_currencies`. **لا تحسبوا** سعر الصرف محلياً.

في صفحة المنتج: السعر بعد الخصم هو الأساسي؛ الأصلي مشطوب عند وجود خصم.

نص متاجر التطبيقات ثابت في i18n: «كل ما تحبه، يصلك بابتسامة. عروض رائعة وفرحة صغيرة في كل سلة.» — احذفوا «ومنتجات طازجة».

---

## 12) Checklist

- [ ] Nav من `/nav-menu` + خريطة `route_key` (**أضيفوا `schedules` → `/schedules`**)
- [ ] أقسام: `layout` ثم `variant` — لا فلترة `is_active`
- [ ] منتجات الشجرة بدون فلتر محلي
- [ ] فلاتر §4 كاملة
- [ ] `country` string
- [ ] `shop_variants[0]` مع `?.` دائماً
- [ ] **ضمان:** `warranty.name` / `warranty.description` — fallback `warranty_period`
- [ ] **كمية:** `shop_variants[].quantity` — تعاملوا مع `null`
- [ ] سلة: `id` + `shop_id` + `quantity > 0`
- [ ] لا Cartesian — تركيبات API فقط
- [ ] تسجيل: هاتف مطلوب — التدفق: [`REGISTER_FLOW.md`](./REGISTER_FLOW.md)
- [ ] طلب سريع من `settings.quick_order`
- [ ] أسعار من الـ API فقط
- [ ] سلل مجدولة: تبويبات `/schedules` + `schedule_id` + تخصيص بنفس الـ id
- [ ] كروت الجدولة من `image` + `images` + `top_badges` / `bottom_badges` (بدون aliases)
- [ ] **سعر/خصم/كمية/باركود/SKU:** الكارد من المنتج · التفاصيل من المتغيّر المختار — [`WEB_PRODUCT_PRICING_FIELDS.md`](./WEB_PRODUCT_PRICING_FIELDS.md)

---

## 13) السلل المجدولة + السلة المخصصة

> الدليل الكامل للإرسال: [`WEB_CUSTOM_BASKET.md`](./WEB_CUSTOM_BASKET.md) — **5 أيلول 2026 مساءً**

كروت الفئات على الصفحة: `GET /api/user/sections?page_slug=home` عندما `display_type_id=11` أو `content_type=schedule`. ضغط الكرت → `/schedules/{id}`.  
`display_type_id=5` = سلل أدمن جاهزة — مسار مختلف.

**كروت / قائمة:**

```http
GET /api/user/schedules
GET /api/user/schedules/{id}
Accept-Language: ar
```

عقد ثابت — لا تنتظروا أسماء ثانية:

| الحقل | النوع | ملاحظة |
|--------|--------|--------|
| `id` · `name` | number · **string** | الاسم حسب `Accept-Language` |
| `description` | string \| null | |
| `image` | string \| null | URL كامل — مو `cover_image` / `photo` |
| `images` | string[] | URLs — مو `gallery` / `media` |
| `interval_days` | number | |
| `discount_type` | `percentage` \| `fixed` \| `null` | مو `"none"` |
| `discount_value` | number | بدون خصم غالباً `0` |
| `top_badges` / `bottom_badges` | array | `id` · `name` · `image` · `color` · `position` |

**تخصيص** (Auth) — `GET /api/user/schedules/{id}/custom-basket` ثم items + confirm.  
إضافة: `{ shop_product_variant_id, quantity }` من `shop_variants[].id`.  
`summary.savings_formatted` = وفّرت. نعم: `{ confirm_schedule: true, start_date }` · لا: `{ confirm_schedule: false }` → `cart_items`.

**واجهة التأكيد مطوية:** سطر «هل ترغب بجدولة…» + زر «عرض تفاصيل الجدولة» — التفاصيل بعد الضغط فقط. [`WEB_SCHEDULE_UI_COLLAPSED.md`](./WEB_SCHEDULE_UI_COLLAPSED.md)

التفاصيل والعقد الكامل: [`WEB_CUSTOM_BASKET.md`](./WEB_CUSTOM_BASKET.md)

---
