# الداشبورد — لا اختيار افتراضي للمتغيّر (توضيح)

> **آخر تحديث:** 5 تشرين الثاني 2026  
> **ما في شغل داشبورد لهالميزة.**  
> ويب: [`WEB_NO_DEFAULT_VARIANT_SELECTION.md`](./WEB_NO_DEFAULT_VARIANT_SELECTION.md)  
> Flutter: [`FLUTTER_NO_DEFAULT_VARIANT_SELECTION.md`](./FLUTTER_NO_DEFAULT_VARIANT_SELECTION.md)

اختيار أول متغيّر تلقائياً مشكلة **صفحة المنتج في المتجر** (ويب/Flutter)، مو فورم الأدمن.

| فريق | شغل؟ |
|------|------|
| ويب / Flutter | نعم — لا `shop_variants[0]` عند وجود صفات |
| داشبورد | لا — بس تأكدوا صور المنتج الرئيسية + صور المتغيّرات محفوظة |
| باك | لا endpoint جديد |

صور المنتج والمتغيّر تبقى حسب [`PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md`](./PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md).

### Checklist داشبورد (اختياري)

- [ ] المنتج فيه صور رئيسية (`images` / thumbnail) تظهر قبل اختيار المتغيّر على المتجر
- [ ] كل متغيّر يرفع صوره إن لزم — وإلا المتجر يبقى على صورة المنتج بعد الاختيار
