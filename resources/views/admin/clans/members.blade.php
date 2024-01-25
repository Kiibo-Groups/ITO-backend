@extends('layouts.app')

@section('wrapper')

<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Miembros del clan <b>"{{ $Clan->name }}"</b></div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item" aria-current="page"><a href="{{ Asset(env('admin').'/clans') }}">Clanes</a></li>
                        <li class="breadcrumb-item active" aria-current="subpage">Miembros</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
 

        <div class="row ">
            <div class="col-xl-12 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table mb-0" style="width:100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Credencial</th>
                                        <th>Nombre</th>
                                        <th>Telefono</th>
                                        <th>email</th> 
                                        <th>Típo</th>
                                        <th style="text-align: right">Opciones</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    @foreach($data['data'] as $row)
                                    <tr>
                                        <td>
                                            @if ($row['dboy']['credential'] != NULL)
                                            <div style="background-image: url('{{ $row['dboy']['credential'] }}');background-position: center center;background-size: cover;width: 60px;height: 60px;border-radius: 2003px;"></div>
                                            @else 
                                            <div style="background-image: url('{{ asset('assets/images/errors-images/404-error.png') }}');background-position: center center;background-size: cover;width: 60px;height: 60px;border-radius: 2003px;"></div>
                                            @endif
                                        
                                        </td>
                                        <td style="vertical-align: middle;">{{ $row['name'] }}</td>
                                        <td style="vertical-align: middle;">{{ $row['dboy']['phone'] }}</td>
                                        <td style="vertical-align: middle;">{{ $row['dboy']['email'] }}</td> 
                                        <td style="vertical-align: middle;">{{ $row['type'] }}</td>

                                        <td width="20%" style="vertical-align: middle;text-align: right">
                                            <button class="btn btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Acciones
                                            </button>
                                            <ul class="dropdown-menu" style="margin: 0px; position: absolute; inset: 0px auto auto 0px; transform: translate(0px, 38px);" data-popper-placement="bottom-start">
                                                <li><button class="dropdown-item" type="button" data-toggle="tooltip" data-placement="top"    data-original-title="Delete This Entry"
                                                    onclick="deleteConfirm('{{ Asset($link."delete_member/".$row['group_id']."/".$row['dboy']['id']) }}')"><i class="lni lni-trash"></i>&nbsp;&nbsp;&nbsp; Eliminar Miembro </button></li>

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
