# الويب — دوائر اللون من `hex`

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 28 تشرين الأول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> الداشبورد: [`DASHBOARD_PRODUCT_COLOR_HEX.md`](./DASHBOARD_PRODUCT_COLOR_HEX.md)  
> Flutter: [`FLUTTER_PRODUCT_COLOR_HEX.md`](./FLUTTER_PRODUCT_COLOR_HEX.md)

الداشبورد يحفظ الدرجة على قيمة صفة الفئة بـ `color_id` من جدول `colors`، والسجل فيه `hex`.  
الموقع **ما يرسل `color_id`**. يقرأ `hex` فقط ويملأ الدائرة منه.  
إذا القيمة نص بلا ربط، مثل `2` أو `7`، الرد يبقى `hex: null` حتى يختار الداشبورد سجل اللون. إعادة حفظ المنتج بلا تغيير القيمة لا تكفي.

الدائرة تبقى رمادية إذا `hex` فاضي. لا تخمّنوا الدرجة من الاسم.

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
| `options[].hex` أو `attributes[].hex` | لون الدائرة. مثال `#3E434A` |
| `options[].name` / `attributes[].value` | نص بجانب الدائرة فقط. **ما يلوّن** |
| `options[].id` | اختيار الدرجة. يطابق `shop_variants[].attributes[].id` |
| `shop_variants[].id` | إرسال المتغيّر للسلة |
| `color_id` | رقم سجل اللون عند الداشبورد. **مو موجود** في رد المستخدم، ومو رقم الخيار |

صفة اللون هي:

- `type == "color"`، أو
- اسمها `Color` / `اللون` حتى لو `type` بقي `square`

`type` في الرد يبقى كما حفظه الداشبورد. لا تحوّلوا `square` إلى `color` في البيانات المحفوظة. العرض فقط يرسم دائرة عندما الصفة لون.

بدون `hex` → دائرة رمادية `rgb(161 161 170)`، مو لون مستنتج من «أسود» أو «موف».

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
    "options": [{
      "id": 147,
      "name": "Black with Silver Glitter (1)",
      "hex": "#3E434A"
    }]
  }],
  "shop_variants": [{
    "id": 195,
    "attributes": [{
      "id": 147,
      "attribute": "Color",
      "value": "Black with Silver Glitter (1)",
      "type": "color",
      "hex": "#3E434A"
    }]
  }]
}
```

نفس `hex` في المكانين لنفس `id`.  
`147` رقم قيمة الصفة. `195` رقم صف المتغيّر الذي يدخل السلة.

مع `Accept-Language: ar` الاسم يصير «اسود مع لمعة فضية (1)» و`hex` ما يتغيّر.

### فلتر الفئة

```http
GET /api/user/categories/{id}/attributes
```

```json
{
  "name": "اللون",
  "type": "color",
  "values": [
    { "id": 147, "name": "اسود مع لمعة فضية (1)", "hex": "#3E434A" }
  ]
}
```

دوائر الفلتر من `values[].hex` عندما `type == "color"` أو اسم الصفة `Color` / `اللون`.  
مفتاح الفلتر `attribute_values` = `values[].id`.

---

## 3) الاختيار والسلة

1. المستخدم يضغط دائرة `options[].id`.
2. طابقوا هذا الـ id مع `shop_variants[].attributes[].id`.
3. أضيفوا للسلة `shop_variants[].id` للصف المطابق، مو `options[].id`.

إذا `hex` فاضي والدائرة رمادية، الاختيار بالـ id يبقى شغّال. النقص من سجل اللون على السيرفر، مو من الموقع.

---

## 4) العرض

التعبئة من `hex` فقط. `#RGB` و`#RRGGBB` يصيران `rgb(r g b)` معتم. أي شي ثاني، بما فيه اسم اللون، مو لون دائرة.

```ts
const MISSING = "rgb(161 161 170)";

function isColorAttribute(type?: string | null, name?: string | null) {
  if ((type ?? "").trim().toLowerCase() === "color") return true;
  const names = new Set(["color", "colour", "اللون", "لون"]);
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
- [ ] صفة `color` أو اسمها `Color` / `اللون` حتى لو `type` هو `square`
- [ ] `#RGB` و`#RRGGBB`، والتعبئة معتمة
- [ ] `hex: null` → `rgb(161 161 170)`، بلا تخمين من الاسم
- [ ] الاختيار بـ `options[].id`
- [ ] السلة بـ `shop_variants[].id`
- [ ] فلتر الفئة من `values[].hex` و`values[].id`
- [ ] لا تستخدموا `color_id` في الموقع

**الباك جاهز — الموقع يتبع هذا الملف.**
