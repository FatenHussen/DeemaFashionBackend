# الويب — دوائر اللون من `hex`

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 29 تشرين الأول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> الداشبورد: [`DASHBOARD_PRODUCT_COLOR_HEX.md`](./DASHBOARD_PRODUCT_COLOR_HEX.md)  
> Flutter: [`FLUTTER_PRODUCT_COLOR_HEX.md`](./FLUTTER_PRODUCT_COLOR_HEX.md)

الداشبورد يكتب اسم اللون، ودرجة اللون **اختيارية**. إذا اختار درجة تُحفظ في جدول `colors` ويرجع الموقع `hex`.  
الموقع **ما يرسل `color_id`** وما يخزّن لوناً. يقرأ `hex` ويملأ الدائرة منه.

إذا القيمة نص بلا درجة، مثل `2` أو `7`، الرد `hex: null` والدائرة رمادية `rgb(161, 161, 170)`. هذا متوقع إلى أن تُختار درجة على الداشبورد. إعادة حفظ المنتج لا تلوّنها. لا تخمّنوا الدرجة من الاسم.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [من أين تُجلب](#2-من-أين-تجلب)
3. [الاختيار والسلة](#3-الاختيار-والسلة)
4. [العرض](#4-العرض)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القاعدة

| المصدر | الاستخدام |
|--------|-----------|
| `options[].hex` أو `attributes[].hex` | لون الدائرة. مثال `#FDD0DB` |
| `options[].name` / `attributes[].value` | نص بجانب الدائرة فقط. **ما يلوّن** |
| `options[].id` | اختيار الدرجة. يطابق `shop_variants[].attributes[].id` |
| `shop_variants[].id` | إرسال المتغيّر للسلة |
| `color_id` | رقم سجل اللون عند الداشبورد. **مو موجود** في رد المستخدم |

صفة اللون هي:

- `type == "color"`، أو
- اسمها `Color` / `colour` / `اللون` / `لون` / `الالوان` / `الألوان` / `الوان` / `ألوان` حتى لو `type` بقي `square`

`type` في الرد يبقى كما حفظه الداشبورد. لا تحوّلوا `square` إلى `color` في البيانات. العرض فقط يرسم دائرة عندما الصفة لون.

بدون `hex` → `rgb(161 161 170)`. مو لون مستنتج من «أسود» أو «موف» أو من رقم مثل `2`.

`attributes_map[].values` أسماء قديمة بلا `hex` وبلا id. ابنوا الدوائر من `options[]`.

---

## 2) من أين تُجلب

### شاشة المنتج

```http
GET /api/user/products/{id}
```

```json
{
  "attributes_map": [{
    "id": 6,
    "attribute": "Color",
    "type": "color",
    "options": [
      { "id": 160, "name": "2", "hex": null },
      { "id": 147, "name": "Light Nude Pink", "hex": "#FDD0DB" }
    ]
  }],
  "shop_variants": [{
    "id": 208,
    "attributes": [{
      "id": 160,
      "attribute": "Color",
      "value": "2",
      "type": "color",
      "hex": null
    }]
  }]
}
```

نفس `hex` في المكانين لنفس `id`.  
`160` رقم قيمة الصفة. `208` رقم صف المتغيّر الذي يدخل السلة.

مع `Accept-Language: ar` الاسم يتغيّر و`hex` ما يتغيّر. `null` يبقى `null` في اللغتين.

### فلتر الفئة

```http
GET /api/user/categories/{id}/attributes
```

```json
{
  "name": "اللون",
  "type": "color",
  "values": [
    { "id": 147, "name": "وردي نيود فاتح", "hex": "#FDD0DB" },
    { "id": 160, "name": "2", "hex": null }
  ]
}
```

دوائر الفلتر من `values[].hex`. مفتاح الفلتر `attribute_values` = `values[].id`. صف `hex: null` دائرة رمادية، والفلترة بالـ id تبقى شغّالة.

---

## 3) الاختيار والسلة

1. المستخدم يضغط دائرة `options[].id`.
2. طابقوا هذا الـ id مع `shop_variants[].attributes[].id`.
3. أضيفوا للسلة `shop_variants[].id` للصف المطابق، مو `options[].id`.

الدائرة الرمادية خيار صالح. النقص من درجة لم تُختر على الداشبورد، مو من الموقع.

---

## 4) العرض

التعبئة من `hex` فقط. `#RGB` و`#RRGGBB` يصيران `rgb(r g b)` معتم. أي شي ثاني، بما فيه اسم اللون، مو لون دائرة.

```ts
const MISSING = "rgb(161 161 170)";

function isColorAttribute(type?: string | null, name?: string | null) {
  if ((type ?? "").trim().toLowerCase() === "color") return true;
  const names = new Set([
    "color",
    "colour",
    "اللون",
    "لون",
    "الالوان",
    "الألوان",
    "الوان",
    "ألوان",
  ]);
  return names.has((name ?? "").trim().toLowerCase());
}

function fill(hex?: string | null) {
  const rgb = opaqueRgbFromHex(hex);
  return rgb ?? MISSING;
}
```

العنوان من `name`. الدائرة من `fill(option.hex)`.

---

## 5) غلط vs صح

**غلط**

```ts
backgroundColor: colorFromName(option.name);
style={{ backgroundColor: "#a1a1aa" }} // لكل الخيارات، حتى اللي معها hex
```

**صح**

```ts
style={{ backgroundColor: fill(option.hex) }}
```

---

## 6) Checklist

- [ ] دوائر المنتج من `options[].hex`، ونفس القيمة من `shop_variants[].attributes[].hex`
- [ ] صفة `color` أو اسمها لون حتى لو `type` هو `square`
- [ ] `#RGB` و`#RRGGBB`، والتعبئة معتمة
- [ ] `hex: null` → `rgb(161 161 170)`، بلا تخمين من الاسم
- [ ] الخيار الرمادي يبقى قابلاً للاختيار
- [ ] الاختيار بـ `options[].id`
- [ ] السلة بـ `shop_variants[].id`
- [ ] فلتر الفئة من `values[].hex` و`values[].id`
- [ ] لا تستخدموا `color_id` في الموقع

**الباك جاهز — الموقع يتبع هذا الملف.**
