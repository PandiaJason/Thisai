<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificate of Completion</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #1e293b;
        }
        .certificate-container {
            width: 100%;
            height: 100%;
            padding: 40px;
            box-sizing: border-box;
            border: 15px double #1e3a8a;
            position: relative;
            background-color: #fafbfc;
        }
        .header {
            text-align: center;
            margin-top: 20px;
        }
        .logo-text {
            font-size: 28px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 2px;
            margin: 0;
        }
        .logo-subtext {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            letter-spacing: 3px;
            margin-top: 5px;
            text-transform: uppercase;
        }
        .title {
            text-align: center;
            margin-top: 50px;
        }
        .title h1 {
            font-size: 36px;
            font-weight: 300;
            color: #0f172a;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .title p {
            font-size: 14px;
            color: #64748b;
            margin-top: 5px;
            font-style: italic;
        }
        .recipient {
            text-align: center;
            margin-top: 40px;
        }
        .recipient-text {
            font-size: 16px;
            color: #475569;
        }
        .recipient-name {
            font-size: 28px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 2px solid #e2e8f0;
            display: inline-block;
            padding-bottom: 5px;
            margin-top: 10px;
            min-width: 300px;
        }
        .achievement {
            text-align: center;
            margin-top: 30px;
            padding: 0 50px;
            line-height: 1.6;
        }
        .achievement-text {
            font-size: 15px;
            color: #475569;
        }
        .achievement-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 8px;
        }
        .footer-section {
            margin-top: 80px;
            width: 100%;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-col {
            width: 33%;
            vertical-align: bottom;
        }
        .cert-info {
            font-size: 11px;
            color: #64748b;
            line-height: 1.5;
        }
        .cert-number {
            font-family: monospace;
            font-weight: bold;
            color: #334155;
        }
        .signature-area {
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            width: 180px;
            display: inline-block;
            margin-top: 45px;
            text-align: center;
            padding-top: 5px;
        }
        .signature-text {
            font-size: 12px;
            color: #475569;
            font-weight: bold;
        }
        .signature-title {
            font-size: 10px;
            color: #64748b;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            font-size: 120px;
            font-weight: bold;
            color: #1e3a8a;
            z-index: -1;
            pointer-events: none;
            letter-spacing: 10px;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="watermark">THISAI</div>
        
        <div class="header">
            <div class="logo-text">THISAI</div>
            <div class="logo-subtext">IAS Academy</div>
        </div>

        <div class="title">
            <h1>Certificate of Completion</h1>
            <p>This is proudly presented to</p>
        </div>

        <div class="recipient">
            <div class="recipient-name">{{ $certificate->user->name }}</div>
        </div>

        <div class="achievement">
            <span class="achievement-text">for successfully completing the requirements of the course / exam</span>
            <div class="achievement-title">
                {{ $certificate->course?->title ?? $certificate->exam?->title ?? 'IAS Evaluation Module' }}
            </div>
        </div>

        <div class="footer-section">
            <table class="footer-table">
                <tr>
                    <td class="footer-col">
                        <div class="cert-info">
                            <div><strong>Date Issued:</strong> {{ $certificate->issued_at->format('F d, Y') }}</div>
                            <div><strong>Verification Link:</strong></div>
                            <div style="font-size: 10px; color: #3b82f6;">{{ route('certificates.verify', $certificate->certificate_number) }}</div>
                        </div>
                    </td>
                    <td class="footer-col" style="text-align: center;">
                        <div class="cert-info">
                            <div><strong>Certificate ID:</strong></div>
                            <div class="cert-number">{{ $certificate->certificate_number }}</div>
                        </div>
                    </td>
                    <td class="footer-col">
                        <div class="signature-area">
                            <div class="signature-line">
                                <div class="signature-text">Director</div>
                                <div class="signature-title">THISAI IAS Academy</div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
