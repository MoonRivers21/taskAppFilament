<?php

namespace App\Rules;

use App\Models\Task;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CheckUniqueTaskTitle implements Rule
{
    protected $taskId;
    protected $currentTitle;

    public function __construct($taskId, $currentTitle)
    {
        $this->taskId = $taskId;
        $this->currentTitle = $currentTitle;
    }

    public function passes($attribute, $value)
    {
        // If editing and title is not changed, skip validation
        if ($this->taskId && $value === $this->currentTitle) {
            return true;
        }

        $userId = Auth::id();

        $exists = Task::where('user_id', $userId)
            ->where('title', $value)
            ->exists();

        return !$exists;
    }

    public function message()
    {
        return 'The title must be unique.';
    }
}
