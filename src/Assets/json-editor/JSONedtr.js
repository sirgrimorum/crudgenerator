function JSONedtr(data, outputElement, config = {}) {

    if (!window.jQuery) {
        console.error("JSONedtr requires jQuery");
        return;
    }

    var JSONedtr = {};

    JSONedtr.config = config;

    if (JSONedtr.config.instantChange == null)
        JSONedtr.config.instantChange = true;
    if (JSONedtr.config.locked == null)
        JSONedtr.config.locked = false;

    JSONedtr.level = function(node, lvl = 0) {
        var output = '';
        var padreIsArray = false;
        if (Array.isArray(node)) {
            padreIsArray = true;
        }

        $.each(node, function(key, value) {
            JSONedtr.i++;

            if (typeof key == 'string') {
                key = key.replace(/\"/g, "&quot;");
            }

            if (typeof value == 'object') {
                var type = 'object';

                if (Array.isArray(value)) {
                    type = 'array';
                }
                if (padreIsArray) {
                    output += '<div class="jse--row jse--row-' + type + '" id="jse--row-' + JSONedtr.i + '"><div class="jse--container"><input type="hidden" class="jse--' + type + '" data-level="' + lvl + '" value="' + key + '"> : <span class="jse--typeof">(' + type + ')</span></div>';
                } else {
                    output += '<div class="jse--row jse--row-' + type + '" id="jse--row-' + JSONedtr.i + '"><div class="jse--container"><input type="text" class="jse--key jse--' + type + '" data-level="' + lvl + '" value="' + key + '"> : <span class="jse--typeof">(' + type + ')</span></div>';
                }
                output += JSONedtr.level(value, lvl + 1);
                if (!JSONedtr.config.locked) {
                    output += '<div class="jse--delete">✖</div></div>';
                } else {
                    output += '</div>';
                }
            } else if (typeof value == 'boolean') {
                var checked = '';
                if (value) {
                    checked = 'checked';
                }
                if (padreIsArray) {
                    output += '<div class="jse--row" id="jse--row-' + JSONedtr.i + '"><div class="jse--container">: <span class="jse--typeof">(' + typeof value + ')</span><input type="checkbox" class="jse--value" data-level="' + lvl + '"  value="' + value + '" data-key="' + key + '" ' + checked + '>';
                } else {
                    output += '<div class="jse--row" id="jse--row-' + JSONedtr.i + '"><div class="jse--container"><input type="text" class="jse--key" data-level="' + lvl + '" value="' + key + '"> : <span class="jse--typeof">(' + typeof value + ')</span><input type="checkbox" class="jse--value" value="' + value + '" data-key="' + key + '" ' + checked + '>';
                }
                if (!JSONedtr.config.locked) {
                    output += '<div class="jse--delete">✖</div></div></div>';
                } else {
                    output += '</div></div>';
                }
            } else {
                if (typeof value == 'string')
                    value = value.replace(/\"/g, "&quot;");
                var tipoCampo;
                if (isInteger(value) || isFloat(value)) {
                    tipoCampo = "number";
                    if (isInteger(value)) {
                        value = parseInt(value);
                    } else if (isFloat(value)) {
                        value = parseFloat(value);
                    }

                } else {
                    tipoCampo = "text";
                }
                if (padreIsArray) {
                    output += '<div class="jse--row" id="jse--row-' + JSONedtr.i + '"><div class="jse--container">: <span class="jse--typeof">(' + typeof value + ')</span><input type="' + tipoCampo + '" class="jse--value" data-level="' + lvl + '"  value="' + value + '" data-key="' + key + '">';
                } else {
                    output += '<div class="jse--row" id="jse--row-' + JSONedtr.i + '"><div class="jse--container"><input type="text" class="jse--key" data-level="' + lvl + '" value="' + key + '"> : <span class="jse--typeof">(' + typeof value + ')</span><input type="' + tipoCampo + '" class="jse--value" value="' + value + '" data-key="' + key + '">';
                }
                if (!JSONedtr.config.locked) {
                    output += '<div class="jse--delete">✖</div></div></div>';
                } else {
                    output += '</div></div>';
                }
            }
        });
        if (!JSONedtr.config.locked) {
            output += '<div class="jse--row jse--add" data-level="' + lvl + '"><button class="jse--plus">✚</button></div>';
        }
        return output;
    }

    JSONedtr.getData = function(node = $(JSONedtr.outputElement + ' > .jse--row > .jse--container > input')) {
        var result;
        if ($(node).first().parent().parent().parent().hasClass('jse--row-array')) {
            result = [];
        } else {
            result = {};
        }

        $.each(node, function() {
            var valor;
            var key;
            if ($(this).hasClass('jse--value') && $(this).attr("type") == "checkbox") {
                if ($(this).is(':checked')) {
                    valor = true;
                } else {
                    valor = false;
                }
                key = $(this).data('key');
            } else if ($(this).hasClass('jse--value')) {
                if ($(this).attr("type") == "number") {
                    valor = Number($(this).val());
                } else {
                    valor = $(this).val();
                }
                key = $(this).data('key');
            } else if ($(this).hasClass('jse--object') || $(this).hasClass('jse--array')) {
                var selector = '#' + $(this).parent().parent().attr('id') + ' > .jse--row > .jse--container > input';
                valor = JSONedtr.getData($(selector));
                key = $(this).val();
            }
            if ($(this).hasClass('jse--value') || $(this).hasClass('jse--object') || $(this).hasClass('jse--array')) {
                if ($(this).parent().parent().parent().hasClass('jse--row-array')) {
                    result.push(valor);
                } else {
                    result[key] = valor;
                }
            }
        });
        return result;
    }

    JSONedtr.getDataString = function(node = $(JSONedtr.outputElement + ' > .jse--row > .jse--container > input')) {
        return JSON.stringify(JSONedtr.getData());
    }

    JSONedtr.addRowForm = function(plus) {
        var lvl = $(plus).data('level');
        //
        // TODO: add support for array, reference and number
        //
        // var typeofHTML = '<select class="typeof">'+
        // 					'<option value="text" selected="selected">Text</option>'+
        // 					'<option value="object">Object</option>'+
        // 					'<option value="array">Array</option>'+
        // 					'<option value="reference">Reference</option>'+
        // 					'<option value="boolean">Boolean</option>'+
        // 					'<option value="number">Number</option>'+
        // 				'</select>';
        //

        var typeofHTML = '<select class="jse--typeof">' +
            '<option value="text" selected="selected">Text, Number</option>' +
            '<option value="object">Object</option>' +
            '<option value="array">Array</option>' +
            '<option value="boolean">Boolean</option>' +
            '</select>';
        var tipoPadre = $(plus).parent().hasClass('jse--row-object') ? 'object' : $(plus).parent().hasClass('jse--row-array') ? 'array' : 'otro';
        if (tipoPadre == 'array') {
            $(plus).html('<div class="jse--container">: <span class="jse--typeof">( ' + typeofHTML + ' )</span><input type="text" data-level="' + lvl + '" class="jse--value jse--value__new" value=""><button type="button" class="jse--save">Save</button><button class="jse--cancel">Cancel</button></div>');
        } else {
            $(plus).html('<div class="jse--container"><input type="text" class="jse--key" data-level="' + lvl + '" value=""> : <span class="jse--typeof">( ' + typeofHTML + ' )</span><input type="text" class="jse--value jse--value__new" value=""><button type="button" class="jse--save">Save</button><button class="jse--cancel">Cancel</button></div>');
        }
        $(plus).children('.jse--key').trigger("focus");

        $(plus).find('select.jse--typeof').on('change', function() {
            switch ($(this).val()) {
                case 'text':
                    $(this).parent().siblings('.jse--value__new').replaceWith('<input type="text" data-level="' + $(this).parent().siblings('.jse--value__new').first().data('level') + '" class="jse--value jse--value__new" value="">');
                    $(this).parent().siblings('.jse--value__new').trigger("focus");
                    break;
                case 'boolean':
                    $(this).parent().siblings('.jse--value__new').replaceWith('<input type="checkbox" data-level="' + $(this).parent().siblings('.jse--value__new').first().data('level') + '" class="jse--value jse--value__new" value="">');
                    $(this).parent().siblings('.jse--value__new').trigger("focus");
                    break;
                case 'object':
                case 'array':
                    if (tipoPadre == 'array') {
                        $(this).parent().siblings('.jse--value__new').replaceWith('<input type="hidden" data-level="' + $(this).parent().siblings('.jse--value__new').first().data('level') + '" class="jse--value__new jse--' + $(this).val() + '" value=""/>');
                    } else {
                        $(this).parent().siblings('.jse--value__new').replaceWith('<span class="jse--value__new" data-level="' + $(this).parent().siblings('.jse--value__new').first().data('level') + '"></span>');
                    }
                    break;
            }
        })

        if (!JSONedtr.config.locked) {
            $('.jse--row.jse--add .jse--save').on('click', function(e) {
                JSONedtr.addRow(e.currentTarget.parentElement.parentElement)
            });

            $('.jse--row.jse--add .jse--cancel').on('click', function(e) {
                var x = e.currentTarget.parentElement.parentElement
                $(e.currentTarget.parentElement.parentElement).html('<button class="jse--plus">✚</button>');
                $(x).find('.jse--plus').on('click', function(e) {
                    JSONedtr.addRowForm(e.currentTarget.parentElement);
                });
            });
        }
    }

    JSONedtr.addRow = function(row) {
        var typeOf = $(row).find('select.jse--typeof option:selected').val();
        var ii = $(JSONedtr.outputElement).data('i');
        ii++;
        $(JSONedtr.outputElement).data('i', ii);
        var lvl = $(row).data('level');
        $(row).removeClass('jse--add').attr('id', 'jse--row-' + ii);
        var key = $(row).find('.jse--key').val()
        switch (typeOf) {
            case 'text':
                var value = $(row).find('.jse--value__new').val();
                if (isInteger(value) || isFloat(value)) {
                    typeOf = 'number';
                }
                $(row).find('.jse--value__new').data('key', key).removeClass('jse--value__new').attr("type", typeOf).attr("value", value);
                break;
            case 'boolean':
                if ($(row).find('.jse--value__new').is(':checked')) {
                    $(row).find('.jse--value__new').replaceWith('<input type="checkbox" class="jse--value" value="true" data-key="' + key + '" checked>');
                } else {
                    $(row).find('.jse--value__new').replaceWith('<input type="checkbox" class="jse--value" value="true" data-key="' + key + '">');
                }
                break;
            case 'array':
            case 'object':
                $(row).find('.jse--key').addClass('jse--' + typeOf);
                if (!JSONedtr.config.locked) {
                    $(row).append('<div class="xxx jse--row jse--add" data-level="' + (lvl + 1) + '"><button class="jse--plus">✚</button></div>');
                }
                $(row).addClass('jse--row-' + typeOf);
                break;
        }
        $(row).find('span.jse--typeof').html('(' + typeOf + ')');

        if (!JSONedtr.config.locked) {
            $(row).append('<div class="jse--delete">✖</div>');


            $(row).find('.jse--delete').on('click', function(e) {
                JSONedtr.deleteRow(e.currentTarget.parentElement);
            });
        }

        $(row).children('div.jse--container:first-child').children('.jse--save, .jse--cancel').remove();
        if (!JSONedtr.config.locked) {
            $(row).after('<div class="jse--row jse--add" data-level="' + lvl + '"><button class="jse--plus">✚</button></div>');
            $(row).parent().find('.jse--row.jse--add .jse--plus').on('click', function(e) { JSONedtr.addRowForm(e.currentTarget.parentElement) });
        }

        $(row).find('input').on('change input', function(e) {
            if (JSONedtr.config.runFunctionOnUpdate) {
                if (JSONedtr.config.instantChange || 'change' == e.type)
                    JSONedtr.executeFunctionByName(JSONedtr.config.runFunctionOnUpdate, window, JSONedtr);
            }
        });

        if (JSONedtr.config.runFunctionOnUpdate) {
            JSONedtr.executeFunctionByName(JSONedtr.config.runFunctionOnUpdate, window, JSONedtr);
        }

    }

    JSONedtr.deleteRow = function(row) {
        $(row).remove();
        if (JSONedtr.config.runFunctionOnUpdate) {
            JSONedtr.executeFunctionByName(JSONedtr.config.runFunctionOnUpdate, window, JSONedtr);
        }
    }

    JSONedtr.executeFunctionByName = function(functionName, context /*, args */ ) {
        var args = Array.prototype.slice.call(arguments, 2);
        var namespaces = functionName.split(".");
        var func = namespaces.pop();
        for (var i = 0; i < namespaces.length; i++) {
            context = context[namespaces[i]];
        }
        return context[func].apply(context, args);
    }

    JSONedtr.init = function(data, outputElement) {
        data = JSON.parse(data);
        JSONedtr.i = 0;
        JSONedtr.outputElement = outputElement;
        var html = JSONedtr.level(data);

        $(outputElement).addClass('jse--output').html(html).data('i', JSONedtr.i);

        if (!JSONedtr.config.locked) {
            $(outputElement + ' .jse--row.jse--add .jse--plus').on('click', function(e) {
                JSONedtr.addRowForm(e.currentTarget.parentElement);
            });

            $(outputElement + ' .jse--row .jse--delete').on('click', function(e) {
                JSONedtr.deleteRow(e.currentTarget.parentElement);
            });
        } else {
            $(outputElement + ' input.jse--key').attr("readonly", "readonly");
        }

        $(outputElement + ' .jse--row input').on('change input', function(e) {
            if (JSONedtr.config.runFunctionOnUpdate) {
                if (JSONedtr.config.instantChange || 'change' == e.type)
                    JSONedtr.executeFunctionByName(JSONedtr.config.runFunctionOnUpdate, window, JSONedtr);
            }
        });
    }

    JSONedtr.init(data, outputElement);

    return JSONedtr;
};

function isInteger(n) {
    if (n !== "" && n !== false && n !== true && n !== null && n !== undefined) {
        return Number(n) == n && Number(n) % 1 === 0;
    }
    return false;
}

function isFloat(n) {
    if (n !== "" && n !== false && n !== true && n !== null && n !== undefined) {
        return Number(n) == n && Number(n) % 1 !== 0;
    }
    return false;
}