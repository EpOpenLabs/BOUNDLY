<?php

namespace Domain\Invoices\Entities;

use Infrastructure\FrameworkCore\Attributes\Entity;
use Infrastructure\FrameworkCore\Attributes\Id;
use Infrastructure\FrameworkCore\Attributes\Column;
use Domain\Shared\Entities\AggregateRoot;
use Infrastructure\FrameworkCore\Attributes\Auditable;
use Infrastructure\FrameworkCore\Attributes\SoftDelete;

/**
 * Auto-generated Pure Domain Entity for Invoice.
 * Add properties with #[Column] attributes to evolve the schema.
 */
#[Entity(table: 'invoices', resource: 'invoices')]
#[Auditable]
#[SoftDelete]
class Invoice
{
    use AggregateRoot;

    #[Id]
    private int $id;

    #[Column(type: 'string', length: 150)]
    private string $name;

    // TODO: Add more properties here...

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
}
