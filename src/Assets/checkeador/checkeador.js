$(document).on('ready', function() {
    comenzarCheckeador();
});

function comenzarCheckeador() {
    //console.log("comenzando chekeador");
    $(".checkeador").on('click', function() {
        mostrarCheckeador($(this));
        if ($(this).attr("data-exclusivo") != "" && $(this).attr("data-exclusivo") != undefined) {
            if ($(this).is(':checked')) {
                $($(this).attr("data-exclusivo")).prop('checked', false);
                if ($($(this).attr("data-exclusivo")).hasClass("checkeador")) {
                    mostrarCheckeador($($(this).attr("data-exclusivo")));
                }
            }
        }
        $(".ya_checkeado").removeClass("ya_checkeado");
    });
    $(".checkeador").each(function() {
        mostrarCheckeador($(this));
        if ($(this).attr("data-exclusivo") != "" && $(this).attr("data-exclusivo") != undefined) {
            if ($(this).is(':checked')) {
                $($(this).attr("data-exclusivo")).prop('checked', false);
                if ($($(this).attr("data-exclusivo")).hasClass("checkeador")) {
                    mostrarCheckeador($($(this).attr("data-exclusivo")));
                }
            }
        }
    });
    $(".ya_checkeado").removeClass("ya_checkeado");
    $(".selecteador").on("change", function() {
        mostrarSelecteador($(this));
        $(".ya_checkeado").removeClass("ya_checkeado");
    });
    $(".selecteador").each(function() {
        mostrarSelecteador($(this));
    });
}

function mostrarSelecteador(elemento) {
    var seleccionado = $(elemento).children("option").filter(':selected').val();
    //console.log("mostrarSelecteador", elemento, seleccionado);
    if ($(elemento).attr("data-contenedor") != "") {
        var contenedor = $(elemento).attr("data-contenedor");
        var pre1 = "class";
        var pre2 = ".";
        //console.log("primer caracter", contenedor.substr(0, 1));
        if (contenedor.substr(0, 1) == "#") {
            pre1 = "id";
            pre2 = "#";
            contenedor = contenedor.substr(1, contenedor.length - 1);
        } else if (contenedor.substr(0, 1) == ".") {
            contenedor = contenedor.substr(1, contenedor.length - 1);
        }
        //console.log("rresetear interno", $('[' + pre1 + '*="' + contenedor + '_"]'), $(pre2 + contenedor + "_" + seleccionado + "_not"), $(pre2 + contenedor + "_" + seleccionado), $("[" + pre1 + "*='" + contenedor + "_'][" + pre1 + "$='_not']").not("[" + pre1 + "$='_" + seleccionado + "_not']"));
        resetearInterno($("[" + pre1 + "*='" + contenedor + "_']"));
        resetearInterno($(pre2 + contenedor + "_" + seleccionado + "_not"));
        $(pre2 + contenedor + "_" + seleccionado).not('ya_checkeado').show().addClass('ya_checkeado');
        $("[" + pre1 + "*='" + contenedor + "_'][" + pre1 + "*='_not']").not("[" + pre1 + "*='_" + seleccionado + "_not']").not('ya_checkeado').show().addClass('ya_checkeado');
    }
}

function mostrarCheckeador(elemento) {
    //console.log("mostrarChekeador", elemento);
    if ($(elemento).attr("data-contenedor") != "") {
        if ($(elemento).is(':checked')) {
            resetearInterno($($(elemento).attr("data-contenedor") + "_not"));
            $($(elemento).attr("data-contenedor")).not('.ya_checkeado').show().addClass('ya_checkeado');
        } else {
            resetearInterno($($(elemento).attr("data-contenedor")));
            $($(elemento).attr("data-contenedor") + "_not").not('.ya_checkeado').show().addClass('ya_checkeado');
        }
    }
}

function resetearInterno(elemento) {
    elementos = $(elemento).not('.ya_checkeado');
    $(elementos).hide().addClass('ya_checkeado');
    //console.log("resetear",elemento);
    $(elementos).find("input").each(function(index) {
        //console.log("reseteando",this);
        if ($(this).attr("type") == "checkbox") {
            $(this).prop("checked", false);
            mostrarCheckeador(this);
        } else {
            $(this).val("");
        }
    });
    $(elementos).find("textarea").each(function(index) {
        $(this).val("");
    });
    $(elementos).find("select").each(function(index) {
        $(this).val([]);
    });
}