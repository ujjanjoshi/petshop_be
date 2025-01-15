<x-mail::message>
<h4 style="text-align: center; font-size: 24px; line-height: 28px; font-weight: 500;">
OTP for {{decrypt($actual_email)}}
</h4>
 {!! $body !!}
<h1 style="text-align: center;"> {{$otp}}</h1>
Secure your Pulse account by verifying your email address.
<br>
<br>
This link will expire after 2 hours. To request another verification<br> link, please <a href="{{ config('app.url') }}/login" style="text-decoration: none; color: #007CAD;">log in</a> to prompt a re-send link.
<br>
<br>
{{ config('app.name') }}
</x-mail::message>
