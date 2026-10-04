# Stripe — دفع إلكتروني بالبطاقة

تكامل كامل لطلبات المستخدم (`POST /api/user/orders`) عبر **Stripe PaymentIntent**.

## نظرة عامة على التدفق

```
1) GET  /api/user/payment-methods     → اختر method code = stripe
2) POST /api/user/orders              → يُنشأ الطلب is_paid=false + PaymentIntent
3) الفرونت يؤكد الدفع بـ client_secret (Stripe.js / flutter_stripe)
4) Stripe يرسل webhook → السيرفر يضع is_paid=true
5) (اختياري) GET /api/user/orders/{id}/payment-status للمزامنة
```

---

## إعداد السيرفر (مرة واحدة)

### 1) حساب Stripe

1. سجّل على [https://dashboard.stripe.com](https://dashboard.stripe.com)
2. ابقَ على **Test mode** أولاً
3. Developers → API keys:
   - `Publishable key` → `STRIPE_PUBLISHABLE_KEY`
   - `Secret key` → `STRIPE_SECRET_KEY`

### 2) متغيرات `.env`

```env
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_CURRENCY=usd
STRIPE_AUTOMATIC_PAYMENT_METHODS=true
```

### 3) Migrate + Seed

```bash
php artisan migrate
php artisan db:seed --class=PaymentMethodSeeder
```

سيظهر صف `payment_methods.code = stripe` (Card / Stripe).

### 4) Webhook

**محلياً (Stripe CLI):**

```bash
stripe login
stripe listen --forward-to http://127.0.0.1:8000/api/webhooks/stripe
```

انسخ `whsec_...` إلى `STRIPE_WEBHOOK_SECRET`.

**إنتاج:** Dashboard → Developers → Webhooks → Add endpoint:

- URL: `https://YOUR_DOMAIN/api/webhooks/stripe`
- Events:
  - `payment_intent.succeeded`
  - `payment_intent.payment_failed`
  - `payment_intent.canceled`
  - `payment_intent.processing`

---

## Endpoints

| Method | Path | Auth | وصف |
|--------|------|------|-----|
| GET | `/api/user/payments/stripe/config` | user | `publishable_key`, `currency`, `enabled` |
| POST | `/api/user/orders` | user | إنشاء طلب؛ إن كانت الطريقة Stripe يُرجع `data.payment.client_secret` |
| POST | `/api/user/orders/{orderId}/pay` | user | إعادة إنشاء / استرجاع PaymentIntent |
| GET | `/api/user/orders/{orderId}/payment-status` | user | مزامنة حالة الدفع من Stripe |
| POST | `/api/webhooks/stripe` | لا (توقيع Stripe) | تأكيد الدفع |

### مثال رد إنشاء طلب Stripe

```json
{
  "status": true,
  "data": {
    "id": 120,
    "is_paid": false,
    "payment_status": "pending",
    "requires_payment": true,
    "payment_method": { "id": 4, "name": "Card (Stripe)", "code": "stripe" },
    "payment": {
      "client_secret": "pi_xxx_secret_yyy",
      "payment_intent_id": "pi_xxx",
      "publishable_key": "pk_test_...",
      "payment_status": "requires_action",
      "amount": 25.5,
      "currency": "USD",
      "is_paid": false
    }
  }
}
```

---

## ويب (Stripe.js)

```js
import { loadStripe } from '@stripe/stripe-js';

const stripe = await loadStripe(publishableKey);
const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
  payment_method: { card: cardElement },
});

if (!error) {
  // انتظر webhook أو استدعِ:
  // GET /api/user/orders/{id}/payment-status
}
```

أو Payment Element / Checkout حسب تصميمكم — المهم تمرير `client_secret` من الباك.

## Flutter (`flutter_stripe`)

```dart
Stripe.publishableKey = publishableKey;
await Stripe.instance.initPaymentSheet(
  paymentSheetParameters: SetupPaymentSheetParameters(
    paymentIntentClientSecret: clientSecret,
    merchantDisplayName: 'Deema',
  ),
);
await Stripe.instance.presentPaymentSheet();
// ثم GET payment-status
```

---

## قواعد مهمة

- `cash` → `is_paid=false` حتى التوصيل
- `syriatel` / `mtn_cash` → تُعلَّم مدفوعة عند الإنشاء (كما كان)
- `stripe` → **لا تُعلَّم مدفوعة** إلا بعد `payment_intent.succeeded`
- إلغاء الطلب يلغي PaymentIntent غير المكتمل
- المبالغ تُرسل لسترايب بعملة `STRIPE_CURRENCY` (افتراضي USD) من `orders.total`

---

## بطاقات تجريبية

| بطاقة | نتيجة |
|-------|--------|
| `4242 4242 4242 4242` | نجاح |
| `4000 0000 0000 9995` | رفض |
| أي تاريخ مستقبلي + CVC أي 3 أرقام | — |

---

## Checklist قبل الإنتاج

- [ ] مفاتيح **Live** (`sk_live_` / `pk_live_`)
- [ ] Webhook إنتاجي + سر التوقيع
- [ ] تفعيل العملة الصحيحة في Stripe Account
- [ ] اختبار 3D Secure
- [ ] عدم تسجيل `STRIPE_SECRET_KEY` في Git
