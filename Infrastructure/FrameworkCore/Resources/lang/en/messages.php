<?php

return [
    'scanning_entities' => 'Scanning entities and synchronizing database...',
    'no_entities_found' => 'No registered entities detected.',
    'creating_magic_table' => 'Creating magic table: :table (Based on :class)',
    'table_exists' => 'Table :table already exists.',
    'magic_migration_done' => 'Magic migration finished successfully.',

    'watcher_started' => 'Core Watcher started. Press Ctrl+C to exit.',
    'file_changed' => 'Change detected in the Domain folder.',
    'syntax_error' => 'Syntax error in file: :file',
    'prompt_migrate' => 'Do you want to sync the database right now?',
    'sync_skipped' => 'Synchronization skipped.',

    'resource_not_found' => 'Resource or Table ":resource" not found in the Framework Core.',
    'resource_not_defined' => 'Resource ":resource" is not defined as an Entity in the Domain.',
    'resource_created_magic' => 'Resource created successfully by the Magic Engine.',
    'resource_updated_magic' => 'Resource updated successfully.',
    'resource_deleted_magic' => 'Resource deleted successfully.',
    'unsupported_method' => 'Method :method not supported.',

    // Auth
    'unauthenticated' => 'Authentication required to access this resource.',
    'unauthorized' => 'You do not have the required role to perform this action.',

    // Rate Limiting
    'rate_limit_exceeded' => 'Too many requests. Please slow down and try again later.',
];
