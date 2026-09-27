# الداشبورد — تغيير صورة الأيقونة

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 28 أيلول 2026  
> Base: `/api/admin` + Admin token  
> ويب: [`WEB_ICON_IMAGE.md`](./WEB_ICON_IMAGE.md)  
> Flutter: [`FLUTTER_ICON_IMAGE.md`](./FLUTTER_ICON_IMAGE.md)  
> الربط بالمنتج (بدون تغيير): [`PRODUCT_ICONS_WEB_DASHBOARD.md`](./PRODUCT_ICONS_WEB_DASHBOARD.md)

الحفظ يرجع **200** والاسم يتحدّث، و`data.image` يبقى `null` أو الرابط القديم. السبب: جزء الملف ما بينحفظ، والنص لحاله ما بيستبدل الصورة.

مع كل ملف جديد ابعتوا **ثلاث حقول مع بعض**: الملف، اسمه، ونفس البايتات كنص base64.

إذا خانة الملف فاضية، لا ترسلوا هالحقول. الصورة الحالية تبقى.

---

## 1) Endpoint

```http
POST /api/admin/icons/{id}
```

`multipart/form-data` مع `_method=PUT`.  
لا تضبطوا `Content-Type` يدوياً. المتصفح يضيف `boundary`.  
طلب حقيقي فيه صورة يكون أكبر من فورم النص لحاله. طلب حوالي 12KB مع ملف `webp` مختار يعني البايتات ما انضافت.

خذوا الملف من `input.files[0]` لحظة الضغط على تحديث، وخزّنوه بـ `ref`. لا تعتمدوا على قيمة React Hook Form إذا راحت.

---

## 2) Payload عند تغيير الصورة

```text
_method=PUT
name[ar]=توصيل سريع
name[en]=Fast delivery
description[ar]=...
description[en]=...
is_active=1
image=<File من input.files[0]>
image_filename=OIP (1).webp
image_base64=data:image/webp;base64,...
```

`image_base64` هو ناتج `FileReader.readAsDataURL`.  
على ويندوز ملف `webp` غالباً يطلع `data:application/octet-stream;base64,...` مو `data:image/webp`. ابعتوه برضو. الباك ياخد الامتداد من `image_filename`.

```ts
async function appendIconImage(formData: FormData, file: File) {
  const filename = file.name || 'icon.webp';
  formData.append('image', file, filename);
  formData.append('image_filename', filename);
  const dataUrl = await new Promise<string>((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(String(reader.result ?? ''));
    reader.onerror = () => reject(reader.error);
    reader.readAsDataURL(file);
  });
  if (dataUrl.includes(';base64,')) {
    formData.append('image_base64', dataUrl);
  }
}
```

بدون ملف جديد: لا ترسلوا `image` ولا `image_base64` ولا `image_filename` ولا `icon`.  
لا تحطوا رابط الصورة الحالي جوا `image`.

| الحقل | القاعدة |
|--------|---------|
| `image` | الملف. اختياري عند التعديل |
| `image_filename` | اسم الملف كما هو، مثل `OIP (1).webp` |
| `image_base64` | data URL كامل، حتى لو النوع `application/octet-stream` |
| الصيغ | `jpg` `jpeg` `png` `gif` `svg` `webp` |
| الحجم | 8MB للملف. الأكبر: لا ترسلوا، واعرضوا خطأ محلي |
| `name[ar]` / `name[en]` | مطلوبان إذا انرسل `name` |
| `description[ar]` / `description[en]` | اختياري، حدّه 1000 حرف |
| `is_active` | `1` / `0` |
| `full_description` | الداشبورد يرسله. الباك ما بيخزّنه |

صيغة غلط أو base64 فاضي/مو صورة: **422** و`errors.image`. اعرضوا الرسالة. لا تعتبروا الحفظ نجح.

---

## 3) الرد

`image` و `icon` نفس الرابط، أو الاتنين `null` إذا ما في ملف مخزّن.

نجاح استبدال الصورة:

```json
{
  "status": true,
  "data": {
    "id": 9,
    "image": "https://tickdash.tickmartsy.com/storage/icons/….webp?v=1759000000",
    "icon": "https://tickdash.tickmartsy.com/storage/icons/….webp?v=1759000000"
  }
}
```

| بعد 200 | شو تعملوا |
|---------|-----------|
| ما انرسل ملف | نجاح. الصورة القديمة تبقى |
| انرسل ملف و`data.image` رابط ومساره (بدون `?v=`) اختلف | نجاح. المعاينة والقائمة من `data.image` |
| انرسل ملف و`data.image` هو `null` | خطأ. لا رسالة «تم الحفظ» |
| انرسل ملف والمسار نفسه | خطأ. لا رسالة «تم الحفظ» |

`?v=` لحاله ما يعني إن الصورة تبدّلت. قارنوا المسار بدون الاستعلام.

```ts
const pathOf = (url?: string | null) => (url ?? '').split(/[?#]/)[0];

if (sentFile && (!body.data?.image || pathOf(body.data.image) === pathOf(previousImage))) {
  toast.error('الصورة الجديدة ما انحفظت');
  return;
}
```

خانة «Choose File» ترجع فاضية بعد النجاح. هذا طبيعي. المعاينة من `data.image` ومعها `?v=`.

ربط الأيقونة بالمنتج ما تغيّر: `icon_ids`.

---

## 4) Checklist

- [ ] التعديل `POST` + `_method=PUT`، مو PUT فاضي
- [ ] الملف من `input.files[0]` لحظة التحديث
- [ ] مع الملف: `image` + `image_filename` + `image_base64`
- [ ] `webp` ينبعت حتى لو الـ data URL نوعه `application/octet-stream`
- [ ] بدون ملف جديد: ما في `image` ولا base64 ولا رابط قديم
- [ ] `Content-Type` ما ينضبط يدوي
- [ ] `data.image === null` أو المسار ما تغيّر: خطأ، مو نجاح
- [ ] 422: نص `errors.image` تحت خانة الصورة
