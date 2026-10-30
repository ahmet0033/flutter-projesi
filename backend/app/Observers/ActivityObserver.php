<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class ActivityObserver
{
    /**
     * @var array<string>
     */
    private array $ignored = [
        'password',
        'remember_token',
        'app_authentication_secret',
        'app_authentication_recovery_codes',
        'created_at',
        'updated_at',
    ];

    public function created(Model $model): void
    {
        $this->write('created', $model, [], Arr::except($model->getAttributes(), $this->ignored));
    }

    public function updated(Model $model): void
    {
        $changes = Arr::except($model->getChanges(), $this->ignored);

        if ($changes === []) {
            return;
        }

        $old = Arr::only($model->getOriginal(), array_keys($changes));

        $this->write('updated', $model, $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->write('deleted', $model, Arr::except($model->getAttributes(), $this->ignored), []);
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $attributes
     */
    private function write(string $event, Model $model, array $old, array $attributes): void
    {
        $label = class_basename($model);

        if (method_exists($model, 'getAttribute') && filled($model->getAttribute('number'))) {
            $label .= ' '.$model->getAttribute('number');
        } elseif (method_exists($model, 'getAttribute') && filled($model->getAttribute('name'))) {
            $label .= ' '.$model->getAttribute('name');
        }

        ActivityLog::query()->create([
            'user_id' => Auth::id(),
            'event' => $event,
            'subject_type' => $model->getMorphClass(),
            'subject_id' => $model->getKey(),
            'description' => "{$label} {$event}",
            'properties' => array_filter([
                'old' => $old,
                'attributes' => $attributes,
            ], fn (array $value): bool => $value !== []),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
