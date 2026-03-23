<?php

namespace Domain\Posts\Entities;

use Domain\Shared\Entities\AggregateRoot;
use Infrastructure\FrameworkCore\Attributes\Entity;
use Infrastructure\FrameworkCore\Attributes\Id;
use Infrastructure\FrameworkCore\Attributes\Column;


#[Entity(table: 'posts', resource: 'posts')]
class Post
{
    use AggregateRoot;
    #[Id]
    private int $id;

    #[Column(type: 'string')]
    private string $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }
}
