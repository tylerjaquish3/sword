<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Your Sword Account Has Been Activated</title>
</head>
<body style="margin:0; padding:0; background:#eceef3; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eceef3; padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08);">

                {{-- Header --}}
                <tr>
                    <td align="center" style="background:#0e1628; padding:32px 24px;">
                        <img src="{{ asset('images/logo.png') }}" alt="Sword" width="130" height="64" style="display:block; margin:0 auto;">
                    </td>
                </tr>

                {{-- Gold rule --}}
                <tr>
                    <td style="height:3px; background:#c9a84c; line-height:3px; font-size:3px;">&nbsp;</td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:36px 32px 12px;">
                        <div style="font-size:10px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:#c9a84c; margin-bottom:10px;">Account Activated</div>
                        <h1 style="margin:0 0 16px; font-family:Georgia,'Times New Roman',serif; font-size:24px; font-weight:500; color:#0e1628; line-height:1.3;">You're in, {{ $name }}.</h1>
                        <p style="margin:0 0 24px; font-size:15px; line-height:1.6; color:#4b5563;">
                            An administrator has reviewed and activated your Sword account. You can now sign in and start reading, journaling your commentary, tracking prayers, and building your topics and memory verses.
                        </p>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
                            <tr>
                                <td style="background:#0e1628; border-radius:8px;">
                                    <a href="{{ $loginUrl }}" style="display:inline-block; padding:13px 28px; font-size:14px; font-weight:600; color:#c9a84c; text-decoration:none; border-radius:8px;">Sign In to Sword &rarr;</a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0; font-size:13px; line-height:1.6; color:#9ca3af;">
                            If the button above doesn't work, copy and paste this link into your browser:<br>
                            <a href="{{ $loginUrl }}" style="color:#4b5563; word-break:break-all;">{{ $loginUrl }}</a>
                        </p>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:20px 32px 28px; border-top:1px solid rgba(14,22,40,0.08);">
                        <p style="margin:0; font-size:12px; color:#9ca3af; text-align:center;">
                            You're receiving this because an account was created for you at Sword.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
