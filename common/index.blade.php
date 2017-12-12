@extends('layouts/admin/master')

@section('content')
    <div class="row actionbar">
        <a href="{{route($route.'.create', ['product_slug' => $product_slug])}}" class="btn btn btn-info hvr-float-shadow hvr-bob addbtn" data-toggle="tooltip" title="Add New User">
            <i class="fa fa-plus"> &nbsp&nbsp </i>Add
        </a>
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">
            <i class="fa fa-align-justify" aria-hidden="true"></i> {{ucwords( str_replace( "-", " ", $product_slug ) )}}
        </div>
        <!-- /.panel-heading -->
        <div class="panel-body">

            {{--@if ($message = Session::get('success'))
                <div class="alert alert-success">
                    {{ $message }}
                </div>
            @endif--}}

            <div class="alert alert-success text-center Msg" style="display: none;"></div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr class="">
                        @php $c = 1; @endphp
                        @foreach($columns as $column_name => $column_details)
                            @php $column_label = (!isset($labels[$column_name])) ? ucwords( str_replace( "_", " ", $column_name ) ) : $labels[$column_name]; @endphp
                            @if(!in_array($column_name, $ignore))
                                <th>{{ucwords( str_replace( "_", " ", $column_label ) )}}</th>
                                @php
                                    if( $c >= $max_index_column) {break;}
                                    $c++;
                                @endphp
                            @endif
                        @endforeach
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>

				    <?php $i = 0; ?>
                    @if(!$products->isEmpty())
                        @foreach($products as $product)
                            <tr class="odd gradeX tr_{{$product->id}}">
                                @php $c = 1; @endphp
                                @foreach($columns as $column_name => $column_details)
                                    @if(!in_array($column_name, $ignore))
                                        @if (array_key_exists($column_name, $relational_fields))
                                            <td>
                                                @php
                                                    $imploded_data = '';
                                                    foreach (explode(",", $product->$column_name) as $pc) {
                                                       if(isset($relational_fields_data[$column_name][$pc])) {
                                                           $imploded_data .= $relational_fields_data[$column_name][$pc]. ", ";
                                                       } else {
                                                           $imploded_data .= $pc;
                                                       }
                                                    }
                                                    echo rtrim($imploded_data, ", ");
                                                @endphp
                                            </td>
                                        @elseif (array_key_exists($column_name, $radio_fields))
                                            <td>
                                                {{$radio_fields[$column_name][$product->$column_name]}}
                                            </td>
                                        @elseif (in_array($column_name, $image_fields))
                                            <td>
                                                @if(file_exists( public_path() .'/images/catalog/products/'.$product->$column_name))
                                                    <a target="_blank" href="{{ asset('images/catalog/products/'.$product->$column_name) }}">View Image</a>
                                                @else
                                                    No image yet
                                                @endif
                                            </td>
                                        @elseif (array_key_exists($column_name, $raw_arr_fields))
                                            <td>
                                                @php
                                                    $imploded_data = '';
                                                    foreach (explode(",", $product->$column_name) as $pc) {
                                                        foreach($$raw_arr_fields[$column_name] as $rd) {
                                                           if(isset($rd['data'][$pc])) {
                                                               $imploded_data .= $rd['data'][$pc]. ", ";
                                                               break;
                                                           }
                                                        }
                                                    }
                                                    echo rtrim($imploded_data, ", ");
                                                @endphp
                                            </td>
                                        @else
                                            <td>{{$product->$column_name}}</td>
                                        @endif
                                        @php
                                            if( $c >= $max_index_column) {break;}
                                            $c++;
                                        @endphp
                                    @endif
                                @endforeach
                                <td>
                                    {{--<a href="{{route('manage-products.show', ['id'=> $product->id, 'product_slug' => $product_slug])}}"><button  type="button" class="btn btn-warning btn-circle" data-toggle="tooltip" title="Preview"><span class="glyphicon glyphicon-eye-open"></span> </button></a>--}}
                                    <a href="{{route($route.'.edit', ['id'=> $product->id, 'product_slug' => $product_slug])}}"><button  type="button" class="btn btn-success btn-circle" data-toggle="tooltip" title="Edit"><span class="glyphicon glyphicon-edit"></span> </button></a>
                                    {!! Form::open(['method' => 'DELETE','route' => [$route.'.destroy', $product->id, $product_slug],'style'=>'display:inline']) !!}
                                        <a href="javascript:void(0)" onclick="var r = confirm('Are you sure?');if(r == true){$(this).closest('form').submit();}else{return false;}"><button  type="button" class="btn btn-danger btn-circle" data-toggle="tooltip" title="Delete"><span class="glyphicon glyphicon-trash"></span> </button></a>
                                    {!! Form::close() !!}
                                </td>

                            </tr>

                        @endforeach
                    @else
                        <tr>
                            <td colspan="50"><div class="alert alert-danger text-center">No Data Available</div></td>
                        </tr>
                    </tbody>
                    @endif
                </table>
                {!! $products->render() !!}
            </div>

        </div>
        <!-- /.panel-body -->
    </div>

@endsection