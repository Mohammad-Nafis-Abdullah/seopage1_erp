@extends('layouts.app')
@push('styles')
<style type="text/css">
	.sp1_incentive_pdf_container{
		width: 100%;
		max-width: 1400px;
		padding: 48px;
		position: relative;
	}

	@media screen and (max-width: 557px){
		.sp1_incentive_pdf_container{
			padding: 16px;
			height: 100%;
		}
	}

	.sp1_styled-table{
		width: 100%;
	}


	.sp1_styled-table thead th{
		background: #009879;
		color: #fff;
		padding: 6px 12px;
		/* white-space: nowrap; */
	}

	.sp1_styled-table tbody td{
		color: #555;
		padding: 12px 12px;
		/* white-space: nowrap; */
	}

	.sp1_styled_tabel_container{
		max-width: 100%;
		overflow-x: auto;
		padding-bottom: 16px;
	}

	.sp1_styled-table {
		border-bottom: 2px solid #009879;
	}

	.sp1_inc_pdf_dl_btn{
		background: #009879;
		color: #fff;
		padding: 6px 12px;
		position: absolute;
		top: 58px;
		right: 48px;
		border-radius: 4px;
	}

	.sp1_inc_pdf_dl_btn > span{
		color: inherit;
	}

	.sp1_inc_pdf_dl_btn:hover{
		background: #008369;
		color: #fff !important;
	}
</style>
@endpush
@section('content')


@php
    $data = $user_incentive;
@endphp

    <section>
        <div class="d-flex align-items-center justify-content-center py-5">

            <div class="sp1_incentive_pdf_container bg-white" >
                        <a href="/account/monthly-incentive" class="btn-primary rounded p-2">
                            <i class="fa-regular fa-hand-point-left"></i>
                            <span class="d-none d-sm-inline">Go Back</span>
                        </a>
                    <div class="">
                        <img src="{{ invoice_setting()->logo_url }}" alt="{{ mb_ucwords(global_setting()->company_name) }}"
                            class="logo pt-4" style="height:70px;"
                        />
                        <a href="{{ route('monthly-incentive.download', $user_incentive->id) }}" aria-label="download" class="sp1_inc_pdf_dl_btn">

                            <i class="fa-solid fa-download"></i>
                            <span class="d-none d-sm-inline">Download</span>
                        </a>
                    </div>

                <ul class="mt-3">
                    <li class="mb-1"> {{ mb_ucwords(global_setting()->company_name) }} </li>
                    <li class="mb-1"> {!! nl2br(default_address()->address) !!} </li>
                    <li class="mb-1"> {{ global_setting()->company_phone }} </li>
                </ul>

                <ul class="mt-3">
                    <li class="mb-2"><span class="f-14 font-weight-bold text-secondary">@lang('app.employee')</span></li>
                    <li>{{$data->user->name}}</li>
                    <li>{{$data->user->email}}</li>
                    <li>{{$data->user->getRole->name}}</li>
                </ul>

                <ul class="mt-3">
                    <li class="mb-2"><span class="f-14 font-weight-bold text-secondary">@lang('app.subject')</span></li>
                    <li>Incentive Disbursment</li>
                </ul>

                <ul class="mt-4">
                    <li class="mb-2"><span class="f-14 font-weight-bold text-secondary">Project Deliverables</span></li>
                </ul>

                <div class="sp1_styled_tabel_container">
                    <table class="sp1_styled-table">
                        <thead>
                            <tr>

                                <th>Month</th>

                                <th>Non Incentive Points</th>


                                <th>User's Achieved Point</th>
                                {{-- <th>Incentive Deduction</th> --}}
                                <th>Amount Before Deduction</th>
                                <th>User's Deducted Point</th>
                                <th>User's Point's After Deduction</th>
                                <th>Amount After Deduction</th>
                                <th>Deducted Incentive Amount</th>
                                <th>Incentive Amount After 20% Held</th>
                                <th>Incentive Held Amount</th>
                                <th>Final Payable Incentive Amount</th>
                                {{-- <th>Total Goal</th>
                                <th>Achieved Goal</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            <tr>

                                <td>{{\Carbon\Carbon::parse($user_incentive->month)->format('M (Y)')}}</td>

                                    <td>
                                        @php
                                            $non_incentive_point = App\Models\IncentiveSetting::where('start_month', $user_incentive->month)->first();
                                        @endphp
                                        {{$non_incentive_point->every_shift_every_point_above}}
                                    </td>


                                    <td>{{$user_incentive->user_achieved_points}}</td>
                                    <td>{{$user_incentive->amount_before_deduction}}</td>

                                    <td>{{$user_incentive->user_deducted_points}}</td>
                                    <td>{{$user_incentive->user_point_after_deduction}}</td>
                                    <td>{{$user_incentive->amount_after_deduction}} BDT</td>
                                    <td>{{$user_incentive->deducted_incentive_amount}} BDT</td>
                                    <td>{{$user_incentive->incentive_amount_after_20_percent_held}} BDT</td>
                                    <td>{{$user_incentive->incentive_held_amount}} BDT</td>
                                    <td>{{$user_incentive->final_payable_incentive_amount}} BDT</td>


                                {{-- <td>Total Goal</td>
                                <td>Achieved Goal</td> --}}

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

@endsection
