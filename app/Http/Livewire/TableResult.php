<?php

namespace App\Http\Livewire;

use Livewire\Component;

class TableResult extends Component
{
    public $rows;

    public function render()
    {
        return view('livewire.table-result');
    }

    protected $listeners  = ['loadData'];

    public function loadData($data){
        $this->rows = $data;
    }
}
