<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat - {{ $course->title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            width: 100%;
            height: 100%;
        }
        .page {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        /* Background and Frame - Fixed to ensure they don't affect flow */
        .certificate-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }
        
        .certificate-frame {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        
        /* Content Container - Use Table for Vertical Centering in DomPDF */
        .certificate-content-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .certificate-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        .certificate-cell {
            vertical-align: middle;
            text-align: center;
            padding: 67px; /* 6cqw approx */
        }
        
        .flag-logo {
            position: absolute;
            top: 45px;
            left: 67px;
            width: 100px; /* Increased from 90px */
            height: auto;
        }

        .logo-text {
            position: absolute;
            top: 45px;
            left: 67px;
            font-size: 28px; /* Increased */
            font-weight: bold;
            color: #4f46e5;
        }
        
        .certificate-title {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 72px; /* Increased from 60px */
            font-weight: 300;
            letter-spacing: 10px;
            color: #2c2c2c;
            margin: 0 0 15px 0;
            text-transform: uppercase;
            line-height: 1;
        }
        
        .certificate-subtitle {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 20px; /* Increased from 16px */
            font-weight: 400;
            letter-spacing: 4px;
            color: #666;
            margin: 0 0 50px 0;
            position: relative;
            display: inline-block;
            text-transform: uppercase;
        }
        
        /* Decorative lines for subtitle */
        .line-left, .line-right {
            display: inline-block;
            width: 70px; /* Increased */
            height: 2px;
            background-color: #c41e3a;
            vertical-align: middle;
            margin: 0 20px;
        }
        
        .presented-to {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 18px; /* Increased from 15px */
            color: #666;
            margin-bottom: 25px;
        }
        
        .recipient-name {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 64px; /* Increased from 48px */
            font-weight: 400;
            font-style: italic;
            color: #2c2c2c;
            margin: 0 0 35px 0;
            line-height: 1.2;
        }
        
        .completion-text {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 18px; /* Increased from 15px */
            color: #666;
            margin-bottom: 20px;
        }
        
        .module-name {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 42px; /* Increased from 32px */
            font-weight: 600;
            font-style: italic;
            color: #2c2c2c;
            margin-bottom: 70px;
        }
        
        .issued-by {
            position: absolute;
            bottom: 45px;
            right: 67px;
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px; /* Increased from 12px */
            color: #666;
            text-align: right;
            line-height: 1.5;
        }
        
        .issued-by strong {
            font-weight: 600;
            color: #2c2c2c;
        }

        .cert-number {
            position: absolute;
            bottom: 100px; /* Adjusted for bottom right placement */
            right: 67px;
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #999;
            text-align: right;
        }
    </style>
</head>
<body>
    <!-- Background Image -->
    <img src="{{ public_path('Sertif Background-min.png') }}" alt="Background" class="certificate-bg">
    
    <!-- Frame Image -->
    <img src="{{ public_path('Sertif-Frame-removed.png') }}" alt="Frame" class="certificate-frame">

    <div class="page">
        <div class="certificate-content-wrapper">
            <!-- Logo/Flag -->
            @if(file_exists(public_path('Logo DML.png')))
                <img src="{{ public_path('Logo DML.png') }}" alt="DML Logo" class="flag-logo">
            @else
                <div class="logo-text">DML LMS</div>
            @endif

            <!-- Certificate Number (Moved to Top) -->
            

            <table class="certificate-table">
                <tr>
                    <td class="certificate-cell">
                        <h1 class="certificate-title">CERTIFICATE</h1>
                        
                        <div class="certificate-subtitle">
                            <span class="line-left"></span>
                            OF COMPLETION
                            <span class="line-right"></span>
                        </div>
                        
                        <p class="presented-to">This certificate is proudly presented to :</p>
                        
                        <h2 class="recipient-name">{{ $user->name }}</h2>
                        
                        <p class="completion-text">for successfully completing the learning module:</p>
                        
                        <p class="module-name">{{ $course->title }}</p>
                    </td>
                </tr>
            </table>

            <div class="issued-by">
                <strong>Issued by:</strong> Dutabahari Menara Line<br>
                <strong>Date:</strong> {{ $date }}
            </div>
            <div class="cert-number">
                No. Sertifikat: {{ $certificate->certificate_number }}
            </div>
        </div>
    </div>
</body>
</html>
