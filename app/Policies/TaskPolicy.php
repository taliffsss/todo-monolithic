<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Response as HTTP_RESPONSE;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view tasks
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->is_guest) {
            throw new HttpException(HTTP_RESPONSE::HTTP_UNAUTHORIZED, 'Guest users cannot view tasks.');
        }

        return $user->id === $task->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        if ($user->is_guest) {
            throw new HttpException(HTTP_RESPONSE::HTTP_UNAUTHORIZED, 'Guest users cannot create tasks.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): Response
    {
        if ($user->is_guest) {
            throw new HttpException(HTTP_RESPONSE::HTTP_UNAUTHORIZED, 'Guest users cannot update tasks.');
        }

        if ($user->id !== $task->user_id) {
            return Response::deny('You can only update your own tasks.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): Response
    {
        if ($user->is_guest) {
            throw new HttpException(HTTP_RESPONSE::HTTP_UNAUTHORIZED, 'Guest users cannot delete tasks.');
        }

        if ($user->id !== $task->user_id) {
            return Response::deny('You can only delete your own tasks.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can complete the task.
     */
    public function complete(User $user, Task $task): Response
    {
        if ($user->is_guest) {
            throw new HttpException(HTTP_RESPONSE::HTTP_UNAUTHORIZED, 'Guest users cannot complete tasks.');
        }

        if ($user->id !== $task->user_id) {
            return Response::deny('You can only complete your own tasks.');
        }

        return Response::allow();
    }
}
