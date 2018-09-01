<?php

use Botble\Widget\AbstractWidget;

class PopularPostsWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $frontendTemplate = 'frontend';

    /**
     * @var string
     */
    protected $backendTemplate = 'backend';

    /**
     * @var string
     */
    protected $widgetDirectory = 'popular-posts';

    /**
     * PopularPostsWidget constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'name' => 'Popular Posts - DungThinh Theme',
            'description' => 'Show list popular posts',
            'number_display' => 5,
        ]);
    }
}