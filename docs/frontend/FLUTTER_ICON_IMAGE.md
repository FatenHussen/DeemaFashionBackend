# Flutter — صورة الأيقونة بعد تغييرها

> **أرسلوا هذا الملف لفريق Flutter.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_ICON_IMAGE.md`](./DASHBOARD_ICON_IMAGE.md)  
> ويب: [`WEB_ICON_IMAGE.md`](./WEB_ICON_IMAGE.md)

الأيقونات على صفحة المنتج تبقى من `icons[]`.  
لما الأدمن يستبدل الصورة، **مسار الملف** في الرابط يتغيّر، وفيه `?v=`.  
اعرضوا الرابط كما رجع. لا تقصّوا `?v=` ولا تثبّتوا الكاش على `id` الأيقونة.

لا تثبّتوا أصول SVG داخل التطبيق لهالأيقونات.

---

## 1) من وين

```http
GET /api/user/products/{id}
Accept-Language: ar
```

```json
{
  "icons": [
    {
      "id": 8,
      "name": "عضوي",
      "icon": "https://tickdash.tickmartsy.com/storage/icons/ab12.png?v=1758970000",
      "image": "https://tickdash.tickmartsy.com/storage/icons/ab12.png?v=1758970000",
      "description": "منتج عضوي طبيعي"
    }
  ]
}
```

| الحقل | استخدموا |
|--------|----------|
| الصورة | `icon` أو `image` — نفس الرابط |
| العنوان | `name` |
| التلميح | `description` إذا موجود |

`?v=` جزء من الرابط. لا تقصّوه قبل `Image.network` أو `CachedNetworkImage`.  
اسم الملف بعد الاستبدال بيتغيّر كمان. مفتاح الكاش = الرابط كامل (المسار + `?v=`)، مو `icons[i].id`.

```dart
final src = (icon.icon ?? icon.image ?? '').trim();
if (src.isEmpty) return const SizedBox.shrink();

CachedNetworkImage(
  imageUrl: src,
  cacheKey: src,
);
```

إذا `icons` فاضي أو `null`: أخفوا الصف. لا أيقونات محلية بديلة.

بعد الرجوع لصفحة المنتج، اطلبوا `GET` من جديد. لا تعرضوا رابطاً محفوظاً من قبل تعديل الأيقونة.

---

## 2) Checklist

- [ ] الصورة من `icon` أو `image` كما رجع من الـ API، مع `?v=`
- [ ] مفتاح الكاش = الرابط كامل، مو `id` الأيقونة
- [ ] ما في صف أيقونات ثابت داخل التطبيق
- [ ] فتح صفحة المنتج من جديد يقرأ آخر `GET`
