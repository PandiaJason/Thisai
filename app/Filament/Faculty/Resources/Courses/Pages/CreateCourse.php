<?php

namespace App\Filament\Faculty\Resources\Courses\Pages;

use App\Filament\Faculty\Resources\Courses\CourseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
}
