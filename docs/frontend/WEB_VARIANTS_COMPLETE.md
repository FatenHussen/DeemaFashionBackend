# الويب — متغيّرات المنتج (دليل شامل + مقارنة موقع ديما)

> **الجمهور:** فريق ويب ديما فاشن (React) — المشروع منفصل عن تيك مارت / tikmool-website  
> **Base:** `/api/user` + `Accept-Language: ar|en`  
> **مصدر الحقيقة:** كود الباك (`ShopVariantResource`, `OneResource`, `ProductVariant`)  
> **آخر تحديث:** 31 آب 2026

---

## الفهرس

1. [ملخص تنفيذي](#1-ملخص-تنفيذي)
2. [مقارنة tikmool-website × الباك](#2-مقارنة-tikmool-website--الباك)
3. [معمارية المتغيّرات](#3-معمارية-المتغيّرات)
4. [Endpoints](#4-endpoints)
5. [استجابة GET /products/{id} — الحقول الكاملة](#5-استجابة-get-productsid--الحقول-الكاملة)
6. [shop_variants — العقد الحقيقي من الباك](#6-shop_variants--العقد-الحقيقي-من-الباك)
7. [attributes_map vs attributes](#7-attributes_map-vs-attributes)
8. [الخصم — discount_value · discount_type · discount](#8-الخصم--discount_value--discount_type--discount)
9. [الأسعار USD / SYP](#9-الأسعار-usd--syp)
10. [اختيار المتغيّر (Picker)](#10-اختيار-المتغيّر-picker)
11. [الكمية · التسليم · country](#11-الكمية--التسليم--country)
12. [منتجات المنصة vs المتجر](#12-منتجات-المنصة-vs-المتجر)
13. [إضافة للسلة / الطلب](#13-إضافة-للسلة--الطلب)
14. [TypeScript — أنواع موصى بها](#14-typescript--أنواع-موصى-بها)
15. [تحديثات مطلوبة في repo الويب](#15-تحديثات-مطلوبة-in-repo-الويب)
16. [اختبار API حي](#16-اختبار-api-حي)
17. [Checklist نهائي](#17-checklist-نهائي)
18. [مراجع](#18-مراجع)

---

## 1) ملخص تنفيذي

| الطبقة | المسؤولية |
|--------|-----------|
| **الداشبoard** | إنشاء/تعديل `product_variants` + ربط `shop_variants` (تكلفة فقط) |
| **الباك (User API)** | يجمّع كل متغيّر في `shop_variants[]` مع سعر + خصم + كمية |
| **الويب** | يعرض ويختار من `shop_variants` — **لا يبني Cartesian** — لا يحسب سعر الصرف |

**قواعد ذهبية للويب:**

1. المصدر الوحيد للتركيبات = **`shop_variants`** (مو `attributes_map` × Cartesian).
2. المطابقة عند الاختيار = **`attribute` + `value`** (strings) — **لا `id`** داخل `shop_variants[].attributes`.
3. السعر المعروض = **`price_after_discount_currencies`** إذا في خصم، وإلا **`price_currencies`**.
4. الطلب يحتاج **`shop_variants[].id`** (معرّف `shop_product_variants`) — إذا `null` → لا شراء.
5. **لا يوجد** `variant.name` في API — الهوية = صفات + `sku`.

---

## 2) مقارنة tikmool-website × الباك

> بناءً على مراجعة repo الويب (آب 2026) مقابل checklist [`LATEST_UPDATES.md`](../LATEST_UPDATES.md).

### ✅ مُنفَّذ في الويب

| البند | أين في الويب | ملاحظة |
|-------|--------------|--------|
| Nav Menu ديناميكي | `useNavMenu` + `Navbar.tsx` | `GET /user/nav-menu` |
| Page Builder / CMS | `CmsPage.tsx` + `ApiSectionsRenderer` | `GET /user/sections?page_slug=` |
| layout + variant | `sectionCardVariant.ts` | slider/list/grid + horizontal/vertical/square |
| صفحات الفئات | `apiRoutes.categories.page` | `GET /categories/{id}/page` |
| shop_variants picker | `useVariantSelector` | من API وليس Cartesian |
| price_currencies | `formatApiPrice.ts` | USD + SYP |
| quantity | `ShopVariant.quantity` | حقل واحد |
| platform variant | `isPurchasableVariant()` | `id` / `shop_id` null |
| country string | `ProductCountry` + `resolveProductCountry` | |
| طلب سريع | `useQuickOrderSettings` + `QuickOrderHomeBanner` | `page_slugs` من settings |
| تسجيل بدون إيميل | `CustomerSignUp` | |
| Eye toggle | — | الباك يستبعد الأقسام المخفية — لا فلترة client |
| فلاتر منتجات | `UserProductListFilters` | |

### ⚠️ فجوات / تحديثات مطلوبة

| # | البند | الوضع الحالي | المطلوب |
|---|-------|--------------|---------|
| 1 | **خصم المتغيّر** | Types: `discount` + `price_after_discount` فقط | إضافة `discount_value` + `discount_type` + badge نسبة |
| 2 | **عرض نوع الخصم** | يعتمد على السعر المحسوب | badge `-10%` أو «خصم X ل.س» من `discount_type` |
| 3 | **شكل attributes** | قد يفترض `id` / `category_attribute` | الباك يرسل `{ attribute, value, type }` فقط |
| 4 | **hex للون** | غير موجود في `shop_variants.attributes` | جلب من `GET /categories/{id}/attributes` أو map محلي |
| 5 | **fallback بدون variant** | — | الباك **لا يرسل** `discount_value`/`discount_type` في fallback — استخدم defaults |
| 6 | **endpoint الصفحات** | `/user/sections?page_slug=` | يعمل — `/user/pages/{slug}` **غير موجود** في routes الباك |
| 7 | **Nav على الإنتاج** | 404 deploy | مشكلة باك/سيرفر — [`BACKEND_NAV_MENU_DEPLOY.md`] إن وُجد في repo الويب |
| 8 | **إضافة للسلة** | `POST /cart/items` | توكن · `{ shop_product_variant_id, quantity }` |

---

## 3) معمارية المتغيّرات

```
┌─────────────────────────────────────────────────────────────┐
│  product (منتج)                                              │
│  ├── price, discount_type, delivery_time, country (string)  │
│  ├── attributes_map[]     ← للـ picker UI فقط                 │
│  └── shop_variants[]      ← المصدر للاختيار + السعر + السلة │
│       │                                                      │
│       ├── product_variant (SKU, price, discount, quantity)   │
│       └── shop_product_variant (id, shop_id, cost_price)     │
└─────────────────────────────────────────────────────────────┘
```

| الجدول | ما يخزّن |
|--------|----------|
| `product_variants` | SKU · price · **discount** · **discount_type** · quantity · attributes_values_ids |
| `shop_product_variants` | ربط variant ↔ shop · **cost_price** فقط (لا price) |

**أولوية الخصم (محسوبة في الباك — `ProductVariant::resolveEffectiveDiscount`):**

1. خصم المتغيّر (`discount` + `discount_type` ≠ `none`)
2. خصم المنتج / Flash Sale
3. لا خصم

---

## 4) Endpoints

| الغرض | Method | Path | Auth |
|-------|--------|------|------|
| تفاصيل منتج | GET | `/api/user/products/{id}?lat=&lng=` | اختياري |
| صفات الفئة (IDs + hex اختياري) | GET | `/api/user/categories/{categoryId}/attributes` | لا |
| معاينة طلب | POST | `/api/user/orders/preview` | نعم |
| إنشاء طلب | POST | `/api/user/orders` | نعم |
| أقسام صفحة CMS | GET | `/api/user/sections?page_slug={slug}` | لا |
| Nav | GET | `/api/user/nav-menu` | لا |

> **لا يوجد** `GET /api/user/pages/{slug}`. السلة: `POST /api/user/cart/items` (توكن).

---

## 5) استجابة GET /products/{id} — الحقول الكاملة

### حقول المنتج المرتبطة بالمتغيّرات

| الحقل | النوع | الاستخدام في الويب |
|-------|-------|---------------------|
| `id` | number | |
| `name` | string (مترجم) | |
| `country` | **string** | `"تركيا"` — ليس object |
| `price` | number | سعر المنتج الافتراضي (USD base) |
| `price_currencies` | object | USD + SYP |
| `price_after_discount` | number | سعر المنتج بعد خصم المنتج |
| `price_after_discount_currencies` | object | |
| `discount_type` | string | على **مستوى المنتج** — لا تخلط مع variant |
| `quantity` | number | مخزون المنتج (fallback) |
| `max_purchase_quantity` | number? | حد أقصى للشراء |
| `delivery_time` | string? | **مرة واحدة** في الصفحة — ليس per variant |
| `sku` | string? | للمنتج البسيط |
| `thumbnail` | string? | |
| `images` | array | صور المنتج |
| `attributes_map` | array | بناء picker |
| `shop_variants` | array | **اختيار + سعر + سلة** |
| `category.id` | number | لجلب صفات الفئة (hex) |

### مثال مختصر

```json
{
  "status": true,
  "data": {
    "id": 42,
    "name": "سويت شيرت",
    "country": "تركيا",
    "delivery_time": "3-5 أيام",
    "max_purchase_quantity": 5,
    "attributes_map": [
      { "attribute": "لون", "type": "color", "values": ["أحمر", "أسود"] },
      { "attribute": "قياس", "type": "square", "values": ["S", "M", "L"] }
    ],
    "shop_variants": [ "..." ]
  }
}
```

---

## 6) shop_variants — العقد الحقيقي من الباك

> المصدر: `app/Http/Resources/Product/ShopVariantResource.php`

### منتج بمتغيّرات

```json
{
  "id": 55,
  "variant_id": 15,
  "sku": "SWEAT-RED-M",
  "model": "4280",
  "barcode": "1234567890",
  "attributes": [
    { "attribute": "لون", "value": "أحمر", "type": "color" },
    { "attribute": "قياس", "value": "M", "type": "square" }
  ],
  "price": 25,
  "currency": "USD",
  "currency_symbol": "$",
  "price_formatted": "$ 25",
  "price_currencies": {
    "USD": { "amount": 25, "currency": "USD", "symbol": "$", "formatted": "$ 25" },
    "SYP": { "amount": 325000, "currency": "SYP", "symbol": "ل.س", "formatted": "ل.س 325,000" }
  },
  "discount_value": 10,
  "discount_type": "percentage",
  "discount": 2.5,
  "discount_formatted": "$ 2.5",
  "discount_currencies": {
    "USD": { "amount": 2.5, "symbol": "$", "formatted": "$ 2.5" },
    "SYP": { "amount": 32500, "symbol": "ل.س", "formatted": "ل.س 32,500" }
  },
  "price_after_discount": 22.5,
  "price_after_discount_formatted": "$ 22.5",
  "price_after_discount_currencies": {
    "USD": { "amount": 22.5, "symbol": "$", "formatted": "$ 22.5" },
    "SYP": { "amount": 292500, "symbol": "ل.س", "formatted": "ل.س 292,500" }
  },
  "quantity": 8,
  "shop_id": 1,
  "is_restaurant": false,
  "city_id": 3,
  "images": [{ "id": 1, "path": "https://cdn.example.com/v.webp" }]
}
```

### fallback — منتج بدون variants table rows

> المصدر: `OneResource::fallbackShopVariant()` — **لاحظ:** لا يتضمن `discount_value` / `discount_type`

```json
{
  "id": null,
  "variant_id": null,
  "sku": "LIG-8188-BASE",
  "attributes": [],
  "price": 20,
  "price_currencies": { "USD": { "amount": 20 }, "SYP": { "amount": 260000 } },
  "discount": 0,
  "price_after_discount": 20,
  "price_after_discount_currencies": { "USD": { "amount": 20 }, "SYP": { "amount": 260000 } },
  "quantity": 100,
  "shop_id": null,
  "city_id": null,
  "images": []
}
```

### جدول الحقول

| الحقل | المعنى | ملاحظة للويب |
|-------|--------|--------------|
| `id` | **shop_product_variants.id** | مطلوب للطلب — `null` = لا شراء |
| `variant_id` | product_variant id (أو shop id fallback) | للعرض/تتبع — **ليس** للسلة |
| `attributes` | `{ attribute, value, type }[]` | **بدون** `id` · **بدون** `hex` |
| `discount_value` | القيمة المُدخلة (10 = 10%) | من `product_variants.discount` |
| `discount_type` | `none` \| `percentage` \| `fixed` | |
| `discount` | **مبلغ** الخصم (USD base + currencies) | للعرض «وفّرت X» |
| `price_after_discount*` | السعر النهائي | استخدم للعرض |
| `quantity` | مخزون | حقل واحد — لا `stock` |

---

## 7) attributes_map vs attributes

### attributes_map (على المنتج)

```json
[
  { "attribute": "لون", "type": "color", "values": ["أحمر", "أسود"] },
  { "attribute": "قياس", "type": "square", "values": ["S", "M"] }
]
```

- **values = strings** (أسماء مترجمة) — **ليس IDs**
- للبناء UI: swatches · أزرار مقاس
- **لا تفترض** كل value × value موجود — فلتر حسب `shop_variants`

### attributes (داخل كل shop_variant)

```json
{ "attribute": "لون", "value": "أحمر", "type": "color" }
```

- للمطابقة عند الاختيار
- **لا hex** — للون: `GET /categories/{categoryId}/attributes` يعطي `values[].id` + `name` (بدون hex في User API حالياً)

### صفات الفئة (للـ hex / IDs)

```http
GET /api/user/categories/{categoryId}/attributes
```

```json
[
  {
    "id": 1,
    "name": "لون",
    "type": "color",
    "values": [
      { "id": 6, "name": "أحمر" }
    ]
  }
]
```

**استراتيجية الويب للألوان:**

1. ابنِ picker من `attributes_map` (أسماء).
2. للـ hex: map من category attributes + colors catalog إن وُجد، أو دائرة بالاسم فقط.
3. المطابقة النهائية دائماً بـ **string** `attribute` + `value` ضد `shop_variants`.

---

## 8) الخصم — discount_value · discount_type · discount

### الفرق بين الحقول

| الحقل | مثال | الاستخدام |
|-------|------|-----------|
| `discount_type` | `percentage` | نوع الخصم |
| `discount_value` | `10` | 10% أو 10$ حسب النوع |
| `discount` | `2.5` | **المبلغ المخصوم** (محسوب) |
| `price` | `25` | قبل الخصم |
| `price_after_discount` | `22.5` | **اعرض هذا** كسعر حالي |

### منطق العرض (React)

```ts
export function variantPriceDisplay(v: ShopVariant, preferSyp: boolean) {
  const hasDiscount =
    v.discount_type !== 'none' &&
    v.discount_type != null &&
    (v.discount ?? 0) > 0;

  const current = preferSyp
    ? v.price_after_discount_currencies?.SYP?.formatted
    : v.price_after_discount_currencies?.USD?.formatted;

  const original = hasDiscount
    ? preferSyp
      ? v.price_currencies?.SYP?.formatted
      : v.price_currencies?.USD?.formatted
    : null;

  const badge =
    v.discount_type === 'percentage' && (v.discount_value ?? 0) > 0
      ? `-${v.discount_value}%`
      : v.discount_type === 'fixed' && (v.discount_value ?? 0) > 0
        ? `خصم ${v.discount_value}`
        : null;

  const savedLabel = hasDiscount
    ? preferSyp
      ? v.discount_currencies?.SYP?.formatted
      : v.discount_currencies?.USD?.formatted
    : null;

  return { current, original, badge, savedLabel, hasDiscount };
}
```

### ما يجب تحديثه في types الويب

```ts
// ❌ قديم — ناقص
interface ShopVariant {
  discount?: number;
  price_after_discount?: number;
}

// ✅ كامل — مطابق للباك
interface ShopVariant {
  id: number | null;
  variant_id: number | null;
  sku: string | null;
  attributes: VariantAttribute[];
  price: number;
  price_currencies: DualCurrency;
  discount_value: number;       // ← أضف
  discount_type: 'none' | 'percentage' | 'fixed';  // ← أضف
  discount: number;
  discount_currencies?: DualCurrency;
  price_after_discount: number;
  price_after_discount_currencies: DualCurrency;
  quantity: number;
  shop_id: number | null;
  images: ProductImage[];
}

interface VariantAttribute {
  attribute: string;
  value: string;
  type: 'color' | 'square' | 'circle' | string;
}
```

> **لا تحسب** الخصم محلياً إلا للـ preview — اعتمد `price_after_discount_*` من API.

---

## 9) الأسعار USD / SYP

الباك يخزّن USD كأساس ويولّد `*_currencies.USD` و `*_currencies.SYP` عبر `HasCurrencyConversion`.

| للعرض | الحقل |
|-------|-------|
| USD قبل خصم | `price_currencies.USD.amount` |
| SYP قبل خصم | `price_currencies.SYP.amount` |
| USD بعد خصم | `price_after_discount_currencies.USD.amount` |
| SYP بعد خصم | `price_after_discount_currencies.SYP.amount` |
| formatted جاهز | `*.formatted` |

```ts
// formatApiPrice.ts — تأكد أنه يقرأ *_currencies وليس price_usd flat
export function pickVariantPrice(v: ShopVariant, code: 'USD' | 'SYP', afterDiscount: boolean) {
  const bucket = afterDiscount ? v.price_after_discount_currencies : v.price_currencies;
  return bucket?.[code]?.formatted ?? bucket?.[code]?.amount;
}
```

> حقول `price_usd` / `price_syp` **قد لا تُرسل** — استخدم `*_currencies` دائماً.

---

## 10) اختيار المتغيّر (Picker)

### UI

```
اللون:    ( ● أحمر ) ( ○ أسود )
المقاس:   [ S ] [ M ] [ L ]

السعر:    22.5 $  ~~25 $~~   [-10%]
متوفر:    8
التسليم:  3-5 أيام          ← product.delivery_time
```

### State

```ts
// Map: attribute name → selected value (string)
type SelectionMap = Record<string, string>;

const [selected, setSelected] = useState<SelectionMap>({});
```

### إيجاد المتغيّر

```ts
export function findSelectedVariant(
  variants: ShopVariant[],
  selected: SelectionMap
): ShopVariant | null {
  const entries = Object.entries(selected).filter(([, v]) => v);
  if (!entries.length) return variants.length === 1 ? variants[0] : null;

  return (
    variants.find(v =>
      entries.every(([attr, val]) =>
        (v.attributes ?? []).some(
          a => a.attribute === attr && a.value === val
        )
      )
    ) ?? null
  );
}
```

### قيم متاحة (فلترة — لا Cartesian)

```ts
export function availableValuesForAttribute(
  variants: ShopVariant[],
  attributeName: string,
  currentSelection: SelectionMap
): string[] {
  const filtered = variants.filter(v =>
    Object.entries(currentSelection).every(([attr, val]) => {
      if (!val || attr === attributeName) return true;
      return (v.attributes ?? []).some(
        a => a.attribute === attr && a.value === val
      );
    })
  );

  return [
    ...new Set(
      filtered.flatMap(v =>
        (v.attributes ?? [])
          .filter(a => a.attribute === attributeName)
          .map(a => a.value)
      )
    ),
  ];
}
```

### قواعد UX

1. اعرض **فقط** تركيبات موجودة في `shop_variants`.
2. تغيير اللون → أعد تعيين المقاس إن لم يعد متاحاً.
3. `quantity <= 0` → «غير متوفر».
4. بدون صفات → اختر `shop_variants[0]` تلقائياً.
5. **لا** تعرض اسم متغيّر.
6. الصور: `variant.images` → `product.images` → `thumbnail`.

### ربط useVariantSelector

إذا `useVariantSelector` يطابق بـ `attr.id` — **حدّثه** لمطابقة `attribute` + `value`:

```ts
// ❌ قديم (إن وُجد)
// selected[valueId] === attr.id

// ✅ صح
// selected["لون"] === "أحمر"
```

---

## 11) الكمية · التسليم · country

### الكمية

```ts
const maxQty = Math.min(
  selectedVariant?.quantity ?? 0,
  product.max_purchase_quantity ?? Infinity
);
```

- حقل واحد: **`quantity`**
- لا `stock` على المتغيّر

### delivery_time

```tsx
{product.delivery_time && (
  <p>التسليم: {product.delivery_time}</p>
)}
```

- على **المنتج** — ليس per variant
- لا تكرر لكل اختيار لون/مقاس

### country

```ts
// ✅
const countryName: string = product.country; // "تركيا"

// ❌
const countryName = product.country?.name?.ar;
```

---

## 12) منتجات المنصة vs المتجر

| الحالة | `id` | `shop_id` | الشراء |
|--------|------|-----------|--------|
| platform + default shop | number | number | ✅ |
| shop channel | number | number | ✅ |
| variant بدون ربط shop | null | null | ❌ عرض فقط |
| fallback منتج بسيط | null | null | ❌ حتى يربط الأدمن |

```ts
export function isPurchasableVariant(v: ShopVariant | null | undefined): boolean {
  return Boolean(
    v?.id != null &&
    v?.shop_id != null &&
    (v.quantity ?? 0) > 0
  );
}
```

> منتج `sale_channel=platform` — الباك يربط تلقائياً بفرع المنصة الافتراضي → `id` و `shop_id` يكونان موجودين بعد الإنشاء الصحيح.

---

## 13) إضافة للسلة / الطلب

### ❌ غير موجود

```http
POST /api/user/cart/items   ← لا route في الباك
```

### ✅ الطريقة الصحيحة

**معاينة:**

```http
POST /api/user/orders/preview
Authorization: Bearer {token}
Content-Type: application/json

{
  "address_id": 12,
  "payment_method_id": 2,
  "items": [
    { "shop_product_variant_id": 55, "quantity": 2 }
  ]
}
```

**إنشاء:**

```http
POST /api/user/orders
```

نفس body + حقول إضافية (coupon، points، …) — انظر [`api/ORDER_USER_FLOW.md`](../api/ORDER_USER_FLOW.md).

```ts
// السلة المحلية في الويب
interface CartLine {
  shopProductVariantId: number; // shop_variants[].id — NOT variant_id
  quantity: number;
  productId: number;
  snapshot?: ShopVariant; // للعرض
}
```

**Validation الباك:** `items.*.shop_product_variant_id` → `exists:shop_product_variants,id`

---

## 14) TypeScript — أنواع موصى بها

```ts
export interface CurrencyAmount {
  amount: number | null;
  currency: string;
  symbol: string;
  formatted: string | null;
}

export type DualCurrency = Partial<Record<'USD' | 'SYP', CurrencyAmount>>;

export interface VariantAttribute {
  attribute: string;
  value: string;
  type: string;
}

export interface AttributeMapRow {
  attribute: string;
  type: string;
  values: string[];
}

export interface ShopVariant {
  id: number | null;
  variant_id: number | null;
  sku: string | null;
  model?: string | null;
  barcode?: string | null;
  attributes: VariantAttribute[];
  price: number;
  currency?: string;
  currency_symbol?: string;
  price_formatted?: string;
  price_currencies: DualCurrency;
  discount_value: number;
  discount_type: 'none' | 'percentage' | 'fixed';
  discount: number;
  discount_formatted?: string;
  discount_currencies?: DualCurrency;
  price_after_discount: number;
  price_after_discount_formatted?: string;
  price_after_discount_currencies: DualCurrency;
  quantity: number;
  shop_id: number | null;
  is_restaurant?: boolean;
  city_id?: number | null;
  images: Array<{ id: number; path: string }>;
}

export interface ProductDetail {
  id: number;
  name: string;
  country: string | null;
  delivery_time?: string | null;
  max_purchase_quantity?: number | null;
  attributes_map: AttributeMapRow[];
  shop_variants: ShopVariant[];
  images: Array<{ id: number; path: string }>;
  thumbnail?: string | null;
  category?: { id: number; name: string };
}
```

---

## 15) تحديثات مطلوبة في repo الويب

### أولوية 1 — Types + عرض الخصم

1. **`ShopVariant` type** — أضف `discount_value`, `discount_type`.
2. **صفحة المنتج** — badge `-X%` أو fixed discount.
3. **السعر** — `original` (price_currencies) + `current` (price_after_discount_currencies) عند `hasDiscount`.
4. **«وفّرت»** — من `discount_currencies` إن أردت.

### أولوية 2 — Picker

5. تأكد `useVariantSelector` يطابق بـ **string** attribute/value.
6. احذف أي اعتماد على `attributes[].id` أو `category_attribute`.

### أولوية 3 — fallback

7. عند غياب `discount_value`/`discount_type` في fallback:

```ts
const discountType = v.discount_type ?? 'none';
const discountValue = v.discount_value ?? 0;
```

### أولوية 4 — docs في repo الويب

8. انسخ هذا الملف + [`product-variants-storefront-update.md`](./product-variants-storefront-update.md) إلى `tikmool-website/docs/`.

### لا تغيّر (يعمل)

- `GET /user/sections?page_slug=` للـ CMS
- `formatApiPrice` إن يقرأ `*_currencies`
- `isPurchasableVariant`
- Eye toggle — backend-only

---

## 16) اختبار API حي

```bash
# تفاصيل منتج
curl -s "$API/user/products/42?lat=33.5&lng=36.3" \
  -H "Accept-Language: ar" | jq '.data | {attributes_map, shop_variants: .shop_variants[0]}'

# Nav
curl -s "$API/user/nav-menu" -H "Accept-Language: ar" | jq '.data | length'

# صفات فئة
curl -s "$API/user/categories/5/attributes" -H "Accept-Language: ar" | jq '.data[0]'
```

**تحقق من:**

- [ ] `shop_variants[].discount_value` و `discount_type` موجودان
- [ ] `attributes[]` = `{ attribute, value, type }` بدون id
- [ ] `country` string
- [ ] `price_*_currencies.USD` و `.SYP`
- [ ] `id` غير null للمنتجات القابلة للشراء

---

## 17) Checklist نهائي

### API / Types

- [ ] `ShopVariant` يتضمن `discount_value` + `discount_type`
- [ ] `VariantAttribute` = `{ attribute, value, type }` — بدون id
- [ ] استخدام `*_currencies` — لا `price_usd` flat
- [ ] `country: string`

### UI صفحة المنتج

- [ ] picker من `attributes_map` + فلترة بـ `shop_variants`
- [ ] مطابقة `attribute` + `value`
- [ ] عرض سعر قبل/بعد خصم + badge
- [ ] `delivery_time` مرة واحدة
- [ ] `quantity` + `max_purchase_quantity`
- [ ] `isPurchasableVariant` قبل «أضف للسلة»
- [ ] صور fallback

### سلة / طلب

- [ ] `shop_product_variant_id` = `shop_variants[].id`
- [ ] `POST /orders/preview` قبل checkout
- [ ] لا اعتماد على `POST /cart/items`

### ما هو مُنجز (لا تعيد)

- [x] Nav · CMS · فئات · فلاتر · تسجيل · طلب سريع
- [x] picker من shop_variants (تحقق فقط من شكل attributes)
- [x] formatApiPrice · platform variant · country string

---

## 18) مراجع

| الملف | المحتوى |
|-------|---------|
| [`product-variants-storefront-update.md`](./product-variants-storefront-update.md) | ملخص Flutter + Web |
| [`product-variants-web.md`](./product-variants-web.md) | أمثلة React |
| [`LATEST_UPDATES.md`](../LATEST_UPDATES.md) | كل التحديثات |
| [`api/ORDER_USER_FLOW.md`](../api/ORDER_USER_FLOW.md) | فلو الطلب |
| [`api/ADMIN_PRODUCT_VARIANTS_UPDATE_DELETE.md`](../api/ADMIN_PRODUCT_VARIANTS_UPDATE_DELETE.md) | Admin CRUD |
| Backend code | `ShopVariantResource.php` · `OneResource.php` · `ProductVariant.php` |

---

**الباك جاهز — الأولوية في الويب: types الخصم + badge + التأكد من مطابقة picker بـ attribute/value.**
