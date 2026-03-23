<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// BOUNDLY: Dynamic channels for Domain Entities
Broadcast::channel('domain.{entity}.{id}', function ($user, $entity, $id) {
    // Here we can implement generic permissions based on the Boundly Auth registry
    return true; 
});
