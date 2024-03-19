<?php

namespace Database\Seeders;

use App\Counselling\Models\Counselling;
use App\Counselling\Models\CounsellingMessage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\RoleHelper;

use App\Models\User;
use App\Models\Role;
use App\Course\Models\Course;
use App\Course\Models\CourseMember;
use App\Counselling\Models\CounsellingSetup;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $surnames = [
            'Albrecht',
            'Lippert',
            'Poltermann',
            'Zauter',
            'Lehmann',
            'Mothes',
            'Engert',
            'Widerhold',
            'Rudolph',
            'Seer'
        ];

        foreach ($surnames as $surname) {
            // Create a teacher for each surname
            User::create([
                'name' => 'Trainer ' . $surname,
                'email' => strtolower($surname) . '-trainer@th-nuernberg.de',
                'password' => bcrypt(env('APP_ADMIN_PASSWORD')),
                'main_role_id' => RoleHelper::getIdFromTitle('editingteacher'),
            ]);
            User::create([
                'name' => 'Nonedit-Trainer ' . $surname,
                'email' => strtolower($surname) . '-nonedit-trainer@th-nuernberg.de',
                'password' => bcrypt(env('APP_ADMIN_PASSWORD')),
                'main_role_id' => RoleHelper::getIdFromTitle('teacher'),
            ]);
            // student role is default role
            User::create([
                'name' => 'Student ' . $surname,
                'email' => strtolower($surname) . '-student@th-nuernberg.de',
                'password' => bcrypt(env('APP_ADMIN_PASSWORD')),
            ]);
        }
        // add editing teacher
        $editingTeacher = User::create([
            'name' => 'Kurstrainer',
            'email' => 'teacher-vikl@th-nuernberg.de',
            'password' => bcrypt(env('APP_ADMIN_PASSWORD')),
            'main_role_id' => RoleHelper::getIdFromTitle('editingteacher'),
        ]);

        // add editing teacher
        $noneditingTeacher = User::create([
            'name' => 'Nonedit Trainer',
            'email' => 'nonedit-teacher-vikl@th-nuernberg.de',
            'password' => bcrypt(env('APP_ADMIN_PASSWORD')),
            'main_role_id' => RoleHelper::getIdFromTitle('teacher'),
        ]);

        // add student
        $student = User::create([
            'name' => 'Student',
            'email' => 'student-vikl@th-nuernberg.de',
            'password' => bcrypt(env('APP_ADMIN_PASSWORD')),
        ]);

        // add course
        $course = Course::create([
            'name' => 'Querschnitt Onlineberatung SS24',
            'enrollment_key' => '123456',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(240),
            'settings' => '{"trainer_feedback_contingent":"2","peer_review_start_token":"2","peer_review_available":"1","ai_feedback_available":"1","display_peer_review":"0","display_notes":"0","display_counsellings":"0"}',
            'creator_id' => $editingTeacher->id,
        ]);

        $counsellingSetups = [
            [
                "id" => 1,
                "created_at" => "2023-12-09T17:39:00.000000Z",
                "updated_at" => "2023-12-09T17:39:00.000000Z",
                "mandatory" => false,
                "due_date" => null,
                "settings" => [
                    "counselling_fields" => [2, 3],
                    "personae" => [4, 1]
                ],
                "course_id" => 1
            ],
            [
                "id" => 2,
                "created_at" => "2023-12-09T17:39:00.000000Z",
                "updated_at" => "2023-12-09T17:39:00.000000Z",
                "mandatory" => true,
                "due_date" => "2024-05-25 00:00:00",
                "settings" => [
                    "counselling_fields" => [2],
                    "personae" => [4]
                ],
                "course_id" => 1
            ]
        ];

        foreach ($counsellingSetups as $setup_data) {
            $mandatory = $setup_data['mandatory'] ?? false;
            $dueDate = $setup_data['due_date'] ?? null;
            $settings = $setup_data['settings'];

            $counsellingSetup = CounsellingSetup::create([
                'mandatory' => $mandatory,
                'due_date' => $dueDate,
                'course_id' => $course->id,
                'settings' => $settings
            ]);
        }

        // add teacher to course
        CourseMember::create([
            'course_id' => $course->id,
            'user_id' => $editingTeacher->id,
            'role_id' => RoleHelper::getIdFromTitle('editingteacher'),
        ]);

        // add student to course
        CourseMember::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'role_id' => RoleHelper::getIdFromTitle('student'),
            'pseudo_first_name' => 'Sofia',
            'pseudo_last_name' => 'Meier',
            'properties' => '{"feedback_requests_made": "0", "peer_review_token": "2"}'
        ]);

        // add default counselling
        Counselling::create([
            'title' => 'Elke - 23.2.2024',
            'course_member_id' => 2,
            'counselling_setup_id' => 1,
            'persona_id' => 1,
            'status_chat_id' => 5
        ]);

        $counsellingMessages = [
            [
                "counselling_id" => 1,
                "message_number" => 1,
                "content" => "Hallo. Ich bin Elke und mein Kind Lukas nimmt Drogen … Können Sie mir hier helfen?",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 2,
                "content" => "Hallo Elke. Toll, dass du dich meldest. Ich bin Sofia und Beraterin. Ich versuche gerne dir zu helfen. Ich würde zuerst technische und organisatorische Rahmenbedingungen mit dir klären. Ist das okay?",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 3,
                "content" => "Vielen Dank. Ja ist okay",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 4,
                "content" => "Der Chat hat einen Zeitrahmen von etwa einer Stunde. Ich würde dich bitten, dass wir abwechselnd schreiben, damit wir nicht durcheinander kommen.",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 5,
                "content" => "Okay habe ich verstanden",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 6,
                "content" => "Super, dann können wir starten. Du hast ja am Anfang erzählt, dass dein Sohn Drogen nimmt. Wie kann ich dir denn hierbei helfen?",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 7,
                "content" => "… Durch die Drogen hat er jetzt auch Probleme in der Schule und das möchte ich nicht",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 8,
                "content" => "Welche Probleme hat er denn dadurch?",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 9,
                "content" => "Ja seine Noten verschlechtern sich dadurch und das möchte ich für ihn nicht, weil er dadurch vielleicht weniger Möglichkeiten in der Zukunft hat",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 10,
                "content" => "Ich verstehe deine Sorgen. Du möchtest also, dass dein Sohn eine gute Zukunft führen kann?",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 11,
                "content" => "Ja das möchte ich…",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 12,
                "content" => "Welche Drogen konsumiert er denn und in welchem Maße?",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 13,
                "content" => "Er raucht öfters einen Joint mit seinen Freunden",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 14,
                "content" => "Das ist eine geringe Menge, bedeutet du musst dir vorrangig keine Sorgen machen. Haben Sie darüber schonmal mit ihrem Sohn gesprochen und versucht eine Lösung mit ihm zu finden?",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 15,
                "content" => "Ja, ich habe ihm gesagt, dass ich es blöd finde was er macht und er damit aufhören soll… ",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 16,
                "content" => "Das ist schonmal gut Elke, dass du das angesprochen hast. Wie hat er denn reagiert?",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 17,
                "content" => "Nicht gut. Er hat mich gar nicht an sich ran gelassen",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 18,
                "content" => "Vielleicht können wir mal zu dritt ein Gespräch über die Drogenthematik führen. Liegt das in ihrem Anliegen?",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 19,
                "content" => "Ja super gerne. Ich frage ihn gleich",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 20,
                "content" => "Das freut mich, dass Sie dazu bereit sind.",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 21,
                "content" => "Er hat gesagt, dass er sich es mal überlegt.",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 22,
                "content" => "Okay. Das ist aufjedenfall schonmal ein Fortschritt. Ich schlage vor, wir vereinbaren eine weitere Chatberatung. In der können wir dann zusammen ausmachen, wie ein Gespräch mit deinem Sohn ablaufen kann zu dritt, wenn er zustimmt.",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 23,
                "content" => "Das hört sich gut an. So machen wir es. Dankeschön",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 24,
                "content" => "Am besten wäre es, wenn du dir für unser nächstes Gespräch überlegst, was du deinem Sohn sagen möchtest. Wir können dann zusammen deine Äußerungen so formulieren, dass er sich nicht angefriffen fühlt.",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 25,
                "content" => "Das werde ich machen",
                "author" => "vikl",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 26,
                "content" => "Super, dann bis zum nächsten Mal",
                "author" => "user",
            ],
            [
                "counselling_id" => 1,
                "message_number" => 27,
                "content" => "Ja bis dann. Tschüss",
                "author" => "vikl",
            ],
        ];

        foreach ($counsellingMessages as $message) {

            CounsellingMessage::create([
                "counselling_id" => $message['counselling_id'],
                "message_number" => $message['message_number'],
                "content" => $message['content'],
                "author" => $message['author'],
                "additions" => "{}"
            ]);
        }

    }
}
