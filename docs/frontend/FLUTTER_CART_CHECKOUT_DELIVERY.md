# Flutter — السلة: أقل طلب + طريقة التوصيل

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 8 تشرين الأول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> ويب: [`WEB_CART_CHECKOUT_DELIVERY.md`](./WEB_CART_CHECKOUT_DELIVERY.md)  
> الداشبورد: [`DASHBOARD_CART_CHECKOUT_DELIVERY.md`](./DASHBOARD_CART_CHECKOUT_DELIVERY.md)  
> هوية ديما: [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md) — المشروع منفصل عن تيك مارت

السلة قائمة واحدة. تحتها ثلاثة سطور من `checkout`: أقرب توصيل، أقل طلب، وسعر التوصيل. بعدها خيار توصيل واحد للطلب كله.

`product.deliveryTime` (مثل «3–5 أيام») يبقى على **صفحة المنتج** فقط.  
هذا مو جدولة السلة (`startDate` + `deliveryTime` بصيغة `HH:mm`).

---

## 1) السلة

`GET /api/user/cart`

```json
{
  "items": [],
  "checkout": {
    "source": "tikmart",
    "shop_id": null,
    "min_order_amount": { "amount": 25, "currency": "USD", "symbol": "$", "formatted": "$ 25.00" },
    "subtotal": { "amount": 17, "currency": "USD", "symbol": "$", "formatted": "$ 17.00" },
    "remaining_amount": { "amount": 8, "currency": "USD", "symbol": "$", "formatted": "$ 8.00" },
    "can_checkout": false,
    "delivery_min_hours": 24,
    "delivery_max_hours": 48,
    "earliest_delivery_at": "2026-09-28 22:50",
    "instant_only": false,
    "fulfillment": null,
    "delivery_price": { "amount": 9, "currency": "USD", "symbol": "$", "formatted": "$ 9.00" },
    "message": "أقل طلب $ 25.00. باقي $ 8.00."
  }
}
```

| السطر | المصدر |
|--------|--------|
| أقرب توصيل | `deliveryMinHours`–`deliveryMaxHours`، مثل «24–48 ساعة» |
| أقل قيمة للطلب | `minOrderAmount.formatted` |
| سعر التوصيل | `deliveryPrice.formatted` |

السعر من مسافة العنوان الافتراضي. نفس المنطقة: رسوم المنطقة. تحت 5 كم × 1.5، من 5 لأقل من 8 × 2، من 8 وفوق × 3. `amount == 0`: «مجاني» (`is_free_delivery` على كل المتاجر). `deliveryPrice == null`: ما في عنوان، أخفوا السطر. المسافة ما بتغيّر الساعات.

المطعم لحاله توصيل فوري. `instant_only: true` و`fulfillment: "restaurant_or_tikmart"` (مفتاح API قديم = مطعم أو عامل **ديما**). أخفوا منتقي اليوم والوقت واعرضوا «توصيل فوري». التوصيل من سائق المطعم أو عامل توصيل ديما فاشن، والزبون ما بيختار. أرسلوا `delivery_choice: "asap"`.

مع منصة ديما أو متجر: طلب واحد، أكبر حد على مجموع السلة، وأطول مدة بين المنصة والمتجر. ساعات المطعم ما بتطوّل النافذة. السعر من أبعد متجر توصيله مو مجاني.

- لا تقسّموا القائمة حسب المتجر.
- `canCheckout == false`: عطّلوا زر الإتمام واعرضوا `message` كما رجعت.
- `message == null`: الزر يشتغل.
- `source` (`tikmart` = منصة ديما، أو `shop`) للمعرفة فقط. **في الواجهة اكتبوا «ديما فاشن» / «المنصة» مو «تيك مارت».** لا تعملوا منه أقسام.

اعرضوا `formatted`. الأرقام محوّلة لعملة المستخدم.

---

## 2) طريقة التوصيل

بعد `canCheckout == true`. إذا `instantOnly` الخيار الوحيد فوري: `delivery_choice: "asap"`.

إذا `instantOnly` مو `true`، خيار واحد:

1. **أقرب وقت ممكن.** بدون منتقي ساعة. `delivery_choice: "asap"`.
2. **يوم ووقت.** `delivery_choice: "scheduled"` و`scheduled_delivery_at` بصيغة `yyyy-MM-dd HH:mm`.  
   ارفضوا وقتاً قبل `earliestDeliveryAt`.

`is_instant_delivery: true` = أقرب وقت، و`false` = يوم ووقت. استخدموا `delivery_choice`.

---

## 3) العنوان والدفع والتأكيد

`POST /api/user/orders/preview` يرجع `checkout` ومعه `delivery_error` إذا الموعد أبكر من المسموح. عندها `can_checkout` يصير `false`.

`POST /api/user/orders`

```dart
// أقرب وقت
{ "delivery_choice": "asap", "address_id": 1, "items": [] }

// يوم ووقت
{
  "delivery_choice": "scheduled",
  "scheduled_delivery_at": "2026-09-29 16:30",
  "address_id": 1,
  "items": []
}
```

`422` والرسالة في `message` إذا المجموع تحت الحد، أو الموعد قبل `earliest_delivery_at`. لا تحفظوا الطلب محلياً كأنه نجح.

---

## 4) الملخص وكرت الطلب

| الحقل | العرض |
|--------|--------|
| `delivery_choice_label` | «أقرب وقت ممكن» أو `2026-09-29 16:30` |
| `delivery_choice` | `asap` أو `scheduled` |
| `scheduled_delivery_at` | الساعة، أو `null` |

اعرضوا `deliveryChoiceLabel` في الملخص وعلى كرت الطلب.

---

## 5) Checklist

- [ ] سلة واحدة + أقرب توصيل + أقل طلب + `deliveryPrice`
- [ ] `amount == 0` يعني مجاني، و`null` يخفي السطر
- [ ] الزر يتعطل مع `message` لما `canCheckout` يكون `false`
- [ ] مطعم لحاله: `instantOnly` وفوري، بدون منتقي يوم ووقت
- [ ] `asap` بدون ساعة
- [ ] يوم ووقت بعد `earliestDeliveryAt` فقط، والحقل `scheduled_delivery_at`
- [ ] الملخص من `deliveryChoiceLabel`
- [ ] `product.deliveryTime` مو بالسلة، وجدولة السلة ما تتغير
