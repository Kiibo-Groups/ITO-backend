
<div class="row g-3" style="padding-bottom: 1rem;">
    <div class="form-group col-md-6">
        <label for="inputEmail6">Nombre</label>
        <input type="text" value="{{ $data->name }}" class="form-control" id="inputEmail6" name="name" required="required">
    </div>

    <div class="form-group col-md-6">
        <label for="inputEmail6">Usuario</label>
        <input type="text" value="{{ $data->username }}" class="form-control" id="inputEmail6" name="username" required="required">
    </div>
</div>

<div class="row g-3" style="padding-bottom: 1rem;">
    <div class="form-group col-md-6">
        @if($data->id)
            <label for="inputEmail6">Cambiar Contraseña <small>(Solo si deseas hacerlo)</small> </label>
            <input type="password" name="password" class="form-control">
        @else
        <label for="inputEmail6">Contraseña</label>
        <input type="password" name="password" class="form-control" required="required">
        @endif
    </div>

    <div class="form-group col-md-6">
        <label for="inputEmail6">Asignar Permisos</label>
        <select name="perm[]" class="multiple-select" data-placeholder="Choose anything" multiple="multiple">
            @foreach(DB::table('perm')->get() as $p)
            <option value="{{ $p->name }}" @if(in_array($p->name,$array)) selected @endif>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row g-3" style="padding-bottom: 1rem;">
    <div class="form-group col-md-6">
        <label for="inputEmail6">Ciudad</label>
        <select name="city_id" class="form-select" required="required">
            <option value="">Select City</option>
            @foreach($citys as $city)
                <option value="{{$city->id}}" @if($data->city_id == $city->id) selected @endif>{{$city->name}}</option>
            @endforeach
        </select>
    </div>
</div>

<button type="submit" class="btn btn-success btn-cta">Guardar Cambios</button>


