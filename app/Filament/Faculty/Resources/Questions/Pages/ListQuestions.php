<?php

namespace App\Filament\Faculty\Resources\Questions\Pages;

use App\Filament\Faculty\Resources\Questions\QuestionResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use App\Models\Subject;
use App\Models\Question;
use App\Enums\QuestionType;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('import')
                ->label('Import Questions')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('exam_id')
                        ->label('Target Exam')
                        ->options(fn () => \App\Models\Exam::where('created_by', auth()->id())->pluck('title', 'id'))
                        ->required()
                        ->helperText('Select the exam to add the imported questions to.'),
                    
                    Forms\Components\FileUpload::make('file')
                        ->label('Upload Text File (.txt)')
                        ->acceptedFileTypes(['text/plain'])
                        ->disk('local')
                        ->directory('imports')
                        ->visibility('private')
                        ->helperText(new \Illuminate\Support\HtmlString('Upload a plain text file. You can download the <a href="/templates/questions_template.txt" target="_blank" class="text-blue-600 dark:text-blue-400 underline font-semibold">Questions Template here</a>.')),
                        
                    Forms\Components\Textarea::make('paste_text')
                        ->label('Or Copy & Paste Text')
                        ->rows(12)
                        ->placeholder("Paste your questions here...\n\nExample:\nConsider the following:\n1. Statement one\n2. Statement two\nA. 1 only\nB. 2 only\nC. Both 1 and 2\nD. Neither 1 nor 2\nANSWER: C\nSUBJECT: Polity\nDIFFICULTY: medium\nEXPLANATION: This is why C is correct.")
                        ->helperText('Paste questions directly. If both a file is uploaded and text is pasted, the uploaded file takes precedence.'),
                ])
                ->action(function (array $data) {
                    $text = '';
                    
                    if (!empty($data['file'])) {
                        $path = Storage::disk('local')->path($data['file']);
                        if (file_exists($path)) {
                            $text = file_get_contents($path);
                            // Delete the temporary file
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

                    // Clean carriage returns
                    $text = str_replace("\r\n", "\n", $text);
                    // Split questions by double newlines
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
                            } elseif (preg_match('/^EXPLANATION:\s*(.*)/i', $line, $matches)) {
                                $explanation = trim($matches[1]);
                                $inExplanation = true;
                            } elseif ($inExplanation) {
                                $explanation .= "\n" . $line;
                            } elseif (preg_match('/^([A-J])\.\s*(.+)/i', $line, $matches)) {
                                $key = strtoupper($matches[1]);
                                $options[$key] = trim($matches[2]);
                            } elseif (preg_match('/^([A-J])\)\s*(.+)/i', $line, $matches)) {
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
                        
                        // Find or match subject
                        $subjectId = null;
                        if ($subjectName) {
                            $subject = Subject::where('name', 'like', '%' . $subjectName . '%')->first();
                            if ($subject) {
                                $subjectId = $subject->id;
                            }
                        }
                        
                        if (!$subjectId) {
                            $subjectId = Subject::first()?->id;
                        }
                        
                        // Create question
                        $question = Question::create([
                            'exam_id' => $data['exam_id'],
                            'subject_id' => $subjectId,
                            'question_text' => $questionText,
                            'explanation' => $explanation ?: null,
                            'type' => QuestionType::SINGLE_CORRECT->value,
                            'difficulty' => in_array($difficulty, ['easy', 'medium', 'hard']) ? $difficulty : 'medium',
                            'marks' => 2,
                            'negative_marks' => 0.67,
                        ]);
                        
                        // Create options
                        $sort = 1;
                        foreach ($options as $key => $optionText) {
                            $isCorrect = ($key === $correctAnswer);
                            $question->options()->create([
                                'option_text' => $optionText,
                                'is_correct' => $isCorrect,
                                'sort_order' => $sort++,
                            ]);
                        }
                        
                        $importedCount++;
                    }
                    
                    Notification::make()
                        ->title('Import Completed')
                        ->body("Successfully imported {$importedCount} questions. Skipped {$skippedCount} malformed blocks.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
