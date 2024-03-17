<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TaskStatus extends Enum
{
    const IN_PROGRESS = 'In-progress';
    const TODO = 'To-do';
    const DONE = 'Done';

    public static function toSelectArray(): array
    {
        return [
            self::TODO => 'To-do',
            self::IN_PROGRESS => 'In-progress',
            self::DONE => 'Done',
        ];
    }

    /**
     * @return string
     * description of the status
     */
    public static function description($value): string
    {
        return match ($value) {
            self::IN_PROGRESS => 'Task status is in progress',
            self::TODO => 'Tasks that need to be done',
            self::DONE => '',
            default => 'No status',
        };
    }


    /**
     * @return string
     * set the color of status
     */
    public function color($value): string
    {
        return match ($value) {
            self::IN_PROGRESS => 'blue',
            self::TODO => 'warning',
            self::DONE => 'success',
            default => 'gray',
        };

    }


    /**
     * @param $value
     * @return string
     * Set the icon of the status
     */
    public function icon($value): string
    {
        return match ($value) {
            self::IN_PROGRESS => 'heroicon-o-clock',
            self::TODO => 'heroicon-o-clipboard-document-list',
            self::DONE => 'heroicon-o-clipboard-document-check',
            default => '',
        };

    }

}
