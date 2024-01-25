@extends('layouts.app')

@section('wrapper')

<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Gestion de clanes</div>
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
                    <button type="button" class="btn btn-success px-3 radius-10">Agregar Clan</button>
                </a>
            </div>
        </div>

        <div class="row ">
            <div class="col-xl-12 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table mb-0" style="width:100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Imagen</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Líder</th>
                                        <th>Likes</th>
                                        <th>Miembros</th>
                                        <th style="text-align: right">Opciones</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    @foreach($data['data'] as $row)
                                    <tr>
                                        <td width="10%">
                                            <img src="{{ isset($row['image']) ? $row['image'] : asset('upload/clans/no_image.jpg') }}" alt="{{ $row['name'] }}" style="width: 60px;height: 60px;background-position: center center;background-size: contain;border-radius: 2003px;">
                                        </td>
                                        <td width="20%">{{ $row['name'] }}</td>
                                        <td width="20%">{{ $row['description'] }}</td>
                                        <td width="20%">{{ $row['created_by']['name'] }}</td>
                                        <td width="20%">{{ $row['like'] }}</td>
                                        <td width="20%">{{ $row['members'] }}</td>

                                        <td width="20%" style="text-align: right">
                                            <button class="btn btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Acciones
                                            </button>
                                            <ul class="dropdown-menu" style="margin: 0px; position: absolute; inset: 0px auto auto 0px; transform: translate(0px, 38px);" data-popper-placement="bottom-start">
                                                <li><a href="{{ Asset($link.'view/'.$row['id']) }}" class="dropdown-item"><i class="lni lni-eye"></i> &nbsp;&nbsp;&nbsp; Ver Miembros</a></li>
                                                <li><a href="{{ Asset($link.$row['id'].'/edit') }}" class="dropdown-item"><i class="lni lni-pencil"></i> &nbsp;&nbsp;&nbsp; Editar</a></li>
                                                <li><button class="dropdown-item" type="button" data-toggle="tooltip" data-placement="top"    data-original-title="Delete This Entry"
                                                    onclick="deleteConfirm('{{ Asset($link."delete/".$row['id']) }}')"><i class="lni lni-trash"></i>&nbsp;&nbsp;&nbsp; Eliminar </button></li>

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
