# إظهار / إخفاء أقسام الصفحة — Dashboard

> **آخر تحديث | Last Updated:** 2026-08-29  
> **الشاشة:** `/sections/pages/details/{pageId}`  
> **Base:** `/api/admin` + Bearer admin token  
> **حالة الداشبورد:** مُنفَّذ — أيقونة العين في `PageDetails.tsx` (DeemaDashboard)

---

## الفكرة

بدل **حذف** قسم من الصفحة ثم **إعادة إضافته** لاحقًا، الأدمن يضغط **أيقونة عين** لإخفاء القسم مؤقتًا أو إظهاره مجددًا.

| قبل | بعد |
|-----|-----|
| DELETE ثم POST من جديد | Toggle `is_active` بنقرة واحدة |
| فقدان الترتيب والإعدادات | القسم يبقى في الصفحة مع `order` و `filters` وكل الإعدادات |

---

## السلوك

| `is_active` | في الداشبورد | في الموقع / التطبيق |
|-------------|--------------|---------------------|
| `true` | يظهر في القائمة (عين مفتوحة) | يُرسل في User API |
| `false` | يظهر في القائمة (عين مغلقة + تمييز بصري) | **لا يُرسل** — مخفي تمامًا |

> القسم المخفي **لا يُحذف** من `page_sections`. يمكن إعادة تفعيله في أي وقت.

---

## Endpoints

| Method | Endpoint | الاستخدام |
|--------|----------|-----------|
| GET | `/api/admin/pages/{pageId}` | جلب الصفحة + كل الأقسام (مع `is_active`) |
| POST | `/api/admin/toggle-status` | **موصى به** — toggle سريع من أيقونة العين |
| PATCH | `/api/admin/page-sections/{id}` | بديل — تحديث `is_active` مباشرة |
| GET | `/api/admin/page-sections/pages/{pageId}/preview` | معاينة كما يراها المستخدم (**نشطة فقط**) |

---

## استجابة الصفحة

```http
GET /api/admin/pages/1
Authorization: Bearer {admin_token}
```

```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": { "ar": "الرئيسية", "en": "Home" },
    "slug": "home",
    "sections": [
      {
        "id": 10,
        "name": { "ar": "بانرات", "en": "Banners" },
        "section_id": 3,
        "type": "manual",
        "manual_model": "banner",
        "order": 1,
        "layout": "slider",
        "variant": "horizontal",
        "is_default": false,
        "is_active": true
      },
      {
        "id": 12,
        "name": { "ar": "عروض", "en": "Offers" },
        "section_id": 7,
        "type": "api",
        "api_method": "products",
        "order": 2,
        "is_active": false
      }
    ]
  }
}
```

**مهم:** قائمة الأقسام في تفاصيل الصفحة تعرض **الكل** (نشطة + مخفية). Preview يعرض **النشطة فقط**.

---

## Toggle — الطريقة الموصى بها

```http
POST /api/admin/toggle-status
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "type": "page_section",
  "id": 12,
  "is_active": 0
}
```

| الحقل | القيمة |
|-------|--------|
| `type` | `"page_section"` (ثابت) |
| `id` | `page_section.id` — **ليس** `section_id` |
| `is_active` | `1` أو `true` = إظهار · `0` أو `false` = إخفاء |

**رد النجاح:**

```json
{
  "success": true,
  "message": "تم تحديث الحالة بنجاح",
  "data": {
    "id": 12,
    "type": "page_section",
    "is_active": false
  }
}
```

---

## Toggle — بديل PATCH

```http
PATCH /api/admin/page-sections/12
Authorization: Bearer {admin_token}
Content-Type: application/json

{ "is_active": false }
```

---

## واجهة المستخدم (UI)

### مكان الأيقونة

في صف **كل قسم** داخل `/sections/pages/details/{pageId}` — بجانب أزرار التعديل / الحذف / السحب.

### حالات العين

| الحالة | الأيقونة | Tooltip (مثال) |
|--------|----------|----------------|
| `is_active: true` | `Eye` / عين مفتوحة | «إخفاء القسم» |
| `is_active: false` | `EyeOff` / عين مغلقة | «إظهار القسم» |

### تمييز القسم المخفي

- opacity ~60% على صف القسم، **أو**
- badge صغير: `Hidden` / `مخفي`

### تفاعل النقر

1. Optimistic UI — غيّر الأيقونة فورًا
2. `POST /api/admin/toggle-status` مع القيمة المعكوسة
3. عند الخطأ — ارجع الحالة السابقة + toast خطأ

```tsx
async function toggleSectionVisibility(section: PageSection) {
  const next = !section.is_active;
  setSections((prev) =>
    prev.map((s) => (s.id === section.id ? { ...s, is_active: next } : s))
  );

  try {
    await api.post("/admin/toggle-status", {
      type: "page_section",
      id: section.id,
      is_active: next ? 1 : 0,
    });
  } catch {
    setSections((prev) =>
      prev.map((s) =>
        s.id === section.id ? { ...s, is_active: section.is_active } : s
      )
    );
    toast.error("تعذّر تحديث حالة القسم");
  }
}
```

---

## Checklist التنفيذ

- [x] اقرأ `is_active` من `GET /api/admin/pages/{id}` → `data.sections[]`
- [x] أضف أيقونة عين لكل صف قسم
- [x] Toggle عبر `POST /api/admin/toggle-status` (`type: page_section`)
- [x] استخدم `page_section.id` — **ليس** `section_id`
- [x] أظهر القسم المخفي في القائمة مع تمييز بصري
- [x] Preview (`GET .../preview`) — لا تتوقع ظهور الأقسام المخفية
- [x] **لا تحذف** القسم لإخفائه — استخدم العين فقط

---

## أخطاء شائعة

| الخطأ | السبب | الحل |
|-------|-------|------|
| Toggle لا يؤثر على الموقع | استخدمت `section_id` بدل `page_section.id` | استخدم `sections[].id` من استجابة الصفحة |
| القسم يختفي من الداشبورد | فلترة `is_active` في القائمة | اعرض **كل** الأقسام؛ ميّز المخفية فقط |
| 422 على toggle-status | `type` خاطئ | `"page_section"` بالضبط |

---

## مراجع

- Toggle API عام: [`../api/TOGGLE_STATUS_API.md`](../api/TOGGLE_STATUS_API.md)
- Page Builder كامل: [`../frontend/dashboard.md`](../frontend/dashboard.md) §1
- سلوك الويب (لا تغيير مطلوب): [`web.md`](./web.md)
