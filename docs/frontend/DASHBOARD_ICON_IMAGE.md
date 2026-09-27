# الداشبورد — تغيير صورة الأيقونة

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull`**  
> ويب: [`WEB_ICON_IMAGE.md`](./WEB_ICON_IMAGE.md)  
> Flutter: [`FLUTTER_ICON_IMAGE.md`](./FLUTTER_ICON_IMAGE.md)  
> الربط بالمنتج (بدون تغيير): [`PRODUCT_ICONS_WEB_DASHBOARD.md`](./PRODUCT_ICONS_WEB_DASHBOARD.md)

استبدال صورة الأيقونة صار **ينحفظ**.  
بعد النجاح اعرضوا الصورة من الرد الجديد (`image` أو `icon`). لا تبقوا على رابط الصورة اللي كان قبل الحفظ.

إذا خانة الملف فاضية، الصورة الحالية تبقى.

---

## 1) Endpoints

```http
POST   /api/admin/icons          إنشاء
POST   /api/admin/icons/{id}     تعديل + _method=PUT أو _method=PATCH
PUT    /api/admin/icons/{id}     تعديل
PATCH  /api/admin/icons/{id}     تعديل
GET    /api/admin/icons/{id}
```

`multipart/form-data`  
لا تضبطوا `Content-Type` يدوياً. خلّوا المتصفح يضيف الـ boundary.

اسم ملف الصورة في الفورم: **`image`**.  
`icon` كملف مقبول أيضاً إذا ما انرسل `image`.

---

## 2) Payload

تغيير الصورة مع الإبقاء على الاسم:

```text
_method=PUT
name[ar]=عضوي
name[en]=Organic
description[ar]=منتج عضوي طبيعي
description[en]=Natural organic product
image=<file>
```

بدون ملف جديد: لا ترسلوا `image` ولا `icon`.  
لا ترسلوا رابط الصورة الحالي كنص داخل `image`. الرابط القديم ما بيستبدل الملف.

| الحقل | القاعدة |
|--------|---------|
| `image` | ملف اختياري عند التعديل. فاضي = الصورة القديمة تبقى |
| الصيغ | `jpg` `jpeg` `png` `gif` `svg` `webp` |
| الحجم | 2MB |
| `name[ar]` / `name[en]` | مطلوبان إذا انرسل `name` |
| `description[ar]` / `description[en]` | اختياري، حدّه 1000 حرف |
| `is_active` | `true` / `false` اختياري |

---

## 3) الرد

`image` و `icon` نفس الرابط، وفيه `?v=` حتى المتصفح ما يعرض الصورة القديمة.

```json
{
  "id": 8,
  "name": "عضوي",
  "image": "https://tickdash.tickmartsy.com/storage/icons/ab12.png?v=1758970000",
  "icon": "https://tickdash.tickmartsy.com/storage/icons/ab12.png?v=1758970000",
  "description": "منتج عضوي طبيعي",
  "is_active": true
}
```

بعد 200: حطوا `data.image` في معاينة الفورم. خانة «Choose File» ترجع فاضية وهذا طبيعي.

ربط الأيقونة بالمنتج ما تغيّر: `icon_ids` على المنتج.

---

## 4) Checklist

- [ ] التعديل يرسل الملف في `image`
- [ ] بدون ملف جديد: لا ترسلوا `image`
- [ ] لا ترسلوا رابط URL قديم مكان الملف
- [ ] بعد الحفظ المعاينة من `data.image` (مع `?v=`)
- [ ] ملف أكبر من 2MB أو بصيغة ثانية: اعرضوا خطأ الـ 422، لا تعتبروا الحفظ نجح
