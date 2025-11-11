<?php

namespace App\Traits;

use App\Services\AuditLogService;

trait Auditable
{
    /**
     * Boot the auditable trait.
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            app(AuditLogService::class)->log(
                strtolower(class_basename($model)).'.created',
                $model,
                null,
                $model->toArray(),
                class_basename($model).' created'
            );
        });

        static::updated(function ($model) {
            app(AuditLogService::class)->log(
                strtolower(class_basename($model)).'.updated',
                $model,
                $model->getOriginal(),
                $model->getChanges(),
                class_basename($model).' updated'
            );
        });

        static::deleted(function ($model) {
            app(AuditLogService::class)->log(
                strtolower(class_basename($model)).'.deleted',
                $model,
                $model->toArray(),
                null,
                class_basename($model).' deleted'
            );
        });
    }
}
