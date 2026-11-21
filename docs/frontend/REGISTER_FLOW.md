# تدفق التسجيل — ويب + Flutter

> **أرسلوا هذا الملف لفريقي الويب و Flutter.**  
> **آخر تحديث:** 16 أيلول 2026  
> Base: `/api/user` — **بدون توكن** حتى خطوة OTP  
> `Accept-Language: ar|en`  
> **الباك جاهز — التعديل UI فقط**

التسجيل = هاتف + كلمة مرور + محافظة/مدينة + **OTP على SMS**. الإيميل اختياري.

---

## الفهرس

1. [الرسم](#1-الرسم)
2. [الشاشات](#2-الشاشات)
3. [المحافظة والمدينة](#3-المحافظة-والمدينة)
4. [إنشاء الحساب](#4-إنشاء-الحساب)
5. [تحقق OTP](#5-تحقق-otp)
6. [إعادة إرسال الكود](#6-إعادة-إرسال-الكود)
7. [بعد النجاح](#7-بعد-النجاح)
8. [الدخول لاحقاً](#8-الدخول-لاحقاً)
9. [الأخطاء](#9-الأخطاء)
10. [Checklist](#10-checklist)

---

## 1) الرسم

```
[شاشة التسجيل]
   1. GET /governorates
   2. اختيار محافظة → GET /cities?governorate_id=
   3. تعبئة: اسم · هاتف · كلمة مرور · مدينة · (إيميل اختياري)
   4. POST /auth/register
        │
        ├─ 200  → لا توكن · انتقل لشاشة OTP  (احفظوا phone + password محلياً)
        ├─ 422  حساب موجود → «الحساب موجود بالفعل» · زِر دخول
        └─ 422  validation → اعرضوا errors
        │
[شاشة OTP]  كود مؤقت **00000** · صلاحية 60 دقيقة · بدون SMS
   5. POST /auth/verify-otp  { phone, code }
        │
        ├─ 200  → data.token + data.user  → احفظوا التوكن · ادخلوا التطبيق
        └─ 4xx  كود غلط/منتهي → «رمز التحقق غير صالح أو منتهي الصلاحية»
        │
   إعادة الإرسال:
        POST /auth/login  { phone, password }   ← مو /send-otp
        403 + SMS جديد → ابقوا على شاشة OTP
        │
[بعد التوكن]
   6. Authorization: Bearer {token}
   7. (اختياري) POST /auth/store-token  { deviceId, fcmToken }
```

**ممنوع:** توكن بعد `register` · `email: ""` · `/auth/send-otp` على شاشة تسجيل الحساب.

`/auth/send-otp` = نسيت كلمة المرور (`reset_password`) — **لا يفعّل** كود التسجيل.

---

## 2) الشاشات

| # | الشاشة | ماذا يحدث |
|---|--------|-----------|
| 1 | تسجيل | فورم + محافظة/مدينة اختياريان |
| 2 | OTP | 5 خانات · عدّاد إعادة إرسال |
| 3 | الرئيسية / الملف | بعد حفظ التوكن |

لا تبويب «إيميل أو هاتف». الهاتف **مطلوب دائماً**، الإيميل label «اختياري».

---

## 3) المحافظة والمدينة

عامان، بدون توكن.

```http
GET /api/user/governorates
Accept-Language: ar
```

```json
{ "status": true, "data": [{ "id": 1, "name": "دمشق" }] }
```

بعد اختيار المحافظة:

```http
GET /api/user/cities?governorate_id=1
```

```json
{ "status": true, "data": [{ "id": 4, "name": "المزة" }] }
```

`governorate_id` **مطلوب** على `/cities` — بدونها 422.

عند تغيير المحافظة: صفّروا `city_id` وأعيدوا جلب المدن.

---

## 4) إنشاء الحساب

```http
POST /api/user/auth/register
Content-Type: application/json
Accept-Language: ar
```

```json
{
  "name": "أحمد محمد",
  "phone": "0991234567",
  "password": "Passw0rd!",
  "city_id": 4,
  "governorate_id": 1
}
```

مع إيميل (اختياري):

```json
{
  "name": "أحمد محمد",
  "phone": "0991234567",
  "email": "ahmad@example.com",
  "password": "Passw0rd!",
  "city_id": 4,
  "governorate_id": 1
}
```

### الحقول

| الحقل | مطلوب؟ | القاعدة |
|--------|--------|---------|
| `name` | نعم | نص |
| `phone` | **نعم** | أرقام فقط `/^\d+$/` — بدون `+` مسافة `-` |
| `email` | لا | إذا وُجد: إيميل صحيح. **لا** `""` — احذفوا المفتاح أو `null` |
| `password` | نعم | ≥ 8 + حرف صغير + كبير + رقم + رمز (`Passw0rd!`) |
| `city_id` | لا | إن وُجد: موجود في `cities`. **لا** `""` — احذفوا المفتاح أو `null` |
| `governorate_id` | لا | إن وُجد: موجود في `governorates`. **لا** `""` — احذفوا المفتاح أو `null` |

الإيميل إن أُرسل يُحفظ **بدون** توثيق. التوثيق على الهاتف فقط.

### نجاح — 200

```json
{
  "status": true,
  "message": "Success",
  "data": []
}
```

`data` فاضية. **ما في توكن.** انتقلوا فوراً لشاشة OTP واحفظوا `phone` + `password` في state الشاشة (لإعادة الإرسال).

الباك ينشئ المستخدم (`is_active: true`). **مؤقت (16 أيلول):** ما في SMS. الكود الثابت **`00000`** لمدة **60 دقيقة**.

---

## 5) تحقق OTP

```http
POST /api/user/auth/verify-otp
```

```json
{
  "phone": "0991234567",
  "code": "00000"
}
```

- `code` نص 5 أرقام. **مؤقت:** دائماً `"00000"` (ما في رسالة SMS).
- نفس `phone` المستخدم في التسجيل (أرقام فقط).

### نجاح — 200 — هنا يجي التوكن

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "user": {
      "id": 12,
      "name": "أحمد محمد",
      "phone": "0991234567",
      "addresses": [],
      "is_subscription": false,
      "affiliate": { "is_affiliate": false, "approved": false },
      "currency": null
    },
    "token": "1|xxxxxxxx"
  }
}
```

احفظوا `data.token` (Bearer). `email` قد لا يظهر في هذا الـ resource إذا الحساب على هاتف.

أول تحقق ناجح يمنح **نقاط تسجيل** (إشعار من الباك — لا تحسبوا النقاط على الفرونت).

`email` في الملف الشخصي لاحقاً: `GET /api/user/auth/profile` → `email` ممكن `null`.

---

## 6) إعادة إرسال الكود

لا يوجد endpoint اسمه resend-register-otp.

| طلب | النتيجة |
|-----|---------|
| `POST /auth/send-otp` | OTP من نوع **reset_password** — **غلط** لشاشة التسجيل |
| `POST /auth/login` بنفس `phone` + `password` | إذا الحساب مو موثّق: يرسل OTP تسجيل جديد ويرجع **403** |

```http
POST /api/user/auth/login
```

```json
{
  "phone": "0991234567",
  "password": "Passw0rd!"
}
```

**403** + رسالة التحقق = نجاح إعادة الإرسال. ابقوا على شاشة OTP. لا ترجعوا للتسجيل ولا تمسحوا الحقول.

إذا الحساب **موثّق أصلاً** نفس الطلب يرجع **200 + token** (دخول عادي).

عدّاد 60 ثانية على الزر قبل السماح بإعادة الإرسال (UX).

---

## 7) بعد النجاح

```http
Authorization: Bearer {token}
```

### إشعارات (موبايل / ويب إن وُجد FCM)

```http
POST /api/user/auth/store-token
Authorization: Bearer {token}
```

```json
{
  "deviceId": "device-uuid",
  "fcmToken": "fcm-token"
}
```

الاثنان مطلوبان.

بعدها: الرئيسية، أو `GET /api/user/auth/profile`.

---

## 8) الدخول لاحقاً

```http
POST /api/user/auth/login
```

```json
{
  "phone": "0991234567",
  "password": "Passw0rd!"
}
```

| حالة | كود | ماذا تفعل الواجهة |
|------|-----|-------------------|
| موثّق + كلمة صحيحة | 200 | احفظوا `data.token` |
| موثّق + كلمة غلط | خطأ بيانات | توست «بيانات تسجيل الدخول غير صحيحة» |
| هاتف غير موجود | خطأ | «الحساب غير مسجل» → زِر تسجيل |
| موجود وغير موثّق | **403** | SMS جديد → شاشة OTP (نفس تدفق التسجيل) |

الدخول بالإيميل ممكن (`email` + `password`) إذا كان محفوظاً — التسجيل نفسه يبقى بالهاتف.

---

## 9) الأخطاء

اعرضوا `message`، و`errors.field[0]` عند 422.

| الحالة | HTTP | معنى |
|--------|------|------|
| validation | 422 | `errors`: phone / password / city_id / … |
| هاتف مسجّل ونشط | 422 | «الحساب موجود بالفعل» |
| OTP غلط أو منتهي | رسالة `otp_valid` | أبقوا شاشة OTP |
| غير موثّق عند login | 403 | أُرسل كود جديد |
| حساب غير موجود | — | «الحساب غير مسجل» |
| كلمة غلط | — | «بيانات تسجيل الدخول غير صحيحة» |

### تنظيف الهاتف قبل الإرسال

```js
const phone = raw.replace(/\D/g, ''); // أرقام فقط
```

```dart
final phone = raw.replaceAll(RegExp(r'\D'), '');
```

### كلمة المرور — تحقق محلي قبل الإرسال

```
طول ≥ 8
حرف صغير  a-z
حرف كبير  A-Z
رقم       0-9
رمز       مثل ! @ # $
```

مثال مقبول: `Passw0rd!`

### إيميل فاضي

```js
const body = { name, phone, password, city_id, governorate_id };
if (email.trim()) body.email = email.trim();
// لا body.email = ''
```

---

## 10) Checklist

- [ ] محافظة/مدينة اختياريان — إن اختيرت محافظة: `GET /cities?governorate_id=` إلزامي
- [ ] `phone` مطلوب، أرقام فقط
- [ ] `email` اختياري — لا `""`
- [ ] كلمة المرور: 8 + صغير + كبير + رقم + رمز
- [ ] `POST /register` → **لا توكن** → شاشة OTP
- [ ] OTP: **`00000`** مؤقتاً · `POST /verify-otp` { `phone`, `code` }
- [ ] التوكن من `data.token` بعد verify فقط
- [ ] إعادة الإرسال = `POST /login` (403 متوقع) — **ليس** `/send-otp`
- [ ] حساب موجود 422 → دخول
- [ ] غير موثّق عند الدخول 403 → OTP
- [ ] `email` في البروفايل `null` آمن
- [ ] بعد التوكن: `store-token` إذا عندكم FCM
- [ ] لا تبويب إيميل/هاتف

**الباك جاهز — ويب و Flutter يتبعون هذا الملف.**
