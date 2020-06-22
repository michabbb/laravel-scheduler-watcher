<?php

namespace macropage\LaravelSchedulerWatcher\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * macropage\LaravelSchedulerWatcher\Models\job_events
 *
 * @property int                 $jobe_id
 * @property int                 $jobe_job_id
 * @property string              $jobe_start
 * @property string              $jobe_end
 * @property string              $jobe_exitcode
 * @property float               $jobe_duration
 * @property string              $jobe_db_created
 * @property jobs                $job
 * @property job_event_outputs[] $jobEventOutputs
 * @property-read int|null $job_event_outputs_count
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_events newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_events newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_events query()
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_events whereJobeDbCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_events whereJobeDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_events whereJobeEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_events whereJobeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_events whereJobeJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\job_events whereJobeStart($value)
 * @mixin \Eloquent
 */
class job_events extends Model {
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'jobe_id';

    /**
     * @var array
     */
    protected $fillable = ['jobe_job_id', 'jobe_start', 'jobe_end', 'jobe_duration', 'jobe_db_created','jobe_exitcode'];

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
        $this->table = config('scheduler-watcher.table_prefix').'job_events';
        parent::__construct($attributes);
    }

    /**
     * @return BelongsTo
     */
    public function job() {
        return $this->belongsTo(jobs::class, 'jobe_job_id', 'job_id');
    }

    /**
     * @return HasMany
     */
    public function jobEventOutputs() {
        return $this->hasMany(job_event_outputs::class, 'jobo_jobe_id', 'jobe_id');
    }
}
