<h1 style="font-size: 20px">Información del bono</h1>
<div class="card py-3 m-b-30">
    <div class="card-body">
        <div class="row g-3" style="padding-bottom: 1rem;">
            <div class="form-group col-md-6">
                <label for="title">Titulo</label>
                <input type="text" name="title" id="title" value="{{ $data->title }}" class="form-control">
            </div>

            <div class="form-group col-md-6">
                <label for="descripcion">Descripción</label>
                <input type="text" name="description" id="description" value="{{ $data->description }}"
                    class="form-control">
            </div>
        </div>
        <div class="row g-3" style="padding-bottom: 1rem;">
            <div class="form-group col-md-6">
                <label for="status">Status</label>
                <select name="status" class="form-select">
                    <option value="0" @if ($data->status == 0) selected @endif>Inactivo</option>
                    <option value="1" @if ($data->status == 1) selected @endif>Activo</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="image">Imagen</label>
                <input type="file" name="image" id="image" class="form-control"
                    @if (!$data->image) required="required" @endif>
            </div>
        </div>
    </div>
</div>

<h1 style="font-size: 20px">Kilometraje objetivo</h1>
<div class="card py-3 m-b-30">
    <div class="card-body">
        <div class="form-group col-md-6">
            <label for="km_meta">km</label>
            <input type="number" name="km_meta" id="km_meta" step="0.01" value="{{ $data->km_meta }}" class="form-control">
        </div>
    </div>
</div> 
