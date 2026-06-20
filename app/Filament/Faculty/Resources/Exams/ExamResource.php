<?php

namespace App\Filament\Faculty\Resources\Exams;

use App\Models\Exam;
use App\Enums\ExamType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Filament\Faculty\Resources\Exams\RelationManagers;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('created_by', auth()->id());
    }

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
                            ->unique(Exam::class, 'slug', ignoreRecord: true),
                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->required(),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options(array_combine(
                                array_map(fn($type) => $type->value, ExamType::cases()),
                                array_map(fn($type) => $type->label(), ExamType::cases())
                            ))
                            ->default(ExamType::DAILY_QUIZ->value),
                        Forms\Components\Select::make('difficulty')
                            ->required()
                            ->options([
                                'easy' => 'Easy',
                                'medium' => 'Medium',
                                'hard' => 'Hard',
                            ])
                            ->default('medium'),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->required()
                            ->numeric()
                            ->default(60),
                        Forms\Components\TextInput::make('max_attempts')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->helperText('Maximum number of attempts allowed for this exam.'),
                        Forms\Components\TextInput::make('total_marks')
                            ->required()
                            ->numeric()
                            ->default(100),
                        Forms\Components\TextInput::make('negative_marking')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->default(0.33)
                            ->helperText('Fraction of marks to subtract on wrong answer (e.g. 0.33 for 1/3 negative marking)'),
                        Forms\Components\Toggle::make('randomize_questions')
                            ->required()
                            ->default(false),
                        Forms\Components\Toggle::make('randomize_options')
                            ->required()
                            ->default(false),
                        Forms\Components\Toggle::make('is_published')
                            ->required()
                            ->default(false),
                        Forms\Components\DateTimePicker::make('starts_at'),
                        Forms\Components\DateTimePicker::make('ends_at'),
                        Forms\Components\TextInput::make('activation_key')
                            ->maxLength(255)
                            ->helperText('Passcode required to start this exam. Leave blank for no passcode.'),
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
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (ExamType $state): string => match ($state) {
                        ExamType::DAILY_QUIZ => 'success',
                        ExamType::SECTION_TEST => 'warning',
                        ExamType::MOCK_TEST => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('difficulty')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->numeric()
                    ->sortable()
                    ->suffix(' min'),
                Tables\Columns\TextColumn::make('max_attempts')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activation_key')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_marks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('type')
                    ->options(array_combine(
                        array_map(fn($type) => $type->value, ExamType::cases()),
                        array_map(fn($type) => $type->label(), ExamType::cases())
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttemptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }
}
