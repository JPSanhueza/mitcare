<?php

namespace App\Services\Diplomas;

use App\Models\Course;
use App\Models\Teacher;
use App\Models\Student;
use Carbon\Carbon;

class DiplomaViewService
{
    public function makeViewData(
        Course $course,
        Teacher $teacher,
        Student $student,
        Carbon $issuedAt
    ): array {
        $organization = $teacher->organization; // 1:N

        return [
            'student'      => $student,
            'course'       => $course,
            'teacher'      => $teacher,
            'organization' => $organization,
            'issued_at'    => $issuedAt,
        ];
    }

    public function renderHtml(
        Course $course,
        Teacher $teacher,
        Student $student,
        Carbon $issuedAt
    ): string {
        $data = $this->makeViewData($course, $teacher, $student, $issuedAt);

        return view('diplomas.template', $data)->render();
    }
}
