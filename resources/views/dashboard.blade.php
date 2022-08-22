<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Agence Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center">
                        <h3 class="uppercase font-bold">Filtrar por periodo</h3>
                        <div class="flex justify-between items-end mt-3 sm:mt-0">
                            <div class="flex-1 mr-2 sm:mr-0">
                                <label for="id_fecha_ini" class="block mb-2 text-sm text-gray-800">Fecha Inicio</label>
                                <input type="date" id="id_fecha_ini"
                                    class="focus:border-blue-500 appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none">
                            </div>
                            <div class="flex-1 ml-2">
                                <label for="id_fecha_ini" class="block mb-2 text-sm text-gray-800">Fecha Fin</label>
                                <input type="date" id="id_fecha_fin"
                                    class="focus:border-blue-500 appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none">
                            </div>
                        </div>
                    </div>
                    <h3 class="my-6 uppercase font-bold">Listado de Consultores</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 sm:gap-3">
                        <div class="bg-white rounded-lg border border-gray-200 w-fit text-gray-900" id="listado_consultores">
                            @foreach ($consultores as $consultor)
                                <button aria-current="true" type="button"
                                    class="text-left px-6 py-2 border-b border-gray-200 w-full rounded-t-lg text-gray-800 hover:bg-blue-600 hover:text-white cursor-pointer consultor__item"
                                    data-usuario="{{ $consultor->co_usuario }}"
                                    data-usuario-nombre="{{ $consultor->no_usuario }}">
                                    {{ $consultor->no_usuario }}
                                </button>
                            @endforeach
                        </div>
                        <div class="my-4 sm:my-0 md:my-0 lg:my-0 xl:my-0 w-full bg-white rounded-lg border border-gray-200 text-gray-900 h-min sm:h-full"
                            id="listado_consultores_seleccionados">
                            <input type="hidden" id="consultores_seleccionados">
                        </div>
                        @livewire('acciones-data')
                    </div>
                    @livewire('table-result')
                    @livewire('graphic-result')
                </div>
            </div>
        </div>
    </div>
    @section('js')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            const list_1 = document.querySelector('#listado_consultores'),
                list_2_selected = document.querySelector('#listado_consultores_seleccionados'),
                consultores_seleccionados = document.querySelector('#consultores_seleccionados'),
                fecha_inicio = document.querySelector('#id_fecha_ini'),
                fecha_fin = document.querySelector('#id_fecha_fin'),
                consultores_array = new Set();

            function moveElement(list, element, selected = false) {
                if (element.classList.contains('consultor__item')) {
                    list.appendChild(element.cloneNode(true))
                    element.remove()

                    if (selected) {
                        consultores_array.add(element.cloneNode(true).dataset.usuario)
                        consultores_seleccionados.value = [...consultores_array]
                    } else {
                        consultores_array.delete(element.cloneNode(true).dataset.usuario)
                        consultores_seleccionados.value = [...consultores_array]
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                list_1.addEventListener('click', e => moveElement(list_2_selected, e.target, true));

                list_2_selected.addEventListener('click', e => moveElement(list_1, e.target));
            })


            document.querySelectorAll('.acciones').forEach(button => {
                button.addEventListener('click', e => {
                    if(consultores_seleccionados.value === ""){
                        alert('Debes seleccionar al menos un consultor para realizar esta accion')
                        return
                    }
                    const data = {
                        consultores: consultores_seleccionados.value,
                        fecha_inicio: fecha_inicio.value,
                        fecha_fin: fecha_fin.value
                    }

                    switch (e.target.dataset.tipo) {
                        case 'relatorio':
                            document.querySelector('#seccion_grafico').style.display = 'none';
                            window.livewire.emit('generarResultado', [JSON.stringify(data), 'relatorio'])
                        break;
                        case 'grafico':
                            document.querySelector('#seccion_tabla').style.display = 'none';
                            if(document.querySelector('#grafico_torta_consultores')){
                                document.querySelector('#grafico_torta_consultores').remove()
                            }
                            window.livewire.emit('generarResultado', [JSON.stringify(data), 'barra'])
                        break;
                        case 'torta':
                            document.querySelector('#seccion_tabla').style.display = 'none';
                            if(document.querySelector('#grafico_consultores')){
                                document.querySelector('#grafico_consultores').remove()
                            }
                            window.livewire.emit('generarResultado', [JSON.stringify(data), 'torta'])
                        break;
                        default:
                        break;
                    }
                })
            });

            window.livewire.on('dataGraphicLoaded', response => {
                const series = response.map(a_item => {
                    let sueldo = [], nombre;
                    a_item.map(item => {
                        nombre = item.consultor
                        sueldo.push(item.receita.toFixed(2))
                    })
                    return {
                        name: nombre,
                        data: sueldo
                    }
                })

                const meses = response.map(a_item => {
                    let mes = [];
                    a_item.map(item => mes.push(item.mes))
                    return mes;
                })

                const categories = [...new Set([...meses.flat()])]

                var options = {
                    series,
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories
                    },
                    yaxis: {
                        title: {
                            text: 'R$'
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return "R$ " + val
                            }
                        }
                    }
                };

                var chart = new ApexCharts(document.querySelector("#grafico_consultores"), options);
                chart.render();
            })

            window.livewire.on('tortaLoaded', response => {
                const a_series = response.map(a_item => {
                    let receita = [];
                    a_item.map(item => receita.push(Number(item.receita.toFixed(2))))
                    return receita
                })

                const a_labels = response.map(a_item => {
                    let consultores = [];
                    a_item.map(item => consultores.push(item.consultor))
                    return consultores
                })

                const series = a_series.flat()
                const labels = a_labels.flat()

                var options = {
                    series,
                    chart: {
                        width: '50%',
                        type: 'pie',
                    },
                    labels,
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 100
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };

                var chart = new ApexCharts(document.querySelector("#grafico_torta_consultores"), options);
                chart.render();
            })
        </script>
    @endsection
</x-app-layout>
