<?php

namespace Botble\Base\Tables;

use Botble\Base\Supports\SystemManagement;
use Illuminate\Contracts\Routing\UrlGenerator;
use Yajra\DataTables\DataTables;

class InfoTable extends \Botble\Table\Abstracts\TableAbstract
{
    /**
     * @var string
     */
    protected $view = 'core.base::elements.simple-table';

    /**
     * @var bool
     */
    protected $has_checkbox = false;

    /**
     * @var bool
     */
    protected $has_operations = false;

    /**
     * InfoTable constructor.
     * @param DataTables $table
     * @param UrlGenerator $urlGenerator
     */
    public function __construct(DataTables $table, UrlGenerator $urlGenerator)
    {
        $this->setOption('id', 'system_management');
        parent::__construct($table, $urlGenerator);
    }

    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     * @author QuocDung Dang
     * @throws \Exception
     */
    public function ajax()
    {
        return $this->table
            ->of($this->query())
            ->editColumn('name', function ($item) {
                return view('core.base::system.partials.info-package-line', compact('item'))->render();
            })
            ->editColumn('dependencies', function ($item) {
                return view('core.base::system.partials.info-dependencies-line', compact('item'))->render();
            })
            ->escapeColumns([])
            ->make(true);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function query()
    {
        $composerArray = SystemManagement::getComposerArray();
        $packages = SystemManagement::getPackagesAndDependencies($composerArray['require']);
        return collect($packages);
    }

    /**
     * @return mixed
     * @author QuocDung Dang
     */
    public function columns()
    {
        return [
            'name' => [
                'name' => 'name',
                'title' => trans('core.base::system.package_name') . ' : ' . trans('core.base::system.version'),
                'class' => 'text-left',
            ],
            'dependencies' => [
                'name' => 'dependencies',
                'title' => trans('core.base::system.dependency_name') . ' : ' . trans('core.base::system.version'),
                'class' => 'text-left',
            ],
        ];
    }

    /**
     * @return mixed
     * @author QuocDung Dang
     */
    public function buttons()
    {
        return [];
    }

    /**
     * @return null|string
     */
    protected function getDom()
    {
        return "rt<'datatables__info_wrap'pli<'clearfix'>>";
    }

    /**
     * @return array
     * @author QuocDung Dang
     * @since 2.1
     * @throws \Throwable
     */
    public function getBuilderParameters()
    {
        return [
            'stateSave' => true,
        ];
    }

    /**
     * @return mixed
     * @author QuocDung Dang
     */
    public function actions()
    {
        return [];
    }
}