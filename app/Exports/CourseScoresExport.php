<?php

namespace App\Exports;

use App\Models\Lms\Enrollment;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CourseScoresExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;
    protected $courseId;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->courseId = $filters['course_id'] ?? null;
    }

    public function query()
    {
        $query = Enrollment::query()
            ->with([
                'user',
                'course',
                'course.pretest',
                'course.posttest',
                // Eager load attempts via User is heavy but ensures we have data.
                // Alternatively rely on N+1 if volume is low.
                // Best compromise: Eager load only if possible, but map() will handle logic.
            ]);

        // If specific course requested
        if ($this->courseId) {
            $query->where('course_id', $this->courseId);
        } else {
            // Apply course filters via whereHas
            $filters = $this->filters;
            $q = trim((string) ($filters['q'] ?? ''));
            $status = $filters['status'] ?? null;
            $categoryId = $filters['category_id'] ?? null;
            $instructorId = $filters['instructor_id'] ?? null;

            $query->whereHas('course', function ($courseQuery) use ($q, $status, $categoryId, $instructorId) {
                $courseQuery->when($q, function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('title', 'like', "%{$q}%")
                            ->orWhere('subtitle', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%");
                    });
                })
                ->when($status, fn ($qq) => $qq->where('status', $status))
                ->when($categoryId, function ($qq) use ($categoryId) {
                    $qq->whereHas('categories', function ($w) use ($categoryId) {
                        $w->where('categories.id', (int) $categoryId);
                    });
                })
                ->when($instructorId, fn ($qq) => $qq->where('instructor_id', $instructorId));
            });
        }
        
        // Order by Course Title then User Name
        $query->join('courses', 'enrollments.course_id', '=', 'courses.id')
              ->join('users', 'enrollments.user_id', '=', 'users.id')
              ->orderBy('courses.title')
              ->orderBy('users.name')
              ->select('enrollments.*');

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nama Peserta',
            'Nama Kelas',
            'Pretest',
            'Waktu Pretest',
            'Posttest',
            'Waktu Posttest',
        ];
    }

    /**
     * @param Enrollment $enrollment
     */
    public function map($enrollment): array
    {
        $user = $enrollment->user;
        $course = $enrollment->course;
        
        $pretestScore = '-';
        $pretestTime = '-';
        $posttestScore = '-';
        $posttestTime = '-';

        if ($course && $pretest = $course->pretest) {
            // Using accessor from Enrollment if efficient, or query manually
             $attempt = \App\Models\Lms\QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $pretest->id)
                ->orderByDesc('score') // Highest score? Or latest? Request said "export score", typically highest is best representation of ability, but pretest is usually "initial". Let's use Latest for Pre/Post usually.
                // But generally "Score" implies the best result or the one that counts.
                // Re-reading request: "export untuk score pretest dan posttest".
                // Let's stick to 'latest' as usually pretest is taken once.
                ->orderBy('created_at', 'desc')
                ->first();
             if ($attempt) {
                 $pretestScore = $attempt->score;
                 $pretestTime = $attempt->finished_at ? $attempt->finished_at->format('Y-m-d H:i') : '-';
             }
        }

        if ($course && $posttest = $course->posttest) {
             $attempt = \App\Models\Lms\QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $posttest->id)
                ->orderByDesc('score') // Posttest often allows retries to pass. Highest score is safer.
                ->first();
             if ($attempt) {
                 $posttestScore = $attempt->score;
                 $posttestTime = $attempt->finished_at ? $attempt->finished_at->format('Y-m-d H:i') : '-';
             }
        }

        return [
            $user->name ?? 'Unknown User',
            $course->title ?? 'Unknown Course',
            $pretestScore,
            $pretestTime,
            $posttestScore,
            $posttestTime,
        ];
    }
}
