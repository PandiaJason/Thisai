<?php

namespace App\Filament\Admin\Resources\Exams\RelationManagers;

use App\Models\ExamAttempt;
use App\Enums\ExamAttemptStatus;
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
                // No create action needed
            ])
            ->recordActions([
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
