<?php

namespace Domain\Users\Entities;

use Infrastructure\FrameworkCore\Attributes\Entity;
use Infrastructure\FrameworkCore\Attributes\Id;
use Infrastructure\FrameworkCore\Attributes\Column;
use Infrastructure\FrameworkCore\Attributes\HasMany;
use Infrastructure\FrameworkCore\Attributes\HasOne;
use Infrastructure\FrameworkCore\Attributes\Hidden;
use Infrastructure\FrameworkCore\Attributes\Auditable;
use Infrastructure\FrameworkCore\Attributes\SoftDelete;
use Domain\Shared\Entities\AggregateRoot;
use Domain\Users\Events\UserCreated;

/**
 * Pure Domain User Entity.
 * By defining this class, BOUNDLY infrastructure automatically creates
 * CRUD endpoints (GET /api/users, etc.) and synchronizes the database.
 */
#[Entity(table: 'users', resource: 'users')]
#[Auditable]
#[SoftDelete]
class User
{
    use AggregateRoot;
    
    #[HasOne(relatedEntity: Profile::class)]
    private ?Profile $profile = null;

    #[HasMany(relatedEntity: \Domain\Posts\Entities\Post::class)]
    private array $posts = [];
    
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
        
        $this->record(new UserCreated($this));
    }

    // Pure Domain Getters
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
}
