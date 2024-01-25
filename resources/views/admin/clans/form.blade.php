
<div class="row g-3" style="padding-bottom: 1rem;">
    <div class="form-group col-md-6">
        <label for="inputEmail6">Nombre</label>
        <input type="text" value="{{ $data->name }}" class="form-control" id="inputEmail6" name="name" required="required">
    </div>

    <div class="form-group col-md-6">
        <label for="created_by">Conductor Adminstrador</label>
        <select name="created_by" id="created_by" class="form-select">
            @foreach ($dboys as $dby)
            <option value="{{ $dby->id }}" @if ($dby->id == $data->created_by) selected @endif >{{ $dby->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row g-3">
    <div class="form-group col-md-12">
        <label for="description">Descripción</label>
        <textarea type="text" class="form-control" id="description" name="description" required="required">{!! $data->description !!}</textarea>
    </div>
</div>

<div class="row g-3">
    <div class="form-group col-md-12">
    <label for="image">Imagen</label>
    <input type="file" name="image" id="image" class="form-control">
    </div>
</div>
</div>

<button type="submit" class="btn btn-success btn-cta">Guardar Cambios</button>


