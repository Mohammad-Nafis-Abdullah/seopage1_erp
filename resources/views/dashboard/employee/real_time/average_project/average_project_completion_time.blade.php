<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<div class="modal fade" id="realTimeaverageComplectionDays{{ count($average_project_completion_rate) }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div class="modal-title" id="exampleModalLabel">
            <h4>Average tasks completion rate for this cycle: {{round($average_completion_days,2)}} days</h4>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <table id="real_time_averageComplectionDayTable" class="display" style="width:100%">
                <thead>
                  <tr>
                    <th scope="col">Sl No</th>
                    <th scope="col">Client Name</th>
                    <th scope="col">Project Name</th>
                    <th scope="col">Project Type</th>
                    <th scope="col">Project Budget</th>
                    <th scope="col">Project Status</th>
                    <th scope="col">Complection Time Days</th>
                    <th scope="col">Status</th>
                  </tr>
                </thead>
                <tbody>
                    @foreach ($average_project_completion_rate as $item)
                        @php
                            $user = \App\Models\User::where('id',$item->client_id)->first();
                            $deal = \App\Models\Deal::where('id',$item->deal_id)->first();
                        @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('clients.show',$user->id) }}">{{ $user->name }}</a>
                        </td>
                        <td>
                            <a href="{{ route('projects.show',$item->id) }}">{{ $item->project_name }}</a>
                        </td>
                        <td>{{ $deal->project_type }}</td>
                        <td>{{ $item->project_budget }} $</td>
                        <td>{{ $item->project_status }}</td>
                        <td>{{ $item->completion_time_days }}</td>
                        <td>
                            @if ($item->status == 'in progress')
                                <span class="badge badge-primary">{{ $item->status }}</span>
                            @endif
                            @if ($item->status == 'finished')
                                <span class="badge badge-success">{{ $item->status }}</span>
                            @endif
                            @if ($item->status == 'partially finished')
                                <span class="badge badge-info">{{ $item->status }}</span>
                            @endif
                            @if ($item->status == 'canceled')
                                <span class="badge badge-danger">{{ $item->status }}</span>
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
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
      new DataTable('#real_time_averageComplectionDayTable',{
        "dom": 't<"d-flex"l<"ml-auto"ip>><"clear">',
      });
  </script>
