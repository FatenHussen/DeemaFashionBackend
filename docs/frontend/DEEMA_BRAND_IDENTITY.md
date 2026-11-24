# ديما فاشن — الهوية البصرية (مرجع الكل)

> **آخر تحديث:** 24 تشرين الثاني 2026  
> المشروع **منفصل عن تيك مارت / Tikmool**. هذا الملف مرجع مشترك؛ أرسلوا ملف الفريق المناسب:

| الفريق | الملف |
|--------|--------|
| Flutter | [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md) |
| ويب | [`WEB_BRAND_IDENTITY.md`](./WEB_BRAND_IDENTITY.md) |
| داشبورد | [`DASHBOARD_BRAND_IDENTITY.md`](./DASHBOARD_BRAND_IDENTITY.md) |

---

## ماذا تغيّر؟

| قبل | بعد (ديما فاشن — هوية أرضية) |
|-----|------------------------------|
| اسم TikMart / Tikmool / تيك مارت | **Deema Fashion** / **ديما فاشن** |
| أساسي فيروزي `#00aed1` ثم ماجنتا `#c720a4` | أساسي بني `#5A3A2E` |
| ثانوي برتقالي / وردي `#ff1493` | ثانوي طيني `#8B5E4A` |
| نطاقات tickmartsy / tikmool | `deemafashion.com` |
| بريد tikmool | `info@deemafashion.com` |

مفاتيح JSON القديمة مثل `source: "tikmart"` و`tikmart_min_order_amount` **باقية في الـ API** للتوافق — معناها الآن **منصة ديما**. النص الظاهر للمستخدم = ديما فاشن / المنصة.

---

## لوحة الألوان (Color Identity)

| الدور | Hex | الاستخدام |
|-------|-----|-----------|
| Primary | `#5A3A2E` | Brand · Headers · Primary buttons |
| Secondary | `#8B5E4A` | Sections · Highlights · Secondary CTAs |
| Accent | `#D4B39A` | Soft buttons · Key elements · Dark-mode brand |
| Background | `#EAD8C7` | Page wash / warm canvas |
| Surface | `#F7F5F2` | Cards · Content areas |
| Highlight | `#A9B07A` | Emphasis · Logo butterfly · Dark secondary |
| Text & Icons | `#1F1F1F` | Primary text · Icons |

### فاتح (`color`) — من الـ API

| الدور | Hex |
|-------|-----|
| أساسي `main_color` | `#5A3A2E` |
| ثانوي `second_color` | `#8B5E4A` |
| نص `text_color` | `#1F1F1F` |

### داكن (`dark_color`)

| الدور | Hex |
|-------|-----|
| أساسي | `#D4B39A` |
| ثانوي | `#A9B07A` |
| نص | `#F7F5F2` |

المصدر: `GET /api/user/settings` → `color` · `dark_color` · `welcome` · `login` · `contact`.

لوحة البائع Filament: `primary = #5A3A2E`.

---

## Stripe

`merchantDisplayName: 'Deema'` — انظر [`../api/STRIPE_PAYMENTS.md`](../api/STRIPE_PAYMENTS.md).
