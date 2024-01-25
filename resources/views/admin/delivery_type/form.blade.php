<h1 style="font-size: 20px">Información del vehiculo</h1>
<div class="card py-3 m-b-30">
    <div class="card-body">
        <div class="row g-3" style="padding-bottom: 1rem;">
            <div class="form-group col-md-6">
                <label for="type">Nombre del Tipo de vehiculo</label>
                <input type="text" name="type" id="type" value="{{$data->type}}" class="form-control">
            </div>

            <div class="form-group col-md-6">
                <label for="name">Nombre para mostrar</label>
                <input type="text" name="name" id="name" value="{{$data->name}}" class="form-control">
            </div>
        </div>  
        <div class="row g-3" style="padding-bottom: 1rem;">
            <div class="form-group col-md-6">
                <label for="status">Status</label>
                <select name="status" class="form-control">
                    <option value="0" @if($data->status == 0) selected @endif>Activo</option>
                    <option value="1" @if($data->status == 1) selected @endif>Inactivo</option>
                </select>
            </div> 
            <div class="form-group col-md-6">
                <label for="icon">Icono</label>
                <input type="file" name="icon" id="icon" class="form-control"  @if(!$data->icon) required="required" @endif>
            </div> 
        </div> 

        <div class="row g-3" style="padding-bottom: 1rem;">
            <div class="form-group col-md-6">
                <label for="status">Tipo vehículo</label>
                <select name="vehicle_type" class="form-control">
                    <option value="Normal"{{ $data->vehicle_type == 'Normal' ? ' selected' : '' }}>Normal</option>
                    <option value="Random"{{ $data->vehicle_type == 'Random' ? ' selected' : '' }}>Random</option>
                </select>
            </div>
        </div>
    </div>
</div>

<h1 style="font-size: 20px">Cargos de comisión para el conductor</h1>
<div class="card py-3 m-b-30">
    <div class="card-body">
        <div class="row g-3" style="padding-bottom: 1rem;">
            <div class="form-group col-md-6">
                <label for="type_comm">Tipo de comisión</label>
                <select name="type_comm" class="form-control">
                    <option value="0" @if($data->type_comm == 0) selected @endif>Valor Fijo</option>
                    <option value="1" @if($data->type_comm == 1) selected @endif>valor en %</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="comm">Valor de la comisión</label>
                <input type="text" name="comm" id="comm" value="{{$data->comm}}" class="form-control">
            </div>
        </div>
    </div>
</div>

