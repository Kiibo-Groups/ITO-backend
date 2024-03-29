@extends('layouts.app')
@section('wrapper')

<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Zonas</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Agregar Elemento</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="row ">
            <div class="col-lg-12 col-md-12 mx-auto">
                <div class="card">
                    <div class="card-body">
                        {!! Form::model($data, ['url' => [$form_url],'files' => true],['class' => 'col s12']) !!}
                            @csrf
                            @include('admin.zones.form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
