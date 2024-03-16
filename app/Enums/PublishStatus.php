<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;


final class PublishStatus extends Enum
{

    const PUBLISHED = 1;
    const DRAFT = 0;

    public static function toSelectArray(): array
    {
        return [
            self::PUBLISHED => 1,
            self::DRAFT => 0,

        ];
    }


    /**
     * @return string
     * description of the status
     */
    public static function description($value): string
    {
        return match ($value) {
            1 => 'This task is published.',
            0 => 'This task has been saved as a draft',
            default => 'No status',
        };
    }

    public static function descriptionHint($value): string
    {
        return match ($value) {
            1 => 'This task is published, toggle to draft',
            0 => 'This task has been saved as a draft, toggle to publish.',
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
            1 => 'success',
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
            1 => 'heroicon-s-document-check',
            0 => 'heroicon-s-document',
            default => '',
        };

    }

    /**
     * @param $value
     * @return string
     * Set the icon of the status
     */
    public function label($value): string
    {

        return match ($value) {
            1 => 'Published',
            0 => 'Draft',
            default => '',
        };

    }


}
