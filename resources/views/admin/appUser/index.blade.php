@extends('layouts.app')
@section('wrapper')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Usuarios Registrados</div>
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
            <div class="col-xl-11 mx-auto" style="text-align: right;">
                <a href="{{ Asset($link.'add') }}" >
                    <button type="button" class="btn btn-success px-3 radius-10">Agregar Usuario</button>
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
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Telefono</th> 
                                        <th>Saldo</th>
                                        <th>Viajes</th>
                                        <th>Estado</th>
                                        <th>Eliminar</th>
                                    </tr>

                                </thead>
                                <tbody>

                                    @foreach($data as $row)

                                    <tr>
                                        <td width="15%">{{ $row->name }}</td>
                                        <td width="20%">{{ $row->email }}</td>
                                        <td width="10%">{{ $row->phone }}</td> 
                                        <td width="10%">
                                            <div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
                                                <b>${{ $row->saldo }}</b>
                                            </div>
                                        </td>
                                        <td width="10%">
                                            <div class="badge rounded-pill text-info bg-light-success p-2 text-uppercase px-3">
                                                <b>{{ $row->countOrder($row->id) }}</b>
                                            </div>
                                        </td>
                                        <td width="10%">
                                            @if($row->status == 0)
                                            <button type="button" class="btn btn-sm m-b-15 ml-2 mr-2 btn-success"
                                                    onclick="confirmAlert('{{ Asset($link.'status/'.$row->id) }}')">Activo</button>
                                            @else
                                            <button type="button" class="btn btn-sm m-b-15 ml-2 mr-2 btn-danger"
                                                    onclick="confirmAlert('{{ Asset($link.'status/'.$row->id) }}')">Bloqueado</button>
                                            @endif
                                        </td>
                                        <td width="10%">
                                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Acciones
                                            </button>
                                            <ul class="dropdown-menu" style="margin: 0px; position: absolute; inset: 0px auto auto 0px; transform: translate(0px, 38px);" data-popper-placement="bottom-start">
                                                <li><a href="{{ Asset($link.$row->id.'/edit') }}" class="dropdown-item"><i class="lni lni-pencil"></i> &nbsp;&nbsp;&nbsp; Editar</a></li>
                                                <li>    
                                                    <button type="button" class="dropdown-item "
                                                        onclick="confirmAlert('{{ Asset($link.'trash/'.$row->id) }}')">
                                                        <i class="lni lni-trash">&nbsp;&nbsp;&nbsp; Eliminar </i>
                                                    </button>
                                                </li>

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

        <div class="row">
            {!! $data->links() !!}
        </div>
    </div>
</div>
@endsection
 