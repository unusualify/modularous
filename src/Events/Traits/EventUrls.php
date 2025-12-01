<?php

namespace Unusualify\Modularity\Events\Traits;

trait EventUrls
{
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

    public function setupEventUrls()
    {
        $this->recentUrl = url()->current() ?? null;
        $this->previousUrl = url()->previous() ?? null;
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
}
