# ويب — هوية ديما فاشن البصرية (منفصل عن تيك مارت)

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 8 تشرين الأول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> Flutter: [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md)  
> داشبورد: [`DASHBOARD_BRAND_IDENTITY.md`](./DASHBOARD_BRAND_IDENTITY.md)  
> المرجع العام: [`DEEMA_BRAND_IDENTITY.md`](./DEEMA_BRAND_IDENTITY.md)

**المشروع صار ديما فاشن — منفصل عن تيك مارت / Tikmool.**  
لا تستخدموا ألوان أو شعار أو اسم TikMart / tikmool-website في الواجهة.

---

## 1) الاسم

| EN | AR | نطاق |
|----|-----|------|
| Deema Fashion | ديما فاشن | `deemafashion.com` |

`document.title` · meta · footer · Stripe = **Deema Fashion** / **ديما فاشن**.

---

## 2) الألوان من الـ API

```http
GET /api/user/settings
```

| CSS variable مقترح | من | افتراضي |
|--------------------|-----|---------|
| `--color-main` | `color.main_color` | `#c720a4` |
| `--color-second` | `color.second_color` | `#ff1493` |
| `--color-text` | `color.text_color` | `#1F2937` |
| `--color-main-dark` | `dark_color.main_color` | `#1a0a16` |
| `--color-second-dark` | `dark_color.second_color` | `#a020f0` |
| `--color-text-dark` | `dark_color.text_color` | `#fce7f3` |

اسحبوا الألوان عند تحميل التطبيق واحقنوها كـ CSS variables. لا تثبتوا `#00aed1` / `#FFA000`.

صور: `welcome.image` · `login.image` · `contact.*` من نفس الـ endpoint.

---

## 3) السلة — نص الواجهة vs مفتاح API

`source: "tikmart"` في الـ JSON = **منصة ديما**. في UI اكتبوا «ديما فاشن» / «المنصة».  
انظر [`WEB_CART_CHECKOUT_DELIVERY.md`](./WEB_CART_CHECKOUT_DELIVERY.md).

---

## 4) Checklist ويب

- [ ] ثيم من `settings.color` / `dark_color`
- [ ] لا ألوان تيك مارت القديمة
- [ ] عنوان الصفحة والفوتر = ديما فاشن
- [ ] لوجو من `login` / `welcome`
- [ ] نصوص التوصيل بدون كلمة «تيك مارت»
- [ ] روابط `contact` → deemafashion

**الباك جاهز — الموقع يتبع هذا الملف.**
