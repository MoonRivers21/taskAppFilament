<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        //It will grant the user to view the records if matches the current logged user and the Task Record
        return $user->id === $task->user_id;

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;

    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {

        //It will grant the user to update the records if matches the current logged user and the Task Record
        return $user->id === $task->user_id;

    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        //It will authorize the user to delete the records if matches the current logged user and the Task Record
        return $user->id === $task->user_id;

    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        //It will authorize the user to restore the records if matches the current logged user and the Task Record
        return $user->id === $task->user_id;

    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;

    }


    public function markTodo(User $user, Task $task): bool
    {
        //Only the owner's of tasks records will be authorized to mark as to-do
        return $user->id === $task->user_id;
    }


    public function markInProgress(User $user, Task $task): bool
    {
        //Only the owner's of tasks records will be authorized to mark as in-progress
        return $user->id === $task->user_id;
    }

    public function markDone(User $user, Task $task): bool
    {
        //Only the owner's of tasks records will be authorized to mark as done
        return $user->id === $task->user_id;
    }

    public function togglePublished(User $user, Task $task): bool
    {
        //Only the owner's of tasks records will be authorized to toggle the published status
        return $user->id === $task->user_id;

    }

}
