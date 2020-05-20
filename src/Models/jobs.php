<?php

namespace macropage\LaravelSchedulerWatcher\Models;

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
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\jobs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\jobs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\jobs query()
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\jobs whereJobDbCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\jobs whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\jobs whereJobMd5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\macropage\LaravelSchedulerWatcher\Models\jobs whereJobName($value)
 * @mixin \Eloquent
 */
class jobs extends Model {
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'job_id';

    /**
     * @var array
     */
    protected $fillable = ['job_md5', 'job_name', 'job_created', 'job_command'];

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

    /**
     * @return HasMany
     */
    public function jobEvents() {
        return $this->hasMany(job_events::class, 'jobe_job_id', 'job_id');
    }
}
