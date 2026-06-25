<?php

namespace App\Filament\Admin\Resources\QuestionBank\Pages;

use App\Filament\Admin\Resources\QuestionBank\QuestionBankResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use App\Models\Subject;
use App\Models\Question;
use App\Enums\QuestionType;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ListQuestionBank extends ListRecords
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('import')
                ->label('Bulk Import Questions')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('exam_id')
                        ->label('Target Exam (optional)')
                        ->options(fn () => \App\Models\Exam::pluck('title', 'id'))
                        ->nullable()
                        ->searchable()
                        ->helperText('Leave blank to create standalone question bank entries.'),

                    Forms\Components\Select::make('default_subject_id')
                        ->label('Default Subject')
                        ->options(fn () => Subject::pluck('name', 'id'))
                        ->searchable()
                        ->helperText('Used when SUBJECT tag is not specified in the import data.'),

                    Forms\Components\FileUpload::make('file')
                        ->label('Upload Text File (.txt)')
                        ->acceptedFileTypes(['text/plain'])
                        ->disk('local')
                        ->directory('imports')
                        ->visibility('private')
                        ->helperText(new \Illuminate\Support\HtmlString('Upload a plain text file with questions. <a href="/templates/questions_template.txt" target="_blank" class="text-blue-600 dark:text-blue-400 underline font-semibold">Download Template</a>')),

                    Forms\Components\Textarea::make('paste_text')
                        ->label('Or Paste Questions Text')
                        ->rows(14)
                        ->placeholder("Paste questions here...\n\nFormat:\nWhat is the capital of France?\nA. London\nB. Berlin\nC. Paris\nD. Madrid\nANSWER: C\nSUBJECT: Geography\nDIFFICULTY: easy\nEXPLANATION: Paris is the capital of France.")
                        ->helperText('Uploaded file takes precedence over pasted text.'),
                ])
                ->action(function (array $data) {
                    $text = '';

                    if (!empty($data['file'])) {
                        $path = Storage::disk('local')->path($data['file']);
                        if (file_exists($path)) {
                            $text = file_get_contents($path);
                            unlink($path);
                        }
                    } elseif (!empty($data['paste_text'])) {
                        $text = $data['paste_text'];
                    }

                    if (empty(trim($text))) {
                        Notification::make()
                            ->title('Import Failed')
                            ->body('Please upload a file or paste question text.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $text = str_replace("\r\n", "\n", $text);
                    $blocks = preg_split('/\n\s*\n/', trim($text));

                    $importedCount = 0;
                    $skippedCount = 0;

                    foreach ($blocks as $block) {
                        $lines = explode("\n", trim($block));
                        if (empty($lines)) continue;

                        $questionTextLines = [];
                        $options = [];
                        $correctAnswer = null;
                        $explanation = '';
                        $subjectName = null;
                        $difficulty = 'medium';
                        $inExplanation = false;
                        $tags = [];

                        foreach ($lines as $line) {
                            $line = trim($line);
                            if ($line === '') continue;

                            if (preg_match('/^ANSWER:\s*([A-J])/i', $line, $matches)) {
                                $correctAnswer = strtoupper($matches[1]);
                                $inExplanation = false;
                            } elseif (preg_match('/^SUBJECT:\s*(.+)/i', $line, $matches)) {
                                $subjectName = trim($matches[1]);
                                $inExplanation = false;
                            } elseif (preg_match('/^DIFFICULTY:\s*(.+)/i', $line, $matches)) {
                                $difficulty = strtolower(trim($matches[1]));
                                $inExplanation = false;
                            } elseif (preg_match('/^TAGS?:\s*(.+)/i', $line, $matches)) {
                                $tags = array_map('trim', explode(',', $matches[1]));
                                $inExplanation = false;
                            } elseif (preg_match('/^EXPLANATION:\s*(.*)/i', $line, $matches)) {
                                $explanation = trim($matches[1]);
                                $inExplanation = true;
                            } elseif ($inExplanation) {
                                $explanation .= "\n" . $line;
                            } elseif (preg_match('/^([A-J])[.)]\s*(.+)/i', $line, $matches)) {
                                $key = strtoupper($matches[1]);
                                $options[$key] = trim($matches[2]);
                            } else {
                                $questionTextLines[] = $line;
                            }
                        }

                        if (empty($questionTextLines) || empty($options) || !$correctAnswer) {
                            $skippedCount++;
                            continue;
                        }

                        $questionText = implode("<br>", $questionTextLines);

                        // Resolve subject
                        $subjectId = null;
                        if ($subjectName) {
                            $subject = Subject::where('name', 'like', '%' . $subjectName . '%')->first();
                            $subjectId = $subject?->id;
                        }
                        if (!$subjectId) {
                            $subjectId = $data['default_subject_id'] ?? Subject::first()?->id;
                        }

                        // Create question
                        $question = Question::create([
                            'exam_id' => $data['exam_id'] ?? null,
                            'subject_id' => $subjectId,
                            'question_text' => $questionText,
                            'explanation' => $explanation ?: null,
                            'type' => QuestionType::SINGLE_CORRECT->value,
                            'difficulty' => in_array($difficulty, ['easy', 'medium', 'hard']) ? $difficulty : 'medium',
                            'marks' => 2,
                            'negative_marks' => 0.67,
                            'tags' => !empty($tags) ? $tags : null,
                        ]);

                        // Create options
                        $sort = 1;
                        foreach ($options as $key => $optionText) {
                            $question->options()->create([
                                'option_text' => $optionText,
                                'is_correct' => ($key === $correctAnswer),
                                'sort_order' => $sort++,
                            ]);
                        }

                        $importedCount++;
                    }

                    Notification::make()
                        ->title('Bulk Import Completed')
                        ->body("Imported {$importedCount} questions. Skipped {$skippedCount} malformed entries.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
