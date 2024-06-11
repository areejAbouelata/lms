<?php

namespace App\Http\Core\Interfaces;

interface QuestionInterface
{
    public function addQuestion($data, $activity);

    public function answer($request, $activity, $user);
}
