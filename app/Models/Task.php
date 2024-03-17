<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Policies\TaskPolicy;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Task extends Model
{
    use HasFactory, SoftDeletes;
 
    public static array $allowedSorts = [
        'title',
        'created_at'
    ];
    public static array $allowedFilters = [
        'title'
    ];

    protected $fillable = [
        'title',
        'content',
        'image',
        'user_id',
        'status',
        'published_at',
        'published',
    ];
    protected $casts = [
        'status' => TaskStatus::class,
    ];


    // Mutator for 'published' attribute
    public function setPublishedAttribute($value)
    {
        $this->attributes['published'] = $value;

        // Update 'publishedAt' if 'published' is set to 1
        if ($value == 1) {
            $this->attributes['published_at'] = now('Asia/Manila');
        } else {
            $this->attributes['published_at'] = null; // Reset 'publishedAt' if 'published' is not 1
        }
    }

    public function scopeWhereOwnTasks(Builder $query): Builder
    {
        return $query->where('user_id', auth()->user()->id);
    }

    /**
     * @return BelongsTo
     * Relationship to table users
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param $record
     * @param $status
     * @return void
     * Handles the update status of Task (Todo, In Progress, Done)
     *
     */
    public function setTaskStatus($record, $status)
    {

        try {
            //Find task id
            $task = $this->findOrFail($record->id);
            $task->status = $status;
            $task->save();
            $this->handleNotificationSuccess($status);

        } catch (Exception $e) {
            // Handle general exceptions
            // For example, log the error and return a generic error response /  Notify the user for the error response
            Log::error('An error occurred: '.$e->getMessage());
            $errorMessage = 'Failed to set as '.$status.', something error occurred, Please contact system admin.';
            $this->handleNotificationError($errorMessage);
        }
    }

    /**
     * @param $status
     * @return Notification
     * Send a notification to the user after a successful update
     * of the status
     */
    public function handleNotificationSuccess($status)
    {
        return
            Notification::make()
                ->title('Task has been successfully set as '.$status)
                ->success()
                ->send();

    }

    /**
     * @param $message
     * @return Notification
     * Send a notification to the user if error occurs
     */
    public function handleNotificationError($message)
    {
        return
            Notification::make()
                ->title($message)
                ->danger()
                ->send();

    }

    /**
     * @param $state
     * @param $record
     * @return void
     * Handles the notification and notify user for the changes
     */
    public function handleTogglePublish($record, $state): void
    {
        $status = ($state == 0) ? 'drafted' : 'published';
        Notification::make()
            ->title("Task <b>{$record->title}</b> has been <b>{$status}</b>.")
            ->success()
            ->send();
    }

    public function handlePolicyMarkAsTodo($record): bool
    {
        return
            $record->status == TaskStatus::TODO ||
            $record->status == TaskStatus::DONE ||
            $record->status == TaskStatus::IN_PROGRESS ||
            !app(TaskPolicy::class)->markTodo(auth()->user(), $record);
    }

    public function handlePolicyMarkInProgress($record): bool
    {
        return
            $record->status == TaskStatus::IN_PROGRESS ||
            $record->status == TaskStatus::DONE ||
            !app(TaskPolicy::class)->markInProgress(auth()->user(), $record);
    }

    public function handlePolicyMarkDone($record): bool
    {
        return
            $record->status == TaskStatus::DONE ||
            !app(TaskPolicy::class)->markDone(auth()->user(),
                $record);
    }

    public function handleTogglePublished($record): bool
    {
        return !app(TaskPolicy::class)->togglePublished(auth()->user(), $record);
    }

}
