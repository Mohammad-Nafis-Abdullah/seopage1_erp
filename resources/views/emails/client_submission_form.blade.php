<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seopage 1</title>



    <style>
 .email_message ul li,.newarea p,body{font-family:Poppins}body{padding:0;margin:0;box-sizing:border-box}.email_templates{max-width:640px;margin:10px auto;display:block;border:1px solid #eee;border-radius:5px}.email_message h4{color:#1d1d1d;font-size:18px;font-weight:500}.custom_containers{width:96%;margin:15px auto;display:block}.email_header{background:#eee;width:100%}.email_header img{width:142px;height:61px}.email_contents{width:100%;display:flex;justify-content:flex-start;align-items:center}.email_contents img{width:35px;height:35px}.email_contents strong{padding-left:5px;font-size:20px;font-weight:600;color:#000}.email_message{width:100%}.email_message span{color:#1d82f5;font-style:italic}.email_message p a{color:#1d82f5;text-decoration:none}.email_message p{font-weight:500;line-height:30px;font-size:17px;text-align:left}.email_message ul{padding:0;margin:0}.email_message ul li{list-style:none!important;padding:17px 0;border-bottom:1px solid #eee;font-size:16px}.email_submits{margin:37px 0;width:100%;display:block}.email_submits a{background:#1d82f5;padding:15px 30px;color:#fff;text-decoration:none;font-style:normal;font-weight:500;border-radius:5px;transition:.5s ease-in-out}.email_message strong{font-family:Poppins;font-size:18px;font-weight:500}.email_submits a:hover{background:#111}.newarea h5{font-size:18px;font-weight:600;color:#1d82f5;margin:0;padding-top: 4px;}.newarea p{font-size:16px;font-weight:400}.message_list hr{border:1px solid #eee}@media only screen and (min-width:320px) and (max-width:600px){.email_templates{max-width:600px;border:1px solid #eee;margin:2%}.email_submits a{padding:15px 10px;margin:0 auto;display:block;width:170px;text-align:center;border-radius:30px;transition:.5s ease-in-out}.email_submits a:hover{background:#1111;color:#fff}}

    </style>

</head>
<body>
<?php

  $deal= App\Models\Deal::where('id',$data->deal_id)->first();
  // $user= App\Models\User::where('id',$deal->client_id)->first();
  $client= App\Models\User::where('id',$deal->client_id)->first();
  //$row->created_at->format($this->global->date_format).$row->created_at->format('h:i:s A');

 ?>

    <section class="email_templates">
        <div class="email_header">
            <img src="{{asset('email/img/logo_seopage1.png')}}" alt="">
        </div>

        <div class="custom_containers">
            <div class="email_contents">
                <img src="{{asset('email/img/check_mark.png')}}" alt="">

                <strong>Client Has Submitted Form Successfully
</strong>
            </div>

            <div class="email_message">
                <h4>Hi {{$user->name}},</h4>
                <p><strong>{{$client->user_name}}</strong> has submiitted his/her details<span> on {{$data->created_at->format('Y-m-d')}} at {{$data->created_at->format('h:i:s A')}}</span> Let's check the short details below. You can check the details about this deal following <a href="erp.seopage1.net/account/deal-url/{{$deal->id}}">this link.</a></p>
            </div>

            <div class="email_message">
                <h4 style="color: #D99218;font-weight: 700;font-size: 22px;">Client Details</h4>
                <ul>
                    <li><strong>Client User Name on Freelancer.com:</strong> {{$data->client_username}}</li>
                    @if($user->role_id == 1)
                   <li><strong>Email:</strong> {{$data->client_email}}</li>
                    <li><strong>Phone:</strong> {{$data->client_phone}}</li>
                      <li><strong>WhatsApp:</strong> {{$data->client_whatsapp}}</li>
                      <li><strong>Skype:</strong> {{$data->client_skype}}</li>
                      <li><strong>Telegram:</strong> {{$data->client_telegram}}</li>
                      <li><strong>Messenger:</strong> {{$data->client_messenger}}</li>
                      <li><strong>Imo:</strong> {{$data->client_imo}}</li>

                    @endif

                      <li><strong>Timezone:</strong> {{$data->timezone}}</li>
                      <li><strong>Available Time:</strong> {!!$data->day!!}</li>
                      <li><strong>Agree for Our Office Time:</strong> @if($data->message == "on")
                        Yes
                        @else
                        No
                        @endif</li>
                      <li><strong>Agree For Promotional Email:</strong> @if($data->checklist == "on")
                        Yes
                        @else
                        No
                        @endif</li>



                </ul>
                <br>




            </div>

            <div class="email_submits">
                <a href="erp.seopage1.net/account/deal-url/{{$deal->id}}">View</a>
            </div>
        </div>

    </section>


</body>
</html>
