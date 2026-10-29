# Flutter — دوائر اللون من `hex`

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 28 تشرين الأول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> الداشبورد: [`DASHBOARD_PRODUCT_COLOR_HEX.md`](./DASHBOARD_PRODUCT_COLOR_HEX.md)  
> الويب: [`WEB_PRODUCT_COLOR_HEX.md`](./WEB_PRODUCT_COLOR_HEX.md)  
> متغيّرات: [`FLUTTER_PRODUCT_ALL_VARIANTS.md`](./FLUTTER_PRODUCT_ALL_VARIANTS.md)

الداشبورد يحفظ الدرجة على قيمة صفة الفئة بـ `color_id` من جدول `colors`، والسجل فيه `hex`.  
التطبيق **ما يرسل `color_id`**. يقرأ `hex` فقط ويملأ الدائرة منه.  
إذا القيمة نص بلا ربط، مثل `2` أو `7`، الرد يبقى `hex: null` حتى يختار الداشبورد سجل اللون. إعادة حفظ المنتج بلا تغيير القيمة لا تكفي.

الدائرة تبقى رمادية إذا `hex` فاضي. لا تخمّنوا الدرجة من الاسم.

ثبت على السيرفر: منتج 68 خيارات `141` و`142` فيهن `hex` والدائرة تلونت. منتج 95 خيارات `160` و`161` (`2` و`7`) رجعن `hex: null` والدائرة `rgb(161, 161, 170)`. النقص من ربط `color_id` على الداشبورد، مو من التطبيق.

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

`type` في الرد يبقى كما حفظه الداشبورد. لا تحوّلوا `square` إلى `color` عندكم.

بدون `hex` → دائرة رمادية واضحة، مو لون مستنتج من «أسود» أو «موف».

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

إذا `hex` فاضي والدائرة رمادية، الاختيار بالـ id يبقى شغّال. النقص من سجل اللون على السيرفر، مو من التطبيق.

---

## 4) العرض

```dart
Color? parseHex(String? hex) {
  if (hex == null || hex.isEmpty) return null;
  var raw = hex.trim();
  if (raw.startsWith('#')) raw = raw.substring(1);
  if (raw.length == 3) {
    raw = raw.split('').map((c) => '$c$c').join();
  }
  if (raw.length != 6) return null;
  final value = int.tryParse(raw, radix: 16);
  if (value == null) return null;
  return Color(value + 0xFF000000);
}

bool isColorAttribute({String? type, String? name}) {
  if ((type ?? '').toLowerCase() == 'color') return true;
  const names = {'color', 'colour', 'اللون', 'لون'};
  return names.contains((name ?? '').trim().toLowerCase());
}

// options من attributes_map
CircleAvatar(
  backgroundColor: parseHex(option.hex) ?? const Color(0xFFA1A1AA),
  child: selected ? const Icon(Icons.check, size: 14) : null,
);
```

العنوان: `Color · ${option.name}` — النص من `name`، التعبئة من `hex`.

---

## 5) غلط vs صح

**غلط**

```dart
// لون من الاسم، أو بناء الدوائر من values بدون hex
CircleAvatar(backgroundColor: Colors.grey);
colorFromName(option.name);
```

**صح**

```dart
for (final opt in colorGroup.options)
  CircleAvatar(
    backgroundColor: parseHex(opt.hex) ?? const Color(0xFFA1A1AA),
  );
```

---

## 6) Checklist

- [ ] دوائر المنتج من `options[].hex`، ونفس القيمة من `shop_variants[].attributes[].hex`
- [ ] صفة `color` أو اسمها `Color` / `اللون` حتى لو `type` هو `square`
- [ ] `#RGB` و`#RRGGBB`، والتعبئة معتمة
- [ ] `hex: null` → رمادي، بلا تخمين من الاسم
- [ ] الاختيار بـ `options[].id`
- [ ] السلة بـ `shop_variants[].id`
- [ ] فلتر الفئة من `values[].hex` و`values[].id`
- [ ] لا تستخدموا `color_id` في التطبيق

**الباك جاهز — التطبيق يتبع هذا الملف.**
