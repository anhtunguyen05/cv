<?php

declare(strict_types=1);

namespace App\Application\Cv\Data;

final readonly class VersionPage
{
    /**
     * @param  list<VersionRecord>  $items
     * @param  array{current_page:int,per_page:int,total:int,last_page:int}  $meta
     * @param  array{first:?string,last:?string,prev:?string,next:?string}  $links
     */
    public function __construct(
        public array $items,
        public array $meta,
        public array $links,
    ) {}
}
