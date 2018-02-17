/*
 Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
CKEDITOR.addTemplates("default",{imagesPath:CKEDITOR.getUrl(CKEDITOR.plugins.getPath("templates")+"templates/images/"),
	templates:[
	{
		title:"Plan de acción - Descripción",
		image:"grimorum.gif",
		description:"Datos que se agregan en el plan de acción a la descripción de un poryecto",
		html:'<hr><h2><strong>PLAN DE ACCIÓN</strong></h2><p><strong>Etapas:</strong><br>Aquí van las etapas de la metodología NOVI que se van a realizar</p><p><strong>Enfoque:</strong><br>Aquí va el enfoque</p>'
	},
	{
		title:"Plan de acción - Objetivos/Metas",
		image:"grimorum.gif",
		description:"Datos que se agregan en el plan de acción al campo de Objetivos/Metas de un poryecto",
		html:'<hr><h2><strong>PLAN DE ACCIÓN</strong></h2><p><strong>Meta:</strong><br>Aquí va la meta del cliente</p><p><strong>Expectativa:</strong><br>Aquí va la expectativa del cliente sobre el rol de Grimorum</p>'
	},
	{
		title:"Reunión de inicio - Equipo",
		image:"grimorum.gif",
		description:"Agregar el equipo de trabajo en la reunión de inicio a la descripción de un poryecto",
		html:'<hr><h2><strong>Equipo de trabajo</strong></h2><p><strong>Líder:</strong>Aquí va el nombre del líder<br><strong>Core:</strong>Aquí van los prototipadores que hacen parte del core del proyecto<br><strong>Apoyo:</strong>Aquí van los prototipadores que hacen parte del equipo de apoyo para del proyecto</p>'
	},
	{
		title:"Image and Title",
		image:"template1.gif",
		description:"One main image with a title and text that surround the image.",
		html:'<h3><img style="margin-right: 10px" height="100" width="100" align="left"/>Type the title here</h3><p>Type the text here</p>'
	},
	{
		title:"Strange Template",
		image:"template2.gif",
		description:"A template that defines two colums, each one with a title, and some text.",
		html:'<table cellspacing="0" cellpadding="0" style="width:100%" border="0"><tr><td style="width:50%"><h3>Title 1</h3></td><td></td><td style="width:50%"><h3>Title 2</h3></td></tr><tr><td>Text 1</td><td></td><td>Text 2</td></tr></table><p>More text goes here.</p>'
	},
	{
		title:"Text and Table",
		image:"template3.gif",
		description:"A title with some text and a table.",
		html:'<div style="width: 80%"><h3>Title goes here</h3><table style="width:150px;float: right" cellspacing="0" cellpadding="0" border="1"><caption style="border:solid 1px black"><strong>Table title</strong></caption><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table><p>Type the text here</p></div>'
	},
        {
		title:"Articulos Conocimiento - Guía",
		image:"grimorum.gif",
		description:"Agregar una nueva guía o formulario a una columna de la página de Conocimiento",
		html:'<div class="list-group-item"  data-href="#"><h4 class="text-center list-group-item-heading" >Título</h4><p class="text-center list-group-item-text">Descripción</p></div>'
	},
        {
		title:"Articulos Conocimiento - Llamado a la acción",
		image:"grimorum.gif",
		description:"Agregar un nuevo llamado a la acción a una columna de la página de Conocimiento",
		html:'<div class="panel panel-warning"><div class="panel-heading"><h3 class="panel-title">#Título</h3></div><div class="panel-body"><div class="well"><p>Texto oferta</p></div><p>Texto descripción</p><p><a class="btn btn-warning" href="#" role="button">Llamado a la acción &raquo;</a></p></div></div>'
	},
        {
		title:"Articulos Conocimiento - Herramienta Novi",
		image:"grimorum.gif",
		description:"Agregar una nueva herramienta a la página de Conocimiento en el módulo de herramientas de la metodología Novi",
		html:'<div class="col-sm-6 col-md-3"><div class="thumbnail"><img src="#" alt="Nombre de la herramienta"><div class="caption"><h3>Nombre de la herramienta</h3><p>Descripción de la herramienta</p><p><a href="#" class="btn btn-primary" role="button">Guía</a><a href="images/img/formatos/tarjeta_persona.jpg" class="btn btn-default" role="button">Fomato</a></p></div></div></div>'
	},
	]
});