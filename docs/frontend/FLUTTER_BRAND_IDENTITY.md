# Flutter — هوية ديما فاشن البصرية (منفصل عن تيك مارت)

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 8 تشرين الأول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> ويب: [`WEB_BRAND_IDENTITY.md`](./WEB_BRAND_IDENTITY.md)  
> داشبورد: [`DASHBOARD_BRAND_IDENTITY.md`](./DASHBOARD_BRAND_IDENTITY.md)  
> المرجع العام: [`DEEMA_BRAND_IDENTITY.md`](./DEEMA_BRAND_IDENTITY.md)

**المشروع صار ديما فاشن — منفصل عن تيك مارت / Tikmool.**  
لا تستخدموا شعار تيك مارت ولا ألوانه القديمة (فيروزي `#00aed1` / برتقالي `#FFA000`) ولا اسم TikMart / Tikmool في الواجهة.

---

## 1) الاسم والنصوص

| الاستخدام | القيمة |
|-----------|--------|
| الاسم EN | `Deema Fashion` |
| الاسم AR | `ديما فاشن` |
| Stripe / PaymentSheet | `merchantDisplayName: 'Deema'` |
| نطاق / دعم | `deemafashion.com` · `info@deemafashion.com` |

لا تكتبوا «تيك مارت» أو «تيكمول» في الشاشات أو الـ splash أو الإشعارات.

---

## 2) الألوان من الـ API (مصدر الحقيقة)

```http
GET /api/user/settings
Accept-Language: ar
```

```json
{
  "color": {
    "main_color": "#c720a4",
    "text_color": "#1F2937",
    "second_color": "#ff1493"
  },
  "dark_color": {
    "main_color": "#1a0a16",
    "text_color": "#fce7f3",
    "second_color": "#a020f0"
  },
  "welcome": { "image": ["https://…/welcome.png"], "text": "مرحبا" },
  "login": { "image": "https://…/logo.png", "link": "…" },
  "contact": {
    "phone": "+963…",
    "whatsapp": "…",
    "email": "info@deemafashion.com",
    "instagram": "https://instagram.com/deemafashion",
    "facebook": "https://facebook.com/deemafashion"
  }
}
```

| مفتاح | دور في التطبيق | افتراضي ديما |
|--------|----------------|--------------|
| `color.main_color` | أزرار أساسية · AppBar · روابط · محددات | `#c720a4` |
| `color.second_color` | تأكيد · CTA ثانوي · badges | `#ff1493` |
| `color.text_color` | نص أساسي فاتح | `#1F2937` |
| `dark_color.*` | نفس الأدوار في الوضع الداكن | انظر الجدول فوق |

**قاعدة:** ابنوا `ThemeData` من رد `settings` بعد الإقلاع. الثوابت أعلاه fallback فقط إذا المفتاح ناقص. لا تثبتوا ألوان تيك مارت في الكود.

---

## 3) صور العلامة

| مصدر | الاستخدام |
|------|-----------|
| `welcome.image` | شاشة الترحيب / onboarding |
| `login.image` | شاشة الدخول / الشعار |
| `quick_action.image` | زر الإجراء السريع إن وُجد |

لا تستخدموا أصول تيك مارت المحلية القديمة. إذا الصورة `null` → placeholder محايد باسم ديما، مو لوجو تيك مارت.

---

## 4) السلة والتوصيل — نصوص الواجهة

مفاتيح الـ API ما زالت `tikmart` / `tikmart_*` و`fulfillment: "restaurant_or_tikmart"` (توافق باك).  
**في الواجهة اعرضوا «ديما فاشن» أو «المنصة» — مو «تيك مارت».**

| قيمة API | نص للمستخدم |
|----------|-------------|
| `source: "tikmart"` | منصة ديما فاشن |
| `fulfillment: "restaurant_or_tikmart"` | سائق المطعم أو عامل توصيل ديما |
| إعدادات `tikmart_min_order_*` | أقل طلب / مدة توصيل **المنصة** |

التفاصيل: [`FLUTTER_CART_CHECKOUT_DELIVERY.md`](./FLUTTER_CART_CHECKOUT_DELIVERY.md)

---

## 5) Checklist Flutter

- [ ] `ThemeData` من `GET /settings` → `color` / `dark_color`
- [ ] لا `#00aed1` ولا `#FFA000` في الثيم
- [ ] اسم التطبيق / Splash / About = ديما فاشن / Deema Fashion
- [ ] شعار من `login.image` أو `welcome.image`
- [ ] نصوص السلة: «المنصة» / «ديما» مو «تيك مارت»
- [ ] PaymentSheet: `merchantDisplayName: 'Deema'`
- [ ] روابط تواصل من `contact` (deemafashion)

**الباك جاهز — التطبيق يتبع هذا الملف.**
