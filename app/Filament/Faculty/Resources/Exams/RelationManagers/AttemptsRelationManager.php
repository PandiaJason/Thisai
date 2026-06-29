<?php

namespace App\Filament\Faculty\Resources\Exams\RelationManagers;

use App\Models\ExamAttempt;
use App\Enums\ExamAttemptStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Table;

class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    protected static ?string $title = 'Exam Attempts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user.name')
                    ->label('Student')
                    ->disabled(),
                TextInput::make('score')
                    ->label('Score')
                    ->disabled(),
                TextInput::make('accuracy')
                    ->label('Accuracy (%)')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('score')
                    ->label('Score')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => "{$record->score} / {$record->total_marks}"),
                TextColumn::make('accuracy')
                    ->label('Accuracy')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ExamAttemptStatus $state): string => match ($state) {
                        ExamAttemptStatus::IN_PROGRESS => 'warning',
                        ExamAttemptStatus::SUBMITTED => 'success',
                        ExamAttemptStatus::AUTO_SUBMITTED => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(array_combine(
                        array_map(fn($status) => $status->value, ExamAttemptStatus::cases()),
                        array_map(fn($status) => $status->label(), ExamAttemptStatus::cases())
                    )),
            ])
            ->headerActions([
                Action::make('export_detailed_csv')
                    ->label('Export Detailed Results')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $exam = $livewire->getOwnerRecord();
                        
                        return response()->streamDownload(function () use ($exam) {
                            $handle = fopen('php://output', 'w');
                            
                            // UTF-8 BOM
                            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                            
                            fputcsv($handle, [
                                'Student Name',
                                'Student Email',
                                'Total Score Obtained',
                                'Max Marks',
                                'Accuracy %',
                                'Question No.',
                                'Question Subject',
                                'Question Topic',
                                'Question Text',
                                'Is Correct',
                                'Marks Obtained',
                                'Time Spent (Seconds)'
                            ]);

                            $attempts = ExamAttempt::where('exam_id', $exam->id)
                                ->where('status', '!=', ExamAttemptStatus::IN_PROGRESS->value)
                                ->with(['user', 'answers.question.subject', 'answers.question.topic'])
                                ->get();

                            foreach ($attempts as $attempt) {
                                foreach ($attempt->answers as $index => $answer) {
                                    $question = $answer->question;
                                    $isCorrectLabel = 'Unanswered';
                                    if ($answer->is_correct === true) {
                                        $isCorrectLabel = 'Correct';
                                    } elseif ($answer->is_correct === false) {
                                        $isCorrectLabel = 'Incorrect';
                                    }

                                    fputcsv($handle, [
                                        $attempt->user->name,
                                        $attempt->user->email,
                                        $attempt->score,
                                        $attempt->total_marks,
                                        $attempt->accuracy,
                                        $index + 1,
                                        $question->subject?->name ?? 'N/A',
                                        $question->topic?->name ?? 'N/A',
                                        strip_tags($question->question_text),
                                        $isCorrectLabel,
                                        $answer->marks_obtained,
                                        $answer->time_spent_seconds
                                    ]);
                                }
                            }

                            fclose($handle);
                        }, 'exam_' . $exam->slug . '_detailed_results.csv');
                    }),
            ])
            ->recordActions([
                Action::make('view_answers')
                    ->label('View Answers')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (ExamAttempt $record) => "Reviewing Attempt: {$record->user->name}")
                    ->modalContent(fn (ExamAttempt $record) => view('filament.faculty.exam-attempt-review', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                DeleteAction::make()
                    ->label('Reset')
                    ->modalHeading('Reset Exam Attempt')
                    ->modalDescription('Are you sure you want to delete this attempt? This will permanently erase the student\'s answers and score, allowing them to start a new attempt.')
                    ->modalSubmitActionLabel('Yes, Reset Attempt'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
