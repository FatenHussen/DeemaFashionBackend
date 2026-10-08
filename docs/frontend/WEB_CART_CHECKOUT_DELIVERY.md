# الويب — السلة: أقل طلب + طريقة التوصيل

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 8 تشرين الأول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> Flutter: [`FLUTTER_CART_CHECKOUT_DELIVERY.md`](./FLUTTER_CART_CHECKOUT_DELIVERY.md)  
> الداشبورد: [`DASHBOARD_CART_CHECKOUT_DELIVERY.md`](./DASHBOARD_CART_CHECKOUT_DELIVERY.md)  
> هوية ديما: [`WEB_BRAND_IDENTITY.md`](./WEB_BRAND_IDENTITY.md) — المشروع منفصل عن تيك مارت

السلة قائمة واحدة. تحتها ثلاثة سطور من `checkout`: أقرب توصيل، أقل طلب، وسعر التوصيل. بعدها خيار توصيل واحد للطلب كله.

`product.delivery_time` (مثل «3–5 أيام») يبقى على **صفحة المنتج** فقط. لا ترسموه في السلة.  
هذا مو جدولة السلة (`start_date` + `delivery_time` بصيغة `HH:mm`).

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
| أقرب توصيل | `delivery_min_hours` إلى `delivery_max_hours`، مثل «24–48 ساعة» |
| أقل قيمة للطلب | `min_order_amount.formatted` |
| سعر التوصيل | `delivery_price.formatted` |

سعر التوصيل من مسافة عنوان الزبون الافتراضي لمناطق المتاجر. نفس المنطقة: رسوم المنطقة. تحت 5 كم × 1.5، من 5 لأقل من 8 × 2، من 8 وفوق × 3. إذا كل المتاجر `is_free_delivery` السعر `0` واعرضوا «مجاني». إذا `delivery_price` هو `null` (ما في عنوان) أخفوا السطر. المعامل ما بيغيّر الساعات.

المطعم لحاله توصيل فوري. `instant_only: true` و`fulfillment: "restaurant_or_tikmart"` (مفتاح API = مطعم أو عامل **ديما**). أخفوا اختيار اليوم والوقت واعرضوا «توصيل فوري». التوصيل يصير من سائق المطعم أو من عامل توصيل ديما فاشن، بدون ما الزبون يختار بينهما. أرسلوا `delivery_choice: "asap"`.

سلة فيها مطعم مع منصة ديما أو متجر: الطلب واحد. أقل قيمة هي الأكبر على مجموع الكل، والمدة هي الأطول بين المنصة والمتجر. ساعات المطعم ما بتطوّل النافذة. السعر من أبعد متجر توصيله مو مجاني.

- قائمة المنتجات ما تنقسم حسب المتجر.
- `can_checkout: false`: عطّلوا إتمام الطلب واعرضوا `message` كما هي.
- `message: null` و`can_checkout: true`: الزر يشتغل.
- `source` للمعرفة فقط (`tikmart` = منصة ديما، أو `shop`). **في الواجهة: «ديما فاشن» / «المنصة» مو «تيك مارت».** لا تعملوا منه أقسام.

الأرقام جاهزة بعملة الزبون. اعرضوا `formatted`.

---

## 2) طريقة التوصيل

بعد ما `can_checkout` يصير `true`. إذا `instant_only` الخيار الوحيد هو أقرب وقت، وهو فوري للمطعم.

إذا `instant_only` مو `true`، خيار واحد للطلب:

1. **أقرب وقت ممكن.** ما في اختيار ساعة. أرسلوا `delivery_choice: "asap"`.
2. **يوم ووقت.** منتقي تاريخ وساعة. أرسلوا `delivery_choice: "scheduled"` و`scheduled_delivery_at` بصيغة `Y-m-d H:i`.  
   ارفضوا أي وقت قبل `earliest_delivery_at`.

`is_instant_delivery: true` ما زال يعني أقرب وقت، و`false` يعني يوم ووقت. الفضّلوا `delivery_choice`.

---

## 3) العنوان والدفع والتأكيد

`POST /api/user/orders/preview` يرجع نفس `checkout`، ومعه `delivery_error` إذا الموعد أبكر من المسموح. إذا `delivery_error` موجود، `can_checkout` يكون `false`.

`POST /api/user/orders`

أقرب وقت:

```json
{ "delivery_choice": "asap", "address_id": 1, "items": [] }
```

يوم ووقت:

```json
{
  "delivery_choice": "scheduled",
  "scheduled_delivery_at": "2026-09-29 16:30",
  "address_id": 1,
  "items": []
}
```

إذا المجموع أقل من الحد، أو الموعد أبكر من المهلة: `422` والرسالة في `message`. لا تنشئوا الطلب محلياً.

---

## 4) الملخص وكرت الطلب

من رد الطلب:

| الحقل | العرض |
|--------|--------|
| `delivery_choice_label` | «أقرب وقت ممكن» أو `2026-09-29 16:30` |
| `delivery_choice` | `asap` أو `scheduled` |
| `scheduled_delivery_at` | الساعة إذا انحددت، وإلا `null` |

اعرضوا `delivery_choice_label` في ملخص التأكيد وعلى كرت الطلب. لا تركّبوا النص من `product.delivery_time`.

---

## 5) Checklist

- [ ] السلة قائمة واحدة، وتحتها أقرب توصيل وأقل طلب وسعر التوصيل من `checkout`
- [ ] `delivery_price` مقدار `0` يظهر «مجاني»، و`null` يخفي السطر
- [ ] الزر يتعطل و`message` تظهر لما `can_checkout` يكون `false`
- [ ] مطعم لحاله: `instant_only` وفوري، بدون منتقي يوم ووقت
- [ ] أقرب وقت يرسل `asap` بدون ساعة
- [ ] يوم ووقت يرفض ما قبل `earliest_delivery_at` ويرسل `scheduled_delivery_at`
- [ ] الملخص يعرض `delivery_choice_label`
- [ ] `product.delivery_time` مو داخل السلة، وجدولة السلة ما تتغير
