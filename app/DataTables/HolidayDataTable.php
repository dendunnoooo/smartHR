<?php

namespace App\DataTables;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class HolidayDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('name', function($row){
                $annualBadge = $row->is_annual ? ' <span class="badge bg-info-light">Annual</span>' : '';
                return $row->name . $annualBadge;
            })
            ->editColumn('type', function($row){
                if ($row->type == 'Regular Holiday') {
                    return '<span class="badge bg-danger-light">Regular Holiday</span>';
                } elseif ($row->type == 'Special Non-Working Holiday') {
                    return '<span class="badge bg-warning-light">Special Non-Working</span>';
                }
                return '';
            })
            ->editColumn('startDate', function($row){
                if(!empty($row->startDate)){
                    return format_date($row->startDate);
                }
            })
            ->editColumn('endDate', function($row){
                if(!empty($row->endDate)){
                    return format_date($row->endDate);
                }
            })
            ->editColumn('created_at', function($row){
                return format_date($row->created_at);
            })
            ->addColumn('action', function($row){
                $id = $row->id;
                return view('pages.holidays.actions',compact(
                    'id'
                ));
            })
            ->rawColumns(['name', 'type', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Holiday $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('holiday-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('frtip')
                    ->orderBy(1);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#'),
            Column::make('name'),
            Column::make('type'),
            Column::make('startDate'),
            Column::make('endDate'),
            Column::make('description'),
            Column::make('created_at'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Holiday_' . date('YmdHis');
    }
}
