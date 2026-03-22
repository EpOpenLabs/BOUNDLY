<?php

namespace Domain\Users\Entities;

use Infrastructure\FrameworkCore\Attributes\Entity;
use Infrastructure\FrameworkCore\Attributes\Id;
use Infrastructure\FrameworkCore\Attributes\Column;
use Infrastructure\FrameworkCore\Attributes\BelongsTo;
use Domain\Users\Entities\User;

/**
 * Domain Profile Entity.
 */
#[Entity(table: 'profiles', resource: 'profiles')]
class Profile
{
    #[Id]
    private int $id;

    #[Column(type: 'string', length: 255)]
    private string $avatar_url;

    #[Column(type: 'string', length: 50)]
    private string $bio;

    #[BelongsTo(relatedEntity: User::class, nullable: false)]
    private int $user_id;

    public function __construct(string $avatarUrl, string $bio, int $userId)
    {
        $this->avatar_url = $avatarUrl;
        $this->bio = $bio;
        $this->user_id = $userId;
    }

    public function getAvatarUrl(): string { return $this->avatar_url; }
    public function getBio(): string { return $this->bio; }
    public function getUserId(): int { return $this->user_id; }
}
