# الداشبورد — عرض الصورة المصغرة بعد حفظ المنتج

> **أرسلوا هذا الملف لفريق الداشبورد.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/admin` + Admin token  
> الرفع ما تغيّر: الحقل يبقى `thumbnail` (ملف صورة في `multipart`)  
> الموقع: [`WEB_PRODUCT_THUMBNAIL.md`](./WEB_PRODUCT_THUMBNAIL.md)  
> Flutter: [`FLUTTER_PRODUCT_THUMBNAIL.md`](./FLUTTER_PRODUCT_THUMBNAIL.md)

الصورة المصغرة كانت تنحفظ، والمعاينة تبقى فاضية لأن الواجهة تقرأ صور المعرض (`images` / `media`) فقط. بعد الحفظ ارسموها من الرابط الراجع، مو من `<input type="file">` — المتصفح يفرّغ حقل الملف دائماً.

---

## 1) وين

| الشاشة | الصورة |
|--------|--------|
| جدول المنتجات | `image` |
| إنشاء / تعديل — خانة الصورة المصغرة | `thumbnail` |
| إنشاء / تعديل — معرض الصور | `images[].url` |

الروابط كاملة (`https://.../storage/...`). لا تضيفوا `/storage` مرة ثانية.

---

## 2) الرد

قائمة `GET /api/admin/products`:

```json
{
  "thumbnail": "https://example.com/storage/product/uuid.jpg",
  "image": "https://example.com/storage/product/uuid.jpg",
  "images": ["https://example.com/storage/product/uuid.jpg"]
}
```

تفاصيل `GET /api/admin/products/{id}`:

```json
{
  "thumbnail": "https://example.com/storage/product/uuid.jpg",
  "image": "https://example.com/storage/product/uuid.jpg",
  "images": [{ "id": null, "url": "https://example.com/storage/product/uuid.jpg" }]
}
```

| الحالة | `thumbnail` | `image` | `images` |
|--------|-------------|---------|----------|
| مصغّرة فقط، بدون معرض | رابطها | نفس الرابط | عنصر واحد = المصغّرة |
| في معرض | رابط المصغّرة | أول صورة معرض | صور المعرض فقط |
| لا مصغّرة ولا معرض | `null` | `null` | `[]` |

عنصر المصغّرة داخل `images` يجي `id: null`. لا ترسلوه ضمن `existing_media_ids`.

---

## 3) السلوك

- خانة الصورة المصغرة: `<img src={product.thumbnail} />` بعد `GET`. إذا `thumbnail` فاضي، الخانة فاضية.
- جدول المنتجات: `<img src={product.image} />`.
- معرض التعديل: ارسموا `images[].url`. إذا المعرض فاضي والمصغّرة موجودة، الباك حاططها داخل `images`.
- عند التعديل بدون تغيير المصغّرة: لا ترسلوا حقل `thumbnail`. القديمة تبقى.
- لتبديلها: أرسلوا ملف `thumbnail` الجديد فقط.

```text
POST /api/admin/products
thumbnail=<file>

PUT /api/admin/products/{id}
thumbnail=<file جديد>
```

---

## 4) Checklist

- [ ] بعد إنشاء منتج بمصغّرة فقط، الجدول يعرض `image`
- [ ] شاشة التعديل تعرض `thumbnail` كصورة، مو حقل ملف فاضي
- [ ] ما في لصق `/storage` على رابط يبدأ بـ `http`
- [ ] حفظ بدون ملف جديد لا يمسح المصغّرة (لا ترسلوا `thumbnail`)
- [ ] `images[].id === null` لا ينرسل في `existing_media_ids`
