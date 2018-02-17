<div class="modal fade" id="modal_mensaje" tabindex="-1" role="dialog" aria-labelledby="modal_mensaje_label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h2 class="modal-title" id="modal_mensaje_label"></h2>
            </div>
            <div class="modal-body" id='modal_mensaje_body'>
            </div>
            <div class="modal-footer" id='modal_mensaje_footer'>
	      <div class="row">
		<div class="col-sm-2 col-sm-offset-0">
		  <img src="{{ asset('images/img/TodosEn4_hapcar_logo.png') }}" class="img-responsive" id="imgmodallogo">
		</div>
		<div class="col-sm-2 col-sm-offset-7">
                  <button type="button" class="btn btn-default btn-modalmensaje" data-dismiss="modal">{{ Lang::get('maintemplate.labels.cerrar') }}</button>
		</div>
	      </div>
            </div>
        </div>
    </div>
</div>
<script>
    var tituloMensajes = "{{ Lang::get('maintemplate.titulos.mensajes') }}";
@if (Session::has("message"))
    $(document).ready(function() {
        alerta("{{ Session::pull('message') }}");
    });
@endif
</script>
