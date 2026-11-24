# ويب — هوية ديما فاشن البصرية (منفصل عن تيك مارت)

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 24 تشرين الثاني 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> Flutter: [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md)  
> داشبورد: [`DASHBOARD_BRAND_IDENTITY.md`](./DASHBOARD_BRAND_IDENTITY.md)  
> المرجع العام: [`DEEMA_BRAND_IDENTITY.md`](./DEEMA_BRAND_IDENTITY.md)

**المشروع صار ديما فاشن — منفصل عن تيك مارت / Tikmool.**  
لا تستخدموا ألوان أو شعار أو اسم TikMart / tikmool-website في الواجهة.  
**لا ماجنتا/وردي قديم** (`#c720a4` / `#ff1493`) — الهوية الآن أرضية دافئة.

---

## 1) الاسم

| EN | AR | نطاق |
|----|-----|------|
| Deema Fashion | ديما فاشن | `deemafashion.com` |

`document.title` · meta · footer · Stripe = **Deema Fashion** / **ديما فاشن**.

---

## 2) لوحة الألوان الكاملة (UI)

| الدور | Hex | الاستخدام في الموقع |
|-------|-----|---------------------|
| Primary | `#5A3A2E` | Brand · Headers · Primary button |
| Secondary | `#8B5E4A` | Sections · Highlights · Secondary CTA |
| Accent | `#D4B39A` | Soft buttons · Key chips |
| Background | `#EAD8C7` | Page background wash |
| Surface | `#F7F5F2` | Cards · Content panels |
| Highlight | `#A9B07A` | Emphasis · variety accents |
| Text | `#1F1F1F` | Primary text & icons |

### أزرار (مرجع بصري)

| النوع | الخلفية | النص |
|-------|---------|------|
| Primary | `#5A3A2E` | أبيض |
| Secondary | `#D4B39A` | `#5A3A2E` |
| Outline | شفاف / أبيض | `#5A3A2E` + حد بني |

---

## 3) الألوان من الـ API

```http
GET /api/user/settings
```

| CSS variable مقترح | من | افتراضي |
|--------------------|-----|---------|
| `--color-main` | `color.main_color` | `#5A3A2E` Primary |
| `--color-second` / `--color-api-second` | `color.second_color` | `#A9B07A` Highlight (تباين حديث) |
| `--color-text` | `color.text_color` | `#1F1F1F` |
| `--color-brand-secondary` | ثابت من اللوحة | `#8B5E4A` Sections sparingly |
| `--color-brand-accent` | ثابت من اللوحة | `#D4B39A` Soft buttons |
| `--color-brand-background` | ثابت | `#EAD8C7` Wash |
| `--color-brand-surface` | ثابت | `#F7F5F2` Canvas |
| `--color-main-dark` | `dark_color.main_color` | `#D4B39A` |
| `--color-second-dark` | `dark_color.second_color` | `#A9B07A` |
| `--color-text-dark` | `dark_color.text_color` | `#F7F5F2` |

**قاعدة تباين:** لا تملؤوا الصفحة ببني+طيني متقارب — Primary للأزرار الرئيسية، Highlight للتأكيد، Accent للكروت الناعمة، Surface للخلفية.

اسحبوا الألوان عند تحميل التطبيق واحقنوها كـ CSS variables.  
لا تثبتوا `#00aed1` / `#FFA000` / `#c720a4` / `#ff1493`.

صور: `welcome.image` · `login.image` · `contact.*` من نفس الـ endpoint.

---

## 4) السلة — نص الواجهة vs مفتاح API

`source: "tikmart"` في الـ JSON = **منصة ديما**. في UI اكتبوا «ديما فاشن» / «المنصة».  
انظر [`WEB_CART_CHECKOUT_DELIVERY.md`](./WEB_CART_CHECKOUT_DELIVERY.md).

---

## 5) Checklist ويب

- [ ] ثيم من `settings.color` / `dark_color` (بني / طيني)
- [ ] لا ألوان تيك مارت ولا ماجنتا القديم
- [ ] خلفية/كروت قريبة من `#EAD8C7` / `#F7F5F2`
- [ ] عنوان الصفحة والفوتر = ديما فاشن
- [ ] لوجو من `login` / `welcome`
- [ ] نصوص التوصيل بدون كلمة «تيك مارت»
- [ ] روابط `contact` → deemafashion

**الباك جاهز — الموقع يتبع هذا الملف.**
