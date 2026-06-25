<?php

namespace App\Filament\Admin\Resources\QuestionBank\Pages;

use App\Filament\Admin\Resources\QuestionBank\QuestionBankResource;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Exam;
use App\Enums\QuestionType;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class BulkImportQuestions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = QuestionBankResource::class;

    protected string $view = 'filament.admin.pages.bulk-import-questions';

    protected static ?string $title = 'Bulk Import Questions';

    public ?array $data = [];

    public array $importResults = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Import Configuration')
                    ->schema([
                        Forms\Components\Select::make('exam_id')
                            ->label('Assign to Exam (optional)')
                            ->options(Exam::pluck('title', 'id'))
                            ->nullable()
                            ->searchable(),
                        Forms\Components\Select::make('default_subject_id')
                            ->label('Default Subject')
                            ->options(Subject::pluck('name', 'id'))
                            ->searchable()
                            ->helperText('Used when SUBJECT: is not specified per question'),
                        Forms\Components\Select::make('default_difficulty')
                            ->label('Default Difficulty')
                            ->options(['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'])
                            ->default('medium'),
                        Forms\Components\TextInput::make('default_marks')
                            ->label('Default Marks')
                            ->numeric()
                            ->default(2),
                        Forms\Components\TextInput::make('default_negative_marks')
                            ->label('Default Negative Marks')
                            ->numeric()
                            ->step(0.01)
                            ->default(0.67),
                    ])->columns(3),
                Forms\Components\Section::make('Question Data')
                    ->schema([
                        Forms\Components\Textarea::make('paste_text')
                            ->label('Paste Questions')
                            ->rows(20)
                            ->placeholder("Question 1 text here\nA. Option A\nB. Option B\nC. Option C\nD. Option D\nANSWER: B\nSUBJECT: Polity\nDIFFICULTY: medium\nTAGS: UPSC 2024, Prelims\nEXPLANATION: This is the explanation.\n\nQuestion 2 text here\n...")
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();
        $text = $data['paste_text'] ?? '';

        if (empty(trim($text))) {
            Notification::make()->title('No text provided')->danger()->send();
            return;
        }

        $text = str_replace("\r\n", "\n", $text);
        $blocks = preg_split('/\n\s*\n/', trim($text));

        $imported = 0;
        $skipped = 0;

        foreach ($blocks as $block) {
            $parsed = $this->parseQuestionBlock($block);
            if (!$parsed) {
                $skipped++;
                continue;
            }

            $subjectId = null;
            if ($parsed['subject']) {
                $subject = Subject::where('name', 'like', '%' . $parsed['subject'] . '%')->first();
                $subjectId = $subject?->id;
            }
            $subjectId = $subjectId ?? $data['default_subject_id'] ?? Subject::first()?->id;

            $question = Question::create([
                'exam_id' => $data['exam_id'] ?? null,
                'subject_id' => $subjectId,
                'question_text' => $parsed['text'],
                'explanation' => $parsed['explanation'] ?: null,
                'type' => QuestionType::SINGLE_CORRECT->value,
                'difficulty' => $parsed['difficulty'] ?? $data['default_difficulty'] ?? 'medium',
                'marks' => $data['default_marks'] ?? 2,
                'negative_marks' => $data['default_negative_marks'] ?? 0.67,
                'tags' => $parsed['tags'] ?: null,
            ]);

            $sort = 1;
            foreach ($parsed['options'] as $key => $optionText) {
                $question->options()->create([
                    'option_text' => $optionText,
                    'is_correct' => ($key === $parsed['answer']),
                    'sort_order' => $sort++,
                ]);
            }
            $imported++;
        }

        $this->importResults = ['imported' => $imported, 'skipped' => $skipped];

        Notification::make()
            ->title('Import Completed')
            ->body("Imported: {$imported} | Skipped: {$skipped}")
            ->success()
            ->send();
    }

    protected function parseQuestionBlock(string $block): ?array
    {
        $lines = explode("\n", trim($block));
        if (empty($lines)) return null;

        $questionLines = [];
        $options = [];
        $answer = null;
        $explanation = '';
        $subject = null;
        $difficulty = null;
        $tags = [];
        $inExplanation = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if (preg_match('/^ANSWER:\s*([A-J])/i', $line, $m)) {
                $answer = strtoupper($m[1]);
                $inExplanation = false;
            } elseif (preg_match('/^SUBJECT:\s*(.+)/i', $line, $m)) {
                $subject = trim($m[1]);
                $inExplanation = false;
            } elseif (preg_match('/^DIFFICULTY:\s*(.+)/i', $line, $m)) {
                $d = strtolower(trim($m[1]));
                $difficulty = in_array($d, ['easy', 'medium', 'hard']) ? $d : null;
                $inExplanation = false;
            } elseif (preg_match('/^TAGS?:\s*(.+)/i', $line, $m)) {
                $tags = array_map('trim', explode(',', $m[1]));
                $inExplanation = false;
            } elseif (preg_match('/^EXPLANATION:\s*(.*)/i', $line, $m)) {
                $explanation = trim($m[1]);
                $inExplanation = true;
            } elseif ($inExplanation) {
                $explanation .= "\n" . $line;
            } elseif (preg_match('/^([A-J])[.)]\s*(.+)/i', $line, $m)) {
                $options[strtoupper($m[1])] = trim($m[2]);
            } else {
                $questionLines[] = $line;
            }
        }

        if (empty($questionLines) || empty($options) || !$answer) return null;

        return [
            'text' => implode('<br>', $questionLines),
            'options' => $options,
            'answer' => $answer,
            'explanation' => $explanation,
            'subject' => $subject,
            'difficulty' => $difficulty,
            'tags' => $tags,
        ];
    }
}
