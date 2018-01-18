<?php

namespace Solunes\Sales\App\Listeners;

class RegisterSaleCredit {

    public function handle($event) {
    	// Revisar que tenga una sesión y sea un modelo del sitio web.
    	if($event){
            $income_sale_credit = \Solunes\Sales\App\Account::getCode('income_sale_credit')->id;
            $accounts_receivable = new \Solunes\Sales\App\AccountsReceivable;
            $accounts_receivable->name = 'Crédito por venta de productos.';
            $accounts_receivable->place_id = $event->parent->place_id;
            $accounts_receivable->account_id = $income_sale_credit;
            $accounts_receivable->currency_id = $event->currency_id;
            $accounts_receivable->sale_id = $event->parent_id;
            $accounts_receivable->due_date = $event->due_date;
            $accounts_receivable->amount = $event->amount;
            $accounts_receivable->reference = $event->detail;
            $accounts_receivable->created_at = $event->created_at;
            $accounts_receivable->save();
            $asset_ctc = \Solunes\Sales\App\Account::getCode('asset_ctc')->id;
            $income_sale_credit = \Solunes\Sales\App\Account::getCode('income_sale_credit')->id;
            $arr[] = \Sales::register_account($event->parent->place_id, 'debit', $asset_ctc, 1, $event->amount, 'Venta de mercadería a crédito');
            $arr[] = \Sales::register_account($event->parent->place_id, 'credit', $income_sale_credit, 1, $event->amount, 'Venta de mercadería a crédito');
            \Sales::register_account_array($arr, $event->created_at, $event->parent->transaction_code);
            return $event;
    	}

    }

}
