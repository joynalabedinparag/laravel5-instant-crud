<?php

namespace App\Http\Controllers;

use App\CustomHelpers\CustomHelper;
use App\Models\Products\ProductCrud;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Schema;
use ReflectionClass;

class CrudController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $table_prefix = 'ln_';
    public $max_index_column = 40;
    public $route = "manage-products";
    public $view_file_location = "common";
    public $image_dir = "/public/images/catalog/products/";
    public $labels = array('rate_high' => 'High Rate', 'bank_id' => 'Bank', 'institution_id' => 'Institution', 'option_value_id' => 'Options');
    public $ignore = array('id', 'created_at', 'updated_at');
    public $optional_fields = array('rate_high', 'rate_low');
    public $image_fields = array('bank_logo', 'card_logo', 'commodity_logo', 'metal_logo');
    public $checkbox_fields = array('test' => array('value' => 1));
    public $radio_fields = array(
        'is_featured' => array(1 => 'Yes', 0 => 'No'),
        'is_buyable' => array(1 => 'Yes', 0 => 'No'),
        'is_account_openable' => array(1 => 'Yes', 0 => 'No'),
        'is_bookable' => array(1 => 'Yes', 0 => 'No')
    );


    public $relational_fields = array (
        'district_ids' => array('options_districts', 'value', 'label', true),
        'destination_id' => array('options_destinations', 'value', 'label', true),
        'bank_id' => array('institutions', 'id', 'name', false, array('institution_type_id', '=', 1)),
        'institution_id' => array('institutions', 'id', 'name', false, array('institution_type_id', '=', 2))
    );
    public $option_value_data;
    public $raw_arr_fields = array('option_value_id' => 'option_value_data'); // column_name => variable name that holds markup for this column

    public $table, $product_slug, $product_title, $product_model, $columns, $relational_fields_data;

    public function __construct(Request $request)
    {
        $this->middleware('auth:admin');
        $this->product_slug = $request->route()->parameter('product_slug');
        $this->product_title = ucwords(str_replace('-', ' ', $this->product_slug));
        $this->table = $this->table_prefix . str_replace("-", "_", $this->product_slug);
        $this->checkIfSchemaExist($this->table);
        $this->columns = $this->getTableColumns();
        $this->prepareRelationalFieldData();
        $this->option_value_data = $this->generateOptionValueData();
       
    }

    public function index(Request $request)
    {
        if (!CustomHelper::getProductsCategoryName('view'))
            return back();

        $data = array();
        $data = array_merge($data, $this->getAllVars());
        $data['products'] = DB::table($this->table)->paginate(5);
       
        return view($this->view_file_location . '.index', $data)
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!CustomHelper::getProductsCategoryName('create'))
            return back();

        // dd(get_object_vars($this));
        $data = array();
        $data = array_merge($data, $this->getAllVars());
        return view($this->view_file_location . '.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (CustomHelper::getProductsCategoryName('create'))
            return back();

        $form_data = $request->all();
        if (isset($form_data['_token'])) {
            unset($form_data['_token']);
        }
        $validation_rules = $this->generateValidationRules();
        $this->validate($request, $validation_rules);
        $form_data = $this->glueArrayInputs($form_data, ',');
        $form_data = $this->handleFileUpload($request, $form_data);

        DB::table($this->table)->insert($form_data);
        return redirect()->route($this->route . '.index', $this->product_slug)
            ->with('success', $this->product_title . ' created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Products\ProductCrud $personalLoan
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!CustomHelper::getProductsCategoryName('view'))
            return back();

        $user = User::find($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Products\ProductCrud $personalLoan
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!CustomHelper::getProductsCategoryName('edit'))
            return back();

        $data = array();
        $data = array_merge($data, $this->getAllVars());
        // $data['product'] = ProductCrud::find($id);
        $product = $this->getTableDataById($id);
        $data['product'] = $product;

        return view($this->view_file_location . '.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Products\ProductCrud $personalLoan
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!CustomHelper::getProductsCategoryName('edit'))
            return back();

        $form_data = $request->all();
        if (isset($form_data['_token'])) {
            unset($form_data['_token']);
        }
        if (isset($form_data['_method'])) {
            unset($form_data['_method']);
        }
        $form_data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');

        $product = $this->getTableDataById($id);
        $validation_rules = $this->generateValidationRules($product);
        $this->validate($request, $validation_rules);
        $form_data = $this->glueArrayInputs($form_data, ',');
        $form_data = $this->handleFileUpload($request, $form_data);

        DB::table($this->table)->where('id', $id)->update($form_data);
        return redirect()->route($this->route . '.index', $this->product_slug)
            ->with('success', $this->product_title . ' were updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Products\ProductCrud $personalLoan
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!CustomHelper::getProductsCategoryName('delete'))
            return back();

        // $product = ProductCrud::find($id); // @TODO Transform to query builder.
        $product = DB::table($this->table)->where('id', $id);
        $product->delete();
        return redirect()->route($this->route . '.index', $this->product_slug)
            ->with('success', 'Product deleted successfully');
    }

    public function getTableColumns()
    {
        $schema = \DB::getDoctrineSchemaManager();
        $columns = $schema->listTableColumns($this->table); //get table columns
        return $columns;
    }

    public function getTableDataById($id)
    {
        return $product = DB::table($this->table)->where('id', $id)->get()[0];
    }

    private function generateValidationRules($product = null)
    {
        $validation_rules = array();
        if (!empty($this->columns)) {
            foreach ($this->columns as $column_name => $column_details) {
                if (!in_array($column_name, $this->ignore) && !in_array($column_name, $this->optional_fields)) {
                    if (in_array($column_name, $this->image_fields) && !empty($product->$column_name)) {

                    } else {
                        $validation_rules [$column_name] = 'required';
                    }
                }
            }
            foreach ($this->image_fields as $image_field) {
                if (isset($validation_rules [$image_field]) && empty($validation_rules [$image_field])) {
                    $validation_rules [$image_field] .= 'image|mimes:jpeg,png,jpg,gif';
                } else if (isset($validation_rules [$image_field]) && !empty($validation_rules [$image_field])) {
                    $validation_rules [$image_field] .= '|image|mimes:jpeg,png,jpg';
                }
            }
        }
        return $validation_rules;
    }

    private function generateRandomString($length = 5)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function handleFileUpload($request, $form_data)
    {
        if (!empty($this->image_fields)) {
            foreach ($this->image_fields as $image_field) {
                if (isset($form_data[$image_field]) & !empty($form_data[$image_field])) {
                    $form_data[$image_field] = $this->product_slug . "-" . $this->generateRandomString() . '.' . $request->file($image_field)->getClientOriginalExtension();
                    $request->file($image_field)->move(
                        base_path() . $this->image_dir, $form_data[$image_field]
                    );
                }
            }
        }
        return $form_data;
    }

    private function prepareRelationalFieldData()
    {
        if (!empty($this->relational_fields)) {
            foreach ($this->relational_fields as $field_name => $relational_field) {
                $options = DB::table($this->table_prefix . $relational_field[0]);

                if (isset($relational_field[4])) {
                    $condition = $relational_field[4];
                    $options = $options->where($condition[0], $condition[1], $condition[2]);
                }
                $options = $options->get()->pluck($relational_field[2], $relational_field[1]);
                $this->relational_fields_data[$field_name] = $options;
            }
        }
    }

    private function generateOptionValueData()
    {
        $data = array();
        $options = DB::table($this->table_prefix . "product_option_map as pom")
            ->leftJoin($this->table_prefix . "products as p", "p.id", '=', 'pom.product_id')
            ->leftJoin($this->table_prefix . "product_options as po", "po.id", '=', 'pom.option_id')
            ->where('p.slug', $this->product_slug)
            ->select('pom.option_id as option_id', 'po.label as label')
            ->get();
        $markup = '';
        foreach ($options as $option) {
            $option_values = DB::table($this->table_prefix . "product_options_value as pov")
                ->where('pov.option_id', $option->option_id)
                ->select('pov.id', 'pov.label')
                ->get()->toArray();
            $markup .= '<label class="control-label">' . $option->label . '</label>';
            $markup .= '<select name="option_value_id[]" class="form-control">';
            $ov_arr = array();
            foreach ($option_values as $ov) {
                $ov_arr[$ov->id] = $ov->label;
                $selected = (isset($product) && isset($column_name) && isset($product->$column_name) && in_array($ov->id, explode(",", $product->$column_name))) ? "selected" : "";
                $markup .= '<option value="' . $ov->id . '" ' . $selected . '>' . $ov->label . '</option>';
            }
            $data[$option->option_id]['label'] = $option->label;
            $data[$option->option_id]['data'] = $ov_arr;
            $markup .= '</select>';
        }
        return $data;
        //return $markup;
    }

    private function glueArrayInputs($arr, $glue = ',')
    {
        $data = array();
        foreach ($arr as $key => $elem) {
            if (is_array($elem)) {
                $data[$key] = implode($glue, $elem);
            } else {
                $data[$key] = $elem;
            }
        }
        return $data;
    }

    private function getAllVars()
    {
        return get_object_vars($this);
    }

    private function checkIfSchemaExist($table)
    {
        if (!Schema::hasTable($table)) {
            abort(404, 'The resource you are looking for could not be found');
            /*	return view('admin.products.common.error');
                exit;*/
        }
    }

    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }
}