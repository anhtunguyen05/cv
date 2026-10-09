<?php

declare(strict_types=1);

namespace App\Domain\Patch\ValueObjects;

use InvalidArgumentException;

final readonly class PatchTarget
{
    private function __construct(
        public string $section,
        public string $field,
        public ?string $itemId,
        public string $operation,
    ) {}

    /** @param array{section?:mixed,field?:mixed,item_id?:mixed,operation?:mixed} $value */
    public static function fromArray(array $value): self
    {
        $keys = array_keys($value);
        sort($keys);
        if ($keys !== ['field', 'item_id', 'operation', 'section']
            || ! is_string($value['section'])
            || ! is_string($value['field'])
            || ($value['item_id'] !== null && ! is_string($value['item_id']))
            || ! is_string($value['operation'])) {
            throw new InvalidArgumentException('The Patch target shape is invalid.');
        }
        if ($value['section'] === '' || $value['field'] === '' || $value['operation'] === '') {
            throw new InvalidArgumentException('The Patch target cannot contain empty values.');
        }

        return new self($value['section'], $value['field'], $value['item_id'], $value['operation']);
    }

    /** @return array{section:string,field:string,item_id:?string,operation:string} */
    public function toArray(): array
    {
        return [
            'section' => $this->section,
            'field' => $this->field,
            'item_id' => $this->itemId,
            'operation' => $this->operation,
        ];
    }
}
