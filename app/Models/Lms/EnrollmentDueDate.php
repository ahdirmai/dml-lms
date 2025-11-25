<?php

namespace App\Models\Lms;

use App\Models\Lms\Enrollment;
use Illuminate\Database\Eloquent\Model;

class EnrollmentDueDate extends Model
{
    protected $fillable = [
        'enrollment_id',
        'start_date',
        'end_date',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
