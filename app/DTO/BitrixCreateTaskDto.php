<?php

namespace App\DTO;

use App\Contracts\DTO\TaskDtoInterface;
use Illuminate\Contracts\Support\Arrayable;

readonly class BitrixCreateTaskDto implements Arrayable, TaskDtoInterface
{
    public function __construct(
        public string $title,
        public string $description,
        public int $priority,
        public int $responsible, // Исполнитель
        public int $creator, // Постановщик
        public array $accomplices, // Соисполнители
        public array $auditors, // наблюдатели
        public int $groupId,
        public string $deadline,
        public string $tag,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'TITLE' => $this->title,
            'DESCRIPTION' => $this->description,
            'PRIORITY' => $this->priority,
            'DEADLINE' => $this->deadline,
            'RESPONSIBLE_ID' => $this->responsible,
            'CREATED_BY' => $this->creator,
            'ACCOMPLICES' => $this->accomplices,
            'AUDITORS' => $this->auditors,
            'GROUP_ID' => $this->groupId,
            'TAGS' => [$this->tag]
        ];
    }

}