# الداشبورد — تاريخ ووقت جدولة سلة الزبون

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull` ثم `php artisan migrate`**  
> ويب: [`WEB_BASKET_SCHEDULE_DATE_TIME.md`](./WEB_BASKET_SCHEDULE_DATE_TIME.md)  
> Flutter: [`FLUTTER_BASKET_SCHEDULE_DATE_TIME.md`](./FLUTTER_BASKET_SCHEDULE_DATE_TIME.md)

عرض فقط. الزبون يختار اليوم والساعة من الموقع أو التطبيق.  
الأدمن **ما يعدّل** التاريخ ولا الوقت من هذه الشاشة.

`start_date` هنا = **تاريخ التوصيل** (يوم واحد)، مو بداية فترة.  
`delivery_time` = الساعة داخل ذلك اليوم (`HH:mm`). قديمة بدون ساعة ترجع `null`.

ما إلها علاقة بـ:

| الشاشة | الحقل |
|--------|--------|
| فورم المنتج | `delivery_time` نص «3–5 أيام» |
| كرت الطلب | `scheduled_delivery_at` — موعد تحدده الإدارة على الطلب |

---

## الفهرس

1. [وين](#1-وين)
2. [الرد](#2-الرد)
3. [العرض](#3-العرض)
4. [Checklist](#4-checklist)

---

## 1) وين

قائمة جداول سلال الزبائن + التفاصيل:

```http
GET /api/admin/user-basket-schedules
GET /api/admin/user-basket-schedules/{id}
Authorization: Bearer {admin_token}
```

صلاحية العرض: `userbasketschedule.view`.

تفعيل / إيقاف الجدولة كما هو (`POST .../enable` و `.../disable`). لا endpoint لتعديل الساعة.

---

## 2) الرد

على عنصر القائمة وعلى التفاصيل:

```json
{
  "id": 14,
  "name": "أسبوعي",
  "is_active": true,
  "start_date": "2026-10-21",
  "delivery_time": "16:30",
  "next_run_date": "2026-10-21",
  "schedule": {
    "id": 2,
    "name": "أسبوعي",
    "interval_days": 7
  }
}
```

| الحقل | العرض |
|--------|--------|
| `start_date` | تاريخ التوصيل · `Y-m-d` |
| `delivery_time` | `HH:mm` أو فراغ |
| `next_run_date` | التوصيل التالي (تاريخ). الساعة نفسها `delivery_time` |
| `schedule.name` | التكرار |
| `schedule.interval_days` | كل كم يوم |

---

## 3) العرض

| العمود | المصدر |
|--------|--------|
| التكرار | `schedule.name` |
| تاريخ التوصيل | `start_date` — **لا** تسموه Start Date |
| وقت التوصيل | `delivery_time` · إذا `null` اتركوا الخلية فاضية أو «—» |
| التوصيل التالي | `next_run_date` وبجانبها الساعة إن وُجدت |

مثال خلية: `2026-10-21 · 16:30`.

لا فورم تاريخ-من / تاريخ-إلى. لا time picker للأدمن على هذا المسار.

---

## 4) Checklist

- [ ] القائمة والتفاصيل يقرآن `start_date` كتاريخ توصيل
- [ ] عمود أو سطر `delivery_time`
- [ ] `null` ما ينعرض كـ `00:00`
- [ ] لا خلط مع مدة توصيل المنتج ولا مع `scheduled_delivery_at` على الطلب
- [ ] لا شاشة تعديل للساعة — القراءة والتشغيل/الإيقاف فقط
