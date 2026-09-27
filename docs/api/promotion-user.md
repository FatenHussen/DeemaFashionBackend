# Promotion — واجهة المستخدم (User API)

> مرجع API المحدّث: [`USER_PROMOTIONS_API.md`](USER_PROMOTIONS_API.md)

الكنترولر: `app\Http\Controllers\User\PromotionController.php`  
المورد: `app\Http\Resources\Promotion\AllResource.php`

**Base URL:** المسار ضمن مجموعة `user` في `routes/api/user.php`.

---

## قائمة العروض النشطة

### `GET /api/user/promotions`

يعيد العروض التي **`is_active`** مع نافذة **`starts_at` / `ends_at`** الصالحة (نطاق `scopeActive` على الموديل).

### Query parameters

| المعامل | الوصف |
|---------|--------|
| **`page_slug`** | اختياري. إذا وُجد يجب أن يكون **`slug`** موجوداً في جدول **`pages`**. عند التمرير: تُعاد العروض **المرتبطة بهذه الصفحة** أو العروض **غير المرتبطة بأي صفحة** (ظهور عام). إذا لم يُرسل: تُعاد **كل** العروض النشطة دون فلترة الصفحة. |

### التحقق

- `page_slug`: `nullable`, `string`, `max:255`, `exists:pages,slug` — قيمة غير صالحة ترجع **422** مع أخطاء التحقق.

### تحميل العلاقات

- يتم تحميل **`pages`** لإرجاع **`page_slugs`** في المورد.

### شكل عنصر العرض (`AllResource`)

```json
{
  "id": 1,
  "name": { "ar": "…", "en": "…" },
  "description": { "ar": "…", "en": "…" },
  "type": "simple_discount",
  "is_active": true,
  "created_at": "2026-05-01 12:00",
  "page_slugs": ["home", "products"]
}
```

- **`page_slugs`:** يظهر عند تحميل العلاقة؛ قائمة **`slug`** للصفحات المرتبطة بالعرض عبر pivot **`page_promotion`**.

### استجابة الـ Controller

يستخدم `sendResponse` من `Controller`:

```json
{
  "status": true,
  "message": "…",
  "data": [ … ]
}
```

حيث **`data`** هي مجموعة موارد العروض.

---

## أنواع العروض (مرجع)

تُعرَّف في طلبات الأدمن والسيرفس؛ أمثلة: `simple_discount`, `spend_x_discount`, `spend_x_get_gift`, `spend_x_get_points`, `free_shipping`, `spend_x_get_free_shipping`, `first_order_discount`, `first_order_free_shipping`, `first_order_gift`, `signup_discount`, `signup_free_shipping`, `signup_gift`.

عروض أول الطلب وإنشاء الحساب تُطبَّق تلقائياً على الطلب (خصم / توصيل مجاني / هدية) ولا تُمرَّر عبر `promotion_id`.

---

## مراجع

- الموديل: `app\Models\Promotion.php` (`pages()`, `scopeActive`, `scopeForPageSlug`)
- الهجرة: `database/migrations/2026_05_02_150000_create_page_promotion_table.php`
