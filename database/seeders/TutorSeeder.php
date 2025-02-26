<?php

namespace Database\Seeders;

use App\Models\Tutor;
use App\Models\Subject;
use App\Models\ClassLevel;
use Illuminate\Database\Seeder;

class TutorSeeder extends Seeder
{
    public function run(): void
    {
        $tutors = Tutor::all();
        $subjects = Subject::all();
        $classLevels = ClassLevel::all();

        foreach ($tutors as $tutor) {
            // Gán môn học ngẫu nhiên cho gia sư
            $randomSubjects = $subjects->random(rand(2, 4));
            foreach ($randomSubjects as $subject) {
                $tutor->subjects()->attach($subject->id, [
                    'price_per_hour' => rand(150000, 300000),
                    'experience_details' => 'Có kinh nghiệm giảng dạy ' . $subject->name . ' cho học sinh các cấp'
                ]);
            }

            // Gán cấp học ngẫu nhiên cho gia sư
            $randomClassLevels = $classLevels->random(rand(3, 6));
            foreach ($randomClassLevels as $level) {
                $tutor->classLevels()->attach($level->id, [
                    'price_per_hour' => rand(150000, 300000),
                    'experience_details' => 'Kinh nghiệm giảng dạy ' . $level->name
                ]);
            }
        }
    }
} 