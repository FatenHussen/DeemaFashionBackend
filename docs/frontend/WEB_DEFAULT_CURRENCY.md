# ويب / تطبيق — عملة العرض الافتراضية

> **أرسلوا هذا الملف لفريق الويب و Flutter.**  
> **آخر تحديث:** 19 تشرين الثاني 2026  
> Base: `/api/user`  
> داشبورد: [`DASHBOARD_DEFAULT_CURRENCY.md`](./DASHBOARD_DEFAULT_CURRENCY.md)

---

## 1) القاعدة

1. الضيوف والمستخدمون **بدون اختيار صريح** → يعرضون عملة `is_default` من الداشبورد.
2. بعد `POST /currencies/update-currency` → يلتزمون باختيارهم (`preference_source: "user"`).
3. غيّر الداشبورد الافتراضي → يتأثر فقط من ما عندهم اختيار صريح.

**اعرضوا** `price` / `price_formatted` / `currency` من الـ API.  
**لا** تفرضوا SYP أو USD من `price_currencies` إلا إذا التصميم يطلب عرض العملتين معاً.

---

## 2) Endpoints

| Method | Path | Auth | استخدام |
|--------|------|------|---------|
| GET | `/currencies` | لا | قائمة نشطة + `is_default` |
| GET | `/currencies/my-currency` | نعم | العملة الحالية + `preference_source` |
| POST | `/currencies/update-currency` | نعم | `{ "currency_id": 2 }` |

```json
{
  "id": 1,
  "code": "USD",
  "symbol": "$",
  "is_default": true,
  "preference_source": "default"
}
```

`preference_source`: `"default"` | `"user"`.

---

## 3) سلوك الواجهة

```ts
// أول زيارة / ضيف
const list = await get('/api/user/currencies');
const display = list.find(c => c.is_default) ?? list[0];

// مسجّل
const mine = await get('/api/user/currencies/my-currency');
// mine.code — اعرضوا أسعاره من price / price_formatted

// تغيير من الـ picker
await post('/api/user/currencies/update-currency', { currency_id });
// ثم أعيدوا جلب الشاشات الحساسة للسعر
```

| غلط | صح |
|-----|-----|
| دائماً `price_currencies.SYP` | `price_formatted` أو `currency` من الرد |
| تجاهل `is_default` من القائمة | أول عرض = العملة اللي `is_default: true` |

---

## 4) اللغة الافتراضية

بدون هيدر `Accept-Language` (أو قيمة غير مدعومة) → اللغة الافتراضية من الداشبورد (حالياً **en**).  
أرسلوا `Accept-Language: ar` أو `en` حسب اختيار المستخدم.

---

## 5) Checklist

- [ ] أول فتح يعرض عملة `is_default` مو ليرة ثابتة
- [ ] تغيير العملة من الـ picker يحفظ عبر `update-currency`
- [ ] الأسعار من `*_formatted` / `currency` بعد كل تغيير
- [ ] `price_currencies` اختياري للعرض الثنائي فقط
