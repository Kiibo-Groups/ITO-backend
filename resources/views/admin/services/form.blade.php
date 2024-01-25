

@foreach($data as $row)
<div class="row g-3" style="padding-bottom: 1rem;">

	<div class="form-group col-md-12">
		<label>Usuario</label>
		<input type="text" class="form-control" value="{{$res->viewUserComm($row->id)}}" readonly="readonly">
	</div>
</div>

<div class="row g-3" style="padding-bottom: 1rem;">
	<div class="form-group col-md-6">
		<label for="d_charges">Costos del viaje</label>
		<input type="text" name="d_charges" class="form-control" value="${{ number_format($row->d_charges,2) }}" readonly="readonly">
	</div>

	<div class="form-group col-md-6">
		<label for="extra_charge">Costos extras</label>
		<input type="text" name="extra_charge" class="form-control" value="${{ number_format($row->extra_charge,2) }}" readonly="readonly">
	</div>

</div>

<div class="row g-3" style="padding-bottom: 1rem;">
	<div class="form-group col-md-6">
		<label for="extra_charge">IVA</label>
		<input type="text" class="form-control" value="${{ number_format($row->iva,2) }}" readonly="readonly">
	</div>

	<div class="form-group col-md-6">
		<label for="extra_charge">ISR</label>
		<input type="text" class="form-control" value="${{ number_format($row->isr,2) }}" readonly="readonly">
	</div>
</div>

<div class="row g-3" style="padding-bottom: 1rem;">
	<div class="form-group col-md-6">
		<label for="d_charges">Subtotal</label>
		<input type="text" name="d_charges" class="form-control" value="${{ $row->subtotal === 0.00 ? number_format($row->total,2) : number_format($row->subtotal,2)  }}" readonly="readonly">
	</div>

	<div class="form-group col-md-6">
		<label for="total">Total a pagar</label>
		<input type="text" name="total" class="form-control" value="${{ number_format(($row->total),2) }}" readonly="readonly">
	</div>
</div>

<div class="row g-3" style="padding-bottom: 1rem;">

	<div class="form-group col-md-6">
		<label for="address_origin">Punto de origen</label>
		<input type="text" name="address_origin" id="address_origin" class="form-control" value="{{$row->address_origin}}" readonly="readonly">
	</div>

	<div class="form-group col-md-6">
		<label for="address_destin">Punto de destino</label>
		<input type="text" name="address_destin" id="address_destin" class="form-control" value="{{$row->address_destin}}" readonly="readonly">
	</div>

</div>

<div class="row g-3" style="padding-bottom: 1rem;">

	<div class="form-group col-md-6">
		<label for="first_instr">Instrucciones de recolección</label>
		<textarea name="first_instr" id="first_instr" class="form-control" cols="5" rows="5">{{$row->first_instr}}</textarea>
	</div>

	<div class="form-group col-md-6">
		<label for="second_instr">Instrucciones de Entrega</label>
		<textarea name="second_instr" id="second_instr" class="form-control" cols="5" rows="5">{{$row->second_instr}}</textarea>
	</div>

</div>

@endforeach
