<link rel="stylesheet" href="{{asset('time-picker2/css/bootstrap-material-datetimepicker.css')}}" />
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="https://code.jquery.com/jquery-1.12.3.min.js" integrity="sha256-aaODHAgvwQW1bFOGXMeX+pC4PZIPsvn2h1sArYOhgXQ=" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://momentjs.com/downloads/moment-with-locales.min.js"></script>
<script type="text/javascript" src="{{asset('time-picker2/js/bootstrap-material-datetimepicker.js')}}"></script>
<style media="screen">
    #timerss {
        /* max-width: a; */
        display: flex;
        flex-direction: column;
        margin: 8px auto;
        /* background: aliceblue; */
        align-items: flex-start;
        border-radius: 7px;
        justify-content: center;
        /* padding: 7px; */
    }
</style>
<div class="modal fade" id="dm-dealaddstagemodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Create Won Deal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createDeal">

                <?php
                $date= \Carbon\Carbon::now();
                ?>
                @if($deal->lead_id != null)
                    <input type="hidden" name="lead_id" value="{{$deal->lead_id}}">
                @endif
                <input type="hidden" name="date" value="{{$date}}">
                <input type="hidden" name="id" value="{{$deal->id}}">

                <div class="modal-body">

                    <div class="row">
                        @if(Session::has('error'))
                            <div class="alert alert-danger" role="alert">

                                <div class="alert-body">
                                    {{Session::get('error')}}
                                </div>
                            </div>
                        @endif

                            {{-- <div class="col-md-12">
                                <div class="mt-3">
                                    <label for="client_name" class="form-label"><strong>Client Name <span style="color:red;">*<span></strong>
                                        <svg class="svg-inline--fa fa-question-circle fa-w-16" data-toggle="popover" data-placement="top" data-content="Type Client Name" data-html="true" data-trigger="hover" aria-hidden="true" focusable="false" data-prefix="fa" data-icon="question-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="" data-original-title="" title="">
                                            <path fill="currentColor" d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zM262.655 90c-54.497 0-89.255 22.957-116.549 63.758-3.536 5.286-2.353 12.415 2.715 16.258l34.699 26.31c5.205 3.947 12.621 3.008 16.665-2.122 17.864-22.658 30.113-35.797 57.303-35.797 20.429 0 45.698 13.148 45.698 32.958 0 14.976-12.363 22.667-32.534 33.976C247.128 238.528 216 254.941 216 296v4c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12v-1.333c0-28.462 83.186-29.647 83.186-106.667 0-58.002-60.165-102-116.531-102zM256 338c-25.365 0-46 20.635-46 46 0 25.364 20.635 46 46 46s46-20.636 46-46c0-25.365-20.635-46-46-46z"></path>
                                        </svg>
                                    </label>
                                    <input name="client_name" id="client_name" type="text" class="form-control height-35 f-14" placeholder="Enter Client Name">
                                    <label id="clientNameError" class="error text-danger" for="client_name"></label>
                                </div>
                            </div> --}}

                        <div class="col-md-6">
                            <div class="mt-3">
                                <label for="deal_id" class="form-label"><strong>Deal ID <span style="color:red;">*<span></strong>
                                    <svg class="svg-inline--fa fa-question-circle fa-w-16" data-toggle="popover" data-placement="top" data-content="Type Client Name" data-html="true" data-trigger="hover" aria-hidden="true" focusable="false" data-prefix="fa" data-icon="question-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="" data-original-title="" title="">
                                        <path fill="currentColor" d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zM262.655 90c-54.497 0-89.255 22.957-116.549 63.758-3.536 5.286-2.353 12.415 2.715 16.258l34.699 26.31c5.205 3.947 12.621 3.008 16.665-2.122 17.864-22.658 30.113-35.797 57.303-35.797 20.429 0 45.698 13.148 45.698 32.958 0 14.976-12.363 22.667-32.534 33.976C247.128 238.528 216 254.941 216 296v4c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12v-1.333c0-28.462 83.186-29.647 83.186-106.667 0-58.002-60.165-102-116.531-102zM256 338c-25.365 0-46 20.635-46 46 0 25.364 20.635 46 46 46s46-20.636 46-46c0-25.365-20.635-46-46-46z"></path>
                                    </svg>
                                </label>
                                <input name="deal_id" value="{{$deal->short_code}}" readonly id="deal_id" type="text" class="form-control height-35 f-14" placeholder="Enter Client Name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($deal->client_name != null)
                                <div class="mt-3">
                                    <label for="client_name" class="form-label"><strong>Client Name <span style="color:red;">*<span></strong>
                                        <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Client Name" data-html="true" data-trigger="hover"></i>
                                    </label>
                                    <input name="client_name" readonly value="{{$deal->client_name}}"   id="client_name" type="text" class="form-control height-35 f-14" placeholder="Enter Client Name">
                                    <label id="clientNameError" class="text-danger" for=""></label>
                                </div>
                            @else
                                <div class="mt-3">
                                    <label for="client_name" class="form-label"><strong>Client Name <span style="color:red;">*<span></strong>
                                        <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Client Name" data-html="true" data-trigger="hover"></i>
                                    </label>
                                    <input name="client_name"  id="clientName" type="text" value="{{old('client_name')}}" class="form-control height-35 f-14 " placeholder="Enter Client Name">
                                    <label id="clientNameError" class="text-danger" for=""></label>
                                </div>
                            @endif
                        </div>


                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-3">
                                <label for="user_name" class="form-label"><strong>Client Username <span style="color:red;">*<span></strong>
                                    <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Client Username" data-html="true" data-trigger="hover"></i>
                                </label>
                                <input name="user_name" id="user_name" readonly value="{{$deal->client_username}}" type="text" class="form-control height-35 f-14 @error('user_name') is-invalid @enderror" placeholder="Enter Client Username" >

                            </div>
                            @error('user_name')
                            <div class="mt-3">
                                <div class="alert alert-danger">{{ $message }}</div>
                            </div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="mt-3">
                                <label for="project_name" class="form-label"><strong>Project Name <span style="color:red;">*<span></strong>
                                    <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Project Name" data-html="true" data-trigger="hover"></i>
                                </label>
                                <input name="project_name" value="{{$deal->project_name}}" readonly id="project_name" type="text" class="form-control height-35 f-14" placeholder="Enter Project Name" required>

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            @if($deal->profile_link != null)
                                <div class="mt-3">
                                    <label for="profile_link" class="form-label"><strong>Profile Link </strong>
                                        <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Profile Link" data-html="true" data-trigger="hover"></i>
                                    </label>
                                    <input name="profile_link" value="{{$deal->profile_link}}" readonly id="profile_link_1" type="text" class="form-control height-35 f-14" placeholder="Enter Client Profile Link" required>

                                </div>
                            @else
                                <div class="mt-3">
                                    <label for="profile_link" class="form-label"><strong>Profile Link</strong>
                                        <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Profile Link" data-html="true" data-trigger="hover"></i>
                                    </label>
                                    <input name="profile_link"  id="profile_link_2" type="text" class="form-control height-35 f-14" placeholder="Enter Client Profile Link" >

                                </div>

                            @endif
                        </div>
                        <div class="col-md-12">
                            @if($deal->message_link != null)
                                <?php
                                $mystring = $deal->message_link;

                                $output = str_replace('<br>',' ', $mystring);

                                $output_final= (trim($output));
                                $data= explode("  ", $output_final);
                                //  dd(($data));

                                ?>
                                @foreach($data as $message)
                                    <div class="mt-3">
                                        <label for="message_link" class="form-label"><strong>Message Link</strong>
                                            <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Message Link" data-html="true" data-trigger="hover"></i>
                                        </label>
                                        <input name="message_link[]" value="{{$message}}" readonly id="message_link" type="text" class="form-control height-35 f-14" placeholder="Enter Project Name" required>

                                    </div>

                                @endforeach



                            @else
                                <div class="mt-3">
                                    <label for="message_link" class="form-label"><strong>Message Link</strong>
                                        <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Message Link" data-html="true" data-trigger="hover"></i>
                                    </label>
                                    <input name="message_link"  id="message_link" type="text" class="form-control height-35 f-14" placeholder="Enter Message Thread Link" required>

                                </div>

                                {{--
                                                                <label class="mt-3" for="Client Username"><strong>Client Message Thread Link</strong></label>
                                                                <div class="col-md-12 dynamic-field" id="dynamic-field-1">

                                                                           <div class="row">
                                                                               <div class="col-md-12 my-2">
                                                                                   <div class="form-group">
                                                                                       <input type="text" id="message_link"  class="form-control height-35 f-14" placeholder="Add Link Here" name="message_link[]" required/>
                                                                                   </div>
                                                                               </div>
                                                                           </div>
                                                                       </div> --}}

                                {{-- <div class="col-md-3 my-2 form-group append-buttons">
                                    <div class="clearfix">
                                        <button type="button" id="add-button" class="btn btn-secondary2 float-left text-uppercase shadow-sm"><i class="fa fa-plus fa-fw"></i></button>
                                        <button type="button" id="remove-button" class="btn btn-secondary2 float-left text-uppercase ml-1" disabled="disabled"><i class="fa fa-minus fa-fw"></i></button>
                                    </div>
                                </div> --}}

                            @endif
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="amount" class="form-label"><strong>Project Type <span style="color:red;">*<span></strong>
                            <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Project Budget" data-html="true" data-trigger="hover"></i>
                        </label>
                        <div class="d-flex py-3 align-items-center justify-content-center">
                            <div class="form-check">
                                <input class="form-control border rounded p-2 h-50 f-14 error" type="radio" name="deal_type" value="fixed" id="deal_type_fixed" @if($deal->project_type == null) checked @endif @if($deal->project_type == 'fixed') checked @endif>
                                <label class="form-check-label" for="deal_type_fixed">
                                    Fixed Project
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-control border rounded p-2 h-50 f-14 error" type="radio" name="deal_type" value="hourly" id="deal_type_hourly" @if($deal->project_type == 'hourly') checked @endif>
                                <label class="form-check-label" for="deal_type_hourly">
                                    Hourly Project
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="amount" class="form-label"><strong>Project Budget <span style="color:red;">*<span></strong>
                            <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Project Budget" data-html="true" data-trigger="hover"></i>
                        </label>
                        <input name="amount" value="{{$deal->actual_amount}}" id="project_amount" min="1" type="number" class="form-control height-35 f-14" placeholder="Enter Amount">
                        <label id="amountError" class="text-danger" for=""></label>
                    </div>
                    <div class="mt-3">
                        <?php
                        $currency_active= App\Models\Currency::where('id',$deal->original_currency_id)->first();
                        $currencies= App\Models\Currency::all();
                        ?>
                        <label for="original_currency_id" class="form-label"><strong>Currency <span style="color:red;">*<span></strong>
                            <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Currency" data-html="true" data-trigger="hover"></i>
                        </label>
                        <select class="form-control height-35 f-14 form-select mb-3" aria-label=".form-select-lg example" name="original_currency_id" id="original_currency_id">
                            <option selected value="{{$currency_active->id}}">({{$currency_active->currency_code}})</option>
                            @foreach ($currencies as $currency)
                                <option value="{{$currency->id}}">({{$currency->currency_code}})</option>
                            @endforeach


                        </select>
                    </div>

                    <div class="mt-3" id="timerss">
                        <h2><strong>Project Award Time <span style="color:red;">*</span></strong>
                            <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Project Award Time" data-html="true" data-trigger="hover"></i>
                        </h2>

                        <input type="text" id="dm-date-format" name="award_time" value="{{old('award_time')}}" class="form-control height-35 f-14 floating-label" placeholder="Select Exact Award Time">
                        <label id="awardTimeError" class="text-danger" for=""></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="dmWonDealBtn">Create Won Deal</button>

                </div>
            </form>
        </div>
    </div>
</div>
@if (count($errors) > 0 || Session::has('error') )
    <script>
        $( document ).ready(function() {
            $('#dealaddstagemodal').modal('show');
        });
    </script>
@endif
<script>
    const projectTypeRadio = document.getElementsByName('deal_type');
    const amountInput = document.getElementById('project_amount');

    for (let i = 0; i < projectTypeRadio.length; i++) {
        projectTypeRadio[i].addEventListener('change', function() {
            if (this.value === 'hourly') {
                amountInput.readOnly = true;
                amountInput.value = '0';
            } else {
                amountInput.readOnly = false;
                amountInput.value = '';
            }
        });
    }
</script>
<script type="text/javascript">
    $(document).ready(function()
    {
        $('#date').bootstrapMaterialDatePicker
        ({
            time: false,
            clearButton: true
        });

        $('#time').bootstrapMaterialDatePicker
        ({
            date: false,
            shortTime: false,
            format: 'HH:mm'
        });

        $('#dm-date-format').bootstrapMaterialDatePicker
        ({
            format: 'DD-MM-YYYY HH:mm:ss'
        });
        $('#date-fr').bootstrapMaterialDatePicker
        ({
            format: 'DD/MM/YYYY HH:mm',
            lang: 'fr',
            weekStart: 1,
            cancelText : 'ANNULER',
            nowButton : true,
            switchOnClick : true
        });

        $('#date-end').bootstrapMaterialDatePicker
        ({
            weekStart: 0, format: 'DD/MM/YYYY HH:mm'
        });
        $('#date-start').bootstrapMaterialDatePicker
        ({
            weekStart: 0, format: 'DD/MM/YYYY HH:mm', shortTime : true
        }).on('change', function(e, date)
        {
            $('#date-end').bootstrapMaterialDatePicker('setMinDate', date);
        });

        $('#min-date').bootstrapMaterialDatePicker({ format : 'DD/MM/YYYY HH:mm', minDate : new Date() });

        $.material.init()
    });

    $(document).ready(function() {
    $('#dm-date-format').bootstrapMaterialDatePicker({
        format: 'DD-MM-YYYY HH:mm:ss'
    });
});

</script>
<script>
    $('#dmWonDealBtn').click(function(e){
        e.preventDefault();
        var project_type = $('input[name="deal_type"]:checked').val();
        $('#dmWonDealBtn').attr("disabled", true);
        $('#dmWonDealBtn').html("Processing...");
        var data= {
            '_token': "{{ csrf_token() }}",
            'deal_id': document.getElementById("deal_id").value,
            @if($deal->client_name != null)
            'client_name': document.getElementById("client_name").value,
            @else
            'client_name': document.getElementById("clientName").value,
            @endif
            'user_name': document.getElementById("user_name").value,
            'project_name': document.getElementById("project_name").value,
            @if($deal->profile_link != null)
            'profile_link': document.getElementById("profile_link_1").value,
            @else
            'profile_link': document.getElementById("profile_link_2").value,
            @endif
            'message_link[]': document.getElementById("message_link").value,
            'amount': document.getElementById("project_amount").value,
            'original_currency_id': document.getElementById("original_currency_id").value,
            'award_time': document.getElementById("dm-date-format").value,
            'project_type': project_type,
            @if($deal->lead_id != null)
            'lead_id': {{$deal->lead_id}},
            @endif
            'date': '{{$date}}',
            'id': {{$deal->id}},
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: "{{route('digital-marketing-store-deals-stage')}}",
            data: data,
            dataType: "json",
            success: function (response) {
                $('#createDeal').trigger("reset");
                if (response.status == 'success') {
                    window.location.href = response.redirectUrl;
                }
                $('#storeWonDeal').attr("disabled", false);
                $('#storeWonDeal').html("Create Won Deal");

            },
            error: function(error) {
                if(error.responseJSON.errors.client_name){
                    $('#clientNameError').text(error.responseJSON.errors.client_name);
                }else{
                    $('#clientNameError').text('');
                }
                if(error.responseJSON.errors.amount){
                    $('#amountError').text(error.responseJSON.errors.amount);
                }else{
                    $('#amountError').text('');
                }
                if(error.responseJSON.errors.award_time){
                    $('#awardTimeError').text(error.responseJSON.errors.award_time);
                }else{
                    $('#awardTimeError').text('');
                    $('#dmWonDealBtn').attr("disabled", false);
                    $('#dmWonDealBtn').html("Create Won Deal");
                }
                $('#dmWonDealBtn').attr("disabled", false);
                $('#dmWonDealBtn').html("Create Won Deal");
            }
        });
    });

</script>

<script>
    $(document).ready(function () {
        var buttonAdd = $("#add-button");
        var buttonRemove = $("#remove-button");
        var className = ".dynamic-field";
        var count = 0;
        var field = "";
        var maxFields = 50;

        function totalFields() {
            return $(className).length;
        }

        function addNewField() {
            count = totalFields() + 1;
            field = $("#dynamic-field-1").clone();
            field.attr("id", "dynamic-field-" + count);
            field.children("label").text("Field " + count);
            field.find("input").val("");
            $(className + ":last").after($(field));
        }

        function removeLastField() {
            if (totalFields() > 1) {
                $(className + ":last").remove();
            }
        }

        function enableButtonRemove() {
            if (totalFields() === 2) {
                buttonRemove.removeAttr("disabled");
                buttonRemove.addClass("shadow-sm");
            }
        }

        function disableButtonRemove() {
            if (totalFields() === 1) {
                buttonRemove.attr("disabled", "disabled");
                buttonRemove.removeClass("shadow-sm");
            }
        }

        function disableButtonAdd() {
            if (totalFields() === maxFields) {
                buttonAdd.attr("disabled", "disabled");
                buttonAdd.removeClass("shadow-sm");
            }
        }

        function enableButtonAdd() {
            if (totalFields() === maxFields - 1) {
                buttonAdd.removeAttr("disabled");
                buttonAdd.addClass("shadow-sm");
            }
        }

        buttonAdd.click(function () {
            addNewField();
            enableButtonRemove();
            disableButtonAdd();
        });

        buttonRemove.click(function () {
            removeLastField();
            disableButtonRemove();
            enableButtonAdd();
        });
    });
</script>








