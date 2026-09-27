# Flutter — صورة الأيقونة بعد تغييرها

> **أرسلوا هذا الملف لفريق Flutter.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_ICON_IMAGE.md`](./DASHBOARD_ICON_IMAGE.md)  
> ويب: [`WEB_ICON_IMAGE.md`](./WEB_ICON_IMAGE.md)

الأيقونات على صفحة المنتج تبقى من `icons[]`.  
بعد ما الأدمن يبدّل الصورة، الرابط نفسه يتغير. اعرضوا الرابط كما رجع، بما فيه `?v=`.

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

`?v=` جزء من الرابط. لا تقصّوه قبل `Image.network` أو `CachedNetworkImage`. بدونها الكاش يعرض الصورة القديمة بعد تغييرها من الداشبورد.

إذا `icons` فاضي أو `null`: أخفوا الصف. لا أيقونات محلية بديلة.

---

## 2) Checklist

- [ ] الصورة من `icon` أو `image` كما رجع من الـ API، مع `?v=`
- [ ] مفتاح الكاش يشمل الرابط كامل، مو `id` الأيقونة لحاله
- [ ] ما في صف أيقونات ثابت داخل التطبيق
