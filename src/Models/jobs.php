<?php

namespace macropage\LaravelSchedulerWatcher\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * macropage\LaravelSchedulerWatcher\Models\jobs
 *
 * @property int           $job_id
 * @property string        $job_md5
 * @property string        $job_name
 * @property string        $job_command
 * @property string        $job_created
 * @property job_events[]  $jobEvents
 * @property string|null   $job_db_created
 * @property-read int|null $job_events_count
 * @method static Builder|jobs newModelQuery()
 * @method static Builder|jobs newQuery()
 * @method static Builder|jobs query()
 * @method static Builder|jobs whereJobDbCreated($value)
 * @method static Builder|jobs whereJobId($value)
 * @method static Builder|jobs whereJobMd5($value)
 * @method static Builder|jobs whereJobName($value)
 * @mixin Eloquent
 */
class jobs extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'job_id';

    /**
     * @var array
     */
    protected $fillable = [
        'job_md5',
        'job_name',
        'job_created',
        'job_command'
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
        $this->table      = config('scheduler-watcher.table_prefix') . 'jobs';
        parent::__construct($attributes);
    }

    /**
     * @return HasMany
     */
    public function jobEvents(): HasMany
    {
        return $this->hasMany(job_events::class, 'jobe_job_id', 'job_id');
    }
}
