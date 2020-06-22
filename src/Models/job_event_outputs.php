<?php

namespace macropage\LaravelSchedulerWatcher\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * macropage\LaravelSchedulerWatcher\Models\job_event_outputs
 *
 * @property int        $jobo_id
 * @property int        $jobo_jobe_id
 * @property string     $jobo_output
 * @property job_events $jobEvent
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_event_outputs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_event_outputs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_event_outputs query()
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_event_outputs whereJoboId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_event_outputs whereJoboJobeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_event_outputs whereJoboOutput($value)
 * @mixin \Eloquent
 */
class job_event_outputs extends Model {
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'jobo_id';

    /**
     * @var array
     */
    protected $fillable = ['jobo_jobe_id', 'jobo_output'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_scheduler';

    public function __construct(array $attributes = []) {
        $this->table = config('scheduler-watcher.table_prefix').'job_event_outputs';
        parent::__construct($attributes);
    }

    /**
     * @return BelongsTo
     */
    public function jobEvent() {
        return $this->belongsTo(job_events::class, 'jobo_jobe_id', 'jobe_id');
    }
}
