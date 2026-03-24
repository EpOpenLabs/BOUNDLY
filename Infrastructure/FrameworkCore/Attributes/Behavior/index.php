<?php

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

class_alias(Auditable::class, 'Auditable');
class_alias(SoftDelete::class, 'SoftDelete');
class_alias(TenantAware::class, 'TenantAware');
class_alias(Authorize::class, 'Authorize');
class_alias(Policy::class, 'Policy');
class_alias(Timestampable::class, 'Timestampable');
class_alias(Blameable::class, 'Blameable');
class_alias(Sluggable::class, 'Sluggable');
