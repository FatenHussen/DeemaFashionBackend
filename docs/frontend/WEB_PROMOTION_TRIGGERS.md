# الويب — عروض أول الطلب وإنشاء الحساب والشراء بقيمة

> **أرسلوا هذا الملف لفريق الويب.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_PROMOTION_TRIGGERS.md`](./DASHBOARD_PROMOTION_TRIGGERS.md)  
> Flutter: [`FLUTTER_PROMOTION_TRIGGERS.md`](./FLUTTER_PROMOTION_TRIGGERS.md)

العرض التلقائي بينطبق لحاله على المعاينة والطلب.  
لا ترسلوا `promotion_id` لهالعروض.

`promotion_id` يبقى لخصم يختاره الزبون: `simple_discount` أو `spend_x_discount`.

---

## 1) وين يظهر

معاينة الطلب (`preview`) والطلب بعد الإنشاء.

| المكافأة | من وين تقرأوها |
|----------|----------------|
| خصم | `discounts.promotion_discount` و `automatic_promotions.discounts[]` |
| توصيل مجاني | `automatic_promotions.free_shipping_applies` = `true` و `delivery_price` = 0 |
| هدية | `automatic_promotions.gifts[]` |

عنصر الخصم التلقائي:

```json
{
  "promotion_id": 12,
  "type": "first_order_discount",
  "discount": 10,
  "discount_type": "percentage",
  "discount_value": "10.00",
  "name": { "ar": "خصم أول طلب", "en": "First order discount" }
}
```

عنصر الهدية:

```json
{
  "promotion_id": 14,
  "type": "signup_gift",
  "gift_description": { "ar": "كوب هدية", "en": "Gift mug" },
  "promotion_name": { "ar": "هدية الحساب", "en": "Account gift" }
}
```

`type` الممكن تلقائياً:

- `first_order_discount` / `first_order_free_shipping` / `first_order_gift`
- `signup_discount` / `signup_free_shipping` / `signup_gift`
- `spend_x_get_free_shipping` / `spend_x_get_gift`
- `free_shipping`

اعرضوا سطر المكافأة في ملخص الدفع إذا المصفوفة فيها عناصر، أو إذا `free_shipping_applies` صحيح.  
المجموع الجاهز في `total`. لا تعيدوا حساب الخصم.

---

## 2) مين بياخذه

| الشرط | الزبون |
|--------|--------|
| أول طلب | ما عنده طلب سابق. الملغى ما يُحسب. |
| إنشاء حساب | حسابه اتنشأ خلال فترة العرض، وهالطلب أول طلب. |
| شراء بقيمة | مجموع الأصناف المؤهلة ≥ `min_spend`. |

إذا الشرط ما تحقق، المفاتيح ترجع فاضية و `free_shipping_applies` = `false`.
