<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class AccionesData extends Component
{
    public $resultado;

    public function render()
    {
        return view('livewire.acciones-data');
    }

    protected $listeners = ['generarResultado'];

    public function get_receita_comissao($consultor, $fecha_inicio, $fecha_fin){
        $result = DB::table('cao_fatura')
        ->join('cao_os', 'cao_os.co_os', '=', 'cao_fatura.co_os')
        ->select(
            DB::raw("ANY_VALUE(data_emissao) AS data_emissao"),
            DB::raw("DATE_FORMAT(data_emissao, '%M') as periodo"),
            DB::raw("SUM(valor - (total_imp_inc / 100)) as receita"),
            DB::Raw("SUM((valor - (valor * total_imp_inc / 100)) * (comissao_cn / 100)) as comissao")
        )
        ->where('co_usuario', '=', $consultor)
        ->where(function($query) use ($fecha_inicio, $fecha_fin) {
            if($fecha_inicio != "" && $fecha_fin != ""){
                $query->whereBetween('data_emissao', [$fecha_inicio, $fecha_fin]);
            }
        })
        ->groupBy('periodo')
        ->orderBy('data_emissao')
        ->get();

        return $result;
    }

    public function get_costo_fijo($consultor){
        $costo = DB::table('cao_salario')
            ->select('brut_salario')
            ->where('co_usuario', '=', $consultor)
            ->first();

        return $costo;
    }

    public function get_receita_consultor($consultor, $fecha_inicio, $fecha_fin){
        $result = DB::table('cao_fatura')
        ->join('cao_os', 'cao_os.co_os', '=', 'cao_fatura.co_os')
        ->select(
            DB::raw("ANY_VALUE(data_emissao) AS data_emissao"),
            DB::raw("DATE_FORMAT(data_emissao, '%M') as mes"),
            DB::raw("SUM(valor - (total_imp_inc / 100)) as receita, co_usuario as consultor")
        )
        ->where('co_usuario', '=', $consultor)
        ->where(function($query) use ($fecha_inicio, $fecha_fin) {
            if($fecha_inicio != "" && $fecha_fin != ""){
                $query->whereBetween('data_emissao', [$fecha_inicio, $fecha_fin]);
            }
        })
        ->groupBy('mes', 'co_usuario')
        ->orderBy('data_emissao')
        ->get();

        return $result;
    }

    public function get_receita_consultor_percentage($consultor, $fecha_inicio, $fecha_fin){
        $result = DB::table('cao_fatura')
            ->join('cao_os', 'cao_os.co_os', '=', 'cao_fatura.co_os')
            ->select(DB::raw("SUM(valor - total_imp_inc) as receita, co_usuario as consultor"))
            ->where('co_usuario', '=', $consultor)
            ->where(function($query) use ($fecha_inicio, $fecha_fin) {
                if($fecha_inicio != "" && $fecha_fin != ""){
                    $query->whereBetween('data_emissao', [$fecha_inicio, $fecha_fin]);
                }
            })
            ->groupBy('co_usuario')
            ->get();

        return $result;
    }

    public function generarResultado($a_data){
        $data = $a_data[0];
        $tipo = $a_data[1];

        $decoded_data = json_decode($data, true);

        $consultores = $decoded_data['consultores'];
        $fecha_inicio = $decoded_data['fecha_inicio'];
        $fecha_fin = $decoded_data['fecha_fin'];

        $array_consultores = explode(',', $consultores);

        $this->resultado = [];

        switch ($tipo) {
            case 'relatorio':
                foreach ($array_consultores as $consultor) {

                    $receitas = $this->get_receita_comissao($consultor, $fecha_inicio, $fecha_fin);

                    if($this->get_costo_fijo($consultor)){
                        $a_costo_fijo = (array) $this->get_costo_fijo($consultor);
                        $costo_fijo = $a_costo_fijo['brut_salario'];
                    }else{
                        $costo_fijo = 0;
                    }

                    foreach ($receitas as $key => $value) {
                        $this->resultado[$consultor][$key]['costo_fijo'] = $costo_fijo;
                        $this->resultado[$consultor][$key]['periodo'] = $value->periodo;
                        $this->resultado[$consultor][$key]['receita'] = $value->receita;
                        $this->resultado[$consultor][$key]['comissao'] = $value->comissao;
                        $this->resultado[$consultor][$key]['lucro'] = $value->receita - ($costo_fijo + $value->comissao);
                    }
                }
                $this->emitTo('table-result', 'loadData', $this->resultado);
            break;
            case 'barra':
                $costos_consultor = 0;
                foreach ($array_consultores as $key => $consultor) {

                    $this->resultado[] = $this->get_receita_consultor($consultor, $fecha_inicio, $fecha_fin);

                    if($this->get_costo_fijo($consultor)){
                        $a_costo_fijo = (array) $this->get_costo_fijo($consultor);
                        $costo_fijo = $a_costo_fijo['brut_salario'];
                        $costos_consultor += $costo_fijo;
                    }else{
                        $costo_fijo = 0;
                    }
                }
                $costo_final = $costos_consultor / count($array_consultores);

                $this->emitTo('graphic-result', 'loadDataGraphic', $this->resultado);
            break;
            case 'torta':
                foreach ($array_consultores as $consultor) {
                    $this->resultado[] = $this->get_receita_consultor_percentage($consultor, $fecha_inicio, $fecha_fin);
                }
                $this->emitTo('graphic-result', 'dataTortaLoaded', $this->resultado);
            break;
            default:
            break;
        }
    }
}
