<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: DejaVu Sans, sans-serif; }
        .certificate {
            border: 6px double #2c3e50;
            border-radius: 2%;
            padding: 20px 30px;
            margin: 0 auto;
            width: 90%;
            box-sizing: border-box;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .logo{
            width: 50%;
            float:left;
        }
        .logo img { height: 70px; }
        .org-name {
            width: 50%;
            float:right;
            text-align: right;
            font-size: 14px;
            color: #555;
        }
        .title {
            text-align: center;
            margin: 20px 0 10px;
        }
        .title h1 {
            font-size: 32px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #555;
            margin-bottom: 25px;
        }
        .recipient {
            text-align: center;
            margin: 10px 0 25px;
        }
        .recipient-name {
            font-size: 26px;
            font-weight: bold;
            border-bottom: 1px solid #999;
            display: inline-block;
            padding: 0 20px 5px;
            margin-top: 5px;
        }
        .course-block {
            text-align: center;
            margin-bottom: 25px;
        }
        .course-name {
            font-size: 22px;
            font-weight: 600;
        }
        .dates {
            text-align: center;
            font-size: 14px;
            margin-bottom: 35px;
        }
        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 20px;
            font-size: 12px;
        }
        .signature-line {
            border-top: 1px solid #999;
            width: 180px;
            margin-top: 40px;
            text-align: center;
            padding-top: 5px;
        }
        .qr-block {
            text-align: right;
        }
        .qr-block img {
            height: 80px;
        }
    </style>
</head>
<body>
<div class="certificate">
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('logo.webp') }}" alt="Logo">
        </div>
        <div class="org-name">
            <strong>{{ config('app.name', 'Certificate Platform') }}</strong><br>
            Official Certificate of Completion
        </div>
    </div>

    <div class="title">
        <h1>Certificate of Completion</h1>
    </div>

    <div class="subtitle">
        This document is to certify that the following student has successfully
        completed the requirements for the course listed below.
    </div>

    <div class="recipient">
        <div>Presented to</div>
        <div class="recipient-name">{{ $student_name }}</div>
    </div>

    <div class="course-block">
        <div>for completing the course</div>
        <div class="course-name">{{ $course }}</div>
        @isset($grade)
            <div style="margin-top:8px;font-size:14px;color:#555;">Grade / Result: <strong>{{ $grade }}</strong></div>
        @endisset
    </div>

    <div class="dates">
        <div>Completion Date: <strong>{{ $completed_date }}</strong></div>
        <div>Issue Date: <strong>{{ $issue_date }}</strong></div>
        @isset($certificate_code)
            <div style="margin-top:8px;font-size:12px;color:#777;">
                Certificate ID: <strong>{{ $certificate_code }}</strong>
            </div>
        @endisset
    </div>

    <div class="footer-row">
        <div class="signature-line">
            Authorized Signatory
        </div>
        <div class="qr-block">
            @if(! empty($sha256_hash))
                <img src="{{ url(route('certificate.qr', $sha256_hash, false)) }}" alt="Verification QR">
            @endif
        </div>
    </div>
</div>
</body>
</html>
