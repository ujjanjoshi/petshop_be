<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<table width="100%" height="100%" cellpadding="0" cellspacing="0" bgcolor="#f5f6f7">
    <tr>
        <td height="50"></td>
    </tr>
    <tr>
        <td align="center" valign="top">
            <table width="612" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="border:1px solid #f1f2f5">
                <tr>
                    <td colspan="3" height="64" bgcolor="#ffffff" style="border-bottom:1px solid #eeeeee; padding:16px;" align="left">
                        <img src="https://rewardscdn.com/img/logo/Pulse_Logo_NavyBlue.jpg" width="120" height="48">
                    </td>
                </tr>
                <tr>
                    <td colspan="3" height="20"></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h4 style="text-align: center; font-size: 24px; line-height: 28px; font-weight: 500;">
                            Reset Password</h4>
                </tr>
                <tr>
                    <td colspan="3" align="center">
                        @php
                        $encryptedEmail = encrypt($email);
                        @endphp
                        <a href="{{ config('app.url') }}/resetPassword/{{ $encryptedEmail }}" style="display:inline-block;text-decoration:none; border-radius: 4px; color: #fff; 
    background-color: #103246;
    border-bottom: 8px solid #103246;
    border-left: 18px solid #103246;
    border-right: 18px solid #103246;
    border-top: 8px solid #103246;">
                            Reset Password
                        </a>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" height="20"></td>
                </tr>
                <tr>
                    <td width="20"></td>
                    <td>
                        <!-- <p style="font-size: 15px; color: #21313C; margin: 36px 32px;">Secure your Pulse account by verifying your email address.
                            <br>
                            <br>
                            <b>This link will expire after 2 hours. To request another verification<br> link, please <a href="{{ config('app.url') }}/login" style="text-decoration: none; color: #007CAD;">log in</a> to prompt a re-send link.
                            </b>
                        </p> -->
                    </td>
                </tr>
                <tr>
                    <td colspan="3" height="20"></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: center">
                        <span style="font-family:Helvetica,Arial,sans-serif;font-size:12px;color:#cccccc;">
                            This message was sent from Pulse Experiential Travel, 9119 Church St, Manassas, VA 20110</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" height="20"></td>
                </tr>
            </table>
        </td>
    </tr>
</table>