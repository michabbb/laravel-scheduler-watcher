<?php

namespace macropage\LaravelSchedulerWatcher\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
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
 * @property-read int|null       $job_event_outputs_count
 * @method static Builder|job_events newModelQuery()
 * @method static Builder|job_events newQuery()
 * @method static Builder|job_events query()
 * @method static Builder|job_events whereJobeDbCreated($value)
 * @method static Builder|job_events whereJobeDuration($value)
 * @method static Builder|job_events whereJobeEnd($value)
 * @method static Builder|job_events whereJobeId($value)
 * @method static Builder|job_events whereJobeJobId($value)
 * @method static Builder|job_events whereJobeStart($value)
 * @method static Builder|job_events whereJobeExitcode($value)
 * @mixin Eloquent
 */
class job_events extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'jobe_id';

    /**
     * @var array
     */
    protected $fillable = [
        'jobe_job_id',
        'jobe_start',
        'jobe_end',
        'jobe_duration',
        'jobe_db_created',
        'jobe_exitcode'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        $this->connection = config('laravel-scheduler-watcher.mysql_connection');
        $this->table      = config('scheduler-watcher.table_prefix') . 'job_events';
        parent::__construct($attributes);
    }

    /**
     * @return BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(jobs::class, 'jobe_job_id', 'job_id');
    }

    /**
     * @return HasMany
     */
    public function jobEventOutputs(): HasMany
    {
        return $this->hasMany(job_event_outputs::class, 'jobo_jobe_id', 'jobe_id');
    }
}
