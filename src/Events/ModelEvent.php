<?php

namespace Unusualify\Modularity\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

abstract class ModelEvent
{
    /**
     * The class of the model.
     *
     * @var string
     */
    public $modelType;

    /**
     * The user model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $user;

    /**
     * The recent URL.
     *
     * @var string
     */
    public $recentUrl;

    /**
     * The previous URL.
     *
     * @var string
     */
    public $previousUrl;

    /**
     * The changed attributes.
     *
     * @var array
     */
    protected $changedAttributes = [];

    /**
     * The changed relationships.
     *
     * @var array
     */
    protected $changedRelationships = [];

    /**
     * The channel name.
     *
     * @var string
     */
    public $broadcastService = 'reverb';

    /**
     * Create a new event instance.
     */
    public function __construct(public $model, public $serializedData = null)
    {
        $this->modelType = get_class($this->model);
        $this->user = Auth::user();
        $this->recentUrl = url()->current() ?? null;
        $this->previousUrl = url()->previous() ?? null;
        $this->changedAttributes = $this->model->getChanges();
        $this->changedRelationships = $this->model->getChangedRelationships();

        if (in_array(InteractsWithBroadcasting::class, class_uses_recursive($this))) {
            $this->broadcastVia($this->broadcastService);
        }
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        // dd(
        //     $this->model,
        //     $this->modelType,
        //     $this->broadcastService
        // );
        return [
            // new PrivateChannel('models.'.$this->type.'.'.$this->model->id),
            // new PresenceChannel('models.'.$this->model->id),
            new PrivateChannel('models.' . $this->model->id),
            new Channel('model'),
        ];
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        return true;
        // return $this->order->value > 100;
    }

    public function broadcastAs()
    {
        // dd(
        //     'modularity.' . Str::replace('_', '.', Str::replace('_event', '', Str::snake(get_class_short_name($this))))
        // );
        return 'modularity.' . Str::replace('_', '.', Str::replace('_event', '', Str::snake(get_class_short_name($this))));
    }

    /**
     * Get the user model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Check if the user model exists.
     *
     * @return bool
     */
    public function hasUser()
    {
        return $this->user !== null;
    }

    /**
     * Get the recent URL.
     *
     * @return string
     */
    public function getRecentUrl()
    {
        return $this->recentUrl;
    }

    /**
     * Get the previous URL.
     *
     * @return string
     */
    public function getPreviousUrl()
    {
        return $this->previousUrl;
    }

    public function wasChanged($values = null)
    {
        if(empty($values)){
            return count($this->changedAttributes) > 0 || count($this->changedRelationships) > 0;
        }

        foreach(Arr::wrap($values) as $value){
            if(array_key_exists($value, $this->changedAttributes) || array_key_exists($value, $this->changedRelationships)){
                return true;
            }
        }

        return false;
    }
}
