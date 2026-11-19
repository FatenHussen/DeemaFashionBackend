# Flutter — لا اختيار افتراضي للمتغيّر

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 5 تشرين الثاني 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز — ما في endpoint جديد**  
> ويب: [`WEB_NO_DEFAULT_VARIANT_SELECTION.md`](./WEB_NO_DEFAULT_VARIANT_SELECTION.md)  
> داشبورد (توضيح): [`DASHBOARD_NO_DEFAULT_VARIANT_SELECTION.md`](./DASHBOARD_NO_DEFAULT_VARIANT_SELECTION.md)  
> صور المعرض: [`PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md`](./PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md)  
> كل المتغيّرات: [`FLUTTER_PRODUCT_ALL_VARIANTS.md`](./FLUTTER_PRODUCT_ALL_VARIANTS.md)

المشكلة: التطبيق يختار `shopVariants.first` تلقائياً → المستخدم يضيف للسلة بدون ما ينتبه للون/المقاس.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [أول فتح للشاشة](#2-أول-فتح-للشاشة)
3. [المعرض](#3-المعرض)
4. [السلة والرسالة](#4-السلة-والرسالة)
5. [منتج بلا متغيّرات](#5-منتج-بلا-متغيّرات)
6. [غلط vs صح](#6-غلط-vs-صح)
7. [Checklist](#7-checklist)

---

## 1) القاعدة

| شرط | السلوك |
|-----|--------|
| `attributesMap` مش فاضي | **لا** اختيار افتراضي. `selectedVariant = null` |
| اختيار ناقص | صور المنتج + رسالة عند السلة |
| تركيبة كاملة مطابقة | سعر/كمية/صور من الصف المختار |
| `attributesMap` فاضي | `shopVariants.first` بدون picker |

`shop_variants` دائماً فيها عنصر واحد على الأقل من الباك — **لا** تستخدموا هالـ fallback كاختيار UI لمنتج بصفات.

---

## 2) أول فتح للشاشة

```dart
final hasAttributes = product.attributesMap.isNotEmpty;

Map<String, String> selectedAttrs = {}; // فاضي
ShopVariant? selectedVariant = hasAttributes
    ? null
    : product.shopVariants.firstOrNull;

// لا:
// selectedVariant = product.shopVariants.first;
```

بعد كل اختيار صفة استدعوا `findVariant`. إذا رجع `null` خلّوا المعرض على صور المنتج.

عند تغيير اللون: إذا المقاس الحالي غير متاح، امسحوا المقاس بدل فرض أول مقاس — حتى ما يصير إضافة بدون انتباه.

---

## 3) المعرض

```dart
List<String> galleryFor(Product product, ShopVariant? selected) {
  if (selected?.hasVariantImages == true &&
      (selected!.images?.isNotEmpty ?? false)) {
    return selected.images!.map((e) => e.path).toList();
  }
  if (product.images.isNotEmpty) {
    return product.images.map((e) => e.path).toList();
  }
  final thumb = product.thumbnail;
  return thumb != null ? [thumb] : [];
}
```

1. أول فتح → صور المنتج / thumbnail.  
2. متغيّر فيه صور → صور المتغيّر.  
3. `hasVariantImages == false` → صور المنتج.

---

## 4) السلة والرسالة

```dart
bool get selectionComplete {
  if (!hasAttributes) return true;
  return product.attributesMap.every(
    (a) => (selectedAttrs[a.attribute] ?? '').isNotEmpty,
  );
}

bool get canAddToCart =>
    selectionComplete &&
    selectedVariant?.id != null &&
    selectedVariant?.shopId != null &&
    (selectedVariant?.quantity ?? 0) > 0;

Future<void> onAddToCart() async {
  if (hasAttributes && !selectionComplete) {
    // SnackBar / dialog
    showMessage('يجب اختيار المتغيرات');
    return;
  }
  if (!canAddToCart) return;
  await cartApi.add(
    shopProductVariantId: selectedVariant!.id!,
    quantity: qty,
  );
}
```

---

## 5) منتج بلا متغيّرات

```dart
if (!hasAttributes) {
  selectedVariant = product.shopVariants.firstOrNull;
}
```

---

## 6) غلط vs صح

**غلط:**

```dart
final selected = product.shopVariants.first;
await addToCart(selected.id!);
```

**صح:**

```dart
final selected = hasAttributes
    ? findVariant(product.shopVariants, selectedAttrs)
    : product.shopVariants.firstOrNull;

if (hasAttributes && selected == null) {
  showMessage('يجب اختيار المتغيرات');
  return;
}
await addToCart(selected!.id!);
```

---

## 7) Checklist

- [ ] منتج بمتغيّرات: أول فتح بلا اختيار
- [ ] المعرض الابتدائي = صور المنتج / thumbnail
- [ ] بعد الاختيار تتبدل الصور عند `hasVariantImages`
- [ ] Add to cart بدون اختيار كامل → رسالة
- [ ] `shopProductVariantId` من الصف المختار مو دائماً `.first`
- [ ] بلا `attributesMap` → `.first` بدون picker
