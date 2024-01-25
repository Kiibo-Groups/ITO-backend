

<h1 style="font-size: 20px">Información del conductor</h1>
<div class="card py-3 m-b-30">
    <div class="card-body">
		<div class="row g-3" style="padding-bottom: 1rem;">
			<div class="form-group col-md-6">
				<label for="name">Nombre</label>
				<input type="text" name="name" class="form-control" value="{{$data->name}}">
			</div>
			<div class="form-group col-md-6">
				<label for="city_id">Ciudad</label>
				<select name="city_id" id="city_id" class="form-select" required="required">
				<option value="">Select City</option>
				@foreach($citys as $city)
				<option value="{{ $city->id }}" @if($data->city_id == $city->id) selected @endif>{{ $city->name }}</option>
				@endforeach
				</select>
			</div>
		</div>

		<div class="row g-3" style="padding-bottom: 1rem;">
			<div class="form-group col-md-6">
				<label for="email">Email (This will be username)</label>
				<input type="email" id="email" name="email" class="form-control" value="{{$data->email}}" required>
			</div>

			<div class="form-group col-md-6">
				<label for="phone">Telefono</label>
				<input type="text" name="phone" class="form-control" value="{{$data->phone}}" required>
			</div>
		</div>

		<div class="row g-3" style="padding-bottom: 1rem;">
			<div class="form-group col-md-6">
				<label for="rfc">RFC/ID</label>
				<input type="text" id="rfc" name="rfc" value="{{$data->rfc}}" required class="form-control">
			</div>

			<div class="form-group col-md-6">
				<label for="type_driver">Tipo de conductor</label>
				<select name="type_driver" id="type_driver" class="form-select">
					@foreach($type_delivery as $type_driver)
						<option value="{{$type_driver->id}}" @if($type_driver->id == $data->type_driver) selected @endif>{{$type_driver->type}}</option>
					@endforeach
				</select>
			</div>
		</div>

		<div class="row g-3" style="padding-bottom: 1rem;">
			<div class="form-group col-md-6">
				<label for="status">Estado</label>
				<select name="status" id="status" class="form-control">
					<option value="0" @if($data->status == 0) selected @endif>Activo</option>
					<option value="1" @if($data->status == 1) selected @endif>Inactivo</option>
				</select>
			</div>
			
			<div class="form-group col-md-6">
				@if($data->id)
					<label for="pass_new">Cambiar Contraeña</label>
					<input type="password" id="pass_new" name="password" class="form-control">
				@else
					<label for="pass">Contraseña</label>
					<input type="password" id="pass" name="password" class="form-control" required="required">
				@endif
			</div>
		</div>
		<div class="row g-3" style="padding-bottom: 1rem;">
			<div class="form-group col-md-6">
				<label for="can_make_calls">Habilitar llamadas</label>
				<select name="can_make_calls" id="can_make_calls" class="form-control">
					<option value="1" @if($data->can_make_calls == 1) selected @endif>Activo</option>
					<option value="0" @if($data->can_make_calls == 0) selected @endif>Inactivo</option>
				</select>
			</div>
		</div>
	</div>
</div>

<h1 style="font-size: 20px">Información del vehiculo</h1>
<div class="card py-3 m-b-30">
    <div class="card-body">
        <div class="row g-3" style="padding-bottom: 1rem;">
            <div class="form-group col-md-6">
				<label for="type_edriver">Tipo de vehiculo</label>
				<select name="type_edriver" id="type_edriver" class="form-control" @if(isset($data->id) && $data->disabled) disabled @endif>
					<option value="0" @if($data->type_edriver == 0) selected @endif>4 Pasajeros</option>
					<option value="1" @if($data->type_edriver == 1) selected @endif>+4 Pasajeros</option>
				</select>
            </div>
            <div class="form-group col-md-6">
                <label for="max_range_km">Rango máximo de entrega</label>
                <input type="number" name="max_range_km" id="max_range_km" value="{{$data->max_range_km}}" class="form-control" @if(isset($data->id) && $data->disabled) disabled @endif>
            </div>
        </div>

        <div class="row g-3" style="padding-bottom: 1rem;">
            <div class="form-group col-md-6">
                <label for="brand">Marca</label>
                <input type="text" name="brand" id="brand" value="{{$data->brand}}" class="form-control" @if(isset($data->id) && $data->disabled) disabled @endif>
            </div>

            <div class="form-group col-md-6">
                <label for="model">Modelo</label>
                <input type="text" name="model" id="model" value="{{$data->model}}" class="form-control" @if(isset($data->id) && $data->disabled) disabled @endif
            </div>
        </div>

        <div class="row g-3" style="padding-bottom: 1rem;">
            <div class="form-group col-md-4">
                <label for="color">Color</label>
                <input type="text" name="color" id="color" value="{{$data->color}}" class="form-control" @if(isset($data->id) && $data->disabled) disabled @endif>
            </div>

            <div class="form-group col-md-4">
                <label for="number_plate">Placas</label>
                <input type="text" name="number_plate" id="number_plate" value="{{$data->number_plate}}" class="form-control"@if(isset($data->id) && $data->disabled) disabled @endif>
            </div>
            
            <div class="form-group col-md-4">
                <label for="passenger">Número de pasajeros</label>
                <input type="number" name="passenger" id="passenger" value="{{$data->passenger}}" class="form-control" @if(isset($data->id) && $data->disabled) disabled @endif>
            </div>
        </div>

        <div class="row g-3" style="padding-bottom: 1rem;">
            <div class="form-group col-md-6">
                <label for="licence">Licencia de conducir</label>
                <input type="file" name="licence" id="licence" class="form-control"  @if(!$data->id) required="required" @if(isset($data->id) && $data->disabled) disabled @endif>
            </div>

            <div class="form-group col-md-6">
                <label for="carnet">Tarjeta de circulación / INE</label>
                <input type="file" name="carnet" id="carnet" class="form-control"  @if(!$data->id) required="required" @if(isset($data->id) && $data->disabled) disabled @endif>
            </div>
        </div>
    </div>
</div>