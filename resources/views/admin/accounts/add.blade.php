@extends('admin.layouts.admin_layout')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    @include('admin.layouts.admin_breadcum')

    <div class="wrapper wrapper-content">
        {{--@include('theme.flash_message')--}}
        @include('admin.flash_message')

        <div class="row">

            <div class="col-lg-12">

                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Add User</h5>
                    </div>
                    <div class="ibox-content">
                        <form method="post" class="form-horizontal" action="{{ route('admin.user.store') }}"  enctype="Multipart/form-data">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label">Member Id*</label>
                                </div>
                                <div class="col-md-6">
                                <select title="None Selected" name="user_id" id="users_select2" class="form-control users_select2" required>
                                    <option value="{{ $user->text }}" selected="selected">{{ $user->text }}</option>
                                </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label">Account Type</label>
                                </div>
                                <div class="col-md-6">
                                    <select name="account_type" id="account_type" class="form-control">
                                    <option value="">Select Account Type</option>
                                    @foreach(App\Account::getTypeOptions() as $type => $value) 
                                    <option value="{{ $type }}">{{ $value }}</option>
                                    @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group" id="duration_div">
                                <div class="col-md-3">
                                    <label class="control-label">Duration(in Months)</label>
                                </div>
                                <div class="col-md-6">
                                <input type="text" class="form-control" name="duration"/> 
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label">Denomination Amount</label>
                                </div>
                                <div class="col-md-6">
                                   <input type="text" class="form-control" name="denomination_amount"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label">Interest Rate (in %)</label>
                                </div>
                                <div class="col-md-6">
                                   <input type="text" class="form-control" name="interest_rate"/>
                                </div>
                            </div>
                           
                           <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label">First Nominee Name</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="nominee_name"
                                           value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label">First Nominee Relation</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="nominee_relation"
                                           value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label">First Nominee Share (in %)</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="nominee_share"
                                           value="100">
                                </div>
                            </div>
                            <button class="btn btn-primary" id="add_second_nominee">Add Second Nominee</button>
                            <div id="second_nominee_div" style="display:none;">
                                <div class="form-group">
                                    <div class="col-md-3">
                                        <label class="control-label">Second Nominee Name</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="second_nominee_name"
                                            value="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-3">
                                        <label class="control-label">Second Nominee Relation</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="second_nominee_relation"
                                            value="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-3">
                                        <label class="control-label">Second Nominee Share</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="second_nominee_share"
                                            value="">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="col-md-3">
                                    <label class="control-label">Attach Documents</label>
                                </div>
                                <div class="col-md-6">
                                    <input type="file" name="documents" multiple>
                                </div>
                            </div>

                            <button class="btn btn-primary" type="submit">Save</button>
                        </form>
                    </div>
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-lg-12 -->



        </div>
    </div>
    <!-- /.row -->
@endsection
@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script>
$(document).ready(function(){
    $('.users_select2').select2({
        ajax: {
    url: "{{ url('admin/get-users-list') }}",
    dataType: 'json',
    processResults: function (data) {
      // Tranforms the top-level key of the response object from 'items' to 'results'
      return {
        results: data.data
      };
    }
    // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
  }
    });

    $("#add_second_nominee").click(function(e){
        e.preventDefault();
        $("#second_nominee_div").show();
    })
    $("#account_type").change(function(){
        var val = $(this).val();    
        if (val != "{{ App\Account::TYPE_MONTHLY_INCOME }}" && val != "{{ App\Account::TYPE_LOAN }}"){    
            update_durations(val);
        }else{
            $("#duration_div").hide();
        }
    })

    function update_durations(val)
    {
        $.ajax({
            url:"{{ url('admin/get-duration') }}",
            type:"POST",
            data:{"_token":"{{ csrf_token() }}", 'val':val},
            success:function(res){
                var data = res.data;
                var html = "";
                for(key in data) {
                    html += '<option value="'+key+'">'+data[key]+'</option>';
                }
                $("#duration").html(html);
                $("#duration_div").show();
            }
        })
    }
})
</script>
@endsection