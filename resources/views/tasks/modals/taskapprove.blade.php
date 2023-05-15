
<style media="screen">
.rating, .rating2, .rating3 {
    /* float:left; */
    display:flex;
    flex-direction: row-reverse;
    font-size:10px;
    margin-right:auto;
  }

  /* :not(:checked) is a filter, so that browsers that don’t support :checked don’t
    follow these rules. Every browser that supports :checked also supports :not(), so
    it doesn’t make the test unnecessarily selective */
  .rating:not(:checked) > input ,.rating2:not(:checked) > input, .rating3:not(:checked) > input {
      position:absolute;
      top:-9999px;
      clip:rect(0,0,0,0);
  }

  .rating:not(:checked) > label ,  .rating2:not(:checked) > label ,  .rating3:not(:checked) > label{
      float:right;
      width:1em;
      /* padding:0 .1em; */
      overflow:hidden;
      white-space:nowrap;
      cursor:pointer;
      font-size:300%;
      /* line-height:1.2; */
      color:#ddd;
  }

  .rating:not(:checked) > label:before, .rating2:not(:checked) > label:before, .rating3:not(:checked) > label:before {
      content: '★ ';

  }

  .rating > input:checked ~ label, .rating2 > input:checked ~ label, .rating3 > input:checked ~ label {
      color: orange;

  }

  .rating:not(:checked) > label:hover,.rating2:not(:checked) > label:hover,.rating3:not(:checked) > label:hover,
  .rating:not(:checked) > label:hover ~ label, .rating2:not(:checked) > label:hover ~ label , .rating3:not(:checked) > label:hover ~ label{
      color: orange;

  }

  .rating > input:checked + label:hover,
  .rating > input:checked + label:hover ~ label,
  .rating > input:checked ~ label:hover,
  .rating > input:checked ~ label:hover ~ label,
  .rating > label:hover ~ input:checked ~ label,
  .rating2 > input:checked + label:hover,
  .rating2 > input:checked + label:hover ~ label,
  .rating2 > input:checked ~ label:hover,
  .rating2 > input:checked ~ label:hover ~ label,
  .rating2 > label:hover ~ input:checked ~ label,
  .rating3 > input:checked + label:hover,
  .rating3 > input:checked + label:hover ~ label,
  .rating3 > input:checked ~ label:hover,
  .rating3 > input:checked ~ label:hover ~ label,
  .rating3 > label:hover ~ input:checked ~ label

   {
      color: orange;

  }

  .rating > label:active, .rating2 > label:active {
      position:relative;
      top:2px;
      left:2px;
  }

</style>
<!------ Include the above in your HEAD tag ---------->
<?php
$task_submission= App\Models\TaskSubmission::where('task_id',$task->id)->orderBy('submission_no','desc')->get();

 ?>
<div class="modal fade" id="taskapprove" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Approve Task</h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form  action="{{route('task-status-approve')}}" method="post">
        @csrf
        <input type="hidden" name="task_id" value="{{$task->id}}">
          <input type="hidden" name="user_id" value="{{$task->last_updated_by}}">




        <div class="modal-body">
            <p>
                <a class="btn btn-primary" data-toggle="collapse" href="#oldSubmission" role="button" aria-expanded="false" aria-controls="oldSubmission">
                    @php
                        if($task_submission != null) {
                            $latestObject = $task_submission->first();

                            // Remove the latest object from the collection
                            $objects = $task_submission->reject(function ($object) use ($latestObject) {
                                return $object->id === $latestObject->id;
                            });

                            $taskSubmissions = json_decode($task_submission, true);

                            // Assuming you have the "task_submissions" array in $taskSubmissions

                            $groupedSubmissions = collect($taskSubmissions)->groupBy(function ($submission) {
                                return $submission['submission_no'] . '_' . $submission['task_id'];
                            })->map(function ($group) {
                                return $group;
                            })->toArray();

                            $newArray = [];

                            function mergeArrays($arr1, $arr2) {
                                $merged = [];

                                foreach ($arr1 as $key => $value) {
                                    if ($value !== null) {
                                        $merged[$key] = $value;
                                    } elseif (isset($arr2[$key])) {
                                        $merged[$key] = $arr2[$key];
                                    }
                                }

                                foreach ($arr2 as $key => $value) {
                                    if ($value !== null && !isset($merged[$key])) {
                                        $merged[$key] = $value;
                                    }
                                }

                                return $merged;
                            }

                            foreach($groupedSubmissions as $key => $value) {
                                if(count($value) > 1) {
                                    if(!is_null($value[0]) && !is_null($value[1])) {
                                        $newArr = mergeArrays($value[0], $value[1]);
                                        array_push($newArray, $newArr);
                                    }
                                } else {
                                    array_push($newArray, $value[0]);
                                }
                            }

                            $newArray = json_encode($newArray);
                            $newArray = json_decode($newArray);
                        }  
                    @endphp
                    Old Submission ({{count($newArray)}})
                </a>
            </p>
            <div class="collapse" id="oldSubmission">
                <div class="card card-body p-2">
                    @if($newArray != null)
                        @php $counter = 1; @endphp
                        @foreach($newArray as $submission)
                            <div class="row mx-0 bg-amt-grey p-2 rounded">
                                <h5 class="align-self-center mb-0">Submission {{$counter++}}</h5>
                                <span class="ml-auto f-14">
                                    {{\Carbon\Carbon::parse($submission->created_at)->format('Y-m-d')}}<br>
                                    {{\Carbon\Carbon::parse($submission->created_at)->format('H:i A')}}
                                </span>
                            </div>
                            
                            @if($submission->link != null)
                                <div class="mb-3 p-2">
                                    <a class="text-lowercase" href="{{$submission->link}}" target="_blank">{{$submission->link}}</a>
                                </div>
                            @endif
                            @if($submission->text != null)
                                <div class="mb-3 text-lowercase p-2">
                                    {!! $submission->text !!}
                                </div>
                            @endif
                            @if(isset($submission->attach))
                                <div class="mb-3 p-2">
                                    <p class="card-text">
                                        <a class="text-dark-grey" style="font-weight:bold;" target="_blank" href="{{asset('storage/TaskSubmission/'.$submission->attach)}}"><i class="fa-solid fa-link"></i> {{$submission->attach}}</a>
                                    </p>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        <hr>
        @php
            $latestObject = \App\Models\TaskSubmission::where([
                'task_id' => $latestObject->task_id,
                'submission_no' => $latestObject->submission_no
            ])->get()->toArray();

            $latestObject = json_encode(mergeArrays($latestObject[0], $latestObject[1]));
            $latestObject = json_decode($latestObject);
        @endphp
        <div class="card card-body p-2 mb-3">
            <h5 class="bg-amt-grey p-2 rounded">Latest Submission <span class="float-right">{{\Carbon\Carbon::parse($latestObject->created_at)->format('Y-m-d')}}</span></h5>
            @if($latestObject->link != null)
                <div class="mb-3 m-2">
                    <a class="text-lowercase" href="{{$latestObject->link}}" target="_blank">  {{$latestObject->link}}</a>

                </div>
            @endif
            @if($latestObject->text != null)
                <div class="mb-3 m-2 text-lowercase">
                    {!! $latestObject->text !!}
                </div>
            @endif
            @if(isset($latestObject->attach))
                <div class="mb-3 m-2">
                    <p class="card-text">  <a class="text-dark-grey" style="font-weight:bold;" target="_blank" href="{{asset('storage/TaskSubmission/'.$latestObject->attach)}}"><i class="fa-solid fa-link"></i> {{$latestObject->attach}}</a></p>
                </div>
            @endif
        </div>
        
        <div class="container">
      	<div class="row flex-column">
          <div class="mb-3">
             Is this task completed within deadline?
              <sup class="f-14 text-danger">*</sup>
              <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Is this task completed within deadline?" data-html="true" data-trigger="hover"></i>
          </div>
      	<div class="rating">
            <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="Meh">5 stars</label>
            <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="Kinda bad">4 stars</label>
            <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="Kinda bad">3 stars</label>
            <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="Sucks big tim">2 stars</label>
            <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="Sucks big time">1 star</label>
        </div>
            <span id="ratingError" class="text-danger mb-2"></span>
        <div class="mb-3">
           How beautifully the task is submitted?
            <sup class="f-14 text-danger">*</sup>
            <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="How beautifully the task is submitted?" data-html="true" data-trigger="hover"></i>
        </div>
        <div class="rating2">
            <input type="radio" id="star6" name="rating2" value="5" /><label for="star6" title="Meh">5 stars</label>
            <input type="radio" id="star7" name="rating2" value="4" /><label for="star7" title="Kinda bad">4 stars</label>
            <input type="radio" id="star8" name="rating2" value="3" /><label for="star8" title="Kinda bad">3 stars</label>
            <input type="radio" id="star9" name="rating2" value="2" /><label for="star9" title="Sucks big tim">2 stars</label>
            <input type="radio" id="star10" name="rating2" value="1" /><label for="star10" title="Sucks big time">1 star</label>
        </div>
            <span id="rating2Error" class="text-danger mb-2"></span>
        <div class="mb-3">
           How perfectly the task requirements are fulfilled?
            <sup class="f-14 text-danger">*</sup>
            <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="How perfectly the task requirements are fulfilled?" data-html="true" data-trigger="hover"></i>
        </div>
        <div class="rating3">
            <input type="radio" id="star11" name="rating3" value="5" /><label for="star11" title="Meh">5 stars</label>
            <input type="radio" id="star12" name="rating3" value="4" /><label for="star12" title="Kinda bad">4 stars</label>
            <input type="radio" id="star13" name="rating3" value="3" /><label for="star13" title="Kinda bad">3 stars</label>
            <input type="radio" id="star14" name="rating3" value="2" /><label for="star14" title="Sucks big tim">2 stars</label>
            <input type="radio" id="star15" name="rating3" value="1" /><label for="star15" title="Sucks big time">1 star</label>
        </div>
            <span id="rating3Error" class="text-danger mb-2"></span>
        <div class="mb-3">
          Any Recommendations for Developer?
            <sup class="f-14 text-danger">*</sup>
            <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Any Recommendations for Developer?" data-html="true" data-trigger="hover"></i>
        </div>
        <div class="">
          <textarea name="comments" id="comments" class="form-control"></textarea>
            <script src="https://cdn.ckeditor.com/4.19.1/standard/ckeditor.js"></script>
            <script>
                CKEDITOR.replace('comments',{
                    height:100,
                });
            </script>
            <span id="commentsError" class="text-danger" ></span>
        </div>

      	</div>
      </div>






      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="approveBtn">Approve</button>

      </div>
        </form>
    </div>
  </div>
</div>


<script>
    $('#approveBtn').click(function(e){
        // alert('ok');
        e.preventDefault();
        $('#approveBtn').attr("disabled", true);
        $('#approveBtn').html("Processing...");
        var rating = $('input[name="rating"]:checked').val();
        var rating2 = $('input[name="rating2"]:checked').val();
        var rating3 = $('input[name="rating3"]:checked').val();
        var comments = CKEDITOR.instances.comments.getData();
        var data= {
            '_token': "{{ csrf_token() }}",
            'rating': rating,
            'rating2': rating2,
            'rating3': rating3,
            'comments': comments,
            'task_id': {{$task->id}},
            'user_id': {{$task->last_updated_by}},
        }
        // console.log(data);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "POST",
            url: "{{route('task-status-approve')}}",
            data: data,
            dataType: "json",
            success: function (response) {
                // console.log(response.status);
                if(response.status==200){
                    $('#taskapprove').trigger("reset");
                    // $('#taskapprove').modal("hide");
                    $('#approveBtn').attr("disabled", false);
                    $('#approveBtn').html("Approve");
                    window.location.reload();
                    toastr.success('Approve Successfully');
                }

            },
            error: function(error) {
                // console.log(response);
                if(error.responseJSON.errors.rating){
                    $('#ratingError').text(error.responseJSON.errors.rating);
                }else{
                    $('#ratingError').text('');
                }
                if(error.responseJSON.errors.rating2){
                    $('#rating2Error').text(error.responseJSON.errors.rating2);
                }else{
                    $('#rating2Error').text('');
                }
                if(error.responseJSON.errors.rating3){
                    $('#rating3Error').text(error.responseJSON.errors.rating3);
                }else{
                    $('#rating3Error').text('');
                }
                if(error.responseJSON.errors.comments){
                    $('#commentsError').text(error.responseJSON.errors.comments);
                }else{
                    $('#commentsError').text('');
                }
                $('#approveBtn').attr("disabled", false);
                $('#approveBtn').html("Approve");
            }
        });
    });

</script>
