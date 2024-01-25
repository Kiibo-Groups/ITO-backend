@extends('layouts.app')

@section('title')
    Listado de Servicios de mantenimiento
@endsection

@section('wrapper')
    <div class="page-wrapper">
        <section class="pull-up">
            <div class="container">
                <div class="row ">
                    <div class="col-lg-7">
                        <div class="card-body">
                            @if (count($bonuses) > 0)
                                {!! Form::model($data, ['url' => [$form_url], 'method' => 'POST'], ['class' => 'col s12']) !!}
                                @csrf
                                <input type="hidden" name="dboy_id" value="{{ $data->id }}">
                                <div style="background:#fff;"
                                    class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
                                    <div class="col p-4 d-flex flex-column position-static">
                                        <label for="bonuses">Listado de Servicios de mantenimiento</label>
                                        <select name="bonuses_id" id="bonuses" class="form-select">
                                            <option value="">Seleccione...</option>
                                            @foreach ($bonuses as $item)
                                                <option value="{{ $item->id }}" data-km_meta="{{ $item->km_meta }}"
                                                    data-id="{{ $item->id }}"
                                                    >
                                                    {{ $item->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div style="background:#fff;padding-bottom: 50px;"
                                    class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
                                    <div class="col p-4 d-flex flex-column position-static">
                                        <label for="bonuses">Porcentaje de avance: <span id="avance">30%</span> </label>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 75%"
                                                aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col">
                                            <span id="folio" class="d-inline-block mb-2">
                                                
                                            </span>
                                        </div>
                                        <div class="col">
                                        <button type="submit" id="active" class="btn btn-md btn-primary"
                                            style="position: absolute;width: 200px;right: 25px;bottom: 10px;">Marcar como
                                            cobrado</button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div style="background:#fff;"
                                    class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
                                    <div class="col p-4 d-flex flex-column position-static">
                                        <strong class="d-inline-block mb-2 text-primary">Upps!!</strong>
                                        <h3 class="mb-0">No existen Servicios de mantenimiento</h3>
                                    </div>
                                </div>
                                <a href="../" class="btn btn-success btn-cta">Volver</a>
                            @endif
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card-body">
                            <div style="background:#fff;"
                                class="row g-0 border rounded overflow-hidden flex-md-row shadow-sm h-md-250 position-relative">
                                <div class="col p-4 d-flex flex-column position-static">
                                    <strong class="d-inline-block mb-2 text-primary">Staff ID #{{ $data->id }}</strong>
                                    <h3 class="mb-0">{{ $data->name }}</h3>
                                    <div class="mb-1 text-muted">Tel: {{ $data->phone }}</div>
                                    <p class="card-text mb-auto">Km Recorridos: {{ $getkm }}km</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div style="background:#fff;"
                                class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
                                <div class="col p-4 d-flex flex-column position-static">
                                    <strong class="d-inline-block mb-2">Servicios cobrados</strong>
                                    <ul>
                                        @foreach ($completedBonuses as $bonus)
                                            <li>{{ $bonus->bonus->title }} - {{ $bonus->created_at->format('d-m-Y') }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.18.0/js/md5.js"></script>
    <script>
        var selectBonuses = $('#bonuses');
        var progressBar = $('.progress-bar');
        var folio =$('#folio')
        var avanceSpan = $('#avance');
        var button = $('#active');
        button.prop('disabled', true);

        selectBonuses.on('change', function() {
            var selectedOption = $(this).find('option:selected');

            var kmMeta = selectedOption.data('km_meta');
            var dboy_id = {{ json_encode($data->id) }};
            var bonus_id = selectedOption.data('id');
            var kmrecorridos = {{ json_encode($getkm) }};
            var bonusDboy = bonus_id.toString() + dboy_id.toString();
            var md5Hash = md5(bonusDboy);
            var nFolio = md5Hash.substring(0, 5).toUpperCase();
            if (kmMeta === undefined) {

                var porcentaje = 0;
            } else {

                var porcentaje = ((kmrecorridos / kmMeta) * 100);
            }

            if (porcentaje > 100) {
                porcentaje = 100;
            }

            if (porcentaje < 100) {
                button.prop('disabled', true);
            } else {
                folio.html('<b>Folio: #</b>' + nFolio)
                button.prop('disabled', false);
            }

            progressBar.width(porcentaje + '%').attr('aria-valuenow', porcentaje);
            progressBar.text(porcentaje + '%');
            avanceSpan.text(porcentaje.toFixed(2) + '%');
        });
    </script>
@endsection
