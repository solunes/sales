<?php 

namespace Solunes\Sales\App\Helpers;

use Form;

class CustomSales {
   
    public static function after_seed_actions() {
        $sale_menu = \Solunes\Master\App\Menu::where('level',1)->where('permission','sales')->first();
        \Solunes\Master\App\Menu::create(['parent_id'=>$sale_menu->id,'level'=>'2','menu_type'=>'admin','icon'=>'user','permission'=>'sales','name'=>'Crear Venta','link'=>'admin/create-manual-sale']);
        \Solunes\Master\App\Menu::create(['parent_id'=>$sale_menu->id,'level'=>'2','menu_type'=>'admin','icon'=>'user','permission'=>'sales','name'=>'Crear Cotización','link'=>'admin/create-manual-quotation']);

        /*// Arreglar Action Fields y Action Nodes
        $node_array['sale'] = ['action_field'=>['view'], 'action_node'=>['back','excel']];
        $node_array['sale-payment'] = ['action_field'=>['view','edit']];
        $node_array['sale-delivery'] = ['action_field'=>['view','edit']];
        $node_array['sale-credit'] = ['action_field'=>['view','edit']];
        \Business::changeNodeActionFields($node_array);

        // Borrar opciones del menú
        \Solunes\Master\App\Menu::where('menu_type', 'admin')->where('level', 2)->whereTranslation('name', 'Ventas')->delete();
        \Solunes\Master\App\Menu::where('menu_type', 'admin')->where('level', 2)->whereTranslation('name', 'Devoluciones')->delete();

        // Menú
        $pm = \Solunes\Master\App\Menu::where('menu_type', 'admin')->whereTranslation('name', 'Ventas')->first();
        $menu_array[] = ['parent_id'=>$pm->id,'level'=>2,'icon'=>'calculator','name'=>'Realizar Venta','link'=>'admin/create-sale'];
        $menu_array[] = ['parent_id'=>$pm->id,'level'=>2,'icon'=>'calculator','name'=>'Realizar Devolución','link'=>'admin/create-refund'];
        $menu_array[] = ['parent_id'=>$pm->id,'level'=>2,'icon'=>'th-list','name'=>'Reporte Diario','link'=>'admin/sales-report?period=day&initial_date=&initial_date_submit=&end_date=&end_date_submit=&currency_id=1&place_id=all'];
        $menu_array[] = ['parent_id'=>$pm->id,'level'=>2,'icon'=>'th-list','name'=>'Reporte de Ventas','link'=>'admin/sales-detail-report'];
        $pm = \Solunes\Master\App\Menu::where('menu_type', 'admin')->whereTranslation('name', 'Reportes')->first();
        $menu_array[] = ['parent_id'=>$pm->id,'level'=>2,'icon'=>'bar-chart','name'=>'Resumen de Ventas','link'=>'admin/sales-report'];
        $menu_array[] = ['parent_id'=>$pm->id,'level'=>2,'icon'=>'bar-chart','name'=>'Detalle de Ventas','link'=>'admin/sales-detail-report'];
        //$menu_array[] = ['parent_id'=>$pm->id,'level'=>2,'icon'=>'bar-chart','name'=>'Estadísticas de Ventas','link'=>'admin/statistics-sales'];
        \Business::createBulkAdminMenu($menu_array);*/
        return 'After seed realizado correctamente.';
    }
       
    public static function get_custom_field($name, $parameters, $array, $label, $col, $i, $value, $data_type) {
        // Type = list, item
        $return = NULL;
        /*if($name=='parcial_cost'){
            $return .= \Field::form_input($i, $data_type, ['name'=>'quantity', 'required'=>true, 'type'=>'string'], ['value'=>1, 'label'=>'Cantidad Comprada', 'cols'=>4]);
            //$return .= \Field::form_input($i, $data_type, ['name'=>'total_cost', 'required'=>true, 'type'=>'string'], ['value'=>0, 'label'=>'Costo Total de Lote', 'cols'=>6], ['readonly'=>true]);
            if(request()->has('purchase_id')){
                $return .= '<input type="hidden" name="purchase_id" value="'.request()->input('purchase_id').'" />';
            }
        }*/
        return $return;
    }

    public static function after_login($user, $last_session, $redirect) {
        if($cart = \Solunes\Sales\App\Cart::where('session_id', $last_session)->status('holding')->first()){
            $cart->session_id = session()->getId();
            $cart->user_id = $user->id;
            $cart->save();
        }
        return true;
    }
    
    public static function check_permission($type, $module, $node, $action, $id = NULL) {
        // Type = list, item
        $return = 'none';
        if($node->name=='sale'){
            if($type=='item'&&$action=='edit'){
                $pending = \Solunes\Sales\App\Sale::find($id);
                if($pending->status=='paid'||$pending->status=='delivered'){
                    $return = 'false';
                }
            }
        }
        return $return;
    }

    public static function get_options_relation($submodel, $field, $subnode, $id = NULL) {
        /*if($field->relation_cond=='account_concepts'){
            $node_name = request()->segment(3);
            if($id){
                $node = \Solunes\Master\App\Node::where('name', request()->segment(3))->first();
                $model = \FuncNode::node_check_model($node);
                $model = $model->find($id);
                $submodel = $submodel->where('id', $model->account_id);
            } else {
                if(auth()->check()&&auth()->user()->hasRole('admin')){
                    if($node_name=='income'||$node_name=='accounts-receivable'){
                        $submodel = $submodel->where('code', 'income_other');
                    } else if($node_name=='expense'||$node_name=='accounts-payable'){
                        $submodel = $submodel->whereIn('code', ['expense_operating_com','expense_operating_adm','expense_operating_dep','expense_operating_int','expense_other']);
                    }
                } else {
                    if($node_name=='income'){
                        $submodel = $submodel->where('code', 'income_other');
                    } else if($node_name=='expense'){
                        $submodel = $submodel->where('code', 'expense_other');
                    }
                }
            }
        }*/
        return $submodel;
    }

}