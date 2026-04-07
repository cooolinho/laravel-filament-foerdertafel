<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        /* Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .content-cell { padding: 24px 16px !important; }
            .header-cell { padding: 24px 16px !important; }
            .footer-cell { padding: 20px 16px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

{{-- Äußerer Wrapper --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f6f8;">
    <tr>
        <td style="padding: 32px 16px;">

            {{-- E-Mail-Container --}}
            <table role="presentation" class="email-container" cellspacing="0" cellpadding="0" border="0"
                   width="600" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">

                {{-- ── HEADER ── --}}
                <tr>
                    <td class="header-cell" style="background-color: #16a34a; padding: 32px 40px; text-align: center;">
                        @if(!empty($logoUrl))
                            <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" height="50"
                                 style="display: block; margin: 0 auto 12px; max-height: 50px; width: auto;">
                        @endif
                        <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.3px;">
                            {{ config('app.name') }}
                        </h1>
                    </td>
                </tr>

                {{-- ── INHALTS-TRENNLINIE --}}
                <tr>
                    <td style="height: 4px; background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);"></td>
                </tr>

                {{-- ── HAUPTINHALT ── --}}
                <tr>
                    <td class="content-cell" style="padding: 40px 40px 32px; color: #374151; font-size: 15px; line-height: 1.7;">

                        {{-- Grußzeile --}}
                        @if(!empty($recipientName))
                            <p style="margin: 0 0 20px; font-size: 16px; color: #111827;">
                                Guten Tag, <strong>{{ $recipientName }}</strong>,
                            </p>
                        @endif

                        {{-- Dynamischer Inhalt aus dem DB-Template --}}
                        {!! $content !!}

                    </td>
                </tr>

                {{-- ── TRENNLINIE VOR FOOTER ── --}}
                <tr>
                    <td style="padding: 0 40px;">
                        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 0;">
                    </td>
                </tr>

                {{-- ── FOOTER ── --}}
                <tr>
                    <td class="footer-cell" style="padding: 24px 40px 28px; background-color: #f9fafb; text-align: center;">
{{--                        <p style="margin: 0 0 8px; font-size: 13px; color: #6b7280; line-height: 1.5;">--}}
{{--                            Diese E-Mail wurde automatisch von <strong>{{ config('app.name') }}</strong> versandt.<br>--}}
{{--                            Bitte antworten Sie nicht direkt auf diese E-Mail.--}}
{{--                        </p>--}}

                        @if(!empty($replyToAddress))
                            <p style="margin: 0 0 8px; font-size: 13px; color: #6b7280;">
                                Bei Fragen wenden Sie sich an:
                                <a href="mailto:{{ $replyToAddress }}" style="color: #16a34a; text-decoration: none;">{{ $replyToAddress }}</a>
                            </p>
                        @endif

                        @if(!empty($appUrl))
                            <p style="margin: 8px 0 0; font-size: 12px; color: #9ca3af;">
                                <a href="{{ $appUrl }}" style="color: #9ca3af; text-decoration: underline;">{{ $appUrl }}</a>
                            </p>
                        @endif

                        <p style="margin: 12px 0 0; font-size: 12px; color: #d1d5db;">
                            &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; Alle Rechte vorbehalten.
                        </p>
                    </td>
                </tr>

            </table>
            {{-- Ende E-Mail-Container --}}

        </td>
    </tr>
</table>

</body>
</html>

