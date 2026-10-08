# الداشبورد — هوية ديما فاشن البصرية (منفصل عن تيك مارت)

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 8 تشرين الأول 2026  
> Flutter: [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md)  
> ويب: [`WEB_BRAND_IDENTITY.md`](./WEB_BRAND_IDENTITY.md)  
> المرجع العام: [`DEEMA_BRAND_IDENTITY.md`](./DEEMA_BRAND_IDENTITY.md)

**المشروع صار ديما فاشن — منفصل عن تيك مارت.**  
لوحة الأدمن تعرض اسم **ديما فاشن** وألوانها. لا تيك مارت في العناوين أو التسميات الظاهرة.

---

## 1) تسميات الإعدادات (واجهة الأدمن)

مفاتيح الـ API ما زالت `tikmart_*` (لا تغيّروها في الطلبات).  
**التسمية في الـ UI:**

| مفتاح API | تسمية الداشبورد |
|-----------|-----------------|
| `tikmart_min_order_amount` | أقل قيمة لطلب المنصة (ديما فاشن) |
| `tikmart_delivery_min_hours` | أقل مدة توصيل للمنصة (ساعات) |
| `tikmart_delivery_max_hours` | أقصى مدة توصيل للمنصة (ساعات) |

تنبيه متجر المنصة (`is_default`): «القيم من إعدادات التوصيل لديما فاشن، مو من حقول المتجر.»

التفاصيل: [`DASHBOARD_CART_CHECKOUT_DELIVERY.md`](./DASHBOARD_CART_CHECKOUT_DELIVERY.md)

---

## 2) ألوان الواجهة للمستخدم (تعدّل من الإعدادات)

من إعدادات الواجهة / `settings` (نفس اللي يقرأها Flutter والويب):

| مفتاح | لون ديما الافتراضي |
|-------|---------------------|
| `main_color` | `#c720a4` |
| `second_color` | `#ff1493` |
| `text_color` | `#1F2937` |
| `dark_main_color` | `#1a0a16` |
| `dark_second_color` | `#a020f0` |
| `dark_text_color` | `#fce7f3` |

بعد الحفظ، التطبيق والموقع يأخذون القيم من `GET /api/user/settings`.

صور: `login_image` · `welcome_image` · تواصل `email` = `info@deemafashion.com`.

---

## 3) Checklist داشبورد

- [ ] عنوان اللوحة / login = ديما فاشن
- [ ] تسميات `tikmart_*` تظهر كـ «المنصة / ديما فاشن»
- [ ] لا شعار تيك مارت في الهيدر
- [ ] ألوان الإعدادات الافتراضية وردي/ماجنتا ديما
- [ ] روابط التواصل deemafashion

**الباك جاهز — الداشبورد يتبع هذا الملف.**
