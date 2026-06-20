<?php

namespace App\Filament\Faculty\Resources\Videos;

use App\Models\Video;
use App\Models\Course;
use App\Models\CourseSection;
use App\Enums\VideoStatus;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-video-camera';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('uploaded_by', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('course_id')
                            ->relationship('course', 'title', fn ($query) => $query->where('faculty_id', auth()->id()))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('course_section_id', null)),
                        Forms\Components\Select::make('course_section_id')
                            ->label('Section')
                            ->options(function (callable $get) {
                                $courseId = $get('course_id');
                                if (!$courseId) {
                                    return [];
                                }
                                return CourseSection::where('course_id', $courseId)->pluck('title', 'id');
                            })
                            ->required(),
                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->required(),
                        Forms\Components\Hidden::make('uploaded_by')
                            ->default(fn () => auth()->id()),
                        Forms\Components\TextInput::make('bunny_video_id')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bunny_library_id')
                            ->maxLength(255)
                            ->default(fn () => config('bunny.library_id')),
                        Forms\Components\TextInput::make('duration_seconds')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options(array_combine(
                                array_map(fn($status) => $status->value, VideoStatus::cases()),
                                array_map(fn($status) => $status->label(), VideoStatus::cases())
                            ))
                            ->default(VideoStatus::PROCESSING->value),
                        Forms\Components\Toggle::make('is_free')
                            ->required()
                            ->default(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('thumbnail_url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->sortable(),
                Tables\Columns\TextColumn::make('section.title')
                    ->label('Section'),
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => sprintf('%02d:%02d', floor($state / 60), $state % 60))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (VideoStatus $state): string => match ($state) {
                        VideoStatus::PROCESSING => 'warning',
                        VideoStatus::READY => 'success',
                        VideoStatus::FAILED => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_free')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title', fn ($query) => $query->where('faculty_id', auth()->id())),
                Tables\Filters\SelectFilter::make('status')
                    ->options(array_combine(
                        array_map(fn($status) => $status->value, VideoStatus::cases()),
                        array_map(fn($status) => $status->label(), VideoStatus::cases())
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
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
