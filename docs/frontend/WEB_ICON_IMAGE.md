# الويب — صورة الأيقونة بعد تغييرها

> **أرسلوا هذا الملف لفريق الويب.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_ICON_IMAGE.md`](./DASHBOARD_ICON_IMAGE.md)  
> Flutter: [`FLUTTER_ICON_IMAGE.md`](./FLUTTER_ICON_IMAGE.md)

الأيقونات على صفحة المنتج تبقى من `icons[]`.  
بعد ما الأدمن يبدّل الصورة، الرابط نفسه يتغير. اعرضوا الرابط كما رجع، بما فيه `?v=`.

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

`?v=` جزء من الرابط. لا تحذفوه. هو اللي يجبر المتصفح يجيب الصورة الجديدة بعد الحفظ من الداشبورد.

```tsx
{(product.icons ?? []).map((item) => (
  <img key={item.id} src={item.icon || item.image} alt={item.name} />
))}
```

إذا `icons` فاضي: أخفوا الصف. لا صور ثابتة بديلة.

---

## 2) Checklist

- [ ] `<img>` ياخذ `icon` أو `image` كما هو، مع `?v=`
- [ ] ما في أيقونات مكتوبة بالكود (توصيل آمن، عضوي، …)
- [ ] بعد تحديث المنتج، الصفحة تقرأ آخر `GET` مو نسخة محفوظة من الرابط القديم
