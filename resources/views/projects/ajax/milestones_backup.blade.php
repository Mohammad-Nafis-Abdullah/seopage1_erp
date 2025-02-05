<style>
    @media only screen and (max-width: 1432px) {
        .complete_milestone,
        .cancel_milestone {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
    }
    /*@media only screen and (min-width: 768px) {*/
    /*    .complete_milestone,*/
    /*    .cancel_milestone {*/
    /*        display: block;*/
    /*        margin-bottom: 10px;*/
    /*        width: 100%;*/
    /*    }*/
    /*}*/
</style>
@php
$addProjectMilestonePermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('add_project_milestones');
$viewProjectMilestonePermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('view_project_milestones');
$editProjectMilestonePermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('edit_project_milestones');
$deleteProjectMilestonePermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('delete_project_milestones');
@endphp

<!-- ROW START -->
<link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">


<div class="row py-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
@if(Session::has('success'))
          <div class="alert alert-success show mb-2" role="alert"> {{Session::get('success')}}</div>

@endif
        @if($project->deal->project_type != 'hourly')
             @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 4)
            <x-forms.button-primary icon="plus" id="add-project-milestone" class="type-btn mb-3">
                @lang('modules.projects.createMilestone')
            </x-forms.button-primary>
             @endif
        @endif

@if ($viewProjectMilestonePermission == 'all' || $viewProjectMilestonePermission == 'added' || ($viewProjectMilestonePermission == 'owned' && user()->id == $project->client_id))
            <x-cards.data :title="__('modules.projects.milestones')"
                otherClasses="border-0 p-0 d-flex justify-content-between align-items-center table-responsive-sm">
                <x-table class="border-0 pb-3 admin-dash-table table-hover">

                    <x-slot name="thead">
                        <th class="pl-20">#</th>
                        <th>Creation Date</th>
                        <th>@lang('modules.projects.milestoneTitle')</th>
                        <th>@lang('modules.projects.milestoneCost')</th>
                        <th>@lang('app.status')</th>
                        <th>@lang('Invoice Genereted')</th>
                        <th>@lang('Payment Release')</th>
                        <th>@lang('Quick Action')</th>
                        <th class="text-right pr-20">@lang('app.action')</th>
                    </x-slot>
                    @php
                      $milestones= App\Models\ProjectMilestone::where('project_id',$project->id)->get();
                    @endphp

                    @forelse($milestones as $key=>$item)
                        <tr id="row-{{ $item->id }}">
                            <td class="pl-20">{{ $key + 1 }}</td>
                            <td>
                                {{ $item->created_at }}
                            </td>
                            <td>
                                <a href="javascript:;" class="milestone-detail text-darkest-grey f-w-500"
                                    data-milestone-id="{{ $item->id }}">{{ ucfirst($item->milestone_title) }}</a>
                            </td>
                            <td>
                                @if (!is_null($item->original_currency_id))
                                @php
                                  $original_currency= App\Models\Currency::where('id',$item->original_currency_id)->first();
                                @endphp
                                    {{ currency_formatter($item->actual_cost, $original_currency->currency_symbol) }}
                                @else
                                    {{ currency_formatter($item->cost) }}
                                @endif
                            </td>
                            <td>
                                @if ($item->status == 'complete')
                                    <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                                    {{ trans('app.' . $item->status) }}
                                @else
                                    <i class="fa fa-circle mr-1 text-red f-10"></i>
                                     @if($item->status == 'canceled')


                                      <span>  {{ trans('app.' . $item->status) }}
                                        <br>
                                        {!!$item->comments!!}
                                    </span>
                                    @else
                                    {{ trans('app.' . $item->status) }}
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if ($item->invoice_created == 1)
                                    <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                                  Yes
                                @else
                                    <i class="fa fa-circle mr-1 text-red f-10"></i>
                                  No
                                @endif
                            </td>
                            <td>

                              @if($item->invoice_id != null)
                              @php
                              $invoice= App\Models\Invoice::where('milestone_id',$item->id)->orderBy('id','desc')->first();
                              @endphp


                                @if ($invoice->status == 'paid')
                                    <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                                  Paid
                                @else
                                    <i class="fa fa-circle mr-1 text-red f-10"></i>
                                  Unpaid
                                @endif
                                @else
                                <i class="fa fa-circle mr-1 text-red f-10"></i>
                              Unpaid

                                @endif
                            </td>
                            <td>



                              <?php
                              $task= App\Models\Task::where('milestone_id',$item->id)->where('status','incomplete')->count();
                              $total_tasks=  App\Models\Task::where('milestone_id',$item->id)->count();
                              $complete_task= App\Models\Task::where('milestone_id',$item->id)->where('status','completed')->count();
                              $milestone_count= App\Models\ProjectMilestone::where('project_id',$project->id)->count();
                              $incomplete_milestone= App\Models\ProjectMilestone::where('project_id',$project->id)->where('status','incomplete')->count();
                              $canceled_milestone= App\Models\ProjectMilestone::where('project_id',$project->id)->where('status','canceled')->count();
                              $complete_milestone= App\Models\ProjectMilestone::where('project_id',$project->id)->where('status','complete')->count();
                              $invoice_generated= App\Models\ProjectMilestone::where('project_id',$project->id)->where('status','complete')->where('invoice_created',1)->count();
                              $last_milestone= App\Models\ProjectMilestone::where('project_id',$project->id)->orderBy('id','desc')->first();
                              $first_milestone= App\Models\ProjectMilestone::where('project_id',$project->id)->where('status','complete')->where('invoice_id',null)->first();
                              $qc_count= App\Models\ProjectMilestone::where('project_id',$project->id)->where('qc_status',1)->count();
                               // dd($qc_count);
                            $project_completion_count= App\Models\ProjectMilestone::where('project_id',$project->id)->where('project_completion_status',1)->count();
                            $invoice_id = App\Models\Invoice::where('milestone_id',$item->id)->first();
                              //dd($task);
                               ?>
                               <form class="" action="{{route('milestone-complete')}}" method="post">
                                 @csrf
                                   <input type="hidden" name="id" value="{{$item->id}}">

    @if($project->deal->project_type != 'hourly')
        @if($task > 0)

                    @if($item->cancelation_status == null)

                                <button type="submit" disabled class="btn-danger btn-sm rounded f-14 p-2 mr-2 mb-2 mb-lg-0 mb-md-0 complete_milestone">Mark As Complete ({{$complete_task}}/{{$total_tasks}})</button>



                                <a href="{{route('invoices.create')}}?project_id={{$item->project_id}}&client_id={{$project->client_id}}&milestone_id={{$item->id}}" type="submit" class="btn-primary btn-sm rounded f-14 p-2 mr-2 mb-2 mb-lg-0 mb-md-0 mt-3"  id="{{$item->id}}"  data-row-id="{{ $item->id }}" >Partial Payment</a>

                                <button type="button" class="btn-danger rounded f-14 p-2 mr-2 mb-2 mb-lg-0 mb-md-0 cancel_milestone" data-row-id="{{ $item->id }}">Cancel Milestone</button>
                    @else
                            @if(Auth::user()->role_id == 1)
                                   @if($item->cancelation_status == 'submitted')
                                         <button type="submit" class="btn-success btn-sm rounded f-14 p-2 mr-2 mb-2 mb-lg-0 mb-md-0 approve_milestone" data-row-id="{{ $item->id }}" data-row-id2="{{ $item->comments }}">Approve Cancelation</button>
                                    @elseif($item->cancelation_status == 'approved')
                                <i class="fa fa-circle mr-1 text-red f-10"></i>
                                                 Canceled
                                    @endif

                            @else
                                <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                                                  Awaiting Approval
                                                  <br>
                                                  (Milestone Cancelation)
                            @endif

                     @endif

        @else
                    @if($item->status == 'incomplete')

                            @if($item->cancelation_status == null)


                                     <button type="submit" class="btn-primary btn-sm rounded f-14 p-2 mr-2 mb-2 mb-lg-0 mb-md-0 complete_milestone" data-id="{{ $item->id }}">Mark As Complete</button>
                                     <button type="submit" class="btn-danger btn-sm rounded f-14 p-2 mr-2 mb-2 mb-lg-0 mb-md-0 cancel_milestone" data-row-id="{{ $item->id }}" >Cancel Milestone</button>
                            @else
                                    @if(Auth::user()->role_id == 1)

                                                @if($item->cancelation_status == 'submitted')
                                                             <button type="submit" class="btn-success btn-sm rounded f-14 p-2 mr-2 mb-2 mb-lg-0 mb-md-0 approve_milestone" data-row-id="{{ $item->id }}" data-row-id2="{{ $item->comments }}">Approve Cancelation</button>
                                                @elseif($item->cancelation_status == 'approved')
                                                            <i class="fa fa-circle mr-1 text-red f-10"></i>
                                                                            Canceled
                                                @endif


                                    @else
                                            <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                                                                Awaiting Approval
                                                                <br>
                                                                (Milestone Cancelation)
                                    @endif

                            @endif

                    @elseif($item->status == 'canceled')
                                @if($incomplete_milestone == 0 && $qc_count == 0 && $item->id == $last_milestone->id)

                                        @if($item->qc_status == 0)
                                                 <a href="/projects/q&c/{{$project->id}}/{{$item->id}}"  class="btn-success btn-sm rounded f-14 p-2 flex-right">Complete Q&C</a>

                                        @elseif($item->qc_status == 2)
                                                <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                                                        Awaiting Approval
                                                        <br>
                                                        (QC Submission)
                                        @endif
                                @elseif($incomplete_milestone == 0 && $project_completion_count == 0 && $item->id == $last_milestone->id)
                                        @if($item->project_completion_status  == 0)

                                                     <a href="/projects/project-completion/{{$item->id}}"  class="btn-success btn-sm rounded f-14 p-2 flex-right" >Project Completion Form</a>
                                        @elseif($item->project_completion_status  == 2)
                                                        <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                                                        Awaiting Approval
                                                        <br>
                                                        (Project Completion)


                                        @endif

                                @else

                                        <i class="fa fa-circle mr-1 text-red f-10"></i>
                                        Canceled

                                @endif

                                                            {{--  Need to check if milestone is the last milestone then qc and project completion and authorization apply in this section
                                                                --}}



                    @else
                            @if($last_milestone != null && $incomplete_milestone == 0 && $item->id == $last_milestone->id )

                                    @if($item->qc_status == 0)
                                             <a href="/projects/q&c/{{$project->id}}/{{$item->id}}"  class="btn-success btn-sm rounded f-14 p-2 flex-right">Complete Q&C</a>

                                    @elseif($item->qc_status == 2)
                                                        <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                                                                Awaiting Approval
                                                                <br>
                                                                (QC Submission)


                                    @elseif($item->qc_status == 1 && $item->invoice_created == 0)



                                                    <a href="{{route('invoices.create')}}?project_id={{$item->project_id}}&client_id={{$project->client_id}}&milestone_id={{$item->id}}"   class="btn-success btn-sm rounded f-14 p-2 flex-right" id="{{$item->id}}"  data-row-id="{{ $item->id }}">Generate Invoice</a>
                                    @elseif($item->qc_status == 1 && $item->invoice_created == 1 && $item->project_completion_status == 0)
                                                      <a href="/projects/project-completion/{{$item->id}}"  class="btn-success btn-sm rounded f-14 p-2 flex-right" >Project Completion Form</a>


                                    @elseif($item->qc_status == 1 && $item->invoice_created == 1 && $item->project_completion_status == 2)
                                                                <i class="fa fa-circle mr-1 text-yellow f-10"></i>

                                                                            Awaiting Approval
                                                                            <br>
                                                                            (Project Completion)

                                                                            @php


                                                                            @endphp

                                    @elseif($item->qc_status == 1 && $item->invoice_created == 1 && $item->project_completion_status == 1 && $invoice_id->status == 'unpaid')


                                                 <a href="{{route('payments.create')}}?invoice_id={{$item->invoice_id}}&default_client={{$project->client_id}}"  class="btn-warning btn-sm rounded f-14 p-2 flex-right" data-row-id="{{ $item->invoice_id }}">Add Payment</a>

                                    @elseif($invoice_id->status == 'unpaid')

                                                    <a href="{{route('payments.create')}}?invoice_id={{$item->invoice_id}}&default_client={{$project->client_id}}"  class="btn-warning btn-sm rounded f-14 p-2 flex-right" data-row-id="{{ $item->invoice_id }}">Add Payment</a>
                                    @else

                                                     <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                                                Milestone Paid

                                    @endif

                            @else
                                    @if($item->invoice_created == 0)
                                            @if($complete_milestone- $invoice_generated >= 2 && $item->id != $first_milestone->id)
                                                    <button disabled class="btn-success btn-sm rounded f-14 p-2 flex-right">Generate Invoice</a>
                                            @else
                                                        <a href="{{route('invoices.create')}}?project_id={{$item->project_id}}&client_id={{$project->client_id}}&milestone_id={{$item->id}}"  class="btn-success btn-sm rounded f-14 p-2 flex-right" id="{{$item->id}}"  data-row-id="{{ $item->id }}">Generate Invoice</a>

                                            @endif


                                     @elseif($item->invoice_created == 1 )
                                                            @php
                                                            $invoice_id = App\Models\Invoice::where('milestone_id',$item->id)->first();
                                                        //  / dd($invoice_id );
                                                            @endphp
                                            @if($invoice_id->status== 'unpaid')
                                                        <a href="{{route('payments.create')}}?invoice_id={{$item->invoice_id}}&default_client={{$project->client_id}}"  class="btn-warning btn-sm rounded f-14 p-2 flex-right" data-row-id="{{ $item->invoice_id }}">Add Payment</a>
                                            @else

                                                <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                                                Milestone Paid

                                            @endif


                                    @endif


                            @endif


                     @endif

        @endif
    @else

    @if($item->status == 'incomplete')

    @if($item->cancelation_status == null)


             <button type="submit" class="btn-primary btn-sm rounded f-14 p-2 mr-2 mb-2 mb-lg-0 mb-md-0 complete_milestone" data-id="{{ $item->id }}">Mark As Complete ({{$complete_task}}/{{$total_tasks}})</button>
             <button type="submit" class="btn-danger btn-sm rounded f-14 p-2 mr-2 mb-2 mb-lg-0 mb-md-0 cancel_milestone" data-row-id="{{ $item->id }}" >Cancel Milestone</button>
    @else
            @if(Auth::user()->role_id == 1)

                        @if($item->cancelation_status == 'submitted')
                                     <button type="submit" class="btn-success btn-sm rounded f-14 p-2 mr-2 mb-2 mb-lg-0 mb-md-0 approve_milestone" data-row-id="{{ $item->id }}" data-row-id2="{{ $item->comments }}">Approve Cancelation</button>
                        @elseif($item->cancelation_status == 'approved')
                                    <i class="fa fa-circle mr-1 text-red f-10"></i>
                                                    Canceled
                        @endif


            @else
                    <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                                        Awaiting Approval
                                        <br>
                                        (Milestone Cancelation)
            @endif

    @endif

@elseif($item->status == 'canceled')
        @if($incomplete_milestone == 0 && $qc_count == 0 && $item->id == $last_milestone->id)

                @if($item->qc_status == 0)
                         <a href="/projects/q&c/{{$project->id}}/{{$item->id}}"  class="btn-success btn-sm rounded f-14 p-2 flex-right">Complete Q&C</a>

                @elseif($item->qc_status == 2)
                        <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                                Awaiting Approval
                                <br>
                                (QC Submission)
                @endif
        @elseif($incomplete_milestone == 0 && $project_completion_count == 0 && $item->id == $last_milestone->id)
                @if($item->project_completion_status  == 0)

                             <a href="/projects/project-completion/{{$item->id}}"  class="btn-success btn-sm rounded f-14 p-2 flex-right" >Project Completion Form</a>
                @elseif($item->project_completion_status  == 2)
                                <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                                Awaiting Approval
                                <br>
                                (Project Completion)


                @endif

        @else

                <i class="fa fa-circle mr-1 text-red f-10"></i>
                Canceled

        @endif

                                    {{--  Need to check if milestone is the last milestone then qc and project completion and authorization apply in this section
                                        --}}



@else
    @if($last_milestone != null && $incomplete_milestone == 0 && $item->id == $last_milestone->id )

            @if($item->qc_status == 0)
                     <a href="/projects/q&c/{{$project->id}}/{{$item->id}}"  class="btn-success btn-sm rounded f-14 p-2 flex-right">Complete Q&C</a>

            @elseif($item->qc_status == 2)
                                <i class="fa fa-circle mr-1 text-yellow f-10"></i>
                                        Awaiting Approval
                                        <br>
                                        (QC Submission)


            @elseif($item->qc_status == 1 && $item->invoice_created == 0)



                            <a href="{{route('invoices.create')}}?project_id={{$item->project_id}}&client_id={{$project->client_id}}&milestone_id={{$item->id}}"   class="btn-success btn-sm rounded f-14 p-2 flex-right" id="{{$item->id}}"  data-row-id="{{ $item->id }}">Generate Invoice</a>
            @elseif($item->qc_status == 1 && $item->invoice_created == 1 && $item->project_completion_status == 0)
                              <a href="/projects/project-completion/{{$item->id}}"  class="btn-success btn-sm rounded f-14 p-2 flex-right" >Project Completion Form</a>


            @elseif($item->qc_status == 1 && $item->invoice_created == 1 && $item->project_completion_status == 2)
                                        <i class="fa fa-circle mr-1 text-yellow f-10"></i>

                                                    Awaiting Approval
                                                    <br>
                                                    (Project Completion)

                                                    @php


                                                    @endphp

            @elseif($item->qc_status == 1 && $item->invoice_created == 1 && $item->project_completion_status == 1 && $invoice_id->status == 'unpaid')


                         <a href="{{route('payments.create')}}?invoice_id={{$item->invoice_id}}&default_client={{$project->client_id}}"  class="btn-warning btn-sm rounded f-14 p-2 flex-right" data-row-id="{{ $item->invoice_id }}">Add Payment</a>

            @elseif($invoice_id->status == 'unpaid')

                            <a href="{{route('payments.create')}}?invoice_id={{$item->invoice_id}}&default_client={{$project->client_id}}"  class="btn-warning btn-sm rounded f-14 p-2 flex-right" data-row-id="{{ $item->invoice_id }}">Add Payment</a>
            @else

                             <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                        Milestone Paid

            @endif

    @else
            @if($item->invoice_created == 0)
                    @if($complete_milestone- $invoice_generated >= 2 && $item->id != $first_milestone->id)
                            <button disabled class="btn-success btn-sm rounded f-14 p-2 flex-right">Generate Invoice</a>
                    @else
                                <a href="{{route('invoices.create')}}?project_id={{$item->project_id}}&client_id={{$project->client_id}}&milestone_id={{$item->id}}"  class="btn-success btn-sm rounded f-14 p-2 flex-right" id="{{$item->id}}"  data-row-id="{{ $item->id }}">Generate Invoice</a>

                    @endif


             @elseif($item->invoice_created == 1 )
                                    @php
                                    $invoice_id = App\Models\Invoice::where('milestone_id',$item->id)->first();
                                //  / dd($invoice_id );
                                    @endphp
                    @if($invoice_id->status== 'unpaid')
                                <a href="{{route('payments.create')}}?invoice_id={{$item->invoice_id}}&default_client={{$project->client_id}}"  class="btn-warning btn-sm rounded f-14 p-2 flex-right" data-row-id="{{ $item->invoice_id }}">Add Payment</a>
                    @else

                        <i class="fa fa-circle mr-1 text-dark-green f-10"></i>
                        Milestone Paid

                    @endif


            @endif


    @endif


@endif


    @endif
                             </form>


                            </td>
                            <td class="text-right pr-20">
                                <div class="task_view">
                                    <a href="javascript:;" data-milestone-id="{{ $item->id }}"
                                        class="taskView milestone-detail text-darkest-grey f-w-500">@lang('app.view')</a>
                                          @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8)
                                    <div class="dropdown">
                                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle"
                                            type="link" id="dropdownMenuLink-{{ $item->id }}" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-options-vertical icons"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right"
                                            aria-labelledby="dropdownMenuLink-{{ $item->id }}" tabindex="0">


                                                <a class="dropdown-item edit-milestone" href="javascript:;"
                                                    data-row-id="{{ $item->id }}">
                                                    <i class="fa fa-edit mr-2"></i>
                                                    @lang('app.edit')
                                                </a>



                                                <a class="dropdown-item delete-row" href="javascript:;"
                                                    data-row-id="{{ $item->id }}">
                                                    <i class="fa fa-trash mr-2"></i>
                                                    @lang('app.delete')
                                                </a>


                                        </div>
                                    </div>
                                      @endif
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-cards.no-record icon="list" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </x-cards.data>
        @else
        <x-cards.no-record :message="__('messages.noRecordFound')" icon="flag" />
        @endif
    </div>

</div>
<!-- ROW END -->
<input type="hidden" id="project_id"  value="{{$project->id}}">
<input type="hidden" id="client_id"  value="{{$project->client_id}}">

@include('projects.modals.cancel_milestone')
@include('projects.modals.cancel_milestone_approve')
<script type="text/javascript">

    let project_id = document.getElementById('project_id').value;
    let client_id =document.getElementById('client_id').value;





    $('body').on('click', '.create-invoice', function() {
      //id = $(this).attr("id");
        var milestone_id = $(this).data('row-id');

    //  alert(milestone_id);
      var url = `{{ route('invoices.create') }}`;

      string = `?project_id=${project_id}&client_id=${client_id}&milestone_id=${milestone_id}`;
      url += string;
      $(this).prop("disabled", true);
      window.location.href = url;
      // window.open(url);

    });
    $('body').on('click', '.create-payment', function() {
      //id = $(this).attr("id");
        var invoice_id = $(this).data('row-id');
    //  alert(milestone_id);
      var url = `{{ route('payments.create') }}`;

      string = `?invoice_id=${invoice_id}&default_client=${client_id}`;
      url += string;

      window.open(url);

    });

    $('body').on('click', '.create-partial-payment', function() {
      //id = $(this).attr("id");
        var milestone_id = $(this).data('row-id');
       // var milestone_status = $(this).data('row-status');
    //  alert(milestone_id);
      var url = `{{ route('invoices.create') }}`;

      string = `?project_id=${project_id}&client_id=${client_id}&milestone_id=${milestone_id}`;
      url += string;
      $(this).prop("disabled", true);
      window.open(url);

    });





</script>

@if($project->deal->project_type == 'hourly')
<script type="text/javascript">
    $(document).ready(function() {
        $('.complete_milestone').click(function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            var button = $(this); // Store reference to the button
        button.prop('disabled', true); // Disable the button
        button.text('Processing'); // Change the button text
            Swal.fire({
                title: "Will you work more hours on this project after this?",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('create-auto-milestone')}}',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            '_token': '{{ csrf_token() }}',
                            'milestone_id':id,
                            'project_id':{{$project->id}},
                        },
                        success: function(response) {
                            if(response.status==200){
                                swal.fire({
                                    title: 'Success!',
                                    text: 'Milestone completed successfully.',
                                    icon: 'success',
                                }).then(function() {
                                    $(this).closest("form").submit();
                                    window.location.reload();
                                });
                            }
                        },
                        error: function(response) {
                            swal.fire({
                                title: 'Error!',
                                text: 'An error occurred.',
                                icon: 'error',
                            });
                        }
                    });
                }else{


                    $.ajax({
                        url: '{{route('milestone-complete')}}',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            '_token': '{{ csrf_token() }}',
                            'id':id,
                        },
                        success: function(response) {
                            if(response.status==200){
                                swal.fire({
                                    title: 'Success!',
                                    text: 'Milestone Status Updated Successfully',
                                    icon: 'success',
                                }).then(function() {
                                    $(this).closest("form").submit();
                                    window.location.reload();
                                });
                            }
                        },
                        error: function(response) {

                            swal.fire({
                                title: 'Oh No!',
                                text: 'You canceled the operation.',
                                icon: 'error',
                            });

                        }
                    });
                }
            });
        })
    })
</script>


@else
<script type="text/javascript">
     $(document).ready(function() {
        $('.complete_milestone').click(function(e) {
            e.preventDefault();

            Swal.fire({
            title: "Complete milestone",
            text: "Are you sure want to complete milestone?",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirm')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).closest("form").submit();
            }
        });

        })
    })

</script>
@endif
<script type="text/javascript">
  $(document).ready(function() {
      $('.cancel_milestone').click(function(e) {
          e.preventDefault();
          Swal.fire({
          title: "Cancel milestone",
          text: "Are you sure want to cancel the milestone?",
          icon: 'error',
          showCancelButton: true,
          focusConfirm: false,
          confirmButtonText: "@lang('messages.confirm')",
          cancelButtonText: "@lang('app.cancel')",
          customClass: {
              confirmButton: 'btn btn-primary mr-3',
              cancelButton: 'btn btn-secondary'
          },
          showClass: {
              popup: 'swal2-noanimation',
              backdrop: 'swal2-noanimation'
          },
          buttonsStyling: false
      }).then((result) => {
          if (result.isConfirmed) {

          //  / var id = milestone_id;
          var milestone_id = $(this).data('row-id');
          $('#milestoneId').val(milestone_id);

           // alert(milestone_id);
            // /var milestoneID= milestone_id.val();
            $('#cancel-milestone').modal('show');

             // $(this).closest("form").submit();
          }
      });
      })
  })
</script>
<script type="text/javascript">
  $(document).ready(function() {
      $('.approve_milestone').click(function(e) {
          e.preventDefault();

          //  / var id = milestone_id;
          var milestone_id = $(this).data('row-id');
          var comments =  $(this).data('row-id2');

          $('.milestoneId').val(milestone_id);
          $('.commentId').html(comments);

          //$('.milestoneId').val(milestone_id);

           // alert(milestone_id);
            // /var milestoneID= milestone_id.val();
              $('#cancel-milestone-approve').modal('show');

             // $(this).closest("form").submit();


      });
      });

</script>

<script>
    $('#add-project-milestone').click(function() {
        const url = "{{ route('milestones.create') }}" + "?id={{ $project->id }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);

    });

    $('body').on('click', '.edit-milestone', function() {
        var id = $(this).data('row-id');

        var url = "{{ route('milestones.edit', ':id') }}";
        url = url.replace(':id', id);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);

    });

    $('body').on('click', '.milestone-detail', function() {
        var id = $(this).data('milestone-id');
        var url = "{{ route('milestones.show', ':id') }}";
        url = url.replace(':id', id);
        $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_XL, url);
    });


    $('.delete-row').click(function() {

        var id = $(this).data('row-id');
        var url = "{{ route('milestones.destroy', ':id') }}";
        url = url.replace(':id', id);

        var token = "{{ csrf_token() }}";

        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $('#row-' + id).fadeOut();
                        }
                    }
                });
            }
        });

    });
    $('.close-button').click(function() {

      $('#cancel-milestone').modal('hide');
      $('#cancel-milestone-approve').modal('hide');
    });


</script>


<script>

	$(document).ready(function(){

		$thing = $('#demo').book({
			onPageChange:updateProgress,
			speed:200}
		).validate();

		/* IE doesn't have a trunc function */
		if (!Math.trunc) {
			Math.trunc = function (v) {
				return v < 0 ? Math.ceil(v) : Math.floor(v);
			};
		}

		/* Update progress bar whenever the page changes */
		function updateProgress(prevPageIndex, currentPageIndex, pageCount, pageName){
			t = (currentPageIndex / (pageCount-1)) * 100;

			$('.progress-bar').attr('aria-valuenow', t);
			$('.progress-bar').css('width', t+'%');
			// $('.progress span').text('Completed: '+Math.trunc(t)+'%');
			$('.progress-value').text(Math.trunc(t)+'%');

			console.log(pageName);
		}

		/* form's submit button */
		$('#sendForm').on('click', function(e){
			e.preventDefault();

			if ($('#demo').valid()){
				// do ajax thingy here
				alert('Your data was sent.');
			}
		});


	}); // end document ready

</script>

<!-- step alert  -->

<script>
	function myFunction() {
	  var txt;
	  if (confirm("No, I Want To Do IT Later!")) {
		txt = " You pressed Cancel!";
	  }

	  document.getElementById("demo").innerHTML = txt;
	}
  $(document).on('click','#project-qc-form',function(e){


    //console.log(milestone_id);
    $('#ProjectqcrevisionSubmissionModal').modal('show');


  });
</script>



<!-- radio   -->


<script>
	$("input[name='select']").change(function() {
	  var selectedOption = $("input[name='select']:checked").val();
	  $.ajax({
		type: "POST",
		url: "save-to-db.php",
		data: { option: selectedOption },
		success: function(response) {
		  console.log(response);
		}
	  });
	});
</script>


<!-- step  -->

<script>

(function($){
	$.fn.book = function(options){

		var defaults = {
			onPageChange: function(){},
			speed: 400
		};

		var settings = $.extend(defaults, options);


		if (this.length > 1){
			this.each(function(){ $(this).book(options) });
			return this;
		}

		var pageIndex = 0;

		var $this = $(this);

		// The sections need to match the parent (<form>) container's size for animation to look correct
		var pages = $this.children('section').css({width:'100%',height:'100%',position:'relative'});



		// The form will expand to fit the container it's in (unless overridden).
		//this.css({width:'100%', display:'flex', margin:'auto', overflow:'hidden'});



		// Hide all but the first page
		// Add events to next and previous buttons found in the form
		this.initialize = function(){

			pages.hide();
			pages.first('section').show();

			pages.find('.page-next').on('click', this.nextPage);

			pages.find('.page-prev').on('click', this.prevPage);

			return this;
		}


		// Get current page number
		this.getPageIndex = function(){
			return pageIndex;
		}


		// Returns number of pages in this book
		this.getPageCount = function(){
			return pages.length;
		}


		// Set to a specific page
		this.setPage = function(i){

			return changePage(i);
		}




		function changePage(index){

			if (index >= 0 && index < pages.length && index != pageIndex){


				// Only check validation if moving forward. Exit early if validation fails.
				if ((index > pageIndex) && (typeof $this.valid === 'function')){
					if (!$this.valid()){
						return this;
					}
				}

				oldPageIndex = pageIndex;            // retain for callback info
				$currentPage = pages.eq(pageIndex);  // Get currently display page to slide off screen
				$newPage     = pages.eq(index);      // Get target page to slide onto screen
				pageIndex    = index;                // update pageIndex
				pageName     = ($newPage[0].hasAttribute("name")) ? $newPage.attr('name') : null;  // used in callback

				console.log('thingy');
				if (typeof settings.onPageChange == 'function'){
					settings.onPageChange.call(this, oldPageIndex, pageIndex, pages.length, pageName );
				}



				if (index > oldPageIndex){ // move forward

					$currentPage.hide("slide", {direction:"left"}, settings.speed, function(){
						$newPage.show("slide", {direction:"right"}, settings.speed);
					});

				}else{ // move back

					$currentPage.hide("slide", {direction:"right"}, settings.speed, function(){
						$newPage.show("slide", {direction:"left"}, settings.speed);
					});

				}

			}
			return this;
		};



		// Moves forward to the next page, if one is available
		this.nextPage = function(){
			if (pageIndex >= pages.length-1) return this;
			return changePage(pageIndex+1);
		};



		// Moves back to the previous page. If on first page already, does nothing
		this.prevPage = function(){
			if (pageIndex == 0) return this;
			return changePage(pageIndex-1);

		};


		return this.initialize();
	};


}(jQuery));

</script>



