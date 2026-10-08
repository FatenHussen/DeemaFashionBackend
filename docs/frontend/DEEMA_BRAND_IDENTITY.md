# ديما فاشن — الهوية البصرية (مرجع الكل)

> **آخر تحديث:** 8 تشرين الأول 2026  
> المشروع **منفصل عن تيك مارت / Tikmool**. هذا الملف مرجع مشترك؛ أرسلوا ملف الفريق المناسب:

| الفريق | الملف |
|--------|--------|
| Flutter | [`FLUTTER_BRAND_IDENTITY.md`](./FLUTTER_BRAND_IDENTITY.md) |
| ويب | [`WEB_BRAND_IDENTITY.md`](./WEB_BRAND_IDENTITY.md) |
| داشبورد | [`DASHBOARD_BRAND_IDENTITY.md`](./DASHBOARD_BRAND_IDENTITY.md) |

---

## ماذا تغيّر؟

| قبل (تيك مارت / Tikmool) | بعد (ديما فاشن) |
|--------------------------|-----------------|
| اسم TikMart / Tikmool / تيك مارت | **Deema Fashion** / **ديما فاشن** |
| أساسي فيروزي `#00aed1` | أساسي ماجنتا `#c720a4` |
| ثانوي برتقالي `#FFA000` | ثانوي وردي `#ff1493` |
| نطاقات tickmartsy / tikmool | `deemafashion.com` |
| بريد tikmool | `info@deemafashion.com` |

مفاتيح JSON القديمة مثل `source: "tikmart"` و`tikmart_min_order_amount` **باقية في الـ API** للتوافق — معناها الآن **منصة ديما**. النص الظاهر للمستخدم = ديما فاشن / المنصة.

---

## لوحة الألوان

### فاتح (`color`)

| الدور | Hex |
|-------|-----|
| أساسي | `#c720a4` |
| ثانوي | `#ff1493` |
| نص | `#1F2937` |

### داكن (`dark_color`)

| الدور | Hex |
|-------|-----|
| أساسي | `#1a0a16` |
| ثانوي | `#a020f0` |
| نص | `#fce7f3` |

المصدر: `GET /api/user/settings` → `color` · `dark_color` · `welcome` · `login` · `contact`.

لوحة البائع Filament: `primary = #c720a4`.

---

## Stripe

`merchantDisplayName: 'Deema'` — انظر [`../api/STRIPE_PAYMENTS.md`](../api/STRIPE_PAYMENTS.md).
