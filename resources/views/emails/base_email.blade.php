@component('mail::message')
<div style="text-align: center;">
  {!! nl2br(e($messageText)) !!}
</div>

**Contact Us**
- 📧 Email: [info@deemafashion.com](mailto:info@deemafashion.com)
- 📞 Phone: +963 999 999 999

🌐 Follow us on:
- [Instagram](https://www.instagram.com/e)
- [Facebook](https://www.facebook.com/s)

Best regards,<br>
{{ config('app.name') }}

@endcomponent