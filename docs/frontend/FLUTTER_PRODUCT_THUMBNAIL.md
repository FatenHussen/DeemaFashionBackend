# Flutter — عرض الصورة المصغرة للمنتج

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/user` — **ما في endpoint جديد**  
> ويب: [`WEB_PRODUCT_THUMBNAIL.md`](./WEB_PRODUCT_THUMBNAIL.md)  
> الداشبورد (الرفع): [`DASHBOARD_PRODUCT_THUMBNAIL.md`](./DASHBOARD_PRODUCT_THUMBNAIL.md)

منتج بمصغّرة وبدون صور معرض كان يبين بلا صورة. الباك صار يرجّع رابط المصغّرة داخل الحقول اللي الكرت والمعرض يقرؤوها.

الرابط كامل (`https://.../storage/...`). لا تلصقوا `storage/` مرة ثانية. الحقل اسمه `path` مو `url`.

---

## 1) وين

| الشاشة | الحقل |
|--------|--------|
| كرت القائمة، الأقسام، البحث، «يُشترى معه» | `image` |
| صفحة المنتج — المعرض | `images[].path` |
| متغيّر بلا صور خاصة والمعرض فاضي | `shop_variants[].images[].path` |

`thumbnail` موجود لحاله. الكرت يقدر يقرأ `image` مباشرة.

---

## 2) الرد

قائمة `GET /api/user/products` — مصغّرة فقط:

```json
{
  "image": "https://example.com/storage/product/uuid.jpg",
  "thumbnail": "https://example.com/storage/product/uuid.jpg"
}
```

صفحة `GET /api/user/products/{id}` — مصغّرة فقط:

```json
{
  "thumbnail": "https://example.com/storage/product/uuid.jpg",
  "images": [
    { "id": null, "path": "https://example.com/storage/product/uuid.jpg" }
  ],
  "shop_variants": [
    {
      "has_variant_images": false,
      "images": [
        { "id": null, "path": "https://example.com/storage/product/uuid.jpg" }
      ]
    }
  ]
}
```

| الحالة | `image` (القائمة) | `images` (الصفحة) |
|--------|-------------------|-------------------|
| مصغّرة فقط | رابطها | عنصر واحد `path` = المصغّرة |
| في معرض | أول صورة معرض | صور المعرض، المصغّرة تبقى في `thumbnail` |
| لا صور | `null` | `[]` |

---

## 3) السلوك

ترتيب المعرض ما تغيّر:

1. `shopVariants[i].images` إذا `hasVariantImages == true`
2. وإلا `product.images`
3. إذا الاثنين فاضيين: `product.thumbnail`

```dart
String? productPhoto(Map product, Map? selected) {
  final variantImages = (selected?['has_variant_images'] == true)
      ? (selected?['images'] as List?)
      : null;
  if (variantImages != null && variantImages.isNotEmpty) {
    return variantImages.first['path'] as String?;
  }

  final images = product['images'] as List?;
  if (images != null && images.isNotEmpty) {
    return images.first['path'] as String?;
  }

  return product['thumbnail'] as String? ?? product['image'] as String?;
}
```

كرت القائمة:

```dart
Image.network(product['image'] ?? product['thumbnail'] ?? '')
```

`id: null` على عنصر المصغّرة طبيعي. اعرضوا `path` مثل أي صورة معرض. إذا الرابط `null` خلّوا placeholder، ولا تعملوا request على مسار فاضي.

---

## 4) Checklist

- [ ] منتج بمصغّرة فقط: الكرت يعرض `image`
- [ ] صفحة المنتج تعرض `images[0].path` مو مربع فاضي
- [ ] متغيّر بلا صور خاصة يورث نفس `path`
- [ ] ما في لصق `storage/` على رابط يبدأ بـ `http`
- [ ] ما تستخدموا `images[].url` — هذا حقل الداشبورد
