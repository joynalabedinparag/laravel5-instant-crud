@extends('layouts/admin/master')

@section('content')
    <br />
    <div class="panel panel-info">
        <div class="panel-heading">
            <i class="fa fa-plus" aria-hidden="true"></i> Add New {{$product_title}}
            <a class="btn btn-default btn-sm pull-right" style="margin-top:-3px;" href="{{route($route.'.index', ['product_slug' => $product_slug])}}">
                <i class="fa fa-arrow-left"></i> Back To {{$product_title." List"}}
            </a>
        </div>
        <div class="panel-body">
           {{-- @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif--}}
            <div class="row">
                {!! Form::open(array('route' => [$route.'.store', $product_slug], 'method'=>'POST', 'enctype' => 'multipart/form-data')) !!}
                    <div class="col-lg-6">
                        @php
                            foreach($columns as $column_name => $column_details) {
                                if(!in_array($column_name, $ignore)) {
                                    $column_label = (!isset($labels[$column_name])) ? ucwords( str_replace( "_", " ", $column_name ) ) : $labels[$column_name];
                                    $mendatory_sign = ( !in_array($column_name, $optional_fields ) ) ? '<span style="color:red">*</span>' : "" ;
                                    echo '<div class="form-group">';
                                         $label = '<label class="control-label">' . $column_label . $mendatory_sign .'</label>';
                                         if ( in_array($column_name, $image_fields) ) {
                                            echo $label;
                                            echo Form::file($column_name, null, ['class' => 'form-control']);

                                         } else if ( array_key_exists($column_name, $relational_fields) ) {
                                              $multiple = ($relational_fields[$column_name][3] == true) ? 'multiple' : '';
                                              $select_html = "<select name='".$column_name."[]' class='form-control' ".$multiple.">";
                                              foreach($relational_fields_data[$column_name] as $rk => $rd) {
                                                 $selected = (isset($product->$column_name) && in_array($rk, explode(",", $product->$column_name))) ? "selected" : "";
                                                 $select_html .= "<option value='".$rk."' ".$selected.">".$rd."</option>";
                                              }
                                              $select_html .= "</select>";
                                              echo $label;
                                              echo $select_html;
                                              // echo  Form::select($column_name.'[]', $relational_fields_data[$column_name], null, ['class' => 'form-control', 'multiple'=>'multiple']);

                                         } else if ( array_key_exists($column_name, $checkbox_fields) ) {

                                              $checked = ( $checkbox_fields[$column_name]['value'] == $product->$column_name ) ? true : null;
                                              echo $label;
                                              echo  Form::checkbox( $column_name, $checkbox_fields[$column_name]['value'], $checked, ['class' => ''] );

                                         } else if ( array_key_exists($column_name, $radio_fields ) ) {
                                              echo $label;
                                              echo "</br>";
                                              foreach($radio_fields[$column_name] as $value => $label) {
                                                echo Form::radio($column_name, $value) . "  " .$label;
                                                echo "</br>";
                                              }

                                         }  else if( array_key_exists($column_name, $raw_arr_fields) ) {
                                               $raw_data = $$raw_arr_fields[$column_name];
                                               $markup = '';
                                               foreach($raw_data as $rd) {
                                                    $markup .= '<label class="control-label">'.$rd['label'].'</label>';
                                                    $markup .= '<select name="'.$column_name.'[]" class="form-control">';
                                                    foreach($rd['data'] as $oval => $olabel) {
                                                        $markup .= '<option value="'.$oval.'">'.$olabel.'</option>';
                                                    }
                                                    $markup .= "</select>";
                                               }
                                               echo $markup;
                                         } else {
                                            echo $label;
                                            echo Form::text( $column_name, null, array('class' => 'form-control',
                                                        'value' => '@if($errors->any()){{Input::old('.$column_name.')}}@endif',
                                                        'placeholder' => $column_label) );
                                         }
                                         if ( $errors->has($column_name) ) {
                                           echo' <p class="validation-msg">'.$errors->first($column_name).'</p>';
                                         }
                                    echo '</div>';
                                }
                            }
                        @endphp

                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{route($route.'.index', ['product_slug' => $product_slug])}}" class="btn btn-danger">Cancel</a>
                    </div>

                {!! Form::close() !!}
            </div>
            <!-- /.row (nested) -->
        </div>
        <!-- /.panel-body -->
    </div>
    <!-- /.panel -->
@stop