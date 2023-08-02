<?php

namespace App\DataTables;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\CustomField;
use App\Models\TaskboardColumn;
use App\Models\CustomFieldGroup;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectTimeLogBreak;
use App\Models\RoleUser;
use App\Models\Project;
use Auth;
use App\Models\User;
use App\Models\Subtask;
use App\Models\ProjectTimeLog;

class TasksDataTable extends BaseDataTable
{

    private $editTaskPermission;
    private $deleteTaskPermission;
    private $viewTaskPermission;
    private $changeStatusPermission;
    private $viewUnassignedTasksPermission;

    public function __construct()
    {
        parent::__construct();

        $this->editTaskPermission = user()->permission('edit_tasks');
        $this->deleteTaskPermission = user()->permission('delete_tasks');
        $this->viewTaskPermission = user()->permission('view_tasks');
        $this->changeStatusPermission = user()->permission('change_status');
        $this->viewUnassignedTasksPermission = user()->permission('view_unassigned_tasks');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $taskBoardColumns = TaskboardColumn::orderBy('priority', 'asc')->get();

        $task = new Task();
        $customFieldsGroupsId = CustomFieldGroup::where('model', $task->customFieldModel)->pluck('id')->first();
        $customFields = CustomField::where('custom_field_group_id', $customFieldsGroupsId)->where('export', 1)->get();

        $datatables = datatables()->eloquent($query);

        $datatables->addColumn('check', function ($row) {
            return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
        });

        $datatables->addColumn('action', function ($row) {
            $taskUsers = $row->users->pluck('id')->toArray();

            $action = '<div class="task_view">
                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('tasks.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

            if ($this->editTaskPermission == 'all'
            || ($this->editTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
            || ($this->editTaskPermission == 'added' && $row->added_by == user()->id)
            || ($row->project_admin == user()->id)
            || ($this->editTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $row->added_by == user()->id))
            || ($this->editTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $row->added_by == user()->id || in_array('client', user_roles())))
            || ($this->editTaskPermission == 'owned' && in_array('client', user_roles()))
            ) {
                $action .= '<a class="dropdown-item openRightModal" href="' . route('tasks.edit', [$row->id]) . '">
                            <i class="fa fa-edit mr-2"></i>
                            ' . trans('app.edit') . '
                        </a>';
            }

            if ($this->deleteTaskPermission == 'all'
            || ($this->deleteTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
            || ($this->deleteTaskPermission == 'added' && $row->added_by == user()->id)
            || ($row->project_admin == user()->id)
            || ($this->deleteTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $row->added_by == user()->id))
            || ($this->deleteTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $row->added_by == user()->id || in_array('client', user_roles())))
            || ($this->deleteTaskPermission == 'owned' && in_array('client', user_roles()))
            ) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-user-id="' . $row->id . '">
                            <i class="fa fa-trash mr-2"></i>
                            ' . trans('app.delete') . '
                        </a>';
            }

            if ($this->editTaskPermission == 'all'
            || ($this->editTaskPermission == 'owned' && in_array(user()->id, $taskUsers))
            || ($this->editTaskPermission == 'added' && $row->added_by == user()->id)
            || ($this->editTaskPermission == 'both' && (in_array(user()->id, $taskUsers) || $row->added_by == user()->id))
            ) {
                $action .= '<a class="dropdown-item openRightModal" href="' . route('tasks.create') . '?duplicate_task='.$row->id.'">
                            <i class="fa fa-clone"></i>
                            ' . trans('app.duplicate') . '
                        </a>';
            }

            $action .= '</div>
                </div>
            </div>';

            return $action;
        });



        $customFieldsId = $customFields->pluck('id');
        $fieldData = DB::table('custom_fields_data')->where('model', 'App\Models\Task')->whereIn('custom_field_id', $customFieldsId)->select('id', 'custom_field_id', 'model_id', 'value')->get();

        foreach ($customFields as $customField) {
            $datatables->addColumn($customField->name, function ($row) use($fieldData, $customField) {

                $finalData = $fieldData->filter(function ($value) use($customField, $row) {
                    return $value->custom_field_id == $customField->id && $value->model_id == $row->id;
                })->first();

                if($customField->type == 'select') {
                    $data = $customField->values;
                    $data = json_decode($data); // string to array

                    return $finalData ? (($finalData->value >= 0 && $finalData->value != null) ? $data[$finalData->value] : '--') : '--';
                }

                return $finalData ? $finalData->value : '--';
            });
        }

            $datatables->editColumn('due_date', function ($row) {
                if (is_null($row->due_date)) {
                    return '--';
                }

                if ($row->due_date->endOfDay()->isPast()) {
                    return '<span style="color:#d50000;"><strong>' . $row->due_date->format($this->global->date_format) . '</strong></span>';
                }
                elseif ($row->due_date->isToday()) {
                    return '<span style="color:#ef5350;">' . __('app.today') . '</span>';
                }
                // elseif($row->due_date->isToday()+1 ){
                //     return '<span class="text-warning">' . __('Tommorow') . '</span>';
                // }
                return '<span >' . $row->due_date->format($this->global->date_format) . '</span>';
            });
            $datatables->editColumn('users', function ($row) {
                if (count($row->users) == 0) {
                    return '--';
                }

                $members = '<div class="position-relative">';

                foreach ($row->users as $key => $member) {
                    if ($key < 4) {
                        $img = '<img data-toggle="tooltip" data-original-title="' . mb_ucwords($member->name) . '" src="' . $member->image_url . '">';
                        $position = $key > 0 ? 'position-absolute' : '';

                        $members .= '<div class="taskEmployeeImg rounded-circle '.$position.'" style="left:  '. ($key * 13) . 'px"><a href="' . route('employees.show', $member->id) . '">' . $img . '</a></div> ';
                    }
                }

                if (count($row->users) > 4) {
                    $members .= '<div class="taskEmployeeImg more-user-count text-center rounded-circle border bg-amt-grey position-absolute" style="left:  '. (($key - 1) * 13) . 'px"><a href="' .  route('tasks.show', [$row->id]). '" class="text-dark f-10">+' . (count($row->users) - 4) . '</a></div> ';
                }

                $members .= '</div>';

                return $members;
            });
            $datatables->addColumn('short_code', function ($row) {
                return ucfirst($row->task_short_code);
            });

            $datatables->addColumn('timer_action', function ($row) {

                $subtasks = Subtask::where('task_id', $row->id)->get();
                $total_count = 0;
                
                foreach ($subtasks as $subtask) {
                    $task = Task::where('subtask_id', $subtask->id)->first();
                    $count = ProjectTimeLog::where('task_id', $task->id)
                        ->where('start_time', '!=', null)
                        ->where('end_time', null)
                        ->count();
                    $total_count += $count;
                }
                $parent_task_check= ProjectTimeLog::where('task_id',$row->id)->where('start_time','!=',null)->where('end_time',null)->count();
                if($parent_task_check > 0)
                {
                    $total= $total_count + 1;
                }else 
                {
                    $total= $total_count;
                }

                // $time_log= ProjectTimeLog::where('project_id',$row->project_id)->where('end_time',null)->count();
                // $task_count= Task::where('project_id',$row->project_id)->count();
                

                $timer= '';
                if($total > 0)
                {
                    if ($total == 1) {
                        $count= 'task';
                    } else {
                        $count = 'tasks';
                    }
                    
                    $timer .= '<i class="fa-solid fa-circle-play" style="color:green;"></i> ('.$total.' active '.$count.')';

                }else 
                {
                    $timer .= '<i class="fa-solid fa-circle-stop" style="color:red;"></i> No active tasks';
                }

              
                return $timer;
            });
            $datatables->addColumn('created_at', function ($row) {

              $created_at= Task::where('id',$row->id)->first();
              //dd($created_at->created_at);
                return $created_at->created_at->format($this->global->date_format). ' ('.$created_at->created_at->format('h:i:s A').')';
              //  return $row->created_at->format($this->global->date_format). ' ('.$row->created_at->format('h:i:s A').')';
            });
            $datatables->addColumn('name', function ($row) {
                $members = [];

                foreach ($row->users as $member) {
                    $members[] = $member->name;
                }

                return implode(',', $members);
            });

            $datatables->addColumn('timer', function ($row) {
                if ($row->boardColumn->slug != 'completed' && !is_null($row->is_task_user)) {
                  if($row->board_column_id == 6)
                  {
                    return '';
                  }else{
                    if (is_null($row->userActiveTimer)) {
                        return '<a href="javascript:;" class="text-primary btn border f-15 start-timer" data-task-id="'.$row->id.'"><i class="bi bi-play-circle-fill"></i></a>';

                    } else {

                        if (is_null($row->userActiveTimer->activeBreak)) {
                            $timerButtons = '<div class="btn-group" role="group">';
                           // $timerButtons .= '<a href="javascript:;" class="text-secondary btn border f-15 pause-timer" data-time-id="'.$row->userActiveTimer->id.'" data-toggle="tooltip" data-original-title="' . __('modules.timeLogs.pauseTimer') . '"><i class="bi bi-pause-circle-fill"></i></a>';

                            $timerButtons .= '<a href="javascript:;" class="text-secondary btn border f-15 stop-timer" data-time-id="'.$row->userActiveTimer->id.'"><i class="bi bi-stop-circle-fill"></i></a>';
                            $timerButtons .= '</div>';
                            return $timerButtons;

                        } else {
                            return '<a href="javascript:;" class="text-secondary btn border f-15 resume-timer" data-time-id="'.$row->userActiveTimer->activeBreak->id.'"><i class="bi bi-play-circle-fill"></i></a>';
                        }

                    }
                  }

                }
            });
            $datatables->editColumn('clientName', function ($row) {
                return ($row->clientName) ? mb_ucwords($row->clientName) : '-';
            });
            $datatables->addColumn('task', function ($row) {
                return ucfirst($row->heading);
            });

            $datatables->addColumn('timeLogged', function ($row) {

                $timeLog = '--';
                
                if($row->timeLogged) {
                    $totalMinutes = $row->timeLogged->sum('total_minutes');

                    foreach($row->timeLogged as $value) {
                        if (is_null($value->end_time)) {
                            $workingTime = $value->start_time->diffInMinutes(Carbon::now());
                            $totalMinutes = $totalMinutes + $workingTime;
                        }
                    }
                    
                    $breakMinutes = $row->breakMinutes();
                    $totalMinutes = $totalMinutes - $breakMinutes;

                    $timeLog = intdiv($totalMinutes, 60) . ' ' . __('app.hrs') . ' ';

                    if ($totalMinutes % 60 > 0) {
                        $timeLog .= $totalMinutes % 60 . ' ' . __('app.mins');
                    }
                }

                $tas_id = Task::where('id',$row->id)->first();
                $subtasks = Subtask::where('task_id', $tas_id->id)->get();

                //$time = 0;

                foreach ($subtasks as $subtask) {
                    $task = Task::where('subtask_id', $subtask->id)->first();
                    $totalMinutes = $totalMinutes + $task->timeLogged->sum('total_minutes');
                    
                    foreach($task->timeLogged as $value) {
                        if (is_null($value->end_time)) {
                            $workingTime = $value->start_time->diffInMinutes(Carbon::now());
                            $totalMinutes = $totalMinutes + $workingTime;
                        }
                    }
                }

                if($subtasks == null) {
                    return $timeLog;
                } else {
                    $timeL = intdiv(($totalMinutes), 60) . ' ' . __('app.hrs') . ' ';

                    if ($totalMinutes % 60 > 0) {
                        $timeL .= ($totalMinutes) % 60 . ' ' . __('app.mins');
                    }
                    return $timeL;
                }
            });


            $datatables->editColumn('heading', function ($row) {
                $labels = $private = $pin = $timer = $span = '';

                if ($row->is_private) {
                    $private = '<span class="badge badge-secondary mr-1"><i class="fa fa-lock"></i> ' . __('app.private') . '</span>';
                }

                if (($row->pinned_task)) {
                    $pin = '<span class="badge badge-secondary mr-1"><i class="fa fa-thumbtack"></i> ' . __('app.pinned') . '</span>';
                }

                if ($row->active_timer_all_count > 1) {
                    $timer .= '<span class="badge badge-primary mr-1" ><i class="fa fa-clock"></i> ' . $row->active_timer_all_count . ' ' . __('modules.projects.activeTimers'). '</span>';
                }

                if ($row->activeTimer && $row->active_timer_all_count == 1) {
                    $timer .= '<div class="row ml-1 mt-1"><span class="align-items-center badge badge-primary d-inline-flex mr-1"><i class="fa fa-clock"></i> <p class="timer m-0 ml-2">' . $row->activeTimer->timer . '</p></span></div>';
                }

                foreach ($row->labels as $label) {
                    $labels .= '<span class="badge badge-secondary mr-1" style="background-color: '.$label->label_color .'">'. $label->label_name.'</span>';
                }
                $subtask = Task::where('id', $row->id)->first();


                $subtasks_html = '';
                if($subtask->subtask_id != null) {
                    $span .= '';
                } else {
                    $total_subtask = $row->subtasks->count();
                    
                    $disabled = '';
                    if($total_subtask == 0) {
                        $disabled = 'disabled';
                    }
                    
                    $span .= '';
                    $subtasks_html .= '<a class="openRightModal showSubTask d-flex align-items-center '.$disabled.'" href="'.route('tasks.show_subtask', [$row->id, 'tableView']).'" ';
                    
                    $subtasks_html .= '><i style="color:#31D2F2;" class="fa fa-eye ml-3"></i><span class="ml-1">'.$total_subtask.'</span></a>';
                }
                $html = '<div class="media align-items-center">
                    <div class="media-body">
                        <div class="row">
                            <div class="mx-auto mx-sm-0 pb-2 pb-sm-0 align-self-center">'.$subtasks_html.'</div>
                            <div class="col-9">
                                <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('tasks.show', [$row->id]) . '" class="">' . ucfirst($row->heading) . '</a></h5>
                                <p class="mb-0">' . $pin . ' ' .$span.' ' . $timer . ' ' . $labels .  '</p>
                            </div>
                        </div>
                    </div>
                </div>';

                return $html;
            });
            $datatables->editColumn('board_column', function ($row) use ($taskBoardColumns) {
                $taskUsers = $row->users->pluck('id')->toArray();


                 {
                    return '<i class="fa fa-circle mr-1 text-yellow"
                    style="color: '. $row->boardColumn->label_color .'"></i>'. $row->boardColumn->column_name;
                }
            });
            $datatables->addColumn('status', function ($row) {
                return ucfirst($row->boardColumn->column_name);
            });
            $datatables->editColumn('project_name', function ($row) {
                if (is_null($row->project_id)) {
                    return '';
                }

                return '<a href="' . route('projects.show', $row->project_id) . '" class="text-darkest-grey">' . ucfirst($row->project_name) . '</a>';
            });
            $datatables->editColumn('client_name', function ($row) {
                if (is_null($row->project_id)) {
                    return '';
                }
                $client_id= Project::where('id',$row->project_id)->first();
                //dd($client_id);
                $client_name= User::where('id',$client_id->client_id)->first();


                return $client_name->name;
            });
            
            $datatables->setRowId(function ($row) {
                return 'row-' . $row->id;
            });

            /*$datatables->addColumn('collaps_data', function ($row) {

                return '<button class="openRightModal showSubTask" data-url="'.route('tasks.show_subtask', $row->id).'">show</button>';
            });*/
            $datatables->editColumn('progress', function($row) {
                $subtask = $row->subtasks;
                // $milestones = $project->milestones->count();
                // $completed_milestones = $project->milestones->where('status','complete')->count();
                $totalComplted = 0;

                foreach ($subtask as $value) {
                    $task = Task::where('subtask_id', $value->id)->first();
                    if ($task->status == 'completed') {
                        $totalComplted++;
                    }
                }

                if ($subtask->count() < 1 ) {
                   $completion = 0;
                   $statusColor = 'danger';
                } elseif ($subtask->count() >= 1){
                    $percentage = round(($totalComplted / $subtask->count())*100, 2);
                    if($percentage < 50)
                    {
                        $completion= $percentage;
                        $statusColor = 'danger';
                    }
                    elseif ($percentage >= 50 && $percentage < 75) {
                        $completion= $percentage;
                        $statusColor = 'warning';
                    }elseif($percentage >= 75 && $percentage < 99) {
                        $completion= $percentage;
                        $statusColor = 'info';
                    }else {
                        $completion= $percentage;
                        $statusColor = 'success';
                    }
                }
                $html = '<div class="progress" style="height: 15px;">
                    <div class="progress-bar f-12 bg-'.$statusColor.'" role="progressbar" style="width: '.$completion.'%;" aria-valuenow="'.$completion.'" aria-valuemin="0" aria-valuemax="100">'.$completion.'%</div>
                </div>';
                return $html;
            });

            $datatables->editColumn('estimate_time', function($row) {
                $task = Task::find($row->id);

                $totalHours = $task->estimate_hours;
                $totalMinutes = $task->estimate_minutes;
                
                // $tasks = $task->subtasks;
                
                // foreach($tasks as $value) {
                //     $countTask = Task::where('subtask_id', $value->id)->first();
                //     $totalHours = $totalHours + $countTask->estimate_hours;
                //     $totalMinutes = $totalMinutes + $countTask->estimate_minutes;
                // }

                if ($totalMinutes >= 60) {
                    $hours = intval(floor($totalMinutes / 60));
                    $minutes = $totalMinutes % 60;
                    $totalHours = $totalHours + $hours;
                    $totalMinutes = $minutes;
                }

                if ($totalHours == 0 && $totalMinutes == 0) {
                    return '--';
                } else {
                    return $totalHours.' hrs '.$totalMinutes.' mins';
                }
            });
            $datatables->rawColumns(['board_column', 'action', 'project_name', 'clientName', 'due_date', 'users', 'heading', 'check', 'estimate_time', 'timeLogged', 'timer','timer_action', 'progress']);
            $datatables->removeColumn('project_id');
            $datatables->removeColumn('image');
            $datatables->removeColumn('created_image');
            $datatables->removeColumn('label_color');

            return $datatables;
    }

    /**
     * @param Task $model
     * @return mixed
     */
    public function query(Task $model)
    {


        //dd(user_roles());
        if (in_array('admin', user_roles()) || in_array('Team Lead', user_roles()) || in_array('Lead Developer', user_roles()) || in_array('Project Manager', user_roles()) || in_array('Graphics Designer', user_roles()) || in_array('UI/UIX Designer', user_roles())) {


            $model = $model->whereNull('subtask_id');
           
        } else {
            $model = $model->whereNotNull('subtask_id');
        }
        $request = $this->request();
        $startDate = null;
        $endDate = null;

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();
        }

        $projectId = $request->projectId;
        $taskBoardColumn = TaskboardColumn::completeColumn();

        $model = $model->leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
            ->leftJoin('users as client', 'client.id', '=', 'projects.client_id')
            ->join('taskboard_columns', 'taskboard_columns.id', '=', 'tasks.board_column_id');

        if (
            ($this->viewUnassignedTasksPermission == 'all'
            && !in_array('client', user_roles())
            && ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all'))
            || ($request->has('project_admin') && $request->project_admin == 1)
            ) {
            $model->leftJoin('task_users', 'task_users.task_id', '=', 'tasks.id')
                ->leftJoin('users as member', 'task_users.user_id', '=', 'member.id');

        } else {
            $model->join('task_users', 'task_users.task_id', '=', 'tasks.id')
                ->join('users as member', 'task_users.user_id', '=', 'member.id');
        }

            $model->leftJoin('users as creator_user', 'creator_user.id', '=', 'tasks.created_by')
                ->leftJoin('task_labels', 'task_labels.task_id', '=', 'tasks.id')
                ->selectRaw('tasks.id, tasks.task_short_code, tasks.added_by, projects.project_name, projects.project_admin, tasks.heading, client.name as clientName, creator_user.name as created_by, creator_user.image as created_image, tasks.board_column_id,
             tasks.due_date, taskboard_columns.column_name as board_column, taskboard_columns.label_color,
              tasks.project_id, tasks.is_private ,( select count("id") from pinned where pinned.task_id = tasks.id and pinned.user_id = ' . user()->id . ') as pinned_task')
                ->with('users', 'activeTimerAll', 'boardColumn', 'activeTimer', 'timeLogged', 'timeLogged.breaks', 'userActiveTimer', 'userActiveTimer.activeBreak', 'labels', 'taskUsers')
                ->withCount('activeTimerAll')
                ->groupBy('tasks.id');


        if ($request->pinned == 'pinned') {
            $model->join('pinned', 'pinned.task_id', 'tasks.id');
            $model->where('pinned.user_id', user()->id);
        }

        if (!in_array('admin', user_roles())) {
            if ($request->pinned == 'private') {
                $model->where(
                    function ($q2) {
                        $q2->where('tasks.is_private', 1);
                        $q2->where(
                            function ($q4) {
                                $q4->where('task_users.user_id', user()->id);
                                $q4->orWhere('tasks.added_by', user()->id);
                            }
                        );
                    }
                );

            } else {
                $model->where(
                    function ($q) {
                        $q->where('tasks.is_private', 0);
                        $q->orWhere(
                            function ($q2) {
                                $q2->where('tasks.is_private', 1);
                                $q2->where(
                                    function ($q5) {
                                        $q5->where('task_users.user_id', user()->id);
                                        $q5->orWhere('tasks.added_by', user()->id);
                                    }
                                );
                            }
                        );
                    }
                );
            }
        }

        if ($request->assignedTo == 'unassigned' && $this->viewUnassignedTasksPermission == 'all' && !in_array('client', user_roles())) {
            $model->whereDoesntHave('users');
        }

        if ($startDate !== null && $endDate !== null) {
            $model->where(function ($q) use ($startDate, $endDate) {
                if (request()->date_filter_on == 'due_date') {
                    $q->whereBetween(DB::raw('DATE(tasks.`due_date`)'), [$startDate, $endDate]);

                } elseif (request()->date_filter_on == 'start_date') {
                    $q->whereBetween(DB::raw('DATE(tasks.`start_date`)'), [$startDate, $endDate]);

                } elseif (request()->date_filter_on == 'completed_on') {
                    $q->whereBetween(DB::raw('DATE(tasks.`completed_on`)'), [$startDate, $endDate]);
                }

            });
        }

        if ($request->overdue == 'yes' && $request->status != 'all') {
            $model->where(DB::raw('DATE(tasks.`due_date`)'), '<', now(global_setting()->timezone)->toDateString());
        }

        if ($projectId != 0 && $projectId != null && $projectId != 'all') {
            $model->where('tasks.project_id', '=', $projectId);
        }

        if ($request->clientID != '' && $request->clientID != null && $request->clientID != 'all') {
            $model->where('projects.client_id', '=', $request->clientID);
        }

        if ($request->assignedTo != '' && $request->assignedTo != null && $request->assignedTo != 'all' && $request->assignedTo != 'unassigned') {
            $model->where('task_users.user_id', '=', $request->assignedTo);
        }

        if (($request->has('project_admin') && $request->project_admin != 1) || !$request->has('project_admin')) {
            if ($this->viewTaskPermission == 'owned') {
                $model->where(function ($q) use ($request) {
                    $q->where('task_users.user_id', '=', user()->id);

                    if (in_array('client', user_roles())) {
                        $q->orWhere('projects.client_id', '=', user()->id);
                    }

                    if ($this->viewUnassignedTasksPermission == 'all' && !in_array('client', user_roles()) && $request->assignedTo == 'all') {
                        $q->orWhereDoesntHave('users');
                    }
                });

                if ($projectId != 0 && $projectId != null && $projectId != 'all') {
                    $model->where('projects.project_admin', '<>', user()->id);
                }

            }

            if ($this->viewTaskPermission == 'added') {
                $model->where('tasks.added_by', '=', user()->id);
            }

            if ($this->viewTaskPermission == 'both') {
                $model->where(function ($q) use ($request) {
                    $q->where('task_users.user_id', '=', user()->id);

                    $q->orWhere('tasks.added_by', '=', user()->id);

                    if (in_array('client', user_roles())) {
                        $q->orWhere('projects.client_id', '=', user()->id);
                    }

                    if ($this->viewUnassignedTasksPermission == 'all' && !in_array('client', user_roles()) && ($request->assignedTo == 'unassigned' || $request->assignedTo == 'all')) {
                        $q->orWhereDoesntHave('users');
                    }

                });

            }
        }

        if ($request->assignedBY != '' && $request->assignedBY != null && $request->assignedBY != 'all') {
            $model->where('creator_user.id', '=', $request->assignedBY);
        }

        if ($request->status != '' && $request->status != null && $request->status != 'all') {
            if ($request->status == 'not finished' || $request->status == 'pending_task') {
                $model->where('tasks.board_column_id', '<>', $taskBoardColumn->id);
            }
            else {
                $model->where('tasks.board_column_id', '=', $request->status);
            }
        }

        if ($request->label != '' && $request->label != null && $request->label != 'all') {
            $model->where('task_labels.label_id', '=', $request->label);
        }

        if ($request->category_id != '' && $request->category_id != null && $request->category_id != 'all') {
            $model->where('tasks.task_category_id', '=', $request->category_id);
        }

        if ($request->billable != '' && $request->billable != null && $request->billable != 'all') {
            $model->where('tasks.billable', '=', $request->billable);
        }

        if ($request->milestone_id != '' && $request->milestone_id != null && $request->milestone_id != 'all') {
            $model->where('tasks.milestone_id', $request->milestone_id);
        }

        if ($request->searchText != '') {
            $model->leftJoin('users', 'projects.client_id', '=', 'users.id')->where(function ($query) {
                $query->where('tasks.heading', 'like', '%' . request('searchText') . '%')
                    ->orWhere('member.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('projects.project_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('projects.project_short_code', 'like', '%' . request('searchText') . '%')
                    ->orWhere('tasks.task_short_code', 'like', '%' . request('searchText') . '%')
                    ->orWhere('projects.client_id', 'like', '%' . request('searchText') . '%')
                    ->orWhere('tasks.task_short_code', 'like', '%' . request('searchText') . '%')
                    ->orWhere('users.name', 'like', '%' . request('searchText') . '%');
            });
        }

        if ($request->trashedData == 'true') {
            $model->whereNotNull('projects.deleted_at');
        }
        else {
            $model->whereNull('projects.deleted_at');
        }

        if ($request->type == 'public') {
            $model->where('tasks.is_private', 0);
        }
        if (!is_null($request->subTask_id)) {
            $model->join('sub_tasks', 'sub_tasks.task_id', '=', 'tasks.id')->where('sub_tasks.task_id', '=', $request->subTask_id);
            //dd($model->get());
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('allTasks-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->destroy(true)
            ->responsive(true)
            ->serverSide(true)
            ->stateSave(true)
            ->processing(true)
            ->dom($this->domHtml)

            ->language(__('app.datatable'))
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["allTasks-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("#allTasks-table .select-picker").selectpicker();
                    $(".bs-tooltip-top").removeClass("show");
                }',
                'scrollX' => true
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $task = new Task();
        $customFieldsGroupsId = CustomFieldGroup::where('model', $task->customFieldModel)->pluck('id')->first();
        $customFields = CustomField::where('custom_field_group_id', $customFieldsGroupsId)->where('export', 1)->get();
        $customFieldsDataMerge = [];
        if (Auth::user()->role_id == 5) {
            $data = [
                'check' => [
                    'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                    'exportable' => false,
                    'orderable' => false,
                    'searchable' => false
                ],
                // __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id')],
                __('modules.taskCode') => ['data' => 'short_code', 'name' => 'task_short_code', 'title' => __('modules.taskCode')],
                // '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false],
                __('timer').' ' => ['data' => 'timer', 'name' => 'timer', 'exportable' => false, 'searchable' => false, 'sortable' => false, 'title' => '', 'class' => 'text-right'],
                __('app.task') => ['data' => 'heading', 'name' => 'heading', 'exportable' => false, 'title' => __('app.task')],
                __('app.menu.tasks').' ' => ['data' => 'task', 'name' => 'heading', 'visible' => false, 'title' => __('app.menu.tasks')],
                __('app.project')  => ['data' => 'project_name', 'name' => 'projects.project_name', 'title' => __('app.project')],
                __('app.client_name')  => ['data' => 'client_name', 'name' => 'client_name','sortable' => false, 'title' => __('Client')],
                __('modules.tasks.assigned') => ['data' => 'name', 'name' => 'name', 'visible' => false, 'title' => __('modules.tasks.assigned')],
                __('app.dueDate') => ['data' => 'due_date', 'name' => 'due_date', 'title' => __('app.dueDate')],
                __('app.estimate_time') => ['data' => 'estimate_time', 'name' => 'estimate_time', 'title' => __('Estimated Time')],
                __('modules.employees.hoursLogged') => ['data' => 'timeLogged', 'name' => 'timeLogged', 'title' => __('modules.employees.hoursLogged')],
                __('modules.tasks.assignTo') => ['data' => 'users', 'name' => 'member.name', 'exportable' => false, 'title' => __('modules.tasks.assignTo')],
                __('app.created_at') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('Creation Date')],
                __('app.columnStatus') => ['data' => 'board_column', 'name' => 'board_column', 'exportable' => false, 'searchable' => false, 'title' => __('app.columnStatus')],
                __('app.task').' '.__('app.status') => ['data' => 'status', 'name' => 'board_column_id', 'visible' => false, 'title' => __('app.task')],
                Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
            ];
        } else {
            $data = [
                'check' => [
                    'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                    'exportable' => false,
                    'orderable' => false,
                    'searchable' => false
                ],
                // __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id')],
                // 'collaps_data' => ['className' => 'dt-control', 'orderable' => false, 'data' => 'collaps_data', 'defaultContent' => '',],
                // '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false],
                // __('modules.taskCode') => ['data' => 'short_code', 'name' => 'task_short_code', 'title' => __('modules.taskCode')],
                __('timer').' ' => ['data' => 'timer', 'name' => 'timer', 'exportable' => false, 'searchable' => false, 'sortable' => false, 'title' => '', 'class' => 'text-right'],
                __('app.task') => ['data' => 'heading', 'name' => 'heading', 'exportable' => false, 'title' => __('app.task')],
                __('app.menu.tasks').' ' => ['data' => 'task', 'name' => 'heading', 'visible' => false, 'title' => __('app.menu.tasks')],
                __('timer_action').' ' => ['data' => 'timer_action', 'name' => 'timer_action',  'title' => __('Timer Active/Inactive')],
                __('app.project')  => ['data' => 'project_name', 'name' => 'projects.project_name', 'title' => __('app.project')],
                __('app.client_name')  => ['data' => 'client_name','sortable' => false,  'name' => 'client_name', 'title' => __('Client')],
                __('modules.tasks.assigned') => ['data' => 'name', 'name' => 'name', 'visible' => false, 'title' => __('modules.tasks.assigned')],
                __('app.dueDate') => ['data' => 'due_date', 'name' => 'due_date', 'title' => __('app.dueDate')],
                __('app.estimate_time') => ['data' => 'estimate_time', 'name' => 'estimate_time', 'title' => __('Estimated Time')],
                __('modules.employees.hoursLogged') => ['data' => 'timeLogged', 'name' => 'timeLogged', 'title' => __('modules.employees.hoursLogged')],
                __('modules.tasks.assignTo') => ['data' => 'users', 'name' => 'member.name', 'exportable' => false, 'title' => __('modules.tasks.assignTo')],
                __('app.created_at') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('Creation Date')],
                __('app.columnStatus') => ['data' => 'board_column', 'name' => 'board_column', 'exportable' => false, 'searchable' => false, 'title' => __('app.columnStatus')],
                __('app.progress').' '.__('app.progress') => ['data' => 'progress', 'name' => 'progress', 'title' => __('app.progress')],
                __('app.task').' '.__('app.status') => ['data' => 'status', 'name' => 'board_column_id', 'visible' => false, 'title' => __('app.task')],
                Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
            ];
        }



        foreach ($customFields as $customField) {
            $customFieldsData = [$customField->name => ['data' => $customField->name, 'name' => $customField->name, 'title' => $customField->name, 'visible' => false]];
            $customFieldsDataMerge = array_merge($customFieldsDataMerge, $customFieldsData);
        }


        $datamerge = array_merge($data, $customFieldsDataMerge);
        return $datamerge;

    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Task_' . date('YmdHis');
    }

    public function pdf()
    {
        set_time_limit(0);

        if ('snappy' == config('datatables-buttons.pdf_generator', 'snappy')) {
            return $this->snappyPdf();
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('datatables::print', ['data' => $this->getDataForPrint()]);

        return $pdf->download($this->getFilename() . '.pdf');
    }

}
