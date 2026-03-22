<?php

namespace Application\Posts\Actions;

use Domain\Posts\Entities\Post;
use Infrastructure\FrameworkCore\Attributes\Action;
use Infrastructure\FrameworkCore\Database\DynamicRepository;
use Application\Posts\DTOs\CreatePostDTO;

#[Action(resource: 'publicaciones', method: 'POST')]
class CreatePost
{
    public function __construct(protected DynamicRepository $repository) {}

    public function execute(CreatePostDTO $dto)
    {
        // 1. Instanciamos la entidad de dominio
        $post = new Post(
            $dto->title,
            $dto->content,
            $dto->user_id
        );

        // 2. Persistimos los datos usando el nombre del RECURSO lógico
        return $this->repository->insert('publicaciones', [
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'user_id' => $post->getUserId()
        ]);
    }
}
