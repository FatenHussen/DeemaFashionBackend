# Deema Fashion Docs

هيكل نظيف — أرسل لكل فريق **ملف واحد** فيه **كل** التعديلات.

**نظرة شاملة من أول استنساخ حتى الوضع الحالي (بدون تواريخ):** [`FULL_PROJECT_OVERVIEW.md`](./FULL_PROJECT_OVERVIEW.md)

**تقرير شخصي للإدارة — شو اشتغلتِ (ويب + داشبورد، بدون تواريخ):** [`تقرير_عملي_ويب_وداشبورد.txt`](./تقرير_عملي_ويب_وداشبورد.txt)

## للفرونت (نسخ نهائية شاملة)

| الفريق | الملف | يشمل |
|--------|--------|------|
| داشبورد | [`frontend/dashboard.md`](./frontend/dashboard.md) | Page Builder · أقسام · Nav · منتج · **طلب سريع (إعدادات + صفحات الظهور)** · استيراد Excel · … |
| ويب | [`frontend/web.md`](./frontend/web.md) | Nav · أقسام · فئات · منتج · تسجيل · فلاتر · **طلب سريع حسب الصفحة** · نص تحميل · أسعار · **آخر تحديث 2026-08-26** |
| Flutter | [`frontend/flutter.md`](./frontend/flutter.md) | Nav · أقسام layout/variant · فئات دائرية · فلاتر · منتج/سلة · تسجيل · أسعار · **طلب سريع حسب الصفحة** · **آخر تحديث 2026-08-26** |

## إظهار / إخفاء أقسام الصفحة (Eye toggle)

| الفريق | الملف |
|--------|--------|
| داشبورد | [`page-sections/dashboard.md`](./page-sections/dashboard.md) — أيقونة عين · `POST /toggle-status` · UI checklist |
| ويب | [`page-sections/web.md`](./page-sections/web.md) — لا تغيير UI · الباك يستبعد الأقسام المخفية |

## طلبات مخصصة (Custom Orders)

التفصيل الكامل للطلب السريع (فلو الأدمن + ويب + Flutter + إعدادات القسم):

| الفريق | الملف |
|--------|--------|
| داشبورد | [`custom-orders/dashboard.md`](./custom-orders/dashboard.md) — convert/cancel **+ Settings: تفعيل + صفحات الظهور + شكل القسم** |
| ويب | [`custom-orders/web.md`](./custom-orders/web.md) — قسم حسب `page_slugs` + فلو الإنشاء · **آخر تحديث 2026-08-26** |
| Flutter | [`custom-orders/flutter.md`](./custom-orders/flutter.md) — قسم حسب `page_slugs` + فلو الإنشاء · **آخر تحديث 2026-08-26** |

### تحديث مهم (26 آب 2026) — صفحات ظهور الطلب السريع

- المحتوى والشكل يبقى من **Settings** (ليس Page Builder).
- مفتاح جديد: `quick_order_page_ids` — الأدمن يختار صفحات الظهور.
- العميل يقرأ `data.quick_order.page_ids` + `page_slugs` من `GET /api/user/settings`.
- الافتراضي = صفحة `home` فقط.

## دفع Stripe (بطاقة)

| الملف | لمن |
|--------|-----|
| [`api/STRIPE_PAYMENTS.md`](./api/STRIPE_PAYMENTS.md) | باك + ويب + Flutter — إعداد المفاتيح، Webhook، تدفق الدفع، أمثلة كود |

## مرجع API

ملفات المرجع التفصيلي في [`api/`](./api/).

### Base URLs

- Admin: `/api/admin` + Bearer admin token  
- User: `/api/user`  
- Vendor: `/api/vendor`

### شكل الرد

```json
{ "success": true, "message": "...", "data": {} }
```

---

> أرشيف الملفات القديمة: `_archive_backup_2026_08_26/` — احذفوه بعد التأكد.
