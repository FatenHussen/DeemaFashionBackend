# الويب — السلة والدفع والتوصيل (شاشتان حسب المواصفات)

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 21 تشرين الثاني 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> Flutter: [`FLUTTER_CART_CHECKOUT_UI.md`](./FLUTTER_CART_CHECKOUT_UI.md)  
> أقل طلب + نافذة التوصيل: [`WEB_CART_CHECKOUT_DELIVERY.md`](./WEB_CART_CHECKOUT_DELIVERY.md)  
> هوية ديما: [`WEB_BRAND_IDENTITY.md`](./WEB_BRAND_IDENTITY.md)

بعد «عرض السلة» شاشتان فقط — **إنشاء الطلب** ثم **الدفع والتوصيل**.  
لا صفحة «مراجعة» منفصلة. التأكيد من زر «تأكيد الطلب» في الخطوة 2.

هذا الملف يلغي تخطيط [`WEB_CHECKOUT_LAYOUT.md`](./WEB_CHECKOUT_LAYOUT.md) القديم (دفع مختصر + مراجعة).

---

## الفهرس

1. [الخطوة 1 — إنشاء الطلب (السلة)](#1-الخطوة-1--إنشاء-الطلب-السلة)
2. [الخطوة 2 — الدفع والتوصيل](#2-الخطوة-2--الدفع-والتوصيل)
3. [تأكيد الطلب](#3-تأكيد-الطلب)
4. [غلط vs صح](#4-غلط-vs-صح)
5. [Checklist](#5-checklist)

---

## 1) الخطوة 1 — إنشاء الطلب (السلة)

بعد اختيار المنتجات والضغط على **عرض السلة**.

### العناصر (سطر بسطر)

| # | العنصر | المصدر / السلوك |
|---|--------|------------------|
| 1 | جدول المنتجات: الاسم، الكمية، السعر، المتغيرات، SKU | `GET /api/user/cart` → `items[]` |
| 2 | تعديل الكمية | `PUT /api/user/cart/items/{id}` body: `{ "quantity": N }` |
| 3 | حذف منتج | `DELETE /api/user/cart/items/{id}` |
| 4 | إضافة منتجات جديدة | رجوع للكatalog ثم `POST /api/user/cart/items` |
| 5 | المجموع الكلي | `checkout.grand_total.formatted` |
| 6 | زر **انتقل للدفع والتوصيل** | ينتقل للخطوة 2 إذا `can_checkout == true` |

### شكل العنصر في `items[]`

```json
{
  "id": 12,
  "shop_product_variant_id": 101,
  "shop_id": 1,
  "shop": { "id": 1, "name": "متجر المنصة" },
  "quantity": 2,
  "note": null,
  "product": {
    "id": 55,
    "name": "فستان",
    "image": "https://..."
  },
  "sku": "DRS-RED-M",
  "stock_quantity": 8,
  "variant": ["اللون: أحمر", "المقاس: M"],
  "unit_price": { "amount": 20, "currency": "USD", "symbol": "$", "formatted": "$ 20.00" },
  "line_total": { "amount": 40, "currency": "USD", "symbol": "$", "formatted": "$ 40.00" }
}
```

اعرضوا `unit_price.formatted` و`line_total.formatted` و`variant` و`sku`.  
لا تقسّموا الجدول حسب المتجر في الخطوة 1.

### زر الانتقال

- `checkout.can_checkout == false` → عطّلوا الزر واعرضوا `checkout.message`.
- `true` → الخطوة 2.

---

## 2) الخطوة 2 — الدفع والتوصيل

### 2.1 العنوان

| الحالة | الواجهة |
|--------|---------|
| عنوان سابق واحد أو أكثر | اعرضوا العنوان الافتراضي (أو المختار) مباشرة |
| أكثر من عنوان | بجانب العنوان زر **اختر عنوان آخر** → اختيار موجود أو **إضافة عنوان جديد** |
| لا عنوان | خيار **إضافة عنوان** فقط |

```http
GET /api/user/addresses
POST /api/user/addresses
PUT /api/user/addresses/{id}
```

احفظوا `address_id` المختار للـ preview / create.  
عند تغيير العنوان: أعيدوا `POST /api/user/orders/preview` لتحديث أجور التوصيل.

### 2.2 وقت التوصيل — خياران

| الخيار | العرض | الإرسال |
|--------|--------|---------|
| **أ** أقرب وقت ممكن | بجانبه الوقت من الداشبورد: `delivery_min_hours`–`delivery_max_hours`، أو «فوري» إذا `instant_only` | `delivery_choice: "asap"` |
| **ب** الجدولة | زر **اختر التاريخ والوقت** | `delivery_choice: "scheduled"` + `scheduled_delivery_at: "Y-m-d H:i"` |

- مطعم لحاله (`instant_only: true`): أخفوا الجدولة، اعرضوا توصيل فوري، أرسلوا `asap` فقط.  
- الموعد المجدول يجب ألا يكون قبل `earliest_delivery_at`.  
- التفاصيل: [`WEB_CART_CHECKOUT_DELIVERY.md`](./WEB_CART_CHECKOUT_DELIVERY.md).

### 2.3 طريقة الدفع

```http
GET /api/user/payment-methods
```

اعرضوا الخيارات واحفظوا `payment_method_id`.

### 2.4 رصيدك الحالي (نقاط)

إذا عنده نقاط:

1. اعرضوا الرصيد من `GET /api/user/points/summary` → `points` + `value.estimated_value_formatted`.
2. زر **تطبيق**:
   - إن وُجد خصم جاهز من النقاط: `GET /api/user/active-benefits` → صف `key: "point_coupon_exchange_id"`.
   - وإلا: استبدال أولاً `POST /api/user/points/exchange/coupon` ثم جلب `active-benefits`.
3. مرّروا `point_coupon_exchange_id` في الـ preview.

لا تخصموا النقاط يدوياً من الرصيد في الواجهة — الباك يطبّق الخصم عبر الـ exchange.

### 2.5 وفر على طلبك (كود الحسم)

- حقل **ادخل كود الحسم** + زر **تطبيق**.
- مرّروا `coupon: "CODE"` في `POST /api/user/orders/preview`.
- لا تجمعوا كوبون + نقاط خصم معاً (الباك يرجع تعارض).

### 2.6 تفاصيل الطلب

بعد كل تغيير (عنوان، توصيل، كوبون، نقاط، طريقة دفع) استدعوا:

```http
POST /api/user/orders/preview
```

اعرضوا من `order_details`:

| سطر الواجهة | الحقل |
|-------------|--------|
| المتجر المطلوب منه | `order_details.stores[].name` (أسماء مفصولة إن أكثر من متجر) |
| المبلغ الجزئي | `order_details.subtotal.formatted` |
| الضريبة | `order_details.tax.formatted` (حالياً دائماً `$ 0.00` — الباك بلا ضريبة) |
| الرصيد المستخدم من النقاط | `order_details.points_discount.formatted` |
| حسم الكود | `order_details.coupon_discount.formatted` |
| أجور التوصيل | `order_details.delivery_fees.formatted` |
| المجموع الكلي | `order_details.grand_total.formatted` |

### 2.7 زر تأكيد الطلب

يستدعي `POST /api/user/orders` بنفس جسم الـ preview (+ `items` من السلة).

---

## 3) تأكيد الطلب

```json
{
  "address_id": 1,
  "payment_method_id": 2,
  "delivery_choice": "asap",
  "coupon": "SAVE10",
  "point_coupon_exchange_id": null,
  "items": [
    { "shop_product_variant_id": 101, "quantity": 2 }
  ]
}
```

أو مجدول:

```json
{
  "delivery_choice": "scheduled",
  "scheduled_delivery_at": "2026-11-22 16:30",
  "address_id": 1,
  "payment_method_id": 2,
  "items": []
}
```

`items` من `cart.items` (`shop_product_variant_id` + `quantity` + `note` إن وُجد).  
`422` → اعرضوا `message`، لا تنشئوا الطلب محلياً.

بعد نجاح `POST /orders` الباك **يمسح** أسطر السلة التي طُلبت (`cart_items`).  
امسحوا الكاش المحلي للسلة أيضاً، ثم `GET /api/user/cart` يرجع `items: []`.

---

## 4) غلط vs صح

**غلط**

```text
ثلاث خطوات: سلة → دفع مختصر → مراجعة
تفاصيل الطلب مركّبة يدوياً من الحقول القديمة فقط
تطبيق نقاط = طرح رقم من الواجهة بدون exchange
```

**صح**

```text
خطوتان فقط
تفاصيل الطلب من order_details بعد preview
تطبيق نقاط عبر point_coupon_exchange_id
الضريبة من order_details.tax (0 إلى حين دعم الباك)
```

---

## 5) Checklist

- [ ] الخطوة 1: جدول (كمية · سعر · متغيرات · SKU) + تعديل/حذف/إضافة
- [ ] الخطوة 1: `grand_total` + زر الانتقال مع احترام `can_checkout`
- [ ] الخطوة 2: عنوان (عرض / اختر آخر / أضف)
- [ ] الخطوة 2: أقرب وقت أو جدولة (مطعم = فوري فقط)
- [ ] الخطوة 2: طرق الدفع
- [ ] الخطوة 2: رصيد نقاط + تطبيق عبر exchange / active-benefits
- [ ] الخطوة 2: كود حسم + تطبيق عبر preview
- [ ] الخطوة 2: تفاصيل الطلب السبعة من `order_details`
- [ ] زر تأكيد → `POST /orders` بنفس الحقول
- [ ] بعد النجاح: امسحوا كاش السلة محلياً (الباك يفرّغ `cart_items`)

**الباك جاهز — الموقع يتبع هذا الملف.**
