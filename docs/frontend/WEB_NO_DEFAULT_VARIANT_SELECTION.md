# الويب — لا اختيار افتراضي للمتغيّر

> **أرسلوا هذا الملف لفريق الويب.**  
> **آخر تحديث:** 5 تشرين الثاني 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز — ما في endpoint جديد**  
> Flutter: [`FLUTTER_NO_DEFAULT_VARIANT_SELECTION.md`](./FLUTTER_NO_DEFAULT_VARIANT_SELECTION.md)  
> داشبورد (توضيح): [`DASHBOARD_NO_DEFAULT_VARIANT_SELECTION.md`](./DASHBOARD_NO_DEFAULT_VARIANT_SELECTION.md)  
> صور المعرض: [`PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md`](./PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md)  
> كل المتغيّرات: [`WEB_PRODUCT_ALL_VARIANTS.md`](./WEB_PRODUCT_ALL_VARIANTS.md)

المشكلة: الموقع يختار `shop_variants[0]` تلقائياً → المستخدم يضغط «أضف للسلة» بدون ما ينتبه للون/المقاس.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [أول دخول للصفحة](#2-أول-دخول-للصفحة)
3. [المعرض](#3-المعرض)
4. [السلة والرسالة](#4-السلة-والرسالة)
5. [منتج بلا متغيّرات](#5-منتج-بلا-متغيّرات)
6. [غلط vs صح](#6-غلط-vs-صح)
7. [Checklist](#7-checklist)

---

## 1) القاعدة

| شرط | السلوك |
|-----|--------|
| `attributes_map.length > 0` | **لا** اختيار افتراضي. `selectedVariant = null` |
| المستخدم ما كمّل لون/مقاس | معرض المنتج + رسالة عند السلة |
| اختار تركيبة موجودة في `shop_variants` | حدّثوا السعر/الكمية/الصور من هالصف |
| `attributes_map` فاضي | استخدموا `shop_variants[0]` كالعادة |

وجود `shop_variants[0]` في الـ API **ما يعني** اختيار تلقائي على الواجهة.

---

## 2) أول دخول للصفحة

```ts
const hasAttributes = (product.attributes_map?.length ?? 0) > 0;

let selectedAttrs: Record<string, string> = {}; // فاضي
let selectedVariant = hasAttributes
  ? null
  : (product.shop_variants?.[0] ?? null);

// لا:
// selectedVariant = product.shop_variants[0]
// selectedColor = attributes_map[0].options[0]
```

بعد كل ضغطة صفة:

```ts
selectedVariant = findVariant(product.shop_variants, selectedAttrs);
// findVariant يرجع null إذا الاختيار ناقص أو التركيبة غير موجودة
```

عند تغيير اللون: إذا المقاس الحالي غير متاح، **امسحوا المقاس** (أو خلّوه فارغ) — لا تفرضوا أول مقاس تلقائياً إذا بدكم إجبار اختيار صريح. المهم: لا تخلّوا `canAddToCart = true` قبل اكتمال الاختيار.

---

## 3) المعرض

```ts
function galleryFor(
  product: { images?: { path: string }[]; thumbnail?: string | null },
  selected?: { has_variant_images?: boolean; images?: { path: string }[] } | null
) {
  if (selected?.has_variant_images && selected.images?.length) {
    return selected.images;
  }
  if (product.images?.length) return product.images;
  return product.thumbnail ? [{ path: product.thumbnail }] : [];
}

// أول دخول (selectedVariant = null) → صور المنتج / thumbnail
setGallery(galleryFor(product, selectedVariant));
```

1. أول تحميل → `data.images` (أو `thumbnail`).
2. بعد اختيار متغيّر فيه صور → `selected.images`.
3. متغيّر بلا صور خاصة (`has_variant_images === false`) → ارجعوا لصور المنتج.

---

## 4) السلة والرسالة

```ts
const selectionComplete =
  !hasAttributes ||
  (product.attributes_map ?? []).every(
    (a) => Boolean(selectedAttrs[a.attribute])
  );

const canAddToCart = Boolean(
  selectionComplete &&
  selectedVariant?.id &&
  selectedVariant?.shop_id &&
  (selectedVariant.quantity ?? 0) > 0
);

function onAddToCart() {
  if (hasAttributes && !selectionComplete) {
    toast.error("يجب اختيار المتغيرات"); // أو النص من الترجمة عندكم
    return;
  }
  if (!canAddToCart) return;
  // POST /api/user/cart/items
  // { shop_product_variant_id: selectedVariant.id, quantity }
}
```

| حالة | العرض |
|------|--------|
| متغيّرات + ما اختار | زر معطل **أو** مفعّل مع توست «يجب اختيار المتغيرات» |
| اختار تركيبة غير موجودة | لا تضيفوا — أعيدوا ضبط الصفة المتعارضة |
| `id` / `shop_id` = `null` | الصفحة تعرض — السلة معطّلة |

---

## 5) منتج بلا متغيّرات

```ts
if (!hasAttributes) {
  selectedVariant = product.shop_variants?.[0] ?? null;
  // لا picker — أضيفوا مباشرة إذا canAddToCart
}
```

---

## 6) غلط vs صح

**غلط:**

```ts
const selected = product.shop_variants[0]; // افتراضي أول متغيّر
addToCart(selected.id);                   // بدون ما المستخدم يختار
```

**صح:**

```ts
const selected = hasAttributes
  ? findVariant(product.shop_variants, selectedAttrs)
  : product.shop_variants?.[0] ?? null;

if (hasAttributes && !selected) {
  toast.error("يجب اختيار المتغيرات");
  return;
}
addToCart(selected.id);
```

---

## 7) Checklist

- [ ] منتج بمتغيّرات: أول فتح بلا لون/مقاس مختار
- [ ] المعرض الابتدائي = صور المنتج / thumbnail
- [ ] اختيار المتغيّر يبدّل الصور إذا `has_variant_images`
- [ ] «أضف للسلة» بدون اختيار كامل → رسالة واضحة
- [ ] السلة فقط بـ `shop_variants[].id` **المختار** — مو دائماً `[0]`
- [ ] منتج بلا `attributes_map` → `shop_variants[0]` بدون picker
