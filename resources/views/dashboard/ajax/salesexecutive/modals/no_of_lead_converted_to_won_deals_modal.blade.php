<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<div class="modal fade" id="no_of_lead_converted_won_deal{{count($number_of_leads_convert_won_deals_get)}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <div class="modal-title"> 
                <h4>Total Number of Converted Lead to Won Deals : {{count($number_of_leads_convert_won_deals_get)}}</h4>  
                <h4>Total Number of Converted Lead to Won  Deals (Fixed) : {{$number_of_leads_convert_won_deals_fixed}}</h4>  
                <h4>Total Number of Converted Lead to Won  Deals (Hourly) : {{$number_of_leads_convert_won_deals_hourly}}</h4>  
            </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <table id="noOfLeadThatGotConvertedWonDeals" class="display" style="width:100%">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Project Name</th>
                    <th scope="col">Project Budget</th>
                    <th scope="col">Created</th>
                    <th scope="col">Created By</th>
                    <th scope="col">Deal Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($number_of_leads_convert_won_deals_get as $row)
                  @php
                    $currency = App\Models\Currency::where('id',$row->currency_id)->first();
                    $user = App\Models\User::where('id',$row->added_by)->first();
                  @endphp
               
                  <tr>
                      <td>{{$loop->index+1}}</td>
                      <td>
                          {{$row->client_name}}
                      </td>
                      <td>
                        @if ($currency !=null)
                        {{$row->bid_value . $currency->currency_symbol}}
                        @else
                        {{$row->bid_value}}
                        @endif
                      </td>
                      <td>
                          {{$row->created_at}}
                      </td>
                      <td>
                          @if($user->id != null)
                          <a href="{{route('employees.show',$user->id)}}"> {{$user->name}}</a>
                          @else 
                          --
                         @endif
                      </td>
                      <td>
                        @if($row->deal_status == 0)
                          <span>Not Applicable</span>
                        @else 
                          <span>Won</span>
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
      new DataTable('#noOfLeadThatGotConvertedWonDeals',{
        "dom": 't<"d-flex"l<"ml-auto"ip>><"clear">',
      });       
  </script>
