# الداشبورد — تغيير صورة الأيقونة

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 27 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull`**  
> ويب: [`WEB_ICON_IMAGE.md`](./WEB_ICON_IMAGE.md)  
> Flutter: [`FLUTTER_ICON_IMAGE.md`](./FLUTTER_ICON_IMAGE.md)  
> الربط بالمنتج (بدون تغيير): [`PRODUCT_ICONS_WEB_DASHBOARD.md`](./PRODUCT_ICONS_WEB_DASHBOARD.md)

رسالة «تم الحفظ» كانت تظهر والصورة القديمة تبقى، لأن الطلب ينجح حتى لو الملف ما وصل.  
بعد هالتعديل: **نجاح تغيير الصورة** فقط إذا مسار الملف في الرد اختلف عن المسار اللي كان قبل الحفظ.  
`?v=` يتغيّر وحدو ما يعني إن الصورة تبدّلت.

إذا خانة الملف فاضية، الصورة الحالية تبقى وهذا متوقع.

---

## 1) Endpoints

```http
POST   /api/admin/icons          إنشاء
POST   /api/admin/icons/{id}     تعديل + _method=PUT أو _method=PATCH
PUT    /api/admin/icons/{id}     تعديل
PATCH  /api/admin/icons/{id}     تعديل
GET    /api/admin/icons/{id}
GET    /api/admin/icons
```

`multipart/form-data`  
لا تضبطوا `Content-Type` يدوياً. خلّوا المتصفح يضيف الـ boundary.  
إذا انضبط `Content-Type: multipart/form-data` من غير boundary، PHP ما بيقرأ الملف والاسم ينحفظ والصورة لا.

اسم ملف الصورة في الفورم: **`image`**.  
`icon` كملف مقبول أيضاً إذا ما انرسل `image`.

خذوا الملف من `<input type="file">` لحظة الضغط على تحديث (`files[0]`). لا تعتمدوا على قيمة الفورم إذا راحت بعد التحقق.

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
| الحجم | 8MB |
| `name[ar]` / `name[en]` | مطلوبان إذا انرسل `name` |
| `description[ar]` / `description[en]` | اختياري، حدّه 1000 حرف |
| `is_active` | `true` / `false` اختياري |

ملف أكبر من 8MB، أو رفع ناقص، أو صيغة ثانية: **422**. اعرضوا `errors.image`. لا تعتبروا الحفظ نجح.

---

## 3) الرد

`image` و `icon` نفس الرابط. بعد استبدال حقيقي يتغيّر اسم الملف وفيه `?v=`.

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

| بعد 200 | شو تعملوا |
|---------|-----------|
| ما انرسل ملف | نجاح عادي. الصورة القديمة تبقى |
| انرسل ملف ومسار `data.image` (بدون `?v=`) تغيّر | نجاح. المعاينة والقائمة من `data.image` |
| انرسل ملف والمسار نفسه | الصورة ما انحفظت. اعرضوا خطأ، لا رسالة نجاح |

خانة «Choose File» ترجع فاضية بعد الحفظ. هذا طبيعي. لا تعيدوا رسم الصورة من حقل الملف.

```ts
const pathOf = (url?: string | null) => (url ?? '').split('?')[0];

if (sentFile && pathOf(body.data.image) === pathOf(previousImage)) {
  toast.error('الصورة الجديدة ما انحفظت');
  return;
}
setPreview(body.data.image);
```

ربط الأيقونة بالمنتج ما تغيّر: `icon_ids` على المنتج.

---

## 4) Checklist

- [ ] التعديل يرسل الملف في `image` من `input.files[0]`
- [ ] بدون ملف جديد: لا ترسلوا `image`
- [ ] لا ترسلوا رابط URL قديم مكان الملف
- [ ] لا تضبطوا `Content-Type` يدوياً
- [ ] بعد الحفظ المعاينة من `data.image` (مع `?v=`)
- [ ] مسار الملف ما تغيّر رغم إرسال ملف: خطأ، مو «تم الحفظ»
- [ ] 422: اعرضوا خطأ الصورة، لا تعتبروا الحفظ نجح
