<div class="mt-4" id="seccion_tabla">
    @if ($rows)
        <h3 class="mt-6 text-center font-bold">Resultado</h3>
        @foreach ($rows as $key => $row)
            <div class="overflow-x-auto relative shadow-md sm:rounded-lg mt-4">
                <div class="my-4">
                    <div class="p-4">
                        <h5 class="font-bold uppercase text-gray-600">{{ $key }}</h5>
                    </div>
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3 px-6">Período</th>
                                <th scope="col" class="py-3 px-6">Receita Líquida</th>
                                <th scope="col" class="py-3 px-6">Custo Fixo</th>
                                <th scope="col" class="py-3 px-6">Comissão</th>
                                <th scope="col" class="py-3 px-6">Lucro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($row as $value)
                                <tr>
                                    <td class="py-4 px-6">{{ $value['periodo'] }}</td>
                                    <td class="py-4 px-6">R$ {{ $value['receita'] }}</td>
                                    <td class="py-4 px-6">R$ {{ $value['costo_fijo'] }}</td>
                                    <td class="py-4 px-6">R$ {{ $value['comissao'] }}</td>
                                    <td class="py-4 px-6">R$ {{ $value['lucro'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
