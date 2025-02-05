<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<div class="modal fade" id="avg_logged_time_complete_task{{$submit_number_of_tasks_in_this_month}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div class="modal-title"><h4>Received Tasks: {{$number_of_tasks_received}}</h4>
            <h4>Primary Pages: {{$number_of_tasks_received_primary_page}}</h4> 
           <h4>Secondary Pages: {{$number_of_tasks_received_secondary_page}} </h4>  
           <h4>Others:  {{$number_of_tasks_received - ($number_of_tasks_received_primary_page + $number_of_tasks_received_secondary_page)}}</h4>  
             </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <table id="avg_logged_time_complete_task_table" class="display" style="width:100%">
                <thead>
                  <tr>
                    <th scope="col">Sl No</th>
                    <th scope="col">Assign Date</th>
                    <th scope="col">Task Name</th>
                    <th scope="col">Client Name</th>
                    <th scope="col">Submission Date</th>
                    <th scope="col">Deadline</th>
                    <th scope="col">status</th>
                    <th scope="col">Hours Log</th>
                    <th scope="col">Revision Count</th>
                  </tr>
                </thead>
                <tbody>
                    @foreach($submit_number_of_tasks_in_this_month_data as $row)
                    @php
                      $revision_log_hour = App\Models\ProjectTimeLog::where('task_id',$row->id)->where('revision_status',1)->sum('total_hours');
                      $revision_log_total_min = App\Models\ProjectTimeLog::where('task_id',$row->id)->where('revision_status',1)->sum('total_minutes');
                      $revision_log_min = $revision_log_hour * 60 + $revision_log_total_min;
                    @endphp
                 
                    <tr>
                        <td>{{$loop->index+1}}</td>
                        <td>
                            {{$row->assign_date}}
                          
                        </td>
                        <td>
                            <a href="{{route('tasks.show',$row->id)}}">{{$row->heading}}<a>
                         
                        </td>
                        <td>
                            @if($row->cl_id != null)
                            {{$row->cl_name}}
                            @elseif($row->client_name != null)
                            {{$row->client_name}}
                            @else 
                           <a href="{{route('clients.show',$row->clientId)}}"> {{$row->clientName}}</a>

                            @endif

                        </td>
                        <td>
                            @if($row->board_column_id == 1 || $row->board_column_id == 2 || $row->board_column_id == 3)
                            N\A 
                            @else 
                            {{$row->submission_date}}
                        @endif
                    </td>
                        <td>{{$row->due_date}}</td>
                        <td>
                          <span style="color: {{$row->label_color}}"> {{$row->column_name}}</span>
                        </td>
                        <td>
                          <?php
                              $hours = floor($revision_log_min / 60);
                              $minutes = $revision_log_min % 60;
                              echo $hours . ' hrs ' . $minutes . ' mins';
                          ?>
                      </td>
                        <td>
                          {{-- <a href="#" data-toggle="modal" data-target="#revision_count_modal{{ $key }}">
                              {{$row->revision_count}}
                          </a> --}}
                          {{$row->revision_count}}
                        </td>
                    </tr>
                    @endforeach
                   
                </tbody>
              </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  {{-- @foreach($submit_number_of_tasks_in_this_month_data as $key => $row)
  @if ($row->revision_count !='0')
  @php
    $task_revisions = App\Models\TaskRevision::where('task_id',$row->id)->get();
  @endphp
    <div class="modal fade"  id="revision_count_modal{{ $key }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <table class="table my-3">
              <thead class="">
                <tr>
                  <th scope="col">Date</th>
                  <th scope="col">Responsible Person</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($task_revisions as $task_revision)
                @php
                  $rp_client = '';
                  $rp_developer = '';
                  $rp_pm = '';
                  $rp_lead_dev = '';
                  $rp_ud = '';
                  $rp_gd = '';
                  $rp_sales = '';
                  if ($task_revision->final_responsible_person != null) {
                    if ($task_revision->final_responsible_person == 'C') {
                      $project = App\Models\Project::where('id',$task_revision->project_id)->first();
                      $rp_client = App\Models\User::where('id',$project->client_id)->first();
                    }
                    if ($task_revision->final_responsible_person == 'D') {
                      $task = App\Models\Task::where('id',$task_revision->task_id)->first();
                      $taskUser = App\Models\Task::where('task_id',$task->id)->first();
                      $rp_developer = App\Models\User::where('id',$taskUser->user_id)->first();
                    }
                    if ($task_revision->final_responsible_person == 'PM') {
                      $project = App\Models\Project::where('id',$task_revision->project_id)->first();
                      $rp_pm = App\Models\User::where('id',$project->pm_id)->first();
                    }
                    if ($task_revision->final_responsible_person == 'LD') {
                      $project = App\Models\Project::where('id',$task_revision->project_id)->first();
                      $projectMember = App\Models\ProjectMember::where('project_id',$project->id)->groupBy('project_id')->first();
                      $rp_lead_dev = App\Models\User::where('id',$projectMember->lead_developer_id)->first();
                    }
                    if ($task_revision->final_responsible_person == 'UD') {
                      $task = App\Models\Task::where('id',$task_revision->task_id)->first();
                      $taskUser = App\Models\Task::where('task_id',$task->id)->first();
                      $rp_ud = App\Models\User::where('id',$taskUser->user_id)->first();
                    }
                    if ($task_revision->final_responsible_person == 'GD') {
                      $task = App\Models\Task::where('id',$task_revision->task_id)->first();
                      $taskUser = App\Models\Task::where('task_id',$task->id)->first();
                      $rp_gd = App\Models\User::where('id',$taskUser->user_id)->first();
                    }
                    if ($task_revision->final_responsible_person == 'S') {
                      $project = App\Models\Project::where('id',$task_revision->project_id)->first();
                      $deal = App\Models\Deal::where('id',$project->deal_id)->first();
                      $lead = App\Models\Lead::where('id',$deal->lead_id)->first();
                      $rp_sales = App\Models\User::where('id',$lead->added_by)->first();
                    }
                  }
                @endphp
                <tr>
                  <td>
                      {{$task_revision->created_at}}
                  </td>
                  <td>
                    @if ($rp_client !=null)
                    <a href="{{ route('clients.show',$rp_client->id) }}">{{ $rp_client->name }}</a>
                    @endif
                    @if ($rp_developer !=null)
                    <a href="{{ route('employees.show',$rp_developer->id) }}">{{ $rp_developer->name }}</a>
                    @endif
                    @if ($rp_pm !=null)
                    <a href="{{ route('employees.show',$rp_pm->id) }}">{{ $rp_pm->name }}</a>
                    @endif
                    @if ($rp_lead_dev !=null)
                    <a href="{{ route('employees.show',$rp_lead_dev->id) }}">{{ $rp_lead_dev->name }}</a>
                    @endif
                    @if ($rp_ud !=null)
                    <a href="{{ route('employees.show',$rp_ud->id) }}">{{ $rp_ud->name }}</a>
                    @endif
                    @if ($rp_gd !=null)
                    <a href="{{ route('employees.show',$rp_gd->id) }}">{{ $rp_gd->name }}</a>
                    @endif
                    @if ($rp_sales !=null)
                    <a href="{{ route('employees.show',$rp_sales->id) }}">{{ $rp_sales->name }}</a>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  @endif
  @endforeach --}}

  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
      new DataTable('#avg_logged_time_complete_task_table',{
        "dom": 't<"d-flex"l<"ml-auto"ip>><"clear">',
      });
  </script>
