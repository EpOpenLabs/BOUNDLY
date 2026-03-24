<?php

return [
    'scanning_entities' => 'Escaneando entidades y sincronizando base de datos...',
    'no_entities_found' => 'No se detectaron entidades registradas.',
    'creating_magic_table' => 'Creando tabla mágica: :table (Basada en :class)',
    'table_exists' => 'La tabla :table ya existe.',
    'magic_migration_done' => 'Migración mágica finalizada con éxito.',

    'watcher_started' => 'Core Watcher iniciado. Pulsa Ctrl+C para salir.',
    'file_changed' => 'Cambio detectado en la carpeta Domain.',
    'syntax_error' => 'Error de sintaxis en el archivo: :file',
    'prompt_migrate' => '¿Quieres sincronizar la base de datos ahora?',
    'sync_skipped' => 'Sincronización omitida.',

    'resource_not_found' => 'Recurso o Tabla ":resource" no encontrado en el Framework Core.',
    'resource_not_defined' => 'El recurso ":resource" no está definido como Entidad en el Dominio.',
    'resource_created_magic' => 'Recurso creado exitosamente por el motor automático.',
    'resource_updated_magic' => 'Recurso actualizado exitosamente.',
    'resource_deleted_magic' => 'Recurso eliminado exitosamente.',
    'unsupported_method' => 'El método :method no está soportado.',

    // Auth
    'unauthenticated' => 'Se requiere autenticación para acceder a este recurso.',
    'unauthorized' => 'No tienes el rol requerido para realizar esta acción.',

    // Rate Limiting
    'rate_limit_exceeded' => 'Demasiadas solicitudes. Por favor, reduce el ritmo e inténtalo más tarde.',
];
