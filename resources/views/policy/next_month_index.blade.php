@extends('layouts.app')

@section('content')
    <!-- SETTINGS START -->
    <style>
        .settings-box {

            margin-left: 0px;
        }
    </style>
    <style>
        .point__row_wrapper_container{
            display: flex;
            align-items: flex-end;
            -webkit-align-items: flex-end;
            -webkit-flex-wrap: wrap;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            gap: 10px;
        }
        .point__row_wrapper {
            display: flex;
            -webkit-flex-direction: column;
            -ms-flex-direction: column;
            flex-direction: column;
            gap: 16px;
            position: relative;
            width: -moz-fit-content;
            width: fit-content;
        }

        .point__distribution{
            display: flex;
            -webkit-flex-direction: column;
            -ms-flex-direction: column;
            -webkit-flex-wrap: wrap;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            gap: 16px;
        }
        .point__row{
            width: fit-content;
            display: flex;
            align-items: center;
            -webkit-flex-wrap: wrap;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            gap: 10px;
            padding: 16px;
            border: 1px solid #f3f3f3;
            border-radius: 6px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, .03);
        }

        .point__col{
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .point__col > input,
        .point__col > select{
            padding: 1px 10px;
            width: 80px;
            border: 1px solid rgba(0, 0, 0, .1);
            border-radius: 4px;
        }
        .point__col > select {
            width:-moz-fit-content;
            width: fit-content;
            padding: 3px 10px;
            background: transparent;
        }
        .point__input{
            background-color: #e9ecef;
        }

        .__point_field_add_btn_group{
            margin-bottom: 15px;
        }

        .form_height {
            height: 38px !important;
        }
    </style>


    <x-setting-card>
        @if(Auth::user()->role_id == 1)
        <x-slot name="header">
            <div class="s-b-n-header row mx-0" id="tabs">
                <a href="{{route('nextMonthPolicy')}}" class="btn btn-success my-3 ml-3"><i class="fa fa-plus mr-1" aria-hidden="true"></i>Add Next Month Policy</a>
                <div class="align-self-center col-12 col-sm-3">
                    <div class="form-group mb-0">
                        <select class="form-control form_height" name="next_month_kpi" id="next_month_kpi">
                            <option value="">Select month</option>
                            @foreach($next_month_kpi as $value)
                            <option value="{{$value->id}}">
                                @php
                                    $date = \Carbon\Carbon::parse($value->start_month);
                                @endphp
                                {{$date->format('F')}}  ({{$date->format('Y')}})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <hr>
{{--                <h2 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">--}}
{{--                    @lang($pageTitle)</h2>--}}
            </div>
        </x-slot>
        @endif
        <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
            <h3 class="text-center  border-1 shadow-sm mx-auto p-3 rounded text-uppercase" style="width: fit-content;">Base Point Distribution Policy</h3>
            <br>
            <form id="save-kpi-settings" action="" method="PUT">
                @csrf
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">1. The Bidder will get:</label>
                    <div class="col-sm-8 d-flex">
                        <input class="form-control height-35 f-14" type="number" name="the_bidder" id="the_bidder"  value="{{$kpi->the_bidder}}" class="form-control"  placeholder="Percentage of points for bidder" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">2. Sales executive who qualifies the deal:</label>
                    <div class="col-sm-8 d-flex">
                        <input class="form-control height-35 f-14" type="number" name="qualify" id="qualify" value="{{$kpi->qualify}}" class="form-control"  placeholder="Percentage of points for qualified deals" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">3. Sales executive who defined requirements of the deal will get:</label>
                    <div class="col-sm-8 d-flex">
                        <input class="form-control height-35 f-14" type="number" class="form-control" name="requirements_defined" id="requirements_defined" value="{{$kpi->requirements_defined}}"  placeholder="Percentage of points for requirements defined deals" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">4. If anyone helps sales executive for less than:</label>
                    <div class="col-sm-2">
                        <input class="form-control height-35 f-14 mt-2" type="number" class="form-control" name="less_than" id="less_than" value="{{$kpi->less_than}}"  placeholder="Percentage of points for requirements defined deals" readonly>
                    </div>
                    <span class="mt-3">Minutes</span>
                    <label for="inputPassword" class="col-sm-2 col-form-label mt-2">then he/she will get:</label>
                    <div class="col-sm-2 d-flex">
                        <input class="form-control height-35 f-14 mt-2" type="number" name="less_than_get" id="less_than_get" value="{{$kpi->less_than_get}}" class="form-control"  placeholder="Percentage of points for requirements defined deals" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">5. If anyone helps sales executive for more than:</label>
                    <div class="col-sm-2">
                        <input class="form-control height-35 f-14 mt-2" type="number" class="form-control" name="more_than" id="more_than"  value="{{$kpi->more_than}}"  placeholder="Percentage of points for requirements defined deals" readonly>
                    </div>
                    <span class="mt-3">Minutes</span>
                    <label for="inputPassword" class="col-sm-2 col-form-label mt-2">then he/she will get:</label>
                    <div class="col-sm-2 d-flex">
                        <input class="form-control height-35 f-14 mt-2" type="number" class="form-control" name="more_than_get" id="more_than_get" value="{{$kpi->more_than_get}}"  placeholder="Percentage of points for requirements defined deals" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">6. Sales executive who made the proposal of the deal will get:</label>
                    <div class="col-sm-8 d-flex">
                        <input class="form-control height-35 f-14" type="number" name="proposal_made" id="proposal_made" value="{{$kpi->proposal_made}}" class="form-control"  placeholder="Percentage of points for requirements defined deals" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">7. Sales executive who started the negotiation of the deal will get:</label>
                    <div class="col-sm-8 d-flex">
                        <input class="form-control height-35 f-14" type="number" class="form-control" name="negotiation_started" id="negotiation_started" value="{{$kpi->negotiation_started}}"  placeholder="Percentage of points for requirements defined deals" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">8. Sales executive who shared the milestone breakdown of the deal with the client will get:</label>
                    <div class="col-sm-8 d-flex">
                        <input class="form-control height-35 f-14" type="number" class="form-control" name="milestone_breakdown" id="milestone_breakdown" value="{{$kpi->milestone_breakdown}}"  placeholder="Percentage of points for requirements defined deals" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">9. Sales executive who closed (won) the deal will get:</label>
                    <div class="col-sm-8 d-flex">
                        <input class="form-control height-35 f-14" type="number" class="form-control" name="closed_deal" id="closed_deal" value="{{$kpi->closed_deal}}"  placeholder="Percentage of points for requirements defined deals" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">10. Sales executive who shared the contact form with the client and filled out the form for
                        project manager will get:</label>
                    <div class="col-sm-8 d-flex">
                        <input class="form-control height-35 f-14" type="number" name="contact_form" id="contact_form" value="{{$kpi->contact_form}}" class="form-control"  placeholder="Percentage of points for requirements defined deals" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="inputPassword" class="col-sm-4 col-form-label">11. Team Lead will get if he authorize the deal:</label>
                    <div class="col-sm-8 d-flex">
                        <input class="form-control height-35 f-14" type="number" name="authorized_by_leader" id="authorized_by_leader" value="{{$kpi->authorized_by_leader}}" class="form-control"  placeholder="Percentage of points for requirements defined deals" readonly>
                        <label class="mt-2 mx-1">%</label>
                    </div>
                </div>
                <hr>
                <hr>
                <h3 class="text-center mt-1 mb-5 border-1 shadow-sm mx-auto p-3 rounded text-uppercase" style="width: fit-content;">Point distribution for won deal policy</h3>
                <section class="point__distribution">
                    <div class="point__row">
                        <div class="point__col"> For every sales that gets accepted by project manager, respective sales shift will get </div>
                        <div class="point__col"> <input type="number" class="point__input" name="accepted_by_pm" id="accepted_by_pm" value="{{$kpi->accepted_by_pm}}" readonly> </div>
                        <div class="point__col"> %  </div>
                    </div>
                    @foreach($kpi->logged_hours as $value)
                    <div class="point__row_wrapper_container">
                        <div class="point__row dynamicMore-field" id="dynamicMore-field-1">
                            <div class="point__col"> If the hourly rate of the project based on logged hours between </div>
                            <div class="point__col"> $<input type="number" class="point__input" name="logged_hours_between" id="logged_hours_between" value="{{$value->logged_hours_between}}" readonly>  </div>
                            <div class="point__col"> To</div>
                            <div class="point__col"> $<input type="number" class="point__input" name="logged_hours_between_to" id="logged_hours_between" value="{{$value->logged_hours_between_to}}" readonly> </div>
                            <div class="point__col"> shift will get</div>
                            <div class="point__col"> <input type="number" class="point__input" name="logged_hours_sales_amount" id="logged_hours_sales_amount"  value="{{$value->logged_hours_sales_amount}}" readonly> % </div>
                            <div class="point__col"> of the sales amount.</div>
                        </div>
                    </div>
                    @endforeach
                    <div class="point__row">
                        <div class="point__col"> If the hourly rate of the project based on logged hours above </div>
                        <div class="point__col"> $<input type="number" class="point__input" name="logged_hours_above" id="logged_hours_above" value="{{$kpi->logged_hours_above}}" readonly> , </div>
                        <div class="point__col"> shift will get </div>
                        <div class="point__col"> <input type="number" class="point__input" name="logged_hours_above_sales_amount" id="logged_hours_above_sales_amount" value="{{$kpi->logged_hours_above_sales_amount}}" readonly> % </div>
                        <div class="point__col"> of the sales amount. </div>
                    </div>
                    <div class="point__row">
                        <div class="point__col"> To achieve more than </div>
                        <div class="point__col"> <input type="number" class="point__input" name="achieve_more_than" id="achieve_more_than" value="{{$kpi->achieve_more_than}}" readonly> %  </div>
                        <div class="point__col"> points, </div>
                        <div class="point__col"> Minimum project value cannot be less than </div>
                        <div class="point__col"> $<input type="number" class="point__input" name="achieve_less_than" id="achieve_less_than" value="{{$kpi->achieve_less_than}}" readonly> </div>
                    </div>
                    @foreach($kpi->generate_sales as $value)
                    <div class="point__row_wrapper_container">
                        <div class="point__row dynamic-field" id="dynamic-field-1">
                            <div class="point__col"> If sales team generates sales from </div>
                            <div class="point__col"> <input type="number" class="point__input" name="generate_sales_from" id="generate_sales_from" value="{{$value->generate_sales_from}}" readonly>  </div>
                            <div class="point__col"> To</div>
                            <div class="point__col"> <input type="number" class="point__input" name="generate_sales_to" id="generate_sales_to" value="{{$value->generate_sales_to}}" readonly>  </div>
                            <div class="point__col"> per month,</div>
                            <div class="point__col"> team lead will get </div>
                            <div class="point__col"> <input type="number" class="point__input" name="generate_sales_amount" id="generate_sales_amount"  value="{{$value->generate_sales_amount}}" readonly> % </div>
                            <div class="point__col"> points of the sales amount.</div>
                        </div>
                    </div>
                    @endforeach
                    <div class="point__row">
                        <div class="point__col"> If sales team generates sales above </div>
                        <div class="point__col"> $<input type="number" class="point__input" name="generate_sales_above" id="generate_sales_above" value="{{$kpi->generate_sales_above}}" readonly>  </div>
                        <div class="point__col"> per month, </div>
                        <div class="point__col"> team lead will get </div>
                        <div class="point__col"> <input type="number" class="point__input" name="generate_sales_above_point" id="generate_sales_above_point" value="{{$kpi->generate_sales_above_point}}" readonly> % </div>
                        <div class="point__col">  points.</div>
                    </div>
                    <div class="point__row">
                        <div class="point__col"> If a sales shift generate any project equal/more than</div>

                        <div class="point__col"> $<input type="number" class="point__input" name="generate_single_deal" id="generate_single_deal" value="{{$kpi->generate_single_deal}}" readonly>  </div>
                        <div class="point__col"> on single deal, that shift will get a flat</div>
                        <div class="point__col"> <input type="number" class="point__input" name="bonus_point" id="bonus_point" value="{{$kpi->bonus_point}}" readonly>  </div>
                        <div class="point__col"> bonus points.</div>
                    </div>
                    <div class="point__row">
                        <div class="point__col">For every</div>
                        <div class="point__col"> $<input type="number" class="point__input" name="additional_sales_amount" id="additional_sales_amount" value="{{$kpi->additional_sales_amount}}" readonly>  </div>
                        <div class="point__col">addition sales</div>
                        <div class="point__col">
                            <select class="point__select" name="client_type" id="client_type">
                                <option value="new_client" class="point__option" {{$kpi->client_type=='new_client'? 'selected':''}} disabled>New client</option>
                                <option value="existing_client" class="point__option" {{$kpi->client_type=='existing_client'? 'selected':''}} disabled>Existing Client</option>
                                <option value="both" class="point__option" {{$kpi->client_type=='both'? 'selected':''}} disabled>Both</option>
                            </select>
                        </div>
                        <div class="point__col">after</div>
                        <div class="point__col"> $<input type="number" class="point__input" name="after" id="after" value="{{$kpi->after}}" readonly>  </div>
                        <div class="point__col"> milestone per month, </div>
                        <div class="point__col"> shift will get </div>
                        <div class="point__col"> <input type="number" class="point__input" name="after_reach_amount" id="after_reach_amount" value="{{$kpi->after_reach_amount}}" readonly>  </div>
                        <div class="point__col"> points. </div>
                    </div>
                </section>
                <hr>
                <hr>
               
                <h3 class="text-center  border-1 shadow-sm mx-auto p-3 rounded text-uppercase" style="width: fit-content;">Incentive Settings Policy</h3>
                <section class="point__distribution">
                    <div class="point__row">
                        <div class="point__col">1. For every shift, every point above </div>
                        <div class="point__col"> <input type="number" class="point__input" name="every_shift_every_point_above" id="every_shift_every_point_above" value="{{$next_month_incentive->every_shift_every_point_above}}" readonly> </div>
                        <div class="point__col"> points will count towards incentive.</div>
                    </div>
                    
                    <div class="point__row">
                        <div class="point__col">2. If the team does not meet the minimum goal, shifts that meet their individual goals will receive</div>
                        <div class="point__col"> <input type="number" class="point__input" name="individual_goal_percentage" id="individual_goal_percentage" value="{{$next_month_incentive->individual_goal_percentage}}" readonly> % </div>
                        <div class="point__col">  of their actual incentive.</div>
                    </div>
                    <div class="point__row">
                        <div class="point__col">3. Each point will have a value of BDT</div>
                        <div class="point__col"> <input type="number" class="point__input" name="point_of_value" id="point_of_value" value="{{$next_month_incentive->point_of_value}}" readonly> </div>
                        <div class="point__col">  TK.</div>
                    </div>
                    <h3 class="text-center mt-2 mb-5 border-1 shadow-sm mx-auto p-3 rounded text-uppercase" style="width: fit-content;">Logical Settings for Incentive Policy</h3>
                    <div class="point__row">
                        <div class="point__col">1. For missing any 10 days goals, the sales shift will get</div>
                        <div class="point__col"> <input type="number" class="point__input" name="incentive_deduction" id="incentive_deduction" value="{{$next_month_incentive->incentive_deduction}}" readonly> </div>
                        <div class="point__col">% less from the total incentive amount.</div>
                    </div>
                </section>
                <x-slot name="action">
                    <!-- Buttons Start -->
                    <div class="w-100 border-top-grey">
                        <x-setting-form-actions>
                            <x-forms.button-cancel :link="url()->previous()" class="border-0">@lang('app.cancel')
                            </x-forms.button-cancel>
                            </x-settingsform-actions>
                    </div>
                    <!-- Buttons End -->
                </x-slot>
            </form>
    </x-setting-card>

    <!-- SETTINGS END -->
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('#next_month_kpi').change(function() {
            var selectedId = $(this).val();
            if (selectedId) {
                var redirectUrl = '{{route("show_month_policy", '')}}/' + selectedId;
                window.location.href = redirectUrl;
            }
        });
    })
</script>
@endpush
