<?php

namespace Infrastructure\FrameworkCore\Validation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Automatically validates incoming request data against
 * an Entity's column metadata (type, length, nullable, etc.)
 * without requiring the programmer to write validation rules manually.
 */
class EntityValidator
{
    /**
     * Validate $data against the entity's column configuration.
     *
     * @param array $data    The incoming request payload.
     * @param array $config  The entity config from EntityRegistry.
     * @param bool  $partial If true, skip 'required' for fields absent in $data (useful for PATCH).
     *
     * @throws ValidationException
     */
    public function validate(array $data, array $config, bool $partial = false): array
    {
        $rules    = [];
        $messages = [];

        foreach ($config['columns'] as $colName => $colAttr) {
            // Skip the primary key
            if ($colName === $config['primaryKey']) {
                continue;
            }

            $fieldRules = [];

            // --- Required vs Optional ---
            if (!$colAttr->nullable && $colAttr->default === null) {
                // Only mark as required if we are NOT doing a partial (PATCH) update
                if (!$partial || array_key_exists($colName, $data)) {
                    $fieldRules[] = 'required';
                }
            } else {
                $fieldRules[] = 'nullable';
            }

            // --- Type Mapping ---
            $fieldRules[] = match ($colAttr->type) {
                'integer', 'bigInteger', 'tinyInteger', 'smallInteger', 'unsignedBigInteger' => 'integer',
                'float', 'double', 'decimal'                                                 => 'numeric',
                'boolean'                                                                    => 'boolean',
                'date'                                                                       => 'date',
                'datetime', 'timestamp'                                                      => 'date_format:Y-m-d H:i:s',
                'json'                                                                       => 'json',
                'email'                                                                      => 'email',
                default                                                                      => 'string',
            };

            // --- Max Length (only for string types) ---
            if ($colAttr->length !== null && in_array($colAttr->type, ['string', 'char', 'text', 'email'])) {
                $fieldRules[] = "max:{$colAttr->length}";
                $messages["{$colName}.max"] = "The field '{$colName}' must not exceed {$colAttr->length} characters.";
            }

            $rules[$colName] = $fieldRules;
        }

        // --- Validate ManyToMany relationships (must be an array of IDs) ---
        foreach ($config['manyToMany'] ?? [] as $relName => $relAttr) {
            $rules[$relName] = ['array', 'nullable'];
            $rules["{$relName}.*"] = ['integer']; // We assume IDs are integers normally
        }

        // Remove rules for fields that are purely derived (auto-injected by the infra)
        $autoFields = ['created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by'];
        foreach ($autoFields as $f) {
            unset($rules[$f]);
        }

        // Validate and throw standard Laravel ValidationException on failure
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Return only the fields declared in the entity (whitelist)
        return $this->sanitize($data, $config);
    }

    /**
     * Removes any field from the payload that is NOT declared in the entity's columns.
     * This prevents mass-assignment of sensitive fields like 'is_admin'.
     */
    public function sanitize(array $data, array $config): array
    {
        $allowedKeys = array_keys($config['columns']);

        // Also allow BelongsTo FK keys (e.g., 'user_id')
        foreach ($config['belongsTo'] as $relName => $relAttr) {
            $allowedKeys[] = $relAttr->foreignKey ?: $relName . '_id';
        }

        // Also allow ManyToMany array keys (e.g., 'roles')
        foreach ($config['manyToMany'] ?? [] as $relName => $relAttr) {
            $allowedKeys[] = $relName;
        }

        return array_intersect_key($data, array_flip($allowedKeys));
    }
}
