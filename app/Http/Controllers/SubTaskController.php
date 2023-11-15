<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\SubTask\StoreSubTask;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use App\Models\TaskUser;
use App\Helper\Files;
use App\Models\TaskFile;
use Validator;
use App\Models\AuthorizationAction;
use App\Notifications\PrimaryPageNotification;
use Illuminate\Support\Facades\Storage;
use Notification;

class SubTaskController extends AccountBaseController
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->subTask = SubTask::with(['files'])->findOrFail($id);
        return view('tasks.sub_tasks.edit', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->subTask = SubTask::with(['files'])->findOrFail($id);
        return view('tasks.sub_tasks.detail', $this->data);
    }

    /**
     * @param StoreSubTask $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(Request $request)
    {
        // DB::beginTransaction();
        $setting = global_setting();
        $task = Task::find(request()->task_id);

        $startDate = $task->start_date->format($setting->date_format);
        $dueDate = !is_null($task->due_date) ? $task->due_date->format($setting->date_format) : '';
        if($task->independent_task_status != null)
        {
            $task_title = Task::where('project_id', $task->project_id)
            ->where('heading', $request->title)
            ->first();

  if ($task_title !== null) {
      return response()->json(['title' => ['Task title should be unique on this project']], 422);
  }

        }else
        {
            $task_title = Task::where('id', $task->id)
            ->where('heading', $request->title)
            ->first();

  if ($task_title !== null) {
      return response()->json(['title' => ['Task title should be unique on this project']], 422);
  }

        }

        if($task->independent_task_status == 1){
            $rules = [
                'title' => 'required',

                'description' => 'required',
                'user_id' => 'required',


            ];

        }else
        {
            $rules = [
                'title' => 'required',
                'estimate_hours' => 'required',
                'estimate_minutes' => 'required',
                'description' => 'required',
                'user_id' => 'required',


            ];

        }

        $validator = Validator::make($request->all(), $rules);
        if ($request->start_date == "Invalid Date" ) {
            return response($validator->errors(), 422);
        };
        if ($request->end_date == "Invalid Date") {
            return response($validator->errors(), 422);
        };


        $dueDateRule = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . $startDate;

        !is_null($task->due_date) ? $dueDateRule . '|before_or_equal:' . $task->due_date : $dueDateRule;

        if ($task->due_date) {

            $dueDate = $task->due_date->format($setting->date_format);
            $dueDateRule .= '|before_or_equal:' . $dueDate;
        }

        $rules['start_date'] = $dueDateRule;

        $rules['due_date'] = !is_null(request()->start_date) ? ($dueDateRule . '|after_or_equal:' . Carbon::createFromFormat($setting->date_format, request()->start_date)->format($setting->date_format)) : $dueDateRule;



        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        $check_estimation = Task::where('id', $request->task_id)->first();
        $parent_task_minutes = $check_estimation->estimate_hours * 60;
        $total_parent_tasks_minutes= $parent_task_minutes + $check_estimation->estimate_minutes;
        $sub_tasks_hours= Subtask::join('tasks','tasks.subtask_id','sub_tasks.id')->where('sub_tasks.task_id',$request->task_id)
            ->sum('tasks.estimate_hours');
         $sub_tasks_minutes= Subtask::join('tasks','tasks.subtask_id','sub_tasks.id')->where('sub_tasks.task_id',$request->task_id)
            ->sum('tasks.estimate_minutes');

        $total_subtasks_minutes = $sub_tasks_hours+$sub_tasks_minutes;

        $hours = $request->estimate_hours * 60;
        $minutes = $request->estimate_minutes;
        $total_minutes = $hours + $minutes;
        if($task->independent_task_status != 1)
        {
        if (($total_parent_tasks_minutes - $total_subtasks_minutes) - $total_minutes < 1) {

            return response()->json([
                "message" => "The given data was invalid.",
                "errors" => [
                    "hours" => [
                        "Estimate hours cannot exceed from project allocation hours !"
                    ]
                ]
            ], 422);

        }
    }
        $this->addPermission = user()->permission('add_sub_tasks');
        $task = Task::findOrFail($request->task_id);
        $taskUsers = $task->users->pluck('id')->toArray();

        abort_403(!($this->addPermission == 'all'
            || ($this->addPermission == 'added' && $task->added_by == user()->id)
            || ($this->addPermission == 'owned' && in_array(user()->id, $taskUsers))
            || ($this->addPermission == 'added' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
        ));

        $subTask = new SubTask();
        $subTask->title = $request->title;
        $subTask->task_id = $request->task_id;
        $subTask->description = str_replace('<p><br></p>', '', trim($request->description));

        if ($request->start_date != '' && $request->due_date != '') {
            $subTask->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
            $subTask->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        }

        $subTask->assigned_to = $request->user_id ? $request->user_id : null;

        $subTask->save();
       // dd($subTask);

        $task_id = Task::where('id', $request->task_id)->first();
        $task_s = new Task();
        if($task->independent_task_status == 1)
        {
            $task_s->independent_task_status = 1;
        }
        $task_s->task_short_code = $task_id->task_short_code . '-' . $subTask->id;
        $task_s->heading = $subTask->title;
        $task_s->description = str_replace('<p><br></p>', '', trim($request->description));
        if ($request->start_date != '' && $request->due_date != '') {
            $task_s->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
            $task_s->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        }
        $task_s->project_id = $task_id->project_id;
        $task_s->task_category_id = $request->task_category_id;

        $task_s->priority = $request->priority;
        $task_s->is_private = $request->has('is_private') ? 1 : 0;
        $task_s->billable = $request->has('billable') && $request->billable ? 1 : 0;
        $task_s->estimate_hours = $request->estimate_hours;
        $task_s->estimate_minutes = $request->estimate_minutes;
        $task_s->repeat = $request->repeat ? 1 : 0;
        $task_s->milestone_id = $request->milestone_id;
        $total_hours = $request->estimate_hours * 60;
        $total_minutes = $request->estimate_minutes;
        $total_in_minutes = $total_hours + $total_minutes;
        $task_s->estimate_time_left_minutes = $total_in_minutes;

        if ($request->has('repeat')) {
            $task_s->repeat_count = $request->repeat_count;
            $task_s->repeat_type = $request->repeat_type;
            $task_s->repeat_cycles = $request->repeat_cycles;
        }


        $task_s->board_column_id = 2;
        $task_s->task_status = "pending";
        $task_s->dependent_task_id = $request->task_id;
        $task_s->subtask_id = $subTask->id;

        $task_s->added_by = Auth::id();
        $task_s->created_by= Auth::id();
        $task_s->pp_task_id = $task_id->pp_task_id;
        if($task->independent_task_status == 1)
        {
            $task_s->independent_task_status = 1;
            $task_s->client_id = $task->client_id;
            $task_s->client_name = $task->client_name;
        }

        $task_s->save();
        $task_type = new TaskType();
        $task_type->task_id= $task_s->id;
        $task_type->page_type= $request->page_type;
        $task_type->task_type= $request->task_type;
        if($request->page_type == 'Primary Page Development')
        {
            $task_type->authorization_status= 0;
            $helper = new HelperPendingActionController();


            $helper->PrimaryPageAuthorization($task_s);

        }
        $task_type->page_name= $request->page_name;
        $task_type->page_url= $request->page_url;
        $task_type->task_type_other= $request->task_type_other;
        $task_type->page_type_name= $request->page_type_name;
        $task_type->existing_design_link = $request->existing_design_link;
        $task_type->number_of_pages= $request->number_of_pages;
        $task_type->save();

        $topManagement = User::where('role_id', 1)->get();

        foreach ($topManagement as $user) {
            Notification::send($user, new PrimaryPageNotification($task_type));
        }

        if($task->independent_task_status != 1)
        {


        $authorization_action = new AuthorizationAction();
        $authorization_action->model_name = $task_s->getMorphClass();
        $authorization_action->model_id = $task_s->id;
        $authorization_action->type = 'task_assign_by_lead_developer';
        $authorization_action->deal_id = $task_s->project->deal_id;
        $authorization_action->project_id = $task_s->project->id;
        $authorization_action->task_id = $task_s->id;
        $authorization_action->link = route('tasks.show', $task_s->id);
        $authorization_action->title = Auth::user()->name . ' assign new task to developer';
        $authorization_action->authorization_for = $request->user_id ;
        $authorization_action->save();


        $parent_task_authorization= AuthorizationAction::where('task_id',$request->task_id)->first();
        //dd($parent_task_authorization);
        if($parent_task_authorization != null && $parent_task_authorization->status == 0)
        {
            $lead_developer= User::where('role_id',6)->orderBy('id','desc')->first();
            $task_authorization= AuthorizationAction::find($parent_task_authorization->id);
            $task_authorization->authorization_by=  $lead_developer->id;
            $task_authorization->status = '1';
            $task_authorization->save();

        }
    }

        // $task_user= new TaskUser();
        // $task_user->task_id= $request->task_id;
        // $task_user->user_id= $request->user_id ? $request->user_id : null;
        //
        // $task_user->save();

        $hours_s = $request->estimate_hours * 60;
        $minutes_s = $request->estimate_minutes;
        $total_minutes_s = $hours_s + $minutes_s;


        $parent_task = Task::where('id', $subTask->task_id)->first();
        $parent_task_update = Task::find($parent_task->id);
        $parent_task_update->estimate_time_left_minutes = $parent_task->estimate_time_left_minutes - $total_minutes_s;
        $parent_task_update->save();


        if ($request->hasFile('file')) {
            $files = $request->file('file');
            $destinationPath = storage_path('app/public/');
            $file_name = [];

            foreach ($files as $file) {
                $taskFile = new TaskFile();
                $taskFile->task_id = $task_s->id;
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                array_push($file_name, $filename);
                $taskFile->user_id = $this->user->id;
                $taskFile->filename = $filename;
                $taskFile->hashname = $filename;
                $taskFile->size = $file->getSize();
                $taskFile->save();

                Storage::disk('s3')->put('/' . $filename, file_get_contents($file));

                $this->logTaskActivity($task->id, $this->user->id, 'fileActivity', $task->board_column_id);
            }
        }
        // /dd($parent_task, $subTask,$file);
        $task = $subTask->task;
        $this->logTaskActivity($task->id, $this->user->id, 'subTaskCreateActivity', $task->board_column_id, $subTask->id);
        return Reply::successWithData(__('messages.subTaskAdded'), [
            'subTaskID' => $subTask->id,
            'sub_task' => [
                'id' => $task_s->id,
                'title' => \Str::limit($task_s->heading, 30, '...'),
                'subtask_id' => $subTask->id
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subTask = SubTask::findOrFail($id);
        SubTask::destroy($id);

        $this->task = Task::with(['subtasks', 'subtasks.files'])->findOrFail($subTask->task_id);
        $view = view('tasks.sub_tasks.show', $this->data)->render();

        return Reply::successWithData(__('messages.subTaskDeleted'), ['view' => $view]);
    }

    public function changeStatus(Request $request)
    {
        $subTask = SubTask::findOrFail($request->subTaskId);
        $subTask->status = $request->status;
        $subTask->save();

        $this->task = Task::with(['subtasks', 'subtasks.files'])->findOrFail($subTask->task_id);
        $this->logTaskActivity($this->task->id, user()->id, 'subTaskUpdateActivity', $this->task->board_column_id, $subTask->id);

        $view = view('tasks.sub_tasks.show', $this->data)->render();


        return Reply::successWithData('messages.subTaskUpdatedSuccessfully', ['view' => $view]);
    }

    /**
     * @param StoreSubTask $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(Request $request, $id)
    {

        $setting = global_setting();
        $task = Task::find($id);
        $startDate = $task->start_date->format($setting->date_format);
        $dueDate = !is_null($task->due_date) ? $task->due_date->format($setting->date_format) : '';
        $rules = [
            'title' => 'required',
            'estimate_hours' => 'required',
            'estimate_minutes' => 'required',
            'description' => 'required',
            'user_id' => 'required',

        ];

        $dueDateRule = 'required|date_format:"' . $setting->date_format . '"|after_or_equal:' . $startDate;

        !is_null($task->due_date) ? $dueDateRule . '|before_or_equal:' . $task->due_date : $dueDateRule;

        if ($task->due_date) {

            $dueDate = $task->due_date->format($setting->date_format);
            $dueDateRule .= '|before_or_equal:' . $dueDate;
        }

        $rules['start_date'] = $dueDateRule;

        $rules['due_date'] = !is_null(request()->start_date) ? ($dueDateRule . '|after_or_equal:' . Carbon::createFromFormat($setting->date_format, request()->start_date)->format($setting->date_format)) : $dueDateRule;

        $validator = Validator::make($request->all(), $rules);


        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        $subTask = SubTask::findOrFail($request->subTaskID);
        $subTask->title = $request->title;
        $subTask->description = str_replace('<p><br></p>', '', trim($request->description));

        if ($request->start_date != '') {
            $subTask->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
        }

        if ($request->due_date != '') {
            $subTask->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        }

        $subTask->assigned_to = $request->user_id ? $request->user_id : null;

        $subTask->save();

        $task_s = Task::find($id);
        $task_s->task_short_code = $task->task_short_code . '-' . $subTask->id;
        $task_s->heading = $subTask->title;
        $task_s->description = str_replace('<p><br></p>', '', trim($request->description));
        if ($request->start_date != '' && $request->due_date != '') {
            $task_s->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
            $task_s->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        }
        $task_s->project_id = $task->project_id;
        $task_s->task_category_id = $request->task_category_id;

        $task_s->priority = $request->priority;
        $task_s->is_private = $request->has('is_private') ? 1 : 0;
        $task_s->billable = $request->has('billable') && $request->billable ? 1 : 0;
        $task_s->estimate_hours = $request->estimate_hours;
        $task_s->estimate_minutes = $request->estimate_minutes;
        $task_s->repeat = $request->repeat ? 1 : 0;
        $task_s->milestone_id = $request->milestone_id;
        $total_hours = $request->estimate_hours * 60;
        $total_minutes = $request->estimate_minutes;
        $total_in_minutes = $total_hours + $total_minutes;
        $task_s->estimate_time_left_minutes = $total_in_minutes;

        if ($request->has('repeat')) {
            $task_s->repeat_count = $request->repeat_count;
            $task_s->repeat_type = $request->repeat_type;
            $task_s->repeat_cycles = $request->repeat_cycles;
        }


        $task_s->board_column_id = 2;
        $task_s->task_status = "pending";
        $task_s->dependent_task_id = $request->task_id;
        $task_s->subtask_id = $subTask->id;
        $task_s->save();

        if ($request->hasFile('file')) {
            $files = $request->file('file');
            $destinationPath = storage_path('app/public/');
            $file_name = [];

            foreach ($files as $file) {
                $taskFile = new TaskFile();
                $taskFile->task_id = $id;
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                array_push($file_name, $filename);
                $taskFile->user_id = $this->user->id;
                $taskFile->filename = $filename;
                $taskFile->hashname = $filename;
                $taskFile->size = $file->getSize();
                $taskFile->save();

                Storage::disk('s3')->put('/' . $filename, file_get_contents($file));

                $this->logTaskActivity($task->id, $this->user->id, 'fileActivity', $task->board_column_id);
            }
        }
        //$task = $subTask->task;
        $this->logTaskActivity($task->id, $this->user->id, 'subTaskUpdateActivity', $task->board_column_id, $subTask->id);

        $this->task = Task::with(['subtasks', 'subtasks.files'])->findOrFail($subTask->task_id);
        //$view = view('tasks.sub_tasks.show', $this->data)->render();

        return Reply::successWithData(__('messages.subTaskUpdated'), [
            'status' => 'success',
            'message' => 'Data has been updated successfully',
            'sub_task' => [
                'id' => $id,
                'title' => $request->title,
                'subtask_id' => $subTask->id
            ]
        ]);
    }
}



