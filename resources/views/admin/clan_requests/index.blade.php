@extends('layouts.app')
@section('wrapper')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Solicitudes</div>
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
                {{--<a href="{{ Asset($link.'add') }}" >
                    <button type="button" class="btn btn-success px-3 radius-10">Agregar Bono</button>
                </a>--}}
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table mb-0" style="width:100%">
                                <thead class="table-dark">
                                <tr>
                                    <th>Conductor</th>
                                    <th>Nombre del clan</th>
                                    <th>Estado</th>
                                    <th style="text-align:right;">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $row)
                                    <tr>
                                        <td width="17%">
                                            {{ $row->dboy->name ?? 'Eliminado' }} 
                                        </td>
                                        <td width="17%">
                                            {{ $row->clan->name ?? 'Eliminado'  }} 
                                        </td>
                                        <td width="7%">
                                            {{ $row->status }}
                                        </td> 
                                        <td width="10%" style="text-align: right">
                                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Acciones
                                            </button>
                                            <ul class="dropdown-menu" style="margin: 0px; position: absolute; inset: 0px auto auto 0px; transform: translate(0px, 38px);" data-popper-placement="bottom-start">
                                               
                                            @if($row->status == 'Pendiente')
                                            <li> <button class="dropdown-item" data-toggle="tooltip" data-placement="top" data-original-title="Aceptar Solicitud" onclick="confirmAlert('{{ Asset($link.'status/accept/'.$row->id) }}')">
                                                <i class="lni lni-checkmark"></i>&nbsp;&nbsp;&nbsp;
                                                Aceptar
                                            </button>
                                            </li>
                                                <li> <button class="dropdown-item" data-toggle="tooltip" data-placement="top" data-original-title="Rechazar Solicitud" onclick="confirmAlert('{{ Asset($link.'status/reject/'.$row->id) }}')">
                                                    <i class="lni lni-close"></i>&nbsp;&nbsp;&nbsp;
                                                    Rechazar
                                                </button>
                                                </li>
                                          
                                            @endif
                                                <li><button type="button" class="dropdown-item" data-toggle="tooltip" data-placement="top" data-original-title="Eliminar Solicitud" onclick="deleteConfirm('{{ Asset($link."delete/".$row->id) }}')"><i class="lni lni-trash"></i>&nbsp;&nbsp;&nbsp; Eliminar </button></li>
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

@endsection

