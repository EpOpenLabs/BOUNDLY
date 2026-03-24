<?php

namespace Domain\Users\Entities;

use Domain\Comments\Entities\Comment;
use Domain\Shared\Entities\AggregateRoot;
use Infrastructure\FrameworkCore\Attributes\Behavior\Auditable;
use Infrastructure\FrameworkCore\Attributes\Behavior\SoftDelete;
use Infrastructure\FrameworkCore\Attributes\Relations\MorphMany;
use Infrastructure\FrameworkCore\Attributes\Schema\Column;
use Infrastructure\FrameworkCore\Attributes\Schema\Entity;
use Infrastructure\FrameworkCore\Attributes\Schema\Id;
use Infrastructure\FrameworkCore\Attributes\Security\Hidden;

/**
 * Pure Domain User Entity.
 * By defining this class, BOUNDLY infrastructure automatically creates
 * CRUD endpoints (GET /api/users, etc.) and synchronizes the database.
 */
#[Entity(table: 'users', resource: 'users', morphName: 'user')]
#[Auditable]
#[SoftDelete]
class User
{
    use AggregateRoot;

    #[MorphMany(relatedEntity: Comment::class, relation: 'commentable')]
    private array $comments = [];

    #[Id]
    private int $id;

    #[Column(type: 'string', length: 150)]
    private string $name;

    #[Column(type: 'string', length: 150)]
    private string $email;

    #[Column(type: 'string', length: 150, nullable: true, default: '115-0000')]
    private string $phone;

    #[Column(type: 'string', length: 150, nullable: true, default: 'Dirección no especificada')]
    private string $addres;

    #[Hidden]
    #[Column(type: 'string')]
    private string $password;

    public function __construct(string $name, string $email, string $password)
    {
        $this->name = $name;
        $this->email = $email;
        // Pure business logic: hash password on instantiation.
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    // Pure Domain Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
