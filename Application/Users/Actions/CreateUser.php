<?php

namespace Application\Users\Actions;

use Application\Users\DTOs\UserDTO;
use Infrastructure\FrameworkCore\Attributes\UseCase\Action;
use Infrastructure\FrameworkCore\Database\DynamicRepository;
use Illuminate\Http\Request;

#[Action(resource: 'users', method: 'POST')]
class CreateUser
{
    public function __construct(
        protected DynamicRepository $repository
    ) {}

    public function execute(Request $request): array
    {
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone'    => 'nullable|string',
            'address'  => 'nullable|string',
        ]);

        $dto = UserDTO::fromRequest($data);
        $user = $this->repository->insert('users', $dto->toArray());

        return [
            'status'  => 'success',
            'message' => 'User created successfully',
            'data'    => $user
        ];
    }
}
