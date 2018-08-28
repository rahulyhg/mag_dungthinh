<?php

namespace Botble\Base\Charts;

use Botble\Base\Charts\Supports\Chart;
use Botble\Base\Charts\Supports\ChartTypes;

/**
 * Bar Charts
 */
class BarChart extends Chart
{

    /**
     * @var float
     */
    protected $barSizeRatio = 0.75;

    /**
     * @var int
     */
    protected $barGap = 3;

    /**
     * @var float
     */
    protected $barOpacity = 1.0;

    /**
     * @var array
     */
    protected $barRadius = [0, 0, 0, 0];

    /**
     * @var int
     */
    protected $xLabelMargin = 50;

    /**
     * Array containing colors for the series bars.
     *
     * @brief Bars colors
     *
     * @var array $barColors
     */
    protected $barColors = [
        '#0b62a4',
        '#7a92a3',
        '#4da74d',
        '#afd8f8',
        '#edc240',
        '#cb4b4b',
        '#9440ed',
    ];

    /**
     * Set to true to draw bars stacked vertically.
     *
     * @brief Stacked
     *
     * @var bool $stacked
     */
    protected $stacked = true;

    /**
     * Create an instance of MorrisBarCharts class
     */
    public function __construct()
    {
        parent::__construct(ChartTypes::BAR);
    }
}