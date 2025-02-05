@extends('layouts.app')
@push('datatable-styles')
    @include('sections.daterange_css')
@endpush
@push('styles')
    @if (!is_null($viewEventPermission) && $viewEventPermission != 'none')
        <link rel="stylesheet" href="{{ asset('vendor/full-calendar/main.min.css') }}">
    @endif
    <style>
        .h-200 {
            max-height: 340px;
            overflow-y: auto;
        }

        .dashboard-settings {
            width: 600px;
        }

        @media (max-width: 768px) {
            .dashboard-settings {
                width: 300px;
            }
        }

        .fc-list-event-graphic{
            display: none;
        }

        .fc .fc-list-event:hover td{
            background-color: #fff !important;
            color:#000 !important;
        }
        .left-3{
            margin-right: -22px;
        }
        .clockin-right{
            margin-right: -10px;
        }

        .week-pagination li {
            margin-right: 5px;
            z-index: 1;
        }
        .week-pagination li a {
            border-radius: 50%;
            padding: 2px 6px !important;
            font-size: 11px !important;
        }

        .week-pagination li.page-item:first-child .page-link {
            border-top-left-radius: 50%;
            border-bottom-left-radius: 50%;
        }

        .week-pagination li.page-item:last-child .page-link {
            border-top-right-radius: 50%;
            border-bottom-right-radius: 50%;
        }
        .hide-calender .table-condensed thead tr:nth-child(2),
        .hide-calender .table-condensed tbody {
/*            display: none*/
        }
        .hide-calender.daterangepicker {
            width: 320px;
        }
        .hide-calender.monthselect {
            width: 100% !important;
        }
    </style>
@endpush
@section('content')
@php
    $employ_details = \App\Models\EmployeeDetails::where('user_id',$lead_dev->id)->first();
    $designation = \App\Models\Designation::where('id',$employ_details->designation_id)->first();
@endphp
<div class="px-4 py-2 border-top-0">
    <!-- WELOCOME START -->
    @if (!is_null($checkTodayLeave))
        <div class="row pt-4">
            <div class="col-md-12">
                <x-alert type="info" icon="info-circle">
                    <a href="{{ route('leaves.show', $checkTodayLeave->id) }}" class="openRightModal text-dark-grey">
                        <u>@lang('messages.youAreOnLeave')</u>
                    </a>
                </x-alert>
            </div>
        </div>
    @elseif (!is_null($checkTodayHoliday))
        <div class="row pt-4">
            <div class="col-md-12">
                <x-alert type="info" icon="info-circle">
                    <a href="{{ route('holidays.show', $checkTodayHoliday->id) }}" class="openRightModal text-dark-grey">
                        <u>@lang('messages.holidayToday')</u>
                    </a>
                </x-alert>
            </div>
        </div>
    @endif

    <div class="d-lg-flex d-md-flex d-block py-4">
        <!-- WELOCOME NAME START -->
        <div>
			{{-- {{dd($lead_dev)}} --}}
            <h4 class=" mb-0 f-21 text-capitalize font-weight-bold">@lang('app.welcome')
                {{ $lead_dev->name }}</h4>
        </div>
        <!-- WELOCOME NAME END -->

        <!-- CLOCK IN CLOCK OUT START -->
        @if(Auth::user()->role_id == 4 || Auth::user()->role_id == 7)
        <div class="align-items-center border-left-grey border-left-grey-sm-0 h-100 pl-4 ml-5">
            <div class="col-auto">
                <label class="sr-only" for="inlineFormInputGroup"></label>
                <div class="input-group mb-2">
                    <div class="input-group-prepend">
                        <div class="input-group-text">  <i class="fa fa-calendar-alt mr-2 f-14 text-dark-grey"></i></div>
                    </div>
                    <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500" id="datatableRange2" placeholder="Start Date And End Date">
               </div>
            </div>
        </div>
        @endif
        <!-- CLOCK IN CLOCK OUT END -->
    </div>
    <div class="emp-dash-detail">
    	@if(count(array_intersect(['profile', 'shift_schedule', 'birthday', 'notices'], $activeWidgets)) > 0)
	    	@if(Auth::user()->role_id != 4 && Auth::user()->role_id != 7)
	    		<div class="row">
	    			@if (in_array('profile', $activeWidgets))
	    			<!-- EMP DASHBOARD INFO START -->
	    			<div class="col-md-12">
	    				<div class="card border-0 b-shadow-4 mb-3 e-d-info">
	    					<div class="card-horizontal align-items-center">
	    						<div class="card-img">
									@if ($lead_dev->image)
                                        <img class="" src="{{ asset('user-uploads/avatar/'.$lead_dev->image) }}" alt="Card image">
                                    @else
                                        <img class="" src="{{ asset('user-uploads/avatar/avatar_blank.png') }}" alt="Card image">
                                    @endif
	    						</div>
	    						<div class="card-body border-0 pl-0">
	    							<h4 class="card-title f-18 f-w-500 mb-0">{{ mb_ucwords($lead_dev->name) }}</h4>
	    							<p class="f-14 font-weight-normal text-dark-grey mb-2">{{ $designation->name }}</p>
	    							<p class="card-text f-12 text-lightest"> @lang('app.employeeId') : {{ mb_strtoupper($employ_details->employee_id) }}</p>
	    						</div>
	    					</div>
	    				</div>
	    			</div>
	    			<!-- EMP DASHBOARD INFO END -->
	    			@endif
	    		</div>
    		@endif
		@endif
    </div>
	<div class="select-box d-flex border-right-grey border-right-grey-sm-0 mb-3 ml-auto">
        <p class="mb-0 pr-3 f-14 text-dark-grey d-flex align-items-center">@lang('app.date')</p>
        <div class="select-status d-flex">
            <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500"
                id="datatableRange2" placeholder="@lang('placeholders.dateRange')">
        </div>
    </div>
    
	<div id="generalHtml">
		<div class="row">
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Number of tasks received</h5>
							   <div class="d-flex flex-wrap">
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#number_of_task_received{{$number_of_tasks_received_lead}}">
										   {{$number_of_tasks_received_lead}}
									   </a>
									   {{-- <span class="f-12 font-weight-normal text-lightest">
										   @lang('Received tasks this cycle')
										   <i class="fa fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#"></i>
										
									   </span> --}}
								   </p>
								   @include('dashboard.ajax.leaddeveloper.modals.number_of_task_received')
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Number of submitted tasks</h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#submit_number_of_tasks_in_this_month_lead{{$submit_number_of_tasks_in_this_month_lead}}">
										
	   
									   {{$submit_number_of_tasks_in_this_month_lead}}
										 
									   </a>
									   {{-- <span class="f-12 font-weight-normal text-lightest">
										   @lang('Primary pages')
										   <i class="fa fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#"></i>
										
									   </span> --}}
								   </p>
								   @include('dashboard.ajax.leaddeveloper.modals.number_of_task_submitted')
								  
	   
								  
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Number of approved tasks on 1st attempt by Project Manager</h5>
							   <div class="d-flex flex-wrap">
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#">
										  {{$first_attempt_approve_task_in_this_month_lead}}
									   </a>
									 
								   </p>
							   
	   
								 
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				  
			   </div>
			   <div class="row mt-3">
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Number of approved tasks on 1st attempt by Client</h5>
							   <div class="d-flex flex-wrap">
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#num_of_approved_task_attempt_by_client{{count($first_attempt_approve_task_data_client_lead)}}">
										   {{$first_attempt_approve_task_in_this_month_client_lead}}
									   </a>
									 
								   </p>
								   @include('dashboard.ajax.leaddeveloper.modals.num_of_approved_task_attempt_by_client')			   
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Avg number of attempts needed for approval by Project Manager</h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#">
										
	   
										   {{round($average_submission_aproval_in_this_month_lead,2)}}
										 
									   </a>
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Avg number of attempts needed for approval by Client</h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#avg_num_of_approval_by_client{{count($first_attempt_approve_task_data_client_lead)}}">
										
	   
										   {{round($average_submission_aproval_in_this_month_client_lead,2)}}
										 
									   </a>
									   @include('dashboard.ajax.leaddeveloper.modals.avg_num_of_approval_by_client')			   
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				 
				  
	   
			   </div>
			   <div class="row mt-3">
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Percentage of tasks with revisions</h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#percentage_of_task_with_revision{{count($revision_data_lead)}}">
										
	   
										   {{round($percentage_of_tasks_with_revision_lead,2)}}%
										 
									   </a>
									   @include('dashboard.ajax.leaddeveloper.modals.percentage_of_task_with_revision')			   
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">
								   Total number of revisions
								   </h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#total_num_of_revision{{count($revision_data_lead)}}">
										
	   
										   {{round($number_of_total_revision_for_this_month_lead,2)}}
										 
									   </a>
									   @include('dashboard.ajax.leaddeveloper.modals.total_num_of_revision')			   
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				  
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Average task submission time (In days)</h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#avg_task_submission_in_day{{count($average_submit_data_lead)}}">
										
	   
										   {{round($average_submission_day_in_this_month_lead,2)}} Days
										 
									   </a>
									   @include('dashboard.ajax.leaddeveloper.modals.avg_task_submission_in_day')			   
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				 
				   
	   
			   </div>
			   <div class="row mt-3">
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Average number of in-progress tasks</h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#avg_num_in_progress_task{{count($total_in_progress_date_range_table_lead)}}">
										
	   
										   {{round($average_in_progress_date_range_lead ,2)}}
										 
									   </a>
									   @include('dashboard.ajax.leaddeveloper.modals.avg_num_in_progress_task')			   
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">
								   Percentage of tasks where deadline was missed 
								   </h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#percentage_of_tasks_deadline_missed{{count($estimate_missed_task_data_lead)}}">
										
	   
										   {{round($percentage_of_tasks_deadline_lead,2)}}%
										 
									   </a>
									   @include('dashboard.ajax.leaddeveloper.modals.percentage_of_tasks_deadline_missed')			   
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Percentage of tasks where given estimated time was missed</h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#percentage_task_estimate_time_missed{{count($estimate_missed_task_data_lead)}}">
										
										   {{round($percentage_number_task_cross_estimate_time_lead,2)}}%
										 
									   </a>
									   @include('dashboard.ajax.leaddeveloper.modals.percentage_task_estimate_time_missed')	
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				 
				 
	   
			   </div>
			   <div class="row mt-3">
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Rejection rate</h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#">
										
	   
									   0%
										 
									   </a>
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">
								   Cancelation rate 
								   </h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#">
										
	   
									   0%
										 
									   </a>
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Rate of reassign</h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#">
										
	   
									   0
										 
									   </a>
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				  
				  
	   
			   </div>
			   <div class="row mt-3">
				   <div class="col-md-5">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Number of disputes filed</h5>
							   <div class="d-flex flex-wrap">
	   
								   <h6 class="mb-0 f-18 font-weight-bold mr-5">
									   No. of disputes filed:
									   <a href="#" data-toggle="modal" data-target="#">
										
	   
										 {{$number_of_dispute_filed_own_lead}} 
										 
										 
									   </a>
									  
								   </h6>
								   <h6 class="mb-0 f-18 font-weight-bold mr-5">
									   No. of disputes (Overall):
									   <a href="#" data-toggle="modal" data-target="#no_of_dispute_overall{{count($number_of_dispute_filed_all_data_lead)}}">
										
	   
										   {{$number_of_dispute_filed_all_lead}}
										 
										 
									   </a>
									   @include('dashboard.ajax.leaddeveloper.modals.no_of_dispute_overall')			   
									  
								   </h6>
								 
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				   <div class="col-md-7">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">
								   Number of disputes lost
								   </h5>
							   <div class="d-flex flex-wrap">
	   
								   <h6 class="mb-0 f-18 font-weight-bold mr-5">
									   No. of disputes lost(Raised By Developer):
									   <a href="#" data-toggle="modal" data-target="#no_of_dispute_lost{{count($number_of_dispute_lost_own_data_lead )}}">
										
	   
											{{ $number_of_dispute_lost_own_lead}}
		   
									   </a>
									   @include('dashboard.ajax.leaddeveloper.modals.no_of_dispute_lost')			   
									  
								   </h6>
								   <h6 class="mb-0 f-18 font-weight-bold mr-5">
									   No. of disputes lost(Overall):
									   <a href="#" data-toggle="modal" data-target="#no_of_dispute_lost_overall{{count($number_of_dispute_lost_all_data_lead)}}">
										
	   
											{{ $number_of_dispute_lost_all_lead}}
										 
									   </a>

									   @include('dashboard.ajax.leaddeveloper.modals.no_of_dispute_lost_overall')			   
									  
								   </h6>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
				  
	   
			   </div>
			   <div class="row mt-3">
				   <div class="col-md-4">
					   <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mb-4 mb-md-0 mb-lg-0">
						   <div class="d-block text-capitalize">
							   <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Hours spent in revisions</h5>
							   <div class="d-flex flex-wrap">
	   
								   <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
									   <a href="#" data-toggle="modal" data-target="#hours_spent_in_revision{{count($spent_revision_developer_data_lead)}}">
										
										   {{round($spent_revision_developer_lead,2)}} Hours
										 
									   </a>
									   @include('dashboard.ajax.leaddeveloper.modals.hours_spent_in_revision')			   
									  
								   </p>
	   
								 
							   </div>
						   </div>
						   <div class="d-block">
							   <i class="fa fa-list text-lightest f-27"></i>
						   </div>
					   </div>
				   </div>
	   
			   </div>

			   <div class="row my-5">
				
				<div class="col-sm-6 col-lg-6 p-3">
					<x-cards.data :title="__('')" padding="false" otherClasses="h-200">
						<x-table class="border-0 pb-3 admin-dash-table table-hover mt-3">
							<x-slot name="thead">
								<th class="pl-20">#</th>
								<th>Creation Date</th>
								<th>Task Name</th>
								<th>Client Name</th>
								<th>Submittion date</th>
								<th>Current Status</th>
								
							
							</x-slot>
			
							@forelse($tasks as $task)
							
							<tr>
								<td class="pl-20">{{$loop->index+1}}</td>
								<td>
								   {{$task->created_at}}
								</td>
								<td>
									<a href="{{route('tasks.show',$task->id)}}"> {{$task->heading}}</a>
								
								</td>
								<td>
									@if($task->ProjectId != null)
	
									<a href="{{route('clients.show',$task->client_id)}}">{{$task->clientName}}</a>
									@elseif($task->task_client_name != null)
									{{$task->task_client_name}}
									@else 
									{{$task->cl_name}}
	
									@endif
									
								</td>
			
								<td>
									@if($task->board_column_id == 2 || $task->board_column_id == 1 || $task->board_column_id == 3)
									N\A 
									@else 
									{{$task->submission_date}}
								  
									@endif
									
								</td>
								<td>
									<span class="task-status" data-task-id="{{ $task->id }}" style="color: {{ $task->label_color }}; cursor:pointer">
										{{ $task->column_name }}
									</span>
		
	
								   
									
								
								</td>
								
								
								
							  
							</tr>
							@empty
							
							<tr>
								<td colspan="10" class="shadow-none">
									<x-cards.no-record icon="list" :message="__('messages.noRecordFound')" />
								</td>
							</tr>
							@endforelse
							
						</x-table>
					</x-cards.data>
				</div>
		
		
				<div class="col-sm-6 col-lg-6 p-3">
					<x-cards.data :title="__('')" padding="false" otherClasses="h-200">
						<x-table class="border-0 pb-3 admin-dash-table table-hover mt-3">
							<x-slot name="thead">
								<th class="pl-20">#</th>
								<th>Creation Date</th>
								<th>task Name</th>
								<th>Client Name</th>
								<th>Current Status</th>
								
						
							</x-slot>
			
							
							
							@forelse($past_tasks as $task)
								
							<tr>
								<td class="pl-20">{{$loop->index+1}}</td>
								<td>
								   {{$task->created_at}}
								</td>
								<td>
									<a href="{{route('tasks.show',$task->id)}}"> {{$task->heading}}</a>
								
								</td>
								<td>
									@if($task->ProjectId != null)
		
									<a href="{{route('clients.show',$task->client_id)}}">{{$task->clientName}}</a>
									@elseif($task->task_client_name != null)
									{{$task->task_client_name}}
									@else 
									{{$task->cl_name}}
		
									@endif
									
								</td>
			
							  
								<td>
									
									<span class="task-status" data-task-id="{{ $task->id }}" style="color: {{ $task->label_color }}; cursor:pointer">
										{{ $task->column_name }}
									</span>
		
								   
									
								
								</td>
								
								
								
							  
							</tr>
							@empty
							
							<tr>
								<td colspan="10" class="shadow-none">
									<x-cards.no-record icon="list" :message="__('messages.noRecordFound')" />
								</td>
							</tr>
							@endforelse
							
						</x-table>
					</x-cards.data>
				</div>
			  
	   </div>
</div>
@endsection
@push('scripts')
@if (!is_null($viewEventPermission) && $viewEventPermission != 'none')
    <script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('vendor/full-calendar/main.min.js') }}"></script>
    <script src="{{ asset('vendor/full-calendar/locales-all.min.js') }}"></script>
    <script>

        var initialLocaleCode = '{{ user()->locale }}';
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: initialLocaleCode,
            timeZone: '{{ global_setting()->timezone }}',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            navLinks: true, // can click day/week names to navigate views
            selectable: false,
            initialView: 'listWeek',
            selectMirror: true,
            select: function(arg) {
                addEventModal(arg.start, arg.end, arg.allDay);
                calendar.unselect()
            },
            eventClick: function(arg) {
                getEventDetail(arg.event.id,arg.event.extendedProps.event_type);
            },
            editable: false,
            dayMaxEvents: true, // allow "more" link when too many events
            events: {
                url: "{{ route('dashboard.private_calendar') }}",
            },
            eventDidMount: function(info) {
                    $(info.el).css('background-color', info.event.extendedProps.bg_color);
                    $(info.el).css('color', info.event.extendedProps.color);
                    $(info.el).find('td.fc-list-event-title').prepend('<i class="fa '+info.event.extendedProps.icon+'"></i>&nbsp;&nbsp;');
                    // tooltip for leaves
                    if(info.event.extendedProps.event_type == 'leave'){
                        $(info.el).find('td.fc-list-event-title > a').css('cursor','default'); // list view cursor for leave
                        $(info.el).css('cursor','default')
                        $(info.el).tooltip({
                            title: info.event.extendedProps.name,
                            container: 'body',
                            delay: { "show": 50, "hide": 50 }
                        });
                }
            },
            eventTimeFormat: { // like '14:30:00'
                hour: global_setting.time_format == 'H:i' ? '2-digit' : 'numeric',
                minute: '2-digit',
                meridiem: global_setting.time_format == 'H:i' ? false : true
            }
        });

        calendar.render();

        // Task Detail show in sidebar
        var getEventDetail = function(id,type) {
            if(type == 'ticket')
            {
                var url = "{{ route('tickets.show', ':id') }}";
                    url = url.replace(':id', id);
                    window.location = url;
                    return true;
            }

            if(type == 'leave')
            {
                return true;
            }

            openTaskDetail();

            switch (type) {
                case 'task':
                    var url = "{{ route('tasks.show', ':id') }}";
                    break;
                case 'event':
                    var url = "{{ route('events.show', ':id') }}";
                    break;
                case 'holiday':
                    var url = "{{ route('holidays.show', ':id') }}";
                    break;
                case 'leave':
                    var url = "{{ route('leaves.show', ':id') }}";
                    break;
                default:
                    return 0;
                    break;
            }

            url = url.replace(':id', id);

            $.easyAjax({
                url: url,
                blockUI: true,
                container: RIGHT_MODAL,
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        $(RIGHT_MODAL_CONTENT).html(response.html);
                        $(RIGHT_MODAL_TITLE).html(response.title);
                    }
                },
                error: function(request, status, error) {
                    if (request.status == 403) {
                        $(RIGHT_MODAL_CONTENT).html(
                            '<div class="align-content-between d-flex justify-content-center mt-105 f-21">403 | Permission Denied</div>'
                        );
                    } else if (request.status == 404) {
                        $(RIGHT_MODAL_CONTENT).html(
                            '<div class="align-content-between d-flex justify-content-center mt-105 f-21">404 | Not Found</div>'
                        );
                    } else if (request.status == 500) {
                        $(RIGHT_MODAL_CONTENT).html(
                            '<div class="align-content-between d-flex justify-content-center mt-105 f-21">500 | Something Went Wrong</div>'
                        );
                    }
                }
            });

        };

        // calendar filter
        var hideDropdown = false;

        $('#event-btn').click(function(){
            if(hideDropdown == true)
            {
                $('#cal-drop').hide();
                hideDropdown = false;
            }
            else
            {
                $('#cal-drop').toggle();
                hideDropdown = true;
            }
        });


        $(document).mouseup(e => {

            const $menu = $('.calendar-action');

            if (!$menu.is(e.target) && $menu.has(e.target).length === 0)
            {
                hideDropdown = false;
                $('#cal-drop').hide();
            }
        });


        $('.cal-filter').on('click', function() {

            var filter = [];

            $('.filter-check:checked').each(function() {
                filter.push($(this).val());
            });

            if(filter.length < 1){
                filter.push('None');
            }

            calendar.removeAllEventSources();
            calendar.addEventSource({
                url: "{{ route('dashboard.private_calendar') }}",
                extraParams: {
                    filter: filter
                }
            });

            filter = null;
        });
    </script>
    <script>
        $(document).ready(function() {
            var todayDate = moment();
            var monthDate = moment();
            
            $('.todayDate').text(todayDate.format('dddd LL'));

            var todayOnlyDate = moment(todayDate).format('DD');
            if (todayOnlyDate > 21) {
                $('.monthDate').text('21st ' + moment(monthDate).format('MMMM, YYYY')+' to 20th '+moment(monthDate).add(1, 'month').format('MMMM, YYYY'));
            } else {
                $('.monthDate').text('21st ' + moment(monthDate).subtract(1, 'month').format('MMMM, YYYY')+' to 20th '+moment(monthDate).startOf('month').add(20, 'day').format('MMMM, YYYY'));
            }

            $('.fc-prev-button').click(function() {
                var mode = $(this).attr('date-mode');
                if (mode == 'month') {
                    if(todayOnlyDate > 21) {
                        monthDate = moment(monthDate).subtract(1, 'month');
                    } else {
                        monthDate = moment(monthDate).subtract(2, 'month');
                    }
                    $(this).next().text('21st ' + moment(monthDate).format('MMMM, YYYY')+ ' to 20th '+moment(monthDate).add(1, 'month').format('MMMM, YYYY'));
                    date = monthDate
                } else {
                    todayDate = moment(todayDate).subtract(1, 'days');
                    $(this).next().text(todayDate.format('dddd LL'));
                    date = todayDate
                }

                getData(mode, $(this), date);
            });

            $('.fc-next-button').click(function() {
                var mode = $(this).attr('date-mode');
                if (mode == 'month') {
                    monthDate = moment(monthDate).add(1, 'month');
                    $(this).prev().text('21st ' + moment(monthDate).format('MMMM, YYYY')+' to 20th '+moment(monthDate).add(1, 'month').format('MMMM, YYYY'));
                    date = monthDate
                } else {
                    todayDate = moment(todayDate).add(1, 'days');
                    $(this).prev().prev().text(todayDate.format('dddd LL'));
                    date = todayDate
                }
                
                getData(mode, $(this), date);
            });

            $('.fc-today-button').click(function() {
                todayDate = moment();
            });  
        })

        function getData(mode, disableButton, date) {
            $.easyAjax({
                url: this.href,
                type: "GET",
                disableButton: true,
                buttonSelector: disableButton,
                data: {
                    mode: mode,
                    startDate: date.format('YYYY-MM-DD'),
                },
                success: function(resp) {
                    $('#'+mode+'Html').html(resp.html);
                }
            })
        }

        @php
            $startDate = \Carbon\Carbon::now()->startOfMonth();
            $endDate = \Carbon\Carbon::now();
        @endphp
        $(function() {
            var format = '{{ global_setting()->moment_format }}';
            var startDate = "{{ $startDate->format(global_setting()->date_format) }}";
            var endDate = "{{ $endDate->format(global_setting()->date_format) }}";
            var picker = $('#datatableRange2');
            var start = moment(startDate, format);
            var end = moment(endDate, format);

            function cb(start, end) {
                $('#datatableRange2').val(start.format('{{ global_setting()->moment_date_format }}') +
                    ' @lang("app.to") ' + end.format( '{{ global_setting()->moment_date_format }}'));
                $('#reset-filters').removeClass('d-none');
            }

            $('#datatableRange2').daterangepicker({
                locale: daterangeLocale,
                linkedCalendars: false,
                startDate: start,
                endDate: end,
                ranges: daterangeConfig,
                opens: 'left',
                parentEl: '.dashboard-header'
            }, cb);

            $('#datatableRange2').on('apply.daterangepicker', function(ev, picker) {
                showTable();
            });

            function showTable() {
                var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                var startDate = $('#datatableRange').val();
				var user_id = {{Auth::user()->id}};
                if (startDate == '') {
                    startDate = null;
                    endDate = null;
                } else {
                    startDate = dateRangePicker.startDate.format('{{ global_setting()->moment_date_format }}');
                    endDate = dateRangePicker.endDate.format('{{ global_setting()->moment_date_format }}');
                }

                const requestUrl = this.href;


                $.easyAjax({
                    url: requestUrl,
                    blockUI: true,
                    data: {
                        startDate: startDate,
                        endDate: endDate,
						user_id: user_id,
                    },
                    blockUI: true,
                    success: function(resp) {
                        if (resp.status == "success") {
                            $('#generalHtml').html(resp.html)
                        }
                    }
                });
            }
        });
		$(document).ready(function () {
        // Enable Bootstrap popovers
        $('[data-toggle="popover"]').popover();

        // Handle click on task status element
        $('.task-status').on('click', function () {
            var taskId = $(this).data('task-id');
            var url = '{{ route('task_history_dashboard', ':taskId') }}'.replace(':taskId', taskId);

            // Reference 'this' to use inside the AJAX success function
            var self = $(this);

            // Make an AJAX request to fetch task history data
            $.ajax({
                url: url,
                type: 'GET',
                success: function (data) {
                console.log(data);
                    // Format the task history data for display in the popover
                    var popoverContent = '<ul >';
                    $.each(data, function (index, history) {
                        
                        // popoverContent += '<li class="history_color">' + history.column_name + ' (' + history?.created_on + ')</li>';
                        popoverContent += `<li> ${history.column_name} (${history.created_on}) </li>`;
                     

                    });
                    popoverContent += '</ul>';

                    // Open a Bootstrap popover and display the task history data
                    self.popover({
                        content: popoverContent,
                        html: true,
                        title: 'Task History',
                        placement: 'auto',
                        trigger: 'manual' // Set trigger to 'manual' to control popover manually
                    }).popover('show');
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching task history:', error);
                }
            });
        });
    });
    </script>
@endif
@endpush

