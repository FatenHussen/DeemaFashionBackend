# Flutter — دوائر اللون من `hex`

مرجع مشترك للويب والتطبيق، مع حفظ ربط اللون وتشخيص الدوائر الرمادية: [`PRODUCT_COLOR_SWATCHES.md`](./PRODUCT_COLOR_SWATCHES.md).

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 21 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> متغيّرات: [`FLUTTER_PRODUCT_ALL_VARIANTS.md`](./FLUTTER_PRODUCT_ALL_VARIANTS.md)

على شاشة المنتج (ودوائر اللون بفلتر الفئة) الدائرة **رمادية** إذا ما استخدمتوا `hex`.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [من أين تُجلب](#2-من-أين-تجلب)
3. [العرض](#3-العرض)
4. [غلط vs صح](#4-غلط-vs-صح)
5. [Checklist](#5-checklist)

---

## 1) القاعدة

| المصدر | حقل اللون |
|--------|-----------|
| نص «أسود» | للعرض فقط — **ما يلوّن** الدائرة |
| `hex` | `#000000` → لون الدائرة |

بدون `hex` → لا تستخدموا `Colors.grey` كـ «لون المنتج»؛ إمّا اخفوا الدائرة أو fallback واضح.

---

## 2) من أين تُجلب

### شاشة المنتج

```http
GET /api/user/products/{id}
```

```json
{
  "attributes_map": [
    {
      "attribute": "لون",
      "type": "color",
      "options": [
        { "id": 41, "name": "أسود", "hex": "#000000" },
        { "id": 40, "name": "أزرق", "hex": "#0000FF" }
      ]
    }
  ],
  "shop_variants": [
    {
      "attributes": [
        { "id": 41, "attribute": "لون", "value": "أسود", "type": "color", "hex": "#000000" }
      ]
    }
  ]
}
```

دوائر اللون من `attributes_map` حيث `type == 'color'` **أو** اسم الصفة `Color` / `اللون` → **`options[].hex`**.  
أو من `shop_variants[].attributes` بنفس الشرط → **`hex`**.  
صفة اللون المحفوظة `type: "square"` تبقى `square` في الاستجابة، و`hex` يجي من سجل اللون إذا القيمة مربوطة أو اسمها يطابق لوناً مسجّلاً.

### فلتر الفئة (chips)

```http
GET /api/user/categories/{id}/attributes
```

```json
{
  "type": "color",
  "values": [
    { "id": 41, "name": "أسود", "hex": "#000000" }
  ]
}
```

---

## 3) العرض

```dart
Color? parseHex(String? hex) {
  if (hex == null || hex.isEmpty) return null;
  final raw = hex.replaceFirst('#', '');
  if (raw.length != 6) return null;
  return Color(int.parse(raw, radix: 16) + 0xFF000000);
}

// options من attributes_map للون
CircleAvatar(
  backgroundColor: parseHex(option.hex) ?? Colors.grey.shade300,
  child: selected ? const Icon(Icons.check, size: 14) : null,
);
```

العنوان: `لون · ${selectedOption.name}` — الاسم من `name`، اللون من `hex`.

---

## 4) غلط vs صح

**غلط**

```dart
// بناء الدوائر من values: ["أسود","أزرق"] بدون hex
CircleAvatar(backgroundColor: Colors.grey);
```

**صح**

```dart
for (final opt in colorGroup.options)
  CircleAvatar(backgroundColor: parseHex(opt.hex) ?? Colors.grey.shade300);
```

---

## 5) Checklist

- [ ] دوائر المنتج من `options[].hex` أو `attributes[].hex`
- [ ] فلتر الفئة من `values[].hex` عند `type == color`
- [ ] `parseHex` يتحمّل `#RRGGBB`
- [ ] بلا `hex` → رمادي واضح مو «لون وهمي»
- [ ] الاختيار بـ `id` مو باسم اللون فقط

**الباك جاهز — التطبيق يتبع هذا الملف.**
