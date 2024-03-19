<?php // routes/breadcrumbs.php

// Note: Laravel will automatically resolve `Breadcrumbs::` without
// this import. This is nice for IDE syntax and refactoring.
use App\Counselling\Models\Counselling;
use App\Counselling\Models\CounsellingSetup;
use App\Course\Models\Course;
use Diglactic\Breadcrumbs\Breadcrumbs;

// This import is also not required, and you could replace `BreadcrumbTrail $trail`
//  with `$trail`. This is nice for IDE type checking and completion.
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('home'));
});

// Home > Course
Breadcrumbs::for('course.index', function (BreadcrumbTrail $trail, Course $course) {
    $trail->parent('home');
    if ($course) {
        $trail->push($course->name, route('course.index', $course));
    }
});

// Home > Enrollment
Breadcrumbs::for('courses.enrollment', function (BreadcrumbTrail $trail) {
    $trail->parent('home');

    $trail->push('Kurseinschreibung', route('courses.enrollment'));
});

// Home > Trainer Enrollment
Breadcrumbs::for('courses.trainerEnrollment', function (BreadcrumbTrail $trail) {
    $trail->parent('home');

    $trail->push('Kurseinschreibung', route('courses.trainerEnrollment'));
});

// Counselling > Exercise Postprocessing OR Exercise
Breadcrumbs::for('counselling.index', function (BreadcrumbTrail $trail, Counselling $counselling) {
    if($counselling->status_chat_id === 5){
        $trail->push('Übung Nachbereitung', route('counselling.index', $counselling));
    }
    else{
        $trail->push('Übung', route('counselling.index', $counselling));
    }

});

// Counselling > New Exercise
Breadcrumbs::for('counselling.create', function (BreadcrumbTrail $trail, Counselling $counselling) {
    $trail->push('Neue Übung', route('counselling.create', $counselling));
});

// Counselling > New Exercise
Breadcrumbs::for('counselling.createView', function (BreadcrumbTrail $trail, CounsellingSetup $counsellingSetup) {
    $trail->push('Neue Übung', route('counselling.createView', $counsellingSetup));
});

// Counselling > Tasks OR Overview Tasks (Teacher)
Breadcrumbs::for('course.tasks', function (BreadcrumbTrail $trail, Course $course) {
    if(auth()->user()->role->title === 'editingteacher' || auth()->user()->role->title === 'teacher' ){
        $trail->push('Übersicht Pflichtaufgaben', route('course.tasks', $course));
    }
    else{
        $trail->push('Aufgaben', route('course.tasks', $course));
    }
});

// Counselling > Exercises OR Feedback Requests Exercises (Teacher)
Breadcrumbs::for('course.exercises', function (BreadcrumbTrail $trail, Course $course) {
    if(auth()->user()->role->title === 'editingteacher' || auth()->user()->role->title === 'teacher' ){
        $trail->push('Feedbackanfragen Übungen', route('course.tasks', $course));
    }
    else{
        $trail->push('Übungen', route('course.exercises', $course));
    }
});

// Counselling > Feedback Requests Tasks (Teacher)
Breadcrumbs::for('course.tasksWithFeedback', function (BreadcrumbTrail $trail, Course $course) {
    $trail->push('Feedbackanfragen Pflichtaufgaben', route('course.tasksWithFeedback', $course));
});

// Counselling > Peer Review
Breadcrumbs::for('counselling.indexPeer', function (BreadcrumbTrail $trail, Counselling $counselling) {
    $trail->push('Peer Review', route('course.exercises', $counselling));
});

// Course > Peer Review Requests
Breadcrumbs::for('course.peerReviewRequestsView', function (BreadcrumbTrail $trail, Course $course) {
    $trail->push('Peer-Review Anfragen', route('course.peerReviewRequestsView', $course));
});

