@if(!empty($client))
    <div class="row">
        <div class="col-md-5">
            <h6>{{__('Bill to')}}</h6>
            @php
             $company = \App\Models\User::where('id', $client->user_id)->first();
            @endphp
            <div class="bill-to">
                @if(!empty($client))
                <small>
                    <span>{{$company['name']}}</span><br>
                    <span>{{$client['telp']}}</span><br>
                    <span>{{$client['address']}}</span><br>
                    <span>{{$client['city'] . ' , '.$client['state'].' , '.$client['country'].'.'}}</span><br>

                </small>
                @else
                    <br> -
                @endif
            </div>
        </div>
        <div class="col-md-2">
            <a href="#" id="remove" class="text-sm">{{__(' Remove')}}</a>
        </div>
    </div>
@endif
