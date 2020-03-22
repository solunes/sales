<?php

namespace Solunes\Sales\App\Console;

use Illuminate\Console\Command;

class FixSalesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-sales-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisa el estado de las ventas.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        $this->info('Comenzando la revision de estado de ventas pendientes.');
        $count = 0;
        $stock_count = 0;
        if(config('solunes.inventory')&&config('sales.sale_duration_hours')){
            $date = date("Y-m-d");
            $time = date("H:i:s");
            $items = \Solunes\Sales\App\Sale::where(function ($query) use($date, $time) {
                $query->where('status', 'holding')->where('expiration_date',$date)->where('expiration_time','<',$time);
            })->orWhere(function ($query) use($date, $time) {
                $query->where('status', 'holding')->where('expiration_date','<',$date);
            })->get();
            $store_agency = \Solunes\Business\App\Agency::find(config('business.online_store_agency_id'));
            if(count($items)>0){
                foreach($items as $item){
                    $item->status = 'cancelled';
                    $item->save();
                    foreach($item->sale_items as $sale_item){
                        if(config('solunes.inventory')&&$sale_item->product_bridge->stockable){
                            \Inventory::increase_inventory($store_agency, $sale_item->product_bridge, $sale_item->quantity);
                            $stock_count++;
                        }
                    }
                    $this->info('Venta correctamente anulada y revertida: #'.$item->id);
                    $count++;
                }
            }
            $this->info('Finalizando la revision de estado de ventas pendientes. Se revirtieron '.$count.' items y el stock de '.$stock_count.' productos.');
        } else {
            $this->info('No se realizo la prueba porque no esta habilitado el stock.');
        }
    }
}
