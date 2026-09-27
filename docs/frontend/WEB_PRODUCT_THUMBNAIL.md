# الويب — عرض الصورة المصغرة للمنتج

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/user` — **ما في endpoint جديد**  
> Flutter: [`FLUTTER_PRODUCT_THUMBNAIL.md`](./FLUTTER_PRODUCT_THUMBNAIL.md)  
> الداشبورد (الرفع): [`DASHBOARD_PRODUCT_THUMBNAIL.md`](./DASHBOARD_PRODUCT_THUMBNAIL.md)

منتج بمصغّرة وبدون صور معرض كان يبين بلا صورة. الباك صار يرجّع رابط المصغّرة داخل الحقول اللي الكرت والمعرض يقرؤوها.

الرابط كامل (`https://.../storage/...`). لا تلصقوا `/storage` مرة ثانية. على الموقع الحقل اسمه `path` مو `url`.

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

1. `shop_variants[i].images` إذا `has_variant_images === true`
2. وإلا `product.images`
3. إذا الاثنين فاضيين: `product.thumbnail`

```jsx
const gallery =
  selected?.has_variant_images && selected.images?.length
    ? selected.images
    : product.images?.length
      ? product.images
      : product.thumbnail
        ? [{ path: product.thumbnail }]
        : [];

<img src={gallery[0]?.path} alt="" />
```

كرت القائمة:

```jsx
<img src={product.image || product.thumbnail} alt="" />
```

`id: null` على عنصر المصغّرة طبيعي. اعرضوا `path` مثل أي صورة معرض.

---

## 4) Checklist

- [ ] منتج بمصغّرة فقط: الكرت يعرض `image`
- [ ] صفحة المنتج تعرض `images[0].path` مو مربع فاضي
- [ ] متغيّر بلا صور خاصة يورث نفس `path`
- [ ] ما في لصق `/storage` على رابط يبدأ بـ `http`
- [ ] ما تستخدموا `images[].url` — هذا حقل الداشبورد
