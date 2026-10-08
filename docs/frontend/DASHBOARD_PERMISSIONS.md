# الداشبورد — الصلاحيات وإخفاء السايدبار

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 8 تشرين الأول 2026  
> Base: `/api/admin` + Admin Bearer token  
> مرجع تقني أوسع: [`../api/ADMIN_PERMISSIONS_FRONTEND_GUIDE.md`](../api/ADMIN_PERMISSIONS_FRONTEND_GUIDE.md) · [`../api/ADMIN_ROUTE_PERMISSIONS.md`](../api/ADMIN_ROUTE_PERMISSIONS.md)

**المشكلة الحالية:** موظف عنده بس صلاحيات «شريط التنقّل» (`navmenuitem.*`) بس السايدبار يعرض كل الأقسام.  
**السبب:** الباك رجّع الصلاحيات الصحيحة — الواجهة ما بتفلتِر القائمة.

**ويب / Flutter (تطبيق الزبون):** ما يحتاجوا هذا الملف — صلاحيات Spatie خاصة بلوحة الأدمن فقط.

---

## 1) من وين تيجي الصلاحيات؟

بعد Login أو Profile:

```http
POST /api/admin/auth/login
GET  /api/admin/auth/profile
```

```json
{
  "data": {
    "id": 3,
    "email": "employee@admin.com",
    "roles": ["employee"],
    "permissions": [
      "navmenuitem.view",
      "navmenuitem.create",
      "navmenuitem.update",
      "navmenuitem.delete"
    ]
  }
}
```

| حقل | استخدام |
|-----|---------|
| `permissions` | مصفوفة **نصوص** — مصدر إظهار/إخفاء القوائم والأزرار |
| `roles` | للعرض فقط (مثل «موظف») — **لا** تعتمدوا عليها لإخفاء القوائم |

الأسماء حساسة لحالة الأحرف: `navmenuitem.view` مو `NavMenuItem.View`.

---

## 2) القاعدة

```ts
function can(permissions: string[], required: string | string[]): boolean {
  const list = Array.isArray(required) ? required : [required];
  return list.some((p) => permissions.includes(p));
}
```

| العنصر | متى يظهر |
|--------|----------|
| رابط السايدبار | `can(perms, 'xxx.view')` |
| زر إضافة | `can(perms, 'xxx.create')` |
| زر تعديل / حفظ | `can(perms, 'xxx.update')` |
| زر حذف | `can(perms, 'xxx.delete')` |
| مجموعة السايدبار (مثل «الأقسام والصفحات») | تظهر فقط إذا **عنصر واحد على الأقل** داخلها ظاهر |

إذا `permissions` فيها بس `navmenuitem.*` → يظهر **شريط التنقّل فقط**، وباقي الأقسام تختفي.

---

## 3) خريطة السايدبار → صلاحية `view`

اربطوا كل رابط بالقيمة اللي تحت (نفس أسماء Spatie):

### الإحصائيات والتقارير
| عنصر UI | صلاحية |
|---------|--------|
| الإحصائيات | `statistics.view` |
| التقارير | `reports.view` |

### إدارة المحتوى
| عنصر UI | صلاحية |
|---------|--------|
| المنتجات | `product.view` |
| المتغيّرات / متغيّرات المتجر | `productvariant.view` / `shopproductvariant.view` |
| الفئات | `category.view` |
| الماركات | `brand.view` |
| البنرات | `banner.view` |
| الأيقونات | `icon.view` |
| الضمانات | `warranty.view` |
| الوحدات | `unit.view` |
| العروض / البروموشن | `promotion.view` |

### الأقسام والصفحات
| عنصر UI | صلاحية |
|---------|--------|
| الصفحات | `page.view` |
| أقسام الصفحة | `pagesection.view` |
| الأقسام | `section.view` |
| **شريط التنقّل** | **`navmenuitem.view`** |

### المواقع
| عنصر UI | صلاحية |
|---------|--------|
| دول البيع | `salecountry.view` |
| المحافظات / المدن / المناطق | `governorate.view` · `city.view` · `area.view` |
| الدول | `country.view` |

### الطلبات
| عنصر UI | صلاحية |
|---------|--------|
| الطلبات | `order.view` |
| الطلب السريع | `customorderrequest.view` |
| سلل / جداول | `basket.view` · `schedule.view` · `schedulebasket.view` |

### المالية والمحاسبة
| عنصر UI | صلاحية |
|---------|--------|
| محاسبة الموردين | `vendoraccounting.view` |
| طلبات السحب (بائع) | `vendorwithdrawrequest.view` |
| طلبات سحب المسوّقين | `affiliatewithdrawrequest.view` |
| معاملات محفظة المسوّق | `affiliatewallettransaction.view` |

### الإعدادات
| عنصر UI | صلاحية |
|---------|--------|
| الإعدادات العامة | `setting.view` |
| إعدادات النظام | `systemsetting.view` |
| طرق التواصل | راجعوا اسم الصلاحية من `GET /api/admin/permissions` إن وُجدت؛ وإلا اربطوا بـ `setting.view` |
| الألوان | `color.view` |
| اللغات | `language.view` |

### مستخدمون وأدوار
| عنصر UI | صلاحية |
|---------|--------|
| الأدمن | `admin.view` |
| الأدوار | `role.view` |
| المستخدمون | `user.view` |
| السائقون | `driver.view` |
| المتاجر / البائعون | `shop.view` · `vendor.view` |

> قائمة الأسماء الكاملة من السيرفر: `GET /api/admin/permissions` (تحتاج `role.view`).

---

## 4) مثال — موظف شريط التنقّل فقط

`permissions` = الأربعة فوق فقط.

| يظهر | يختفي |
|------|--------|
| شريط التنقّل | الإحصائيات، المنتجات، الصفحات، دول البيع، الطلب السريع، المحاسبة، الألوان، … |
| مجموعة «الأقسام والصفحات» **إذا** فيها رابط ظاهر واحد (شريط التنقّل) | باقي الروابط داخل المجموعة |

ممنوع تسجيل الدخول ثم عرض سايدبار ثابت hardcoded بدون فحص `permissions`.

---

## 5) إنشاء / تعديل دور (سوبر أدمن)

```http
POST /api/admin/roles
PUT  /api/admin/roles/{id}
```

```json
{
  "name": "employee",
  "permissions": [
    { "id": 120 },
    { "id": 121 },
    { "id": 122 },
    { "id": 123 }
  ]
}
```

الباك يعمل **`sync`**: اللي ما انرسل ينشال من الدور.  
بعد تعديل صلاحيات موظف مسجّل دخول: لازم **Refresh profile** أو إعادة Login عشان تتحدّث المصفوفة في الواجهة.

---

## 6) أخطاء الشبكة

| كود | المعنى | الواجهة |
|-----|--------|---------|
| 401 | توكن باطل | رجّعوا لصفحة الدخول |
| 403 | ما عنده الصلاحية | أخفوا العنصر؛ اعرضوا «ليس لديك صلاحية» إذا فتح الرابط مباشرة |

---

## 7) Checklist

- [ ] بعد Login خزّنوا `data.permissions` في الـ store
- [ ] كل رابط سايدبار مربوط بـ `*.view`
- [ ] أزرار create/update/delete مربوطة بنفس البادئة
- [ ] مجموعة فارغة من الروابط → أخفوا عنوان المجموعة
- [ ] موظف `navmenuitem.*` فقط → يظهر شريط التنقّل فقط
- [ ] بعد تعديل الدور → أعدوا جلب Profile
- [ ] لا تعتمدوا على `roles` لإخفاء القوائم
- [ ] عند 403 لا تكسروا الصفحة

**الباك جاهز — الداشبورد يتبع هذا الملف.**
