<?php

namespace Solunes\Sales\App;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model {
	
	protected $table = 'shippings';
	public $timestamps = true;

    /* Creating rules */
    public static $rules_create = array(
        'name'=>'required',
    );

    /* Updating rules */
    public static $rules_edit = array(
        'id'=>'required',
        'name'=>'required',
    );
       
    public function scopeActive($query) {
        return $query->where('active', 1);
    }

    public function scopeInactive($query) {
        return $query->where('active', 0);
    }

    public function scopeOrder($query) {
        return $query->orderBy('order', 'ASC');
    }

    public function city() {
        return $this->belongsTo('Solunes\Business\App\City');
    }

    public function shipping_city() {
        return $this->hasOne('Solunes\Sales\App\ShippingCity', 'parent_id');
    }

    public function shipping_cities() {
        return $this->hasMany('Solunes\Sales\App\ShippingCity', 'parent_id');
    }

    public function shipping_times() {
        return $this->hasMany('Solunes\Sales\App\ShippingTime', 'parent_id');
    }

    public function agency_shipping() {
        return $this->belongsToMany('Solunes\Business\App\Agency', 'agency_shipping', 'shipping_id', 'agency_id');
    }

}