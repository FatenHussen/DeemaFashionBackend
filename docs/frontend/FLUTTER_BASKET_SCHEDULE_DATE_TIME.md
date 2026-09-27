# Flutter — جدولة السلة: تاريخ واحد + وقت داخل اليوم

> **أرسلوا هذا الملف لفريق Flutter.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull` ثم `php artisan migrate`**  
> ويب: [`WEB_BASKET_SCHEDULE_DATE_TIME.md`](./WEB_BASKET_SCHEDULE_DATE_TIME.md)  
> داشبورد: [`DASHBOARD_BASKET_SCHEDULE_DATE_TIME.md`](./DASHBOARD_BASKET_SCHEDULE_DATE_TIME.md)  
> الطيّ الافتراضي: [`FLUTTER_SCHEDULE_UI_COLLAPSED.md`](./FLUTTER_SCHEDULE_UI_COLLAPSED.md)

الزبون يختار **يوم توصيل واحد** و**ساعة داخل اليوم**.  
ما في فترة، فما في لابل **Start Date**.  
زر الحفظ ينفّذ الطلب. ما يبقى `onPressed: null` بصمت.

مسار السلة: `POST /scheduled-baskets`.  
تأكيد السلة المخصصة ما زال `start_date` فقط.

لا تخلطوا:

| الحقل | الشاشة | المعنى |
|--------|--------|--------|
| `product.deliveryTime` | المنتج | نص «3–5 أيام» — مو داخل السلة |
| `scheduled_delivery_at` | كرت الطلب | موعد من الإدارة |
| `delivery_time` هنا | السلة المجدولة | `HH:mm` يختاره الزبون |

---

## الفهرس

1. [الشاشة](#1-الشاشة)
2. [الحفظ](#2-الحفظ)
3. [العرض](#3-العرض)
4. [Checklist](#4-checklist)

---

## 1) الشاشة

بعد زر «عرض تفاصيل الجدولة» واختيار الجدولة:

| العنصر | المطلوب |
|--------|---------|
| فوق التقويم | بلا Start Date |
| `CalendarDatePicker` / جدول الشهر | يوم واحد ≥ اليوم. لا تعكسوا الأعمدة في RTL |
| الوقت | `showTimePicker` أو حقل ساعة. التسمية: **وقت التوصيل** |
| الملخص | التكرار · تاريخ التوصيل · وقت التوصيل · التوصيل التالي مع نفس الساعة |
| زر الحفظ | `onPressed` شغال. أثناء الإرسال: loader. إذا قائمة التكرار فاضية: SnackBar، مو زر معطّل بلا سبب |

نصوص: تاريخ التوصيل / Delivery date · وقت التوصيل / Delivery time.

اليوم: ارفضوا وقتاً قبل `TimeOfDay.now()`. يوم لاحق: أي وقت.

`GET /schedules`: `name` نص. إذا وصل `{ar, en}` خذوا اللغة الحالية ثم `ar` ثم `en`. لا تحذفوا العنصر إذا الاسم مو `String` مباشرة.

---

## 2) الحفظ

```http
POST /api/user/scheduled-baskets
Authorization: Bearer {token}
```

```json
{
  "name": "أسبوعي",
  "schedule_id": 2,
  "is_active": true,
  "start_date": "2026-10-21",
  "delivery_time": "16:30",
  "items": [
    { "shop_product_variant_id": 25, "quantity": 2 }
  ]
}
```

| الحقل | القاعدة |
|--------|---------|
| `start_date` | `yyyy-MM-dd` · ≥ اليوم. يوم التوصيل، مو بداية مدى |
| `delivery_time` | `HH:mm` على 24 ساعة. الواجهة ترسله دائماً. الباك يقبله فاضياً للجدولات القديمة |
| `items` | سطر واحد على الأقل. يكفي `shop_product_variant_id` |
| `category_id` | لا ترسلوه |

```http
PUT /api/user/scheduled-baskets/{id}
```

يقبل `start_date` و `delivery_time` بنفس الصيغة.

422: تاريخ ماضٍ، أو وقت اليوم مضى (`وقت التوصيل لازم يكون من الآن فصاعداً`)، أو عنصر بلا `product_id` وبلا `shop_product_variant_id`.

### السلة المخصصة — بدون تغيير

```http
POST /api/user/schedules/{id}/custom-basket/confirm
```

```json
{ "confirm_schedule": true, "start_date": "2026-10-21" }
```

لا تضيفوا `delivery_time` هنا.

---

## 3) العرض

من `GET /scheduled-baskets` و `GET /scheduled-baskets/{id}`:

```dart
class ScheduledBasket {
  final String? startDate; // yyyy-MM-dd
  final String? deliveryTime; // HH:mm أو null
  final String? nextRunDate;
}
```

اعرضوا `21 Oct 2026 · 16:30`. إذا `deliveryTime == null` اعرضوا التاريخ فقط.

---

## 4) Checklist

- [ ] بلا Start Date
- [ ] يوم واحد + time picker
- [ ] زر الحفظ يستدعي `POST /scheduled-baskets`
- [ ] `delivery_time` بصيغة `HH:mm`
- [ ] لا `category_id`
- [ ] القائمة تعرض الساعة عند وجودها
- [ ] `confirm` المخصصة تبقى `start_date` فقط
