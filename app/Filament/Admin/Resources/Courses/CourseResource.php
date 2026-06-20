<?php

namespace App\Filament\Admin\Resources\Courses;

use App\Models\Course;
use App\Models\User;
use App\Enums\CourseDifficulty;
use App\Enums\CourseStatus;
use App\Enums\UserRole;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, \Filament\Schemas\Components\Utilities\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255)
                            ->unique(Course::class, 'slug', ignoreRecord: true),
                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->required(),
                        Forms\Components\Select::make('faculty_id')
                            ->relationship('faculty', 'name', fn ($query) => $query->whereIn('role', [UserRole::FACULTY->value, UserRole::SUPER_ADMIN->value]))
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options(array_combine(
                                array_map(fn($status) => $status->value, CourseStatus::cases()),
                                array_map(fn($status) => $status->label(), CourseStatus::cases())
                            ))
                            ->default(CourseStatus::DRAFT->value),
                        Forms\Components\Select::make('difficulty')
                            ->required()
                            ->options(array_combine(
                                array_map(fn($difficulty) => $difficulty->value, CourseDifficulty::cases()),
                                array_map(fn($difficulty) => $difficulty->label(), CourseDifficulty::cases())
                            ))
                            ->default(CourseDifficulty::BEGINNER->value),
                        Forms\Components\TextInput::make('duration_hours')
                            ->numeric(),
                        Forms\Components\Toggle::make('is_free')
                            ->required()
                            ->default(true)
                            ->reactive(),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('₹')
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => !$get('is_free')),
                        Forms\Components\TextInput::make('sort_order')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->directory('course-thumbnails'),
                        Forms\Components\RichEditor::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('faculty.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (CourseStatus $state): string => match ($state) {
                        CourseStatus::DRAFT => 'warning',
                        CourseStatus::PUBLISHED => 'success',
                        CourseStatus::ARCHIVED => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->sortable(),
                Tables\Columns\TextColumn::make('difficulty')
                    ->badge()
                    ->color(fn (CourseDifficulty $state): string => match ($state) {
                        CourseDifficulty::BEGINNER => 'success',
                        CourseDifficulty::INTERMEDIATE => 'warning',
                        CourseDifficulty::ADVANCED => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_free')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('faculty')
                    ->relationship('faculty', 'name', fn ($query) => $query->whereIn('role', [UserRole::FACULTY->value, UserRole::SUPER_ADMIN->value])),
                Tables\Filters\SelectFilter::make('status')
                    ->options(array_combine(
                        array_map(fn($status) => $status->value, CourseStatus::cases()),
                        array_map(fn($status) => $status->label(), CourseStatus::cases())
                    )),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
