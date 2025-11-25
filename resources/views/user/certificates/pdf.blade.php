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
        }
        .page {
            width: 100%;
            height: 100%;
            position: relative;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }
        
        /* Page 1: Certificate */
        .cert-container {
            padding: 40px;
            text-align: center;
            height: 100%;
            box-sizing: border-box;
            border: 20px solid #f3f4f6;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .logo {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5; /* brand color */
        }
        .title {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .subtitle {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
        }
        .presented-to {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .recipient-name {
            font-size: 36px;
            font-weight: bold;
            color: #111;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            display: inline-block;
            padding-bottom: 10px;
            min-width: 400px;
        }
        .course-name {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            color: #4f46e5;
        }
        .date-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding: 0 100px;
        }
        .signature {
            text-align: center;
        }
        .sign-line {
            border-top: 1px solid #333;
            width: 200px;
            margin: 10px auto 5px;
        }
        .cert-number {
            position: absolute;
            bottom: 20px;
            right: 40px;
            font-size: 12px;
            color: #999;
        }

        /* Page 2: Syllabus */
        .syllabus-container {
            padding: 50px;
        }
        .syllabus-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .syllabus-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .module-list {
            width: 100%;
            border-collapse: collapse;
        }
        .module-list th, .module-list td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .module-list th {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .lesson-item {
            padding-left: 20px;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
    <!-- Page 1 -->
    <div class="page">
        <div class="cert-container">
            <div class="logo">DML LMS</div>
            
            <div class="title">Sertifikat Kelulusan</div>
            <div class="subtitle">Diberikan sebagai tanda kelulusan kepada:</div>
            
            <div class="recipient-name">{{ $user->name }}</div>
            
            <div class="presented-to">Atas keberhasilannya menyelesaikan kursus:</div>
            <div class="course-name">{{ $course->title }}</div>
            
            <div class="date-section">
                <div class="signature">
                    <div style="height: 50px;"></div> <!-- Space for signature image -->
                    <div class="sign-line"></div>
                    <div><strong>{{ $course->instructor->name ?? 'Instruktur' }}</strong></div>
                    <div>Instruktur</div>
                </div>
                <div class="signature">
                    <div style="height: 50px;"></div>
                    <div class="sign-line"></div>
                    <div><strong>{{ $date }}</strong></div>
                    <div>Tanggal Terbit</div>
                </div>
            </div>

            <div class="cert-number">
                No. Sertifikat: {{ $certificate->certificate_number }}
            </div>
        </div>
    </div>

    <!-- Page 2 -->
    <div class="page">
        <div class="syllabus-container">
            <div class="syllabus-header">
                <div class="syllabus-title">Materi Kursus</div>
                <div style="color: #666; margin-top: 5px;">{{ $course->title }}</div>
            </div>

            <table class="module-list">
                <thead>
                    <tr>
                        <th width="10%">No</th>
                        <th width="60%">Modul / Pelajaran</th>
                        <th width="30%">Durasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($course->modules as $index => $module)
                        <tr style="background-color: #f3f4f6;">
                            <td><strong>{{ $index + 1 }}</strong></td>
                            <td><strong>{{ $module->title }}</strong></td>
                            <td><strong>{{ convert_seconds_to_duration($module->lessons->sum('duration_seconds')) }}</strong></td>
                        </tr>
                        @foreach($module->lessons as $lIndex => $lesson)
                            <tr>
                                <td></td>
                                <td class="lesson-item">{{ $lesson->title }}</td>
                                <td style="color: #777;">{{ convert_seconds_to_duration($lesson->duration_seconds) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
