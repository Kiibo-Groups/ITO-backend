@extends('layouts.app')

@section('wrapper')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Ofertas</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Listado</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="row">
            <div class="col-xl-12 mx-auto" style="text-align: right;">
                <a href="{{ Asset($link.'add') }}" >
                    <button type="button" class="btn btn-success px-3 radius-10">Agregar Oferta</button>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table table-striped table-borderless" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Imagen</th>
                                        <th>Codigo Cupon</th>
                                        <th>Valor Descuento</th>
                                        <th>Tipo</th>
                                        <th>Canjeado</th>
                                        <th>Estado</th>
                                        <th style="text-align: right">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $row)
                                    <tr>
                                        <td width="17%">
                                            <img src="{{ Asset('upload/offers/'.$row->img) }}" style="max-width:80%;">
                                        </td>
                                        <td>{{ $row->code }}</td>
                                        <td>
                                            @if($row->type == 0) {{ $row->value }}% @else {{ $row->value }} @endif
                                        </td>
                                        <td>
                                            @if($row->unique_user == 0)
                                            Ilimitado
                                            @else 
                                            1 Solo uso
                                            @endif    
                                        </td>
                                        <td>
                                            {{ $offer->getOffer($row->id)['count'] }}
                                        </td>
                                        <td>
                                            @if($row->status == 0)
                                                <button type="button" class="btn btn-sm m-b-15 ml-2 mr-2 btn-success" onclick="confirmAlert('{{ Asset($link.'status/'.$row->id) }}')">Activo</button>
                                            @else
                                                <button type="button" class="btn btn-sm m-b-15 ml-2 mr-2 btn-danger" onclick="confirmAlert('{{ Asset($link.'status/'.$row->id) }}')">Inactivo</button>
                                            @endif
                                        </td>
                                        <td width="15%" style="text-align: right">
                                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acciones
                                            </button>
                                            <ul class="dropdown-menu" style="margin: 0px; position: absolute; inset: 0px auto auto 0px; transform: translate(0px, 38px);" data-popper-placement="bottom-start">
                                                <li><a href="{{ Asset($link.$row->id.'/edit') }}" class="dropdown-item"><i class="lni lni-pencil"></i> &nbsp;&nbsp;&nbsp; Editar</a></li>
                                                <li><button class="dropdown-item" type="button" data-toggle="tooltip" data-placement="top"    data-original-title="Delete This Entry"
                                                    onclick="deleteConfirm('{{ Asset($link."delete/".$row->id) }}')"><i class="lni lni-trash"></i>&nbsp;&nbsp;&nbsp; Eliminar </button>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
