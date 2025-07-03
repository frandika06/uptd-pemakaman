<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjek }}</title>
    <style type="text/css">
        /* Reset styles */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            color: #333333;
            background-color: #f4f4f4;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 600px;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            max-width: 100%;
            height: auto;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .content {
            padding: 20px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333333;
            margin-bottom: 10px;
        }

        .section-content {
            font-size: 14px;
            color: #555555;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
        }

        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                border-radius: 0 !important;
            }

            .header,
            .footer {
                border-radius: 0 !important;
            }
        }
    </style>
</head>

<body>
    <table class="container" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header" style="text-align: center;">
                <img src="{{ asset('logo/logo-light.png') }}" alt="{{ env('APP_NAME') }}" style="max-height: 50px; margin-bottom: 10px;">
                <h1 style="margin: 0; font-size: 24px;">{{ env('APP_NAME') }}</h1>
                <p style="margin: 5px 0 0; font-size: 14px;">UPTD Pemakaman DPPP Kabupaten Tangerang</p>
            </td>
        </tr>
        <tr>
            <td class="content">
                <div class="section">
                    <p style="font-size: 16px; color: #333333;">Kepada Yth. {{ $nama_lengkap }},</p>
                    <p style="font-size: 14px; color: #555555; line-height: 1.6;">
                        Terima kasih telah menghubungi kami melalui portal helpdesk. Berikut adalah balasan untuk pesan Anda:
                    </p>
                </div>

                <div class="section">
                    <div class="section-title">Detail Pesan Anda</div>
                    <div class="section-content">
                        <p><strong>Subjek:</strong> {{ $subjek }}</p>
                        <p><strong>Pesan:</strong></p>
                        <div style="border-left: 3px solid #007bff; padding-left: 10px;">
                            {!! nl2br(e($pesan)) !!}
                        </div>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">Balasan Kami</div>
                    <div class="section-content">
                        {!! $balasan !!}
                    </div>
                </div>

                <div class="section">
                    <p style="font-size: 14px; color: #555555; line-height: 1.6;">
                        Jika Anda memiliki pertanyaan lebih lanjut, silakan hubungi kami kembali melalui portal kami.
                    </p>
                    <a href="{{ url('/') }}" class="button">Kunjungi Portal Kami</a>
                </div>
            </td>
        </tr>
        <tr>
            <td class="footer">
                <p style="margin: 0;">
                    Hak Cipta &copy; {{ date('Y') }} &#x25CF; UPTD Pemakaman &#x25CF; DPPP Kabupaten Tangerang
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
