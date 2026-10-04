# إظهار / إخفاء أقسام الصفحة — Web (User Website)

> **آخر تحديث | Last Updated:** 2026-08-29  
> **Base:** `/api/user`  
> **Auth:** عام (بدون توكن لأقسام الصفحات)

---

## ملخص سريع

**لا يوجد عمل UI جديد مطلوب على الويب.**

عندما يخفي الأدمن قسمًا من الداشبورد (`is_active: false`)، الباك **لا يرسل** هذا القسم في User API. موقع الويب يستمر بعرض ما يصله فقط — بدون فلترة إضافية.

---

## Endpoints (بدون تغيير)

| الغرض | Endpoint |
|-------|----------|
| أقسام صفحة عامة | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة | `GET /api/user/categories/{categoryId}/page` → `data.sections` |

---

## ماذا يحدث في الباك؟

```
Admin: is_active = false على page_section
         ↓
User API: يستبعد القسم قبل بناء الرد
         ↓
Web: يستلم sections[] بدون القسم المخفي
```

فلترة الباك (`PageSectionPresentationService`):

- `page_sections.is_active = true` فقط
- `sections.is_active = true` (مكتبة القسم نفسها)

---

## ما **لا** يصل للويب

| الحقل | Admin API | User API |
|-------|-----------|----------|
| `is_active` على `page_section` | ✅ يُرسل | ❌ القسم كاملًا غير موجود في المصفوفة |
| أقسام مخفية | تظهر في قائمة الداشبورد | **لا تظهر أبدًا** |

User API **لا** يرجّع `is_active` على القسم — الأقسام المخفية **مستبعدة** من `data[]` / `data.sections[]`.

---

## مثال

**Admin** — صفحة `home` فيها 3 أقسام، الثاني مخفي:

| order | name | is_active (admin) |
|-------|------|-------------------|
| 1 | Banners | true |
| 2 | Offers | **false** |
| 3 | Products | true |

**Web** — نفس الطلب:

```http
GET /api/user/sections?page_slug=home
Accept-Language: ar
```

```json
{
  "status": true,
  "data": [
    { "id": 10, "order": 1, "name": { "ar": "بانرات", "en": "Banners" }, "items": [...] },
    { "id": 14, "order": 3, "name": { "ar": "منتجات", "en": "Products" }, "items": [...] }
  ]
}
```

القسم `order: 2` **غير موجود** — لا تحاول عرض «فجوة» أو placeholder.

---

## Checklist الويب

- [ ] **لا** تضف فلتر `is_active` — الباك يتولى ذلك
- [ ] **لا** تخزّن cache طويل لأقسام الصفحة بدون invalidation عند نشر الداشبورد
- [ ] اعتمد على `order` للترتيب بين الأقسام **المرسلة**
- [ ] تجاهل أقسام `items` فارغة (كما هو حاليًا)
- [ ] نفس مكوّن الأقسام لـ `sections` و `categories/{id}/page`

---

## Cache (إن وُجد)

إذا كان الويب ي cache استجابات الأقسام:

| الاستراتيجية | ملاحظة |
|--------------|--------|
| ISR / SWR revalidate | مناسب — يظهر/يختفي القسم بعد revalidate |
| Cache طويل بدون invalidation | قد يظهر قسمًا مخفيًا حتى انتهاء TTL |

**التوصية:** revalidate قصير لصفحات Page Builder، أو invalidation عند deploy.

---

## Preview (Admin فقط — للعلم)

```http
GET /api/admin/page-sections/pages/{pageId}/preview
```

هذا endpoint **للداشبورد** — يطابق ما يراه المستخدم (أقسام نشطة فقط). الويب **لا** يستدعيه.

---

## مراجع

- Page Builder على الويب: [`../frontend/web.md`](../frontend/web.md) §2
- تنفيذ الداشبورد (Eye toggle): [`dashboard.md`](./dashboard.md)
- Toggle API: [`../api/TOGGLE_STATUS_API.md`](../api/TOGGLE_STATUS_API.md)
