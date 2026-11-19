# الويب — تدفق التسجيل

> **أرسلوا هذا الملف لفريق الويب.**  
> الدليل الكامل (ويب + Flutter): [`REGISTER_FLOW.md`](./REGISTER_FLOW.md)  
> **آخر تحديث:** 16 أيلول 2026  
> Base: `/api/user` — بدون توكن حتى OTP

```
فورم تسجيل → POST /auth/register (200 بدون توكن)
     → شاشة OTP → POST /auth/verify-otp → احفظوا data.token
```

إعادة إرسال الكود: `POST /auth/login` بنفس الهاتف وكلمة المرور (403 متوقع).  
**لا** تستخدموا `/auth/send-otp` هنا (هذا لنسيت كلمة المرور).

**مؤقت (16 أيلول):** ما في SMS. على شاشة OTP أدخلوا **`00000`**.

| الحقل | مطلوب |
|--------|--------|
| `name` · `phone` · `password` | نعم |
| `email` · `city_id` · `governorate_id` | لا — لا ترسلوا `""`؛ احذفوا المفتاح أو `null` |

`phone`: أرقام فقط. كلمة المرور: ≥ 8 + صغير + كبير + رقم + رمز.

مدن: `GET /api/user/cities?governorate_id={id}` — الباراميتر إلزامي.

التفاصيل والأمثلة والأخطاء: [`REGISTER_FLOW.md`](./REGISTER_FLOW.md).
