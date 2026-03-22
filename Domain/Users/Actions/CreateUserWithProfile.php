<?php

namespace Domain\Users\Actions;

use Illuminate\Support\Facades\DB;

/**
 * Aquí está tu idea del "parcheo".
 * Si crear un usuario requiere lógica compleja de negocio (ej. crear su Perfil también),
 * el programador de tu framework crea esta "Action".
 * Tu ActionDispatcher del Framework la encontrará automáticamente e interceptará el POST.
 */
class CreateUserWithProfile
{
    public function execute(array $payload)
    {
        // En un DDD real, inyectaríamos el Repositorio, pero esto demuestra tu idea del parcheo atómico:
        return DB::transaction(function () use ($payload) {
            
            // 1. Guardamos al Usuario (Reemplaza el CRUD por defecto de FrameworkCore)
            $userId = DB::table('users')->insertGetId([
                'name'     => $payload['name'],
                'email'    => $payload['email'],
                'password' => bcrypt($payload['password']) // Regla saltada del dominio por simplicidad
            ]);

            // 2. Aquí está la "mágia parcheada" de lógica de negocio adicional: Creamos el perfil
            DB::table('profiles')->insert([
                'user_id' => $userId,
                'bio'     => $payload['bio'] ?? 'Generado automáticamente',
            ]);

            return [
                'message' => 'Usuario y Perfil creados en una sola transacción atómica.',
                'user_id' => $userId
            ];
        });
    }
}
