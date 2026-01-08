<?php

namespace App\Entity;

use Psr\Log\LoggerInterface;

enum EventType: string
{
    case DAWN31 = self::EVENTS[10];
    case DAWN32 = self::EVENTS[0];
    case DAWN33 = self::EVENTS[1];
    case DAWN34 = self::EVENTS[2];
    case DAWN35 = self::EVENTS[3];
    case DAWN36 = self::EVENTS[4];
    case DAWN37 = self::EVENTS[5];
    case DAWN38 = self::EVENTS[6];
    case DAWN39 = self::EVENTS[7];
    case DAWN40 = self::EVENTS[8];
    case OTHER = self::EVENTS[9];

    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_HIDDEN = 'hidden';

    const EVENTS = [
        'dawn32',
        'dawn33',
        'dawn34',
        'dawn35',
        'dawn36',
        'dawn37',
        'dawn38',
        'dawn39',
        'dawn40',
        'other',
        'dawn31',
    ];

    const EVENT_STATUS = [
        self::EVENTS[0] => self::STATUS_CLOSED,
        self::EVENTS[1] => self::STATUS_CLOSED,
        self::EVENTS[2] => self::STATUS_CLOSED,
        self::EVENTS[3] => self::STATUS_CLOSED,
        self::EVENTS[4] => self::STATUS_CLOSED,
        self::EVENTS[5] => self::STATUS_CLOSED,
        self::EVENTS[6] => self::STATUS_CLOSED,
        self::EVENTS[7] => self::STATUS_OPEN,
        self::EVENTS[8] => self::STATUS_HIDDEN,
        self::EVENTS[9] => self::STATUS_HIDDEN,
        self::EVENTS[10] => self::STATUS_CLOSED,
    ];

    public function getLabel(): string
    {
        $elem = $this->value;
        $label = match ($elem) {
            self::EVENTS[0]  => 'Dawn 32',
            self::EVENTS[1] => 'Dawn 33',
            self::EVENTS[2] => 'Dawn 34',
            self::EVENTS[3] => 'Dawn 35',
            self::EVENTS[4] => 'Dawn 36',
            self::EVENTS[5] => 'Dawn 37',
            self::EVENTS[6] => 'Dawn 38',
            self::EVENTS[7] => 'Dawn 39',
            self::EVENTS[8] => 'Dawn 40',
            self::EVENTS[9] => 'Autre',
            self::EVENTS[10] => 'Dawn 31',
            default => 'Inconnu',
        };
        return $label;
    }

    public function getDescription(): string
    {
        return match ($this->value) {
            self::EVENTS[0] => 'Dawn 32',
            self::EVENTS[1] => 'Dawn 33',
            self::EVENTS[2] => 'Dawn 34',
            self::EVENTS[3] => 'Dawn 35',
            self::EVENTS[4] => 'Dawn 36',
            self::EVENTS[5] => 'Dawn 37',
            self::EVENTS[6] => 'Dawn 38',
            self::EVENTS[7] => 'Dawn 39',
            self::EVENTS[8] => 'Dawn 40',
            self::EVENTS[9] => 'Autre',
            self::EVENTS[10] => 'Dawn 31',
        };
    }

    public function getStatus(): string
    {
        $status = self::EVENT_STATUS[$this->value];
        return $status;
    }

    /**
     * @return array<string, string>
     */
    public static function getChoices($status = null): array
    {
        return array_reduce(self::cases(), function ($choices, self $type) use ($status) {
            if ($status && $type->getStatus() !== $status) {
                return $choices;
            }
            $choices[(string)$type->getLabel()] = $type->value;
            return $choices;
        }, []);
    }
}
