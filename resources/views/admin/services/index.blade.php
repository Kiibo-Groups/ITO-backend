@extends('layouts.app')
@section('wrapper')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Servicios</div>
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
                <a href="{{ Asset($link.'?export=1') }}" >
                    <button type="button" class="btn btn-success px-3 radius-10">Reporte Excel</button>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-11 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table mb-0" style="width:100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Conductor</th>
                                        <th>Total</th>
                                        <th>Viaje</th>
                                        <th>Comisión</th>
                                        <th>Tipo de pago</th>
                                        <th style="text-align:right;">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $row)
                                        <tr>
                                            <td width="3%">
                                                #{{ $row->id }}
                                            </td>
                                            <td width="12%">
                                            {{$row->name_user}}
                                            </td>
                                            <td width="10%">
                                                <a href="http://maps.google.com/?q={{ $row->address_origin }}" target="_blank">
                                                    {{ substr($row->address_origin,0,25) }} ...
                                                </a>
                                            </td>
                                            <td width="10%">
                                                <a href="http://maps.google.com/?q={{ $row->address_destin }}" target="_blank">
                                                    {{ substr($row->address_destin,0,25) }} ...
                                                </a>
                                            </td>
                                            <td width="9%"> {{ $comm_f->viewDboyComm($row->id) }}</td>
                                            <td width="10%">
                                                <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                    <b>${{ $row->total }}</b>
                                                </div>
                                            </td>
                                            <td width="10%">
                                                <div class="badge rounded-pill text-info bg-light-info p-2 text-uppercase px-3">
                                                    <b> ${{ $row->d_charges }}</b>
                                                </div>
                                            </td>
                                            <td width="10%">
                                            <div class="badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3">
                                                <b>${{ $row->price_comm }}</b>
                                            </div>
                                            </td>
                                            <td width="17%">
                                                @if($row->payment_method == 1)
                                                    Efectivo
                                                @elseif($row->payment_method == 2)
                                                    Saldo Disponible
                                                @elseif($row->payment_method == 3)
                                                    Tarjeta
                                                @else 
                                                    Efectivo
                                                @endif
                                            </td>
                                        
                                            <td width="8%" style="text-align: right">
                                                @if($row->status == 1)
                                                <span style="color:green;">Pedido Aceptado</span>
                                                @elseif($row->status == 4.5)
                                                <span style="color:green;">Pedido en ruta de entrega</span>
                                                @endif
                                                @include('admin.services.action')
                                                
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
