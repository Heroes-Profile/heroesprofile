<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $headline }}</title>
</head>
<body style="margin:0;padding:0;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f3f4;margin:0;padding:32px 0;">
    <tr>
      <td align="center" style="padding:0 12px;">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:600px;background-color:#ffffff;">
          <tr>
            <td style="padding:44px 44px 40px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#3c4043;">
              <div style="font-size:22px;font-weight:700;color:#202124;margin:0 0 20px;">{{ $headline }}</div>

              @foreach ($intro as $line)
                <p style="margin:0 0 16px;">{{ $line }}</p>
              @endforeach

              @if ($reason !== '')
                <div style="border-left:4px solid #008b8b;padding:4px 0 4px 16px;margin:0 0 16px;color:#202124;">{!! $reason !!}</div>
              @endif

              @foreach ($outro as $line)
                <p style="margin:0 0 16px;">{{ $line }}</p>
              @endforeach

              @if ($button)
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 24px;">
                  <tr>
                    <td style="background-color:#008b8b;border-radius:4px;">
                      <a href="{{ $button['url'] }}" style="display:inline-block;padding:10px 20px;color:#ffffff;font-weight:700;text-decoration:none;">{{ $button['text'] }}</a>
                    </td>
                  </tr>
                </table>
              @endif

              <p style="margin:0 0 16px;">{{ $closing }}</p>

              <p style="margin:0;">
                Thanks,<br>
                Zemill<br>
                <a href="mailto:zemill@heroesprofile.com" style="color:#008b8b;">zemill@heroesprofile.com</a>
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
