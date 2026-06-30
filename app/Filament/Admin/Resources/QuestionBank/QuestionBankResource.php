<?php

namespace App\Filament\Admin\Resources\QuestionBank;

use App\Models\Question;
use App\Models\Subject;
use App\Models\QuestionTopic;
use App\Enums\QuestionType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuestionBankResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Question Bank';

    protected static ?string $modelLabel = 'Question';

    protected static ?string $pluralModelLabel = 'Question Bank';

    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Question Details')
                    ->schema([
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->relationship('subject', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive(),
                        Forms\Components\Select::make('topic_id')
                            ->label('Topic')
                            ->options(function (callable $get) {
                                $subjectId = $get('subject_id');
                                if (!$subjectId) {
                                    return [];
                                }
                                return QuestionTopic::where('subject_id', $subjectId)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->nullable(),
                        Forms\Components\Select::make('exam_id')
                            ->label('Linked Exam (optional)')
                            ->relationship('exam', 'title')
                            ->searchable()
                            ->nullable()
                            ->helperText('Leave blank for standalone question bank entry'),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options(array_combine(
                                array_map(fn($type) => $type->value, QuestionType::cases()),
                                array_map(fn($type) => $type->label(), QuestionType::cases())
                            ))
                            ->default(QuestionType::SINGLE_CORRECT->value),
                        Forms\Components\Select::make('difficulty')
                            ->required()
                            ->options([
                                'easy' => 'Easy',
                                'medium' => 'Medium',
                                'hard' => 'Hard',
                            ])
                            ->default('medium'),
                        Forms\Components\TextInput::make('marks')
                            ->required()
                            ->numeric()
                            ->default(1),
                        Forms\Components\TextInput::make('negative_marks')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->default(0.00),
                        Forms\Components\TextInput::make('sort_order')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TagsInput::make('tags')
                            ->helperText('Add tags like "PYQ", "UPSC 2024", "Prelims"'),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Question Image (Diagram, Equation, or Graph)')
                            ->image()
                            ->directory('question-images')
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('question_text')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('explanation')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Answer Options')
                    ->schema([
                        Forms\Components\Repeater::make('options')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('option_text')
                                    ->required()
                                    ->maxLength(65535)
                                    ->columnSpan(9),
                                Forms\Components\Toggle::make('is_correct')
                                    ->required()
                                    ->columnSpan(3),
                            ])
                            ->columns(12)
                            ->minItems(2)
                            ->maxItems(10)
                            ->createItemButtonLabel('Add Option')
                            ->columnSpanFull()
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('topic.name')
                    ->label('Topic')
                    ->sortable()
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Linked Exam')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Standalone'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->sortable(),
                Tables\Columns\TextColumn::make('difficulty')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('marks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('question_text')
                    ->label('Question')
                    ->limit(60)
                    ->html()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tags')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('difficulty')
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options(array_combine(
                        array_map(fn($type) => $type->value, QuestionType::cases()),
                        array_map(fn($type) => $type->label(), QuestionType::cases())
                    )),
                Tables\Filters\SelectFilter::make('exam')
                    ->relationship('exam', 'title'),
                Tables\Filters\TernaryFilter::make('standalone')
                    ->label('Standalone Only')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('exam_id'),
                        false: fn (Builder $query) => $query->whereNotNull('exam_id'),
                    ),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\BulkAction::make('assign_to_exam')
                        ->label('Assign to Exam')
                        ->icon('heroicon-o-link')
                        ->form([
                            Forms\Components\Select::make('exam_id')
                                ->label('Select Exam')
                                ->relationship('exam', 'title')
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $question) {
                                // Add to pivot table if exists, otherwise set exam_id
                                if (\Illuminate\Support\Facades\Schema::hasTable('exam_question')) {
                                    \Illuminate\Support\Facades\DB::table('exam_question')->insertOrIgnore([
                                        'exam_id' => $data['exam_id'],
                                        'question_id' => $question->id,
                                        'sort_order' => 0,
                                    ]);
                                }
                                $question->update(['exam_id' => $data['exam_id']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('change_subject')
                        ->label('Change Subject')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Forms\Components\Select::make('subject_id')
                                ->label('New Subject')
                                ->relationship('subject', 'name')
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $question) {
                                $question->update(['subject_id' => $data['subject_id']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('change_difficulty')
                        ->label('Change Difficulty')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->form([
                            Forms\Components\Select::make('difficulty')
                                ->options([
                                    'easy' => 'Easy',
                                    'medium' => 'Medium',
                                    'hard' => 'Hard',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $question) {
                                $question->update(['difficulty' => $data['difficulty']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestionBank::route('/'),
            'create' => Pages\CreateQuestionBank::route('/create'),
            'edit' => Pages\EditQuestionBank::route('/{record}/edit'),
            'import' => Pages\BulkImportQuestions::route('/import'),
        ];
    }
}
