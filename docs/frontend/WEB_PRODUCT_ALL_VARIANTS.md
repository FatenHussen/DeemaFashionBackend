# الويب — متغيّران على صفحة المنتج (الاثنين يشتغلوا)

مرجع دوائر اللون وربطها بالكتالوج للويب والتطبيق: [`PRODUCT_COLOR_SWATCHES.md`](./PRODUCT_COLOR_SWATCHES.md).

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_PRODUCT_ALL_VARIANTS.md`](./DASHBOARD_PRODUCT_ALL_VARIANTS.md)  
> Flutter: [`FLUTTER_PRODUCT_ALL_VARIANTS.md`](./FLUTTER_PRODUCT_ALL_VARIANTS.md)

الأدمن يضيف **أزرق S** و **أسود L**. الموقع لازم يفعّل **الاثنين** — مو `shop_variants[0]` فقط.  
ما في endpoint جديد: `GET /api/user/products/{id}`.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [شكل `shop_variants`](#2-شكل-shop_variants)
3. [اختيار اللون / المقاس](#3-اختيار-اللون--المقاس)
4. [السلة](#4-السلة)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القاعدة

| | المعنى |
|--|--------|
| صف في `shop_variants` | تركيبة واحدة (لون + مقاس) + سعر + كمية |
| `id` | `shop_product_variant_id` — **هوية السلة** |
| `variant_id` | `product_variants.id` |
| `is_active: false` من الداش | الصف **ما يرجع** من الـ API |

لا تولّدوا Cartesian (كل لون × كل مقاس). اعرضوا **فقط** التركيبات الموجودة.

مثال الأدمن أضاف صفّين فقط:

| اختيار المستخدم | النتيجة |
|-----------------|---------|
| أزرق + S | صف 1 — يشتغل |
| أسود + L | صف 2 — يشتغل |
| أزرق + L أو أسود + S | **ما في صف** — لا تخلوا المقاس عالق |

عند تغيير اللون: إذا المقاس الحالي غير موجود لهاللون، اختاروا أول مقاس متاح.

---

## 2) شكل `shop_variants`

```http
GET /api/user/products/{id}
Accept-Language: ar
```

```json
{
  "attributes_map": [
    {
      "id": 10,
      "attribute": "لون",
      "type": "color",
      "values": ["أزرق", "أسود"],
      "options": [
        { "id": 40, "name": "أزرق", "hex": "#0000FF" },
        { "id": 41, "name": "أسود", "hex": "#000000" }
      ]
    },
    {
      "id": 11,
      "attribute": "قياس",
      "type": "square",
      "values": ["S", "L"],
      "options": [
        { "id": 31, "name": "S", "hex": null },
        { "id": 32, "name": "L", "hex": null }
      ]
    }
  ],
  "shop_variants": [
    {
      "id": 881,
      "variant_id": 91,
      "sku": "JEANS-BLUE-S",
      "attributes": [
        { "id": 40, "attribute": "لون", "value": "أزرق", "type": "color", "hex": "#0000FF" },
        { "id": 31, "attribute": "قياس", "value": "S", "type": "square", "hex": null }
      ],
      "quantity": 10,
      "shop_id": 1,
      "has_variant_images": true,
      "images": [{ "id": 44, "path": "https://…/blue.webp" }]
    },
    {
      "id": 882,
      "variant_id": 92,
      "sku": "JEANS-BLACK-L",
      "attributes": [
        { "id": 41, "attribute": "لون", "value": "أسود", "type": "color", "hex": "#000000" },
        { "id": 32, "attribute": "قياس", "value": "L", "type": "square", "hex": null }
      ],
      "quantity": 10,
      "shop_id": 1,
      "has_variant_images": false,
      "images": [{ "id": 10, "path": "https://…/main.webp" }]
    }
  ]
}
```

- لون الـ swatch: `hex` — بلا `hex` الراديو يطلع فاضي (هذا كان شكل «الأسود ما ينضغط»).
- معرض الصور: إذا `has_variant_images` استخدموا `images[].path` وإلا صور المنتج — [`PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md`](./PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md).
- `id` أو `shop_id` = `null` → اعرضوا السعر **ولا** تفعّلوا السلة.

---

## 3) اختيار اللون / المقاس

المطابقة بـ **`attributes[].id`** أو `attributes_map[].options[].id` (ثابت بعد إعادة التسمية).  
`attribute` + `value` يشتغل كمان، والاسم يتحدّث بعد إعادة التسمية.

```ts
function findVariant(
  variants: ShopVariant[],
  selected: Record<string, string> // { "لون": "أسود", "قياس": "L" }
) {
  const entries = Object.entries(selected).filter(([, v]) => v);
  if (!entries.length) return null;
  return (
    variants.find((v) =>
      entries.every(([attr, val]) =>
        (v.attributes ?? []).some((a) => a.attribute === attr && a.value === val)
      )
    ) ?? null
  );
}

function sizesForColor(variants: ShopVariant[], colorName: string) {
  return variants
    .filter((v) =>
      (v.attributes ?? []).some((a) => a.type === "color" && a.value === colorName)
    )
    .flatMap((v) =>
      (v.attributes ?? []).filter((a) => a.type === "square").map((a) => a.value)
    );
}

function onColorChange(color: string) {
  const sizes = sizesForColor(shopVariants, color);
  if (!sizes.includes(selectedSize)) selectedSize = sizes[0] ?? "";
  selectedColor = color;
  setGallery(galleryFor(product, findVariant(shopVariants, {
    لون: selectedColor,
    قياس: selectedSize,
  })));
}
```

`attributes_map` لبناء الـ picker. القيم المعطّلة = غير موجودة في `shop_variants` مع الاختيار الحالي.

---

## 4) السلة

```ts
const canAddToCart = Boolean(
  selected?.id &&
  selected?.shop_id &&
  (selected.quantity ?? 0) > 0
);
```

```http
POST /api/user/cart/items
```

```json
{ "shop_product_variant_id": 882, "quantity": 1 }
```

`shop_product_variant_id` = `shop_variants[].id` المختار (882 للأسود L) — **مو دائماً** `[0].id`.

---

## 5) غلط vs صح

**غلط:**

```ts
const variant = product.shop_variants[0];
<img src={product.images[0].path} />
selectedSize = "S"; // ثابت بعد تغيير اللون لأسود
```

**صح:**

```ts
const variant = findVariant(product.shop_variants, selected);
if (variant?.has_variant_images) gallery = variant.images;
addToCart(variant.id); // 881 أو 882 حسب الاختيار
```

---

## 6) Checklist

- [ ] لا تستخدموا `shop_variants[0]` إلا كـ fallback لمنتج بلا صفات
- [ ] عدد الصفوف على الشاشة = طول `shop_variants` (الفعّالة)
- [ ] تغيير اللون يعيد المقاس إذا التركيبة غير موجودة
- [ ] swatch اللون من `hex`
- [ ] السلة: `id` + `shop_id` + `quantity > 0` للمتغيّر **المختار**
- [ ] المعرض يتبدل مع المتغيّر إذا `has_variant_images`
- [ ] بعد `git pull` + إعادة حفظ المنتج من الداش: جربوا أزرق S **و** أسود L
