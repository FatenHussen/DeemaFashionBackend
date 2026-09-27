# الداشبورد — عروض: أول طلب، شراء بقيمة، إنشاء حساب

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull`**  
> ويب: [`WEB_PROMOTION_TRIGGERS.md`](./WEB_PROMOTION_TRIGGERS.md)  
> Flutter: [`FLUTTER_PROMOTION_TRIGGERS.md`](./FLUTTER_PROMOTION_TRIGGERS.md)

العرض = **شرط** + **مكافأة**.  
المكافأة مو نقاط بس: خصم، أو توصيل مجاني، أو هدية.

---

## 1) الأنواع

| الشرط | المكافأة | `type` | حقول إضافية |
|--------|----------|--------|-------------|
| أول طلب | خصم | `first_order_discount` | `discount_value` + `discount_type` |
| أول طلب | توصيل مجاني | `first_order_free_shipping` | — |
| أول طلب | هدية | `first_order_gift` | `gift_description.ar` + `gift_description.en` |
| شراء بقيمة | خصم | `spend_x_discount` | `min_spend` + `discount_value` + `discount_type` |
| شراء بقيمة | توصيل مجاني | `spend_x_get_free_shipping` | `min_spend` |
| شراء بقيمة | هدية | `spend_x_get_gift` | `min_spend` + `gift_description` |
| أنشأ حساب | خصم | `signup_discount` | `discount_value` + `discount_type` |
| أنشأ حساب | توصيل مجاني | `signup_free_shipping` | — |
| أنشأ حساب | هدية | `signup_gift` | `gift_description.ar` + `gift_description.en` |

`discount_type`: `percentage` أو `fixed`.

حقول النموذج حسب النوع: `GET /api/admin/promotions/fields-for-type/{type}`

النقاط تبقى خياراً منفصلاً عند الشراء بقيمة: `spend_x_get_points` + `reward_points`. مو المكافأة الوحيدة.

---

## 2) أمثلة

أول طلب وخصم 10%:

```json
{
  "name": { "ar": "خصم أول طلب", "en": "First order discount" },
  "description": { "ar": "خصم 10% على أول طلب", "en": "10% off the first order" },
  "type": "first_order_discount",
  "position": "top",
  "is_active": true,
  "discount_value": 10,
  "discount_type": "percentage"
}
```

شراء بـ 250 وتوصيل مجاني:

```json
{
  "name": { "ar": "توصيل مجاني فوق 250", "en": "Free delivery over 250" },
  "description": { "ar": "توصيل مجاني عند الشراء بقيمة 250", "en": "Free delivery when you spend 250" },
  "type": "spend_x_get_free_shipping",
  "position": "top",
  "is_active": true,
  "min_spend": 250
}
```

إنشاء حساب وهدية:

```json
{
  "name": { "ar": "هدية الحساب الجديد", "en": "New account gift" },
  "description": { "ar": "أنشئ حساباً واحصل على هدية", "en": "Create an account and get a gift" },
  "type": "signup_gift",
  "position": "top",
  "is_active": true,
  "gift_description": { "ar": "كوب هدية", "en": "Gift mug" }
}
```

---

## 3) متى تنطبق

| النوع | السلوك |
|--------|--------|
| أول طلب | تلقائي إذا ما عنده طلب سابق. الطلب الملغى ما يُحسب. |
| شراء بقيمة | تلقائي (أو يختاره الزبون إذا كان خصماً `spend_x_discount`) لما يبلغ `min_spend`. |
| إنشاء حساب | تلقائي على أول طلب إذا الحساب اتنشأ خلال `starts_at` / `ends_at`. |

الداشبورد يحفظ العرض. التطبيق والموقع يطبّقونه وحدهم. ما في حقل جديد على الطلب غير الموجود: `promotion_discount` ولقطة `automatic_promotions_snapshot`.
