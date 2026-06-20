<?php

namespace App\Filament\Faculty\Resources\Questions;

use App\Models\Question;
use App\Models\Exam;
use App\Enums\QuestionType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('exam', function ($query) {
                $query->where('created_by', auth()->id());
            });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('exam_id')
                            ->relationship('exam', 'title', fn ($query) => $query->where('created_by', auth()->id()))
                            ->required(),
                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->required(),
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
                        Forms\Components\TagsInput::make('tags'),
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
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exam.title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->sortable(),
                Tables\Columns\TextColumn::make('difficulty')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('marks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('question_text')
                    ->limit(50)
                    ->html()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam')
                    ->relationship('exam', 'title', fn ($query) => $query->where('created_by', auth()->id())),
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
