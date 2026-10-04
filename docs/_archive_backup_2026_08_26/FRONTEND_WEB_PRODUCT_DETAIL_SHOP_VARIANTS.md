# Frontend Web — صفحة المنتج (`/product/{id}`) وإصلاح `shop_variants`

## المشكلة اللي صارت

صفحة `https://deemafashion.com/product/23` كانت تطلع **بيضاء** وفيها نص `error element` فقط.

الـ API ما كان راجع خطأ — `GET /api/user/products/23` رجع **200**، بس الاستجابة كانت:

```json
{
  "shop_variants": [],
  "country": { "id": 9, "name": { "ar": "تركيا", "en": "Turkey" }, "code": "+90" }
}
```

فالواجهة كانت تقرأ `shop_variants[0].price` (أو `.images` / `.attributes`) على مصفوفة فاضية،
يصير throw داخل الـ render، و React Router يعرض الـ `errorElement` الافتراضي.

الـ Backend انصلح، بس **مطلوب تعديل بالويب** كمان حتى ما تتكرر نفس الحالة.

---

## 1) شنو تغيّر بالـ API

نفس الـ URL، ما في endpoint جديد:

```http
GET /api/user/products/{id}
```

| الحقل | قبل | بعد |
|---|---|---|
| `shop_variants` | ممكن يرجع `[]` | **دائماً فيه عنصر واحد على الأقل** |
| `country` | object كامل (`{id, name:{ar,en}, code, ...}`) | **string** حسب لغة الطلب (`"تركيا"` / `"Turkey"`) |
| `shop_variants[].shop_id` | موجود دائماً | ممكن يكون **`null`** إذا المنتج ما مربوط بفرع |
| `shop_variants[].id` | موجود دائماً | ممكن يكون **`null`** (ما في `shop_product_variant`) |

نفس تعديل `country` مطبّق على قائمة المنتجات `GET /api/user/products`.

---

## 2) شكل `shop_variants` بعد التعديل

### الحالة الطبيعية (منتج مربوط بفرع)

```json
{
  "shop_variants": [
    {
      "id": 44,
      "variant_id": 44,
      "sku": "JEANS-RED",
      "model": "4280",
      "barcode": "",
      "attributes": [
        { "attribute": "اللون", "value": "أزرق فاتح", "type": "color" }
      ],
      "price": 25,
      "currency": "USD",
      "currency_symbol": "$",
      "price_formatted": "$ 25",
      "price_currencies": { "USD": {}, "SYP": {} },
      "discount": 0,
      "price_after_discount": 25,
      "quantity": 12,
      "shop_id": 1,
      "is_restaurant": false,
      "city_id": 3,
      "images": [{ "id": 373, "path": "https://.../variant.webp" }]
    }
  ]
}
```

### حالة الـ fallback (منتج بدون متغيّر/بدون فرع)

```json
{
  "shop_variants": [
    {
      "id": null,
      "variant_id": null,
      "sku": "LIG-8188-BASE",
      "model": "4280",
      "barcode": "",
      "attributes": [],
      "price": 20,
      "currency": "USD",
      "currency_symbol": "$",
      "price_formatted": "$ 20",
      "discount": 0,
      "price_after_discount": 20,
      "quantity": 100,
      "shop_id": null,
      "is_restaurant": false,
      "city_id": null,
      "images": [{ "id": 373, "path": "https://.../product.webp" }]
    }
  ]
}
```

القيم بتجي من المنتج الأب. يعني تقدر تعرض الصفحة كاملة (اسم، سعر، صور، وصف) بدون أي فرع.

**المهم:** `shop_id = null` معناها **ما ينفع تضيف للسلة**، لأن السلة تحتاج `shop_product_variant_id`.

---

## 3) المطلوب بالويب

### 3.1 لا تقرأ `shop_variants[0]` بدون حماية

```js
// خطأ — هذا اللي كان يكسر الصفحة
const variant = product.shop_variants[0];
const price = variant.price;
```

```js
// صح
const variants = product.shop_variants ?? [];
const variant = variants[0] ?? null;
const price = variant?.price ?? product.price;
```

### 3.2 اعتمد على `shop_id` لتفعيل "أضف للسلة"

```js
const canAddToCart =
  Boolean(selectedVariant?.shop_id) &&
  Boolean(selectedVariant?.id) &&
  (selectedVariant?.quantity ?? 0) > 0;
```

إذا `canAddToCart === false`:

- اعرض الزر **معطّل** مع رسالة مثل «غير متوفر حالياً» أو «غير متاح في فرعك».
- **لا** ترمي error ولا تخرّب الصفحة — باقي المحتوى لازم يضل ظاهر.

### 3.3 `country` صار string

```js
// خطأ (كسر بعد التعديل)
product.country.name.ar

// صح
product.country  // "تركيا"
```

إذا عندك مكوّن يعرض `country.name` لازم يتغيّر لعرض النص مباشرة. القيمة قد تكون `null`.

### 3.4 `attributes` و `attributes_map` قد يكونوا فاضيين

```js
const attributesMap = product.attributes_map ?? [];

// لا تعرض قسم اختيار الخصائص إذا فاضي
{attributesMap.length > 0 && <VariantPicker map={attributesMap} />}
```

منتج بدون خصائص = متغيّر واحد فقط، فاعرض السعر والكمية مباشرة.

### 3.5 ضيف `ErrorBoundary` حقيقي للمسار

المشكلة ظهرت كـ `error element` لأن ما في `errorElement` مخصص:

```jsx
{
  path: "/product/:id",
  element: <ProductPage />,
  errorElement: <ProductErrorFallback />,
}
```

الـ fallback لازم يعرض رسالة مفهومة + زر "إعادة المحاولة"، مو شاشة بيضاء.

---

## 4) حقول ممكن ترجع فاضية — تعامل معها

| الحقل | القيمة الفاضية | المعالجة المقترحة |
|---|---|---|
| `shop_variants[].shop_id` | `null` | عطّل السلة |
| `shop_variants[].id` | `null` | عطّل السلة |
| `shop_variants[].attributes` | `[]` | لا تعرض قسم الخصائص |
| `shop_variants[].images` | `[]` | استخدم `product.thumbnail` أو `product.images[0]` |
| `attributes_map` | `[]` | لا تعرض المتغيّرات |
| `available_shops` | `[]` | لا تعرض قائمة الفروع |
| `category_details` / `extra_details` | `[]` | اخفِ التبويب |
| `country` | `null` | اخفِ السطر |
| `description` | `""` | اعتمد على `full_description` |

---

## 5) مثال React مبسّط

```jsx
function ProductPage() {
  const { id } = useParams();
  const { data, isLoading, error } = useQuery(["product", id], () =>
    api.get(`/api/user/products/${id}`).then((r) => r.data.data)
  );

  if (isLoading) return <ProductSkeleton />;
  if (error || !data) return <ProductErrorFallback />;

  const variants = data.shop_variants ?? [];
  const [selected, setSelected] = useState(variants[0] ?? null);

  const canAddToCart =
    Boolean(selected?.shop_id) && Boolean(selected?.id) && (selected?.quantity ?? 0) > 0;

  return (
    <>
      <h1>{data.name}</h1>
      {data.country && <p>بلد المنشأ: {data.country}</p>}

      <Gallery images={selected?.images?.length ? selected.images : data.images ?? []} />

      <Price
        value={selected?.price_after_discount ?? data.price_after_discount}
        formatted={selected?.price_after_discount_formatted ?? data.price_after_discount_formatted}
      />

      {(data.attributes_map ?? []).length > 0 && (
        <VariantPicker map={data.attributes_map} variants={variants} onChange={setSelected} />
      )}

      <button disabled={!canAddToCart}>
        {canAddToCart ? "أضف للسلة" : "غير متوفر حالياً"}
      </button>
    </>
  );
}
```

---

## 6) Checklist

- [x] ما في وصول مباشر لـ `shop_variants[0].*` بدون `?.` أو fallback
- [x] `shop_id === null` → زر السلة معطّل، والصفحة تضل تعرض
- [x] `country` يُعرض كنص، ولا يوجد `country.name`
- [x] `attributes_map` فاضي → قسم الخصائص مخفي
- [x] الصور: fallback من صور المنتج إذا صور المتغيّر فاضية
- [x] `errorElement` مخصص لمسار `/product/:id`
- [x] تجربة `/product/20` و `/product/22` تفتح بشكل طبيعي
- [x] `AddProductModal.tsx` — إصلاح مسار السلة المجدولة للعقد الجديد

---

## 7) ملاحظة مهمة

الـ fallback حل عرض فقط — يمنع كسر الصفحة، بس **ما بيخلي المنتج قابل للشراء**.
حتى يصير الشراء ممكن، لازم الداشبورد يربط المنتج بمتغيّر + فرع.
التفاصيل بـ `FRONTEND_DASHBOARD_PRODUCT_VARIANTS_SAVE.md`.

---

## 8) مشاكل `.env` والإعداد

### 8.1 المتغيّر ميّت وقيمته غلط

```
VITE_SERVER_URL=https://deemafashion.com/api/user
```

ما في سطر واحد بالكود يقرا `VITE_SERVER_URL`. والقيمة نفسها غلط: `deemafashion.com` هو مضيف الواجهة (SPA) مو الـ API.

### 8.2 المضيف الحقيقي مثبَّت بالكود بمكانين

- `interceptor.ts:26` — `https://api.deemafashion.com/api/` للإنتاج
- `vite.config.ts:27` — نفس المضيف كهدف للـ proxy بالتطوير

### 8.3 `.env` مو ضمن `.gitignore`

### الإصلاح المقترح

| الملف | التغيير |
|---|---|
| `.gitignore` | ضيف `.env` و `.env.*` مع استثناء `!.env.example` |
| `.env.example` | ملف جديد متتبَّع فيه `VITE_API_HOST=https://api.deemafashion.com` كتوثيق |
| `.env` | استبدل السطر الميّت بـ `VITE_API_HOST=https://api.deemafashion.com` |
| `interceptor.ts` | `DEV ? "/api" : \`${import.meta.env.VITE_API_HOST ?? "https://api.deemafashion.com"}/api\`` |
| `vite.config.ts` | خُد هدف الـ proxy من `loadEnv` بنفس القيمة الافتراضية |

**⚠️ يحتاج موافقة قبل التنفيذ** — التعديل بيلمس إعداد البناء والنشر.
