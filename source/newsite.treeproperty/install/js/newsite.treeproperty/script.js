//version 1.4

$(document).ready(function () {
    $(document).on('change', '.newsite_treeproperty select[name=type]', function () {
        var val = $(this).val();
        $('select[name=block] option').hide().attr('data-view', '0');
        $('select[name=block] option[data-type=' + val + ']').show().attr('data-view', '1');

        var block = $('select[name=block] option[data-view="1"]:first').attr('value');
        $('select[name=block] option[value=' + block + ']').attr('selected', true);
        $('select[name=block]').trigger('change');
    });

    var jstree = {
        "plugins": ["dnd", "types", "unique", "contextmenu"],

        "types": {
            "section": {
                "valid_children": ["property", "section"],
            },
            "section_free": {
                "valid_children": ["property"],
            },
            "property": {
                "valid_children": [],
            },
        },
        'contextmenu': {
            items: function (o, cb) {
                var menu = {};

                if (o.id != 'not_inherited_props') {
                    var label = '';
                    if (o.type == 'property') {
                        label = 'свойство'
                    } else if (o.type == 'section') {
                        label = 'раздел'
                    }
                    menu.edit_link = {
                        "separator_before": false,
                        "icon": false,
                        "separator_after": false,
                        "_disabled": false,
                        "label": "Редактировать " + label,
                        "action": function (data) {
                            window.open(o.data['edit_link'], "_blank");
                        }
                    };
                }

                if (o.type == 'property') {

                    var text = $.ajax({
                        method: "POST",
                        url: "/bitrix/ajax/newsite_treeproperty_ajax.php",
                        async: false,
                        dataType: 'json',
                        data: {
                            action: 'get_products',
                            property_id: o.id,
                        },
                        success: function(data){

                            menu.productsAll = {
                                "separator_before": true,
                                "icon": false,
                                "separator_after": false,
                                "_disabled": false,
                                "label": 'Заполнено во всех разделах: '+data[0],
                                "action": function(){
                                    $.ajax({
                                        method: "POST",
                                        url: "/bitrix/ajax/newsite_treeproperty_ajax.php",
                                        async: false,
                                        dataType: 'json',
                                        data: {
                                            action: 'go_to_products',
                                            iblock: $("#newsite_treeproperty_form select[name=block]").val(),
                                            property_id: o.id,
                                        },
                                        success: function(data){
                                            if(data['type_ok']){
                                                window.open(data['url'], "_blank");
                                            } else {
                                                var conf = confirm("Для поиска товаров с заполненным свойством \""+o.data['name']+"\" нужно выбрать его в фильтре и указать конкретное значение, по которому нужно искать. 1С-Битрикс не поддерживает поиск по \"любому значению\" свойства данного типа. \n Нажмите \"ОК\" для перехода на страницу поиска товаров (откроется новом окне) или Отмена, чтобы остаться на текущей странице");
                                                if(conf) {
                                                    window.open(data['url'], "_blank");
                                                }
                                            }
                                        }
                                    });
                                },
                            };
                            menu.productsSection = {
                                "separator_before": true,
                                "icon": false,
                                "separator_after": false,
                                "_disabled": false,
                                "label": 'Заполнено в этом разделе: '+data[1],
                                "action": function(){
                                    var url = $.ajax({
                                        method: "POST",
                                        url: "/bitrix/ajax/newsite_treeproperty_ajax.php",
                                        async: false,
                                        dataType: 'json',
                                        data: {
                                            action: 'go_to_products_section',
                                            iblock: $("#newsite_treeproperty_form select[name=block]").val(),
                                            property_id: o.id,
                                        },
                                        success: function(data){
                                            if(data['type_ok']){
                                                window.open(data['url'], "_blank");
                                            } else {
                                                var conf = confirm("Для поиска товаров с заполненным свойством \""+o.data['name']+"\" нужно выбрать его в фильтре и указать конкретное значение, по которому нужно искать. 1С-Битрикс не поддерживает поиск по \"любому значению\" свойства данного типа. \n Нажмите \"ОК\" для перехода на страницу поиска товаров (откроется новом окне) или Отмена, чтобы остаться на текущей странице");
                                                if(conf) {
                                                    window.open(data['url'], "_blank");
                                                }
                                            }
                                        }
                                    });
                                },
                            };
                        }
                    });


                    if (o.icon == 'file not_inherited') {
                        menu.remove = {
                            "separator_before": false,
                            "icon": false,
                            "separator_after": false,
                            "_disabled": false,
                            "label": "Отвязать от текущего раздела",
                            "action": function (data) {
                                var inst = $('#newsite_treeproperty_js').jstree(true),
                                    obj = inst.get_node(data.reference);
                                if (inst.is_selected(obj)) {
                                    inst.delete_node(inst.get_selected());
                                }
                                else {
                                    inst.delete_node(obj);
                                }
                            }
                        };

                        var text = $.ajax({
                            method: "POST",
                            url: "/bitrix/ajax/newsite_treeproperty_ajax.php",
                            // dataType: 'html',
                            async: false,
                            data: {
                                action: 'get_sections',
                                iblock: $("#newsite_treeproperty_form select[name=block]").val(),
                                property_id: o.id,
                            }
                        }).responseText;

                        menu.info = {
                            "separator_before": true,
                            "icon": false,
                            "separator_after": false,
                            "_disabled": true,
                            "label": text,
                            // "label": o.data['text'],
                            "action": false,
                        };
                    }
                }
                return menu;
            }
        },
        'dnd': {
            'is_draggable': function (data, e) {
                if (data[0].type == 'property') {
                    return true;
                }
                return false;
            },
        },
        'core': {
            "check_callback": true,
            'data': {
                "dataType": "json",
                "url": "/bitrix/ajax/newsite_treeproperty_ajax.php",
                "data": function (node) {
                    return {
                        "action": "get",
                        "id": node.id,
                        "iblock": $("#newsite_treeproperty_form select[name=block]").val()
                    };
                },
            }
        },
    };

    $(document).on('change', '.newsite_treeproperty select[name=block]', function () {
        //reload
        $('#newsite_treeproperty_js').jstree(true).destroy();
        $('#newsite_treeproperty_js').jstree(jstree);

        bindEvents();
    });

    $('#newsite_treeproperty_js').jstree(jstree);

    bindEvents();
});

function bindEvents() {

    $("#newsite_treeproperty_js").bind("move_node.jstree", function (e, data) {
        if (data.old_parent != data.parent) {
            $.ajax({
                method: "POST",
                url: "/bitrix/ajax/newsite_treeproperty_ajax.php",
                data: {
                    action: 'move',
                    iblock: $("#newsite_treeproperty_form select[name=block]").val(),
                    property_id: data.node.id,
                    old_parent: data.old_parent,
                    new_parent: data.parent,
                }
            }).done(function (msg) {
                console.log('move');
                if (data.parent == '#' || data.old_parent == '#') {
                    $('#newsite_treeproperty_js').jstree(true).refresh();
                } else {
                    anode = $("#newsite_treeproperty_js").jstree(true).get_node(data.parent);
                    anode.state.loaded = false;
                    $("#newsite_treeproperty_js").jstree(true).refresh_node(anode);
                    anode = $("#newsite_treeproperty_js").jstree(true).get_node(data.old_parent);
                    anode.state.loaded = false;
                    $("#newsite_treeproperty_js").jstree(true).refresh_node(anode);
                }
            });
        } else if (data.parent == '#') {
            $(this).jstree(true).refresh();
        } else {
            anode = $(this).jstree(true).get_node(data.parent);
            anode.state.loaded = false;
            $(this).jstree(true).refresh_node(anode);
            return false;
        }
    });

    $("#newsite_treeproperty_js").bind("copy_node.jstree", function (e, data) {
        if (data.old_parent != data.parent && data.parent != 'not_inherited_props') {
            $.ajax({
                method: "POST",
                url: "/bitrix/ajax/newsite_treeproperty_ajax.php",
                data: {
                    action: 'copy',
                    iblock: $("#newsite_treeproperty_form select[name=block]").val(),
                    property_id: data.original.id,
                    new_parent: data.parent,
                }
            }).done(function (msg) {
                console.log('copy');
                console.log(data.parent);
                if (data.parent == '#') {
                    $('#newsite_treeproperty_js').jstree(true).refresh();
                } else {
                    anode = $("#newsite_treeproperty_js").jstree(true).get_node(data.parent);
                    anode.state.loaded = false;
                    $("#newsite_treeproperty_js").jstree(true).refresh_node(anode);
                }
            });
        } else {
            return false;
        }
    });

    $("#newsite_treeproperty_js").bind("delete_node.jstree", function (e, data) {
        if (data.node.icon == 'file not_inherited') {
            $.ajax({
                method: "POST",
                url: "/bitrix/ajax/newsite_treeproperty_ajax.php",
                data: {
                    action: 'delete',
                    iblock: $("#newsite_treeproperty_form select[name=block]").val(),
                    property_id: data.node.id,
                    parent: data.parent,
                }
            }).done(function (msg) {
                console.log('delete');
                console.log(data.parent);
                if (data.parent == '#') {
                    $('#newsite_treeproperty_js').jstree(true).refresh();
                } else {
                    anode = $("#newsite_treeproperty_js").jstree(true).get_node(data.parent);
                    anode.state.loaded = false;
                    $("#newsite_treeproperty_js").jstree(true).refresh_node(anode);
                }
            });
        } else {
            return false;
        }
    });
}