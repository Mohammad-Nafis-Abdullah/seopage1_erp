<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<div class="modal fade" id="monthlyMilestoneReleasedForThisCycle{{ count($total_milestone_completed_this_cycle) }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div class="modal-title" id="exampleModalLabel"><h4>{{round($total_milestone_assigned_this_cycle_value)}}$ milestones assigned</h4>
            <h4>{{round($total_released_amount_this_cycle,2)}}$ milestones completed</h4>
            <h4>Percentage : {{round($milestone_completion_rate_value_this_cycle,2)}}%</h4>

          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <table id="monthlyMilestoneReleasedForTable" class="display" style="width:100%">
                            <thead>
                              <tr>
                                <th scope="col">Sl No</th>
                                <th scope="col">Client Name</th>
                                <th scope="col">Project Name</th>
                                <th scope="col">Milestone Title</th>
                                <th scope="col">Project Budget</th>
                                <th scope="col">Milestone Cost</th>
                                <th scope="col">Milestone Start</th>
                                <th scope="col">Milestone Complection Date</th>
                                <th scope="col">Milestone Status</th>
                              </tr>
                            </thead>
                            <tbody>
                                @foreach ($total_milestone_completed_this_cycle as $item)
                                    @php
                                        $client = \App\Models\User::where('id',$item->client_id)->first();
                                    @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('clients.show',$client->id) }}">{{ $client->name }}</a>
                                    </td>
                                    <td>
                                        <a href="{{ route('projects.show',$item->id) }}">{{ $item->project_name }}</a>
                                    </td>
                                    <td>{{ $item->milestone_title }}</td>
                                    <td>{{ $item->project_budget }} $</td>
                                    <td>{{ $item->cost }}</td>
                                    <td>{{ $item->milestone_creation_date }}</td>
                                    <td>{{ $item->milestone_completion_date }}</td>
                                    <td>
                                        @if ($item->milestone_status == 'incomplete')
                                            <span class="badge badge-warning">{{ $item->milestone_status }}</span>
                                        @endif
                                        @if ($item->milestone_status == 'complete')
                                            <span class="badge badge-success">{{ $item->milestone_status }}</span>
                                        @endif
                                        @if ($item->milestone_status == 'partially finished')
                                            <span class="badge badge-info">{{ $item->milestone_status }}</span>
                                        @endif
                                        @if ($item->milestone_status == 'canceled')
                                            <span class="badge badge-danger">{{ $item->milestone_status }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                          </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
      new DataTable('#monthlyMilestoneReleasedForTable',{
        "dom": 't<"d-flex"l<"ml-auto"ip>><"clear">',
      });
  </script>
