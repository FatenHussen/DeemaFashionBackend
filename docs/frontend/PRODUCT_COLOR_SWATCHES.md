# ألوان متغيّرات المنتج — الويب والتطبيق

آخر تحديث: 29 تشرين الأول 2026.

في `GET /api/user/products/{id}` يُرسم اللون من `hex`، ويُختار المتغيّر بواسطة معرّفات قيم الصفات.

| الاستخدام | المصدر |
|---|---|
| بناء خيارات اللون | `data.attributes_map[]` حيث `type == "color"` ثم `options[]` |
| لون الدائرة | `options[].hex`، أو `shop_variants[].attributes[].hex` للصفة من نوع `color`، وأيضاً إذا اسم الصفة `Color` / `اللون` حتى لو `type` محفوظ `square` |
| اسم اللون | `options[].name` أو `attributes[].value` |
| مطابقة المتغيّر المختار | `options[].id` مع `shop_variants[].attributes[].id` |
| إضافة المتغيّر للسلة | `shop_variants[].id` للصف المطابق |

`attributes_map[].values` قائمة أسماء قديمة للتوافق؛ لا تحمل `hex` ولا معرّفات الخيارات. استخدموا `options[]` للدوائر، واحفظوا الاختيار بالـ ID حتى يبقى صالحاً بعد تغيير الاسم أو اللغة.

```json
{
  "attributes_map": [{
    "id": 10,
    "attribute": "Color",
    "type": "color",
    "values": ["Black with Silver Glitter (1)"],
    "options": [{
      "id": 40,
      "name": "Black with Silver Glitter (1)",
      "hex": "#232323"
    }]
  }],
  "shop_variants": [{
    "id": 881,
    "attributes": [{
      "id": 40,
      "attribute": "Color",
      "value": "Black with Silver Glitter (1)",
      "type": "color",
      "hex": "#232323"
    }]
  }]
}
```

هذا مثال داخل `data`؛ الأرقام واللون توضيحية وليست بيانات المنتج الظاهر في الصورة. قيمة `#232323` تفترض وجودها مسبقاً في سجل اللون المعتمد بالكتالوج؛ لا تُستنتج من كلمة `Black` أو من الصورة.

عند إنشاء خاصية لون من لوحة الإدارة (`type = color`، أو الاسم `Color` / `اللون` وما يعادله)، درجة اللون اختيارية. الاسم يُحفظ وحده و`hex` يبقى `null` حتى تُختار درجة. إذا وُجد سجل لون، مثل `Black with Silver Glitter (1)` ورقمه `17` ويحمل `hex: "#232323"`، تُرسل القيمة هكذا:

```json
{
  "name": { "en": "Color", "ar": "اللون" },
  "category_id": 5,
  "type": "color",
  "values": [{
    "name": {
      "en": "Black with Silver Glitter (1)",
      "ar": "أسود مع لمعة فضية (1)"
    },
    "color_id": 17
  }]
}
```

`color_id` اختياري، وإذا أُرسل يجب أن يكون سجل لون موجوداً. عند إغفال `values` أو إرسالها فارغة على الإنشاء، تُربط لوحة الألوان كاملة. ترك `values` خارج التعديل لا يعيد كتابة الصفوف، وإغفال `color_id` لا يمسح ربطاً قديماً. استجابات الإدارة تعرض `color_id` و`hex`. `color_id` معرّف سجل اللون، و`options[].id` معرّف قيمة الصفة. اختيار الدرجة من متغير الفئة يحفظ اللون بـ `POST /api/admin/colors` ثم يرسل `data.id` كـ `color_id`. الملفات: [`DASHBOARD_PRODUCT_COLOR_HEX.md`](./DASHBOARD_PRODUCT_COLOR_HEX.md) · [`WEB_PRODUCT_COLOR_HEX.md`](./WEB_PRODUCT_COLOR_HEX.md) · [`FLUTTER_PRODUCT_COLOR_HEX.md`](./FLUTTER_PRODUCT_COLOR_HEX.md).

إذا كانت كل الدوائر رمادية، افحصوا استجابة المنتج لنفس اللغة المستخدمة على الشاشة:

- `hex` صالح مثل `#232323`: راجعوا قراءة `options[].hex` أو `attributes[].hex`، وحفظ الحقل في موديل الواجهة، وتحويل `#RRGGBB` إلى لون معتم في Flutter.
- `hex: null` لصفة `color` أو لصفة اسمها `Color` / `اللون`: راجعوا `color_id` وسجل اللون و`hex` المخزّن. القيم القديمة قد تُحلّ من hex محفوظ أو تطابق اسم لون مسجّل؛ الاسم المركّب وحده لا يضمن وجود لون، ولا يجري تخمين الدرجة من جزء من الاسم. `type` يبقى كما هو محفوظ (`square` أو `color`).
- `hex: null` لصفة قياس أو نوع آخر: هذه قيمة متوقعة؛ لا تعرضوها كدائرة لون.

القيمة المحفوظة كنص بلا `color_id` تبقى `hex: null` حتى تُربط بسجل في `colors` أو تُعاد من الداشبورد باختيار من القائمة. إعادة حفظ المنتج من دون تغيير قيمة الصفة لا تلوّنها. الطلب لا يخمن الدرجة من الاسم: `options[].hex` و`shop_variants[].attributes[].hex` يأتيان من `colors.hex` عبر `color_id`. `options[].id` يبقى رقم قيمة الصفة. درجات المناكير الثلاثة عشر رُبطت مرة واحدة لأسماء ذلك المنتج؛ لا تُكرَّر القائمة لباقي الكتالوج.
