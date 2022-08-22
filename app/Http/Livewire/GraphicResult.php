<?php

namespace App\Http\Livewire;

use Livewire\Component;

class GraphicResult extends Component
{
    public $respuesta;

    public function render()
    {
        return view('livewire.graphic-result');
    }

    protected $listeners  = ['loadDataGraphic', 'dataTortaLoaded'];

    public function loadDataGraphic($data){
        $this->emit('dataGraphicLoaded', $data);
        $this->respuesta = $data;
    }

    public function dataTortaLoaded($data){
        $this->emit('tortaLoaded', $data);
        $this->respuesta = $data;
    }
}
