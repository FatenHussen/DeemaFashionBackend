# الويب — صورة الأيقونة بعد تغييرها

> **أرسلوا هذا الملف لفريق الويب.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_ICON_IMAGE.md`](./DASHBOARD_ICON_IMAGE.md)  
> Flutter: [`FLUTTER_ICON_IMAGE.md`](./FLUTTER_ICON_IMAGE.md)

الأيقونات على صفحة المنتج تبقى من `icons[]`.  
لما الأدمن يستبدل الصورة، **مسار الملف** في الرابط يتغيّر، وفيه `?v=`.  
اعرضوا الرابط كما رجع. لا تحذفوا `?v=` ولا تحتفظوا بنسخة قديمة من الرابط.

لا تثبّتوا ملفات SVG في كود الموقع.

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

`?v=` جزء من الرابط. لا تحذفوه. بعد تغيير الصورة من الداشبورد اسم الملف كمان بيتغيّر (`icons/ab12.png` مو ملف الـ SVG القديم).

```tsx
{(product.icons ?? []).map((item) => {
  const src = item.icon || item.image;
  if (!src) return null;
  return <img key={src} src={src} alt={item.name} />;
})}
```

`key` على الرابط كامل، مو على `id` لحاله. إذا `id` ثابت والرابط تغيّر، المتصفح ممكن يبقي الصورة القديمة.

إذا `icons` فاضي: أخفوا الصف. لا صور ثابتة بديلة.

بعد فتح صفحة المنتج من جديد، اقرؤوا آخر `GET`. لا تعيدوا استخدام رابط انحفظ قبل تعديل الأيقونة.

---

## 2) Checklist

- [ ] `<img>` ياخذ `icon` أو `image` كما هو، مع `?v=`
- [ ] `key` فيه الرابط، مو `id` فقط
- [ ] ما في أيقونات مكتوبة بالكود (توصيل آمن، عضوي، …)
- [ ] بعد تحديث الأيقونة من الداشبورد، الصفحة تقرأ آخر `GET`
