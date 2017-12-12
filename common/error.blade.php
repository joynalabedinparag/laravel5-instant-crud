@extends('layouts/admin/master')

@section('content')
    <div class="row actionbar">
        <a href="{{route('manage-products.create', ['product_slug' => $product_slug])}}" class="btn btn btn-info hvr-float-shadow hvr-bob addbtn" data-toggle="tooltip" title="Add New User">
            <i class="fa fa-plus"> &nbsp&nbsp </i>Add
        </a>
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">
            <p class="fa fa-users"></p> {{ucwords( str_replace( "-", " ", $product_slug ) )}}
        </div>
        <!-- /.panel-heading -->
        <div class="panel-body">

            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    {{ $message }}
                </div>
            @endif

            <div class="alert alert-warning text-center Msg" style="">Service Not Available</div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr class="">

                    </tr>
                    </thead>
                    <tbody>


                    </tbody>
                    @endif
                </table>
                {!! $products->render() !!}
            </div>

        </div>
        <!-- /.panel-body -->
    </div>

@endsection