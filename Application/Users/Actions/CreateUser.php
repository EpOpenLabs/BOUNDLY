<?php

namespace Application\Users\Actions;

use Application\Users\DTOs\UserDTO;
use Infrastructure\FrameworkCore\Attributes\UseCase\Action;
use Infrastructure\FrameworkCore\Database\DynamicRepository;
use Illuminate\Http\Request;

/**
 * Example Application Action: CreateUser
 * 
 * This action overrides the default auto-CRUD behavior for POST /api/users.
 * It demonstrates how to handle custom business logic before persisting.
 */
#[Action(resource: 'users', method: 'POST')]
class CreateUser
{
    public function __construct(
        protected DynamicRepository $repository
    ) {}

    public function execute(Request $request)
    {
        // 1. Validate the request (The EntityValidator already runs, but you can do more here)
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone'    => 'nullable|string',
            'addres'   => 'nullable|string',
        ]);

        // 2. Wrap incoming data in a DTO
        $dto = UserDTO::fromRequest($data);

        // 3. Persist via repository
        $user = $this->repository->insert('users', $dto->toArray());

        return response()->json([
            'status'  => 'success',
            'message' => 'User created through Application Action using DTO',
            'data'    => $user
        ], 201);
    }
}
