/**
 * 
 * 
 * admin js
 * @since 1.0.0
 * @author codecorun 
 * 
 * 
 */

var codecorun_por_offer_container = '#codecorun_por_admin_offer';
var codecorun_por_rule_container = '#codecorun_por_admin_rules';


/**
 * 
 * initialize offer fields
 * @since 1.0.0
 * 
 */
var codecorun_init_to_offer_fields = function(){
    
    var offer_wrapper = codecorun_por_elementor__(
        {
            type: 'div',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_fields'
                }
            ]
        }
    );

    var offer_header = codecorun_por_elementor__(
        {
            type: 'label',
            text: 'Product(s) to offer'
        }
    );
    offer_wrapper.appendChild(offer_header);

    //generate elements
    var offer_select = codecorun_por_elementor__(
        {
            type: 'select',
            attributes: [
                {
                    attr: 'id',
                    value: 'codecorun_por_options'
                },
                {
                    attr: 'class',
                    value: 'codecorun_por_field codecorun_por_select widefat'
                },
                {
                    attr: 'multiple',
                    value: true
                }
            ]
        }
    );
    offer_header.appendChild(offer_select);

    jQuery(codecorun_por_offer_container).append(offer_wrapper);
    //init select2
    codecorun_por_init_selectwoo(
        {
            el: '#codecorun_por_options',
            action: 'codecorun_offer_post_page_options',
            placeholder: 'Select product',
            post_type: 'product'
        }
    );

    
}

/**
 * 
 * init rules fields
 * @since 1.0.0
 * 
 */
var codecorun_init_to_rules_fields = function(){


    
    var rules_wrapper = codecorun_por_elementor__(
        {
            type: 'div',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_fields codecorun_por_rules_fields'
                }
            ]
        }
    );

    var rule_space = codecorun_por_elementor__( { type: 'hr', attributes: [ { attr: 'class', value: 'codecorun_divider' } ] } );
    
    var rule_header = codecorun_por_elementor__( { type: 'label' } );

        /**
         * 
         * last
         * 
         */
    var options = [{text: 'Select Rule', value: ''}];
    if(codecorun_por_rules){
        for(var x in codecorun_por_rules){
            options.push(
                {
                    text: codecorun_por_rules[x],
                    value: x
                }
            );
        }
    }

    var rules_select = codecorun_por_elementor__(
        {
            type: 'select',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_fields codecorun_por_rules_select codecorun_por_block'
                },
                {
                    attr: 'id',
                    value: 'codecorun_por_rules_select'
                }
            ],
            options: options
        }
    );

    var rules_button = codecorun_por_elementor__(
        {
            type: 'input',
            attributes: [
                {
                    attr: 'class',
                    value: 'button button-medium button-primary'
                },
                {
                    attr: 'id',
                    value: 'codecorun_por_add_rules'
                },
                {
                    attr: 'type',
                    value: 'button'
                },
                {
                    attr: 'value',
                    value: 'Add Rule'
                }
            ]
        }
    )
    
    rules_wrapper.appendChild(rule_space);
    rules_wrapper.appendChild(rule_header);
    rule_header.appendChild(rules_select);
    rule_header.appendChild(rules_button);

    //no rules avaiable
    var no_rules_parent = codecorun_por_elementor__(
        {
            type: 'div',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_no_values'
                }
            ]
        }
    );

    var no_rules_text = codecorun_por_elementor__(
        {
            type: 'span',
            text: 'No rules available',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_no_values_text'
                }
            ]
        }
    );

    jQuery(no_rules_parent).append(no_rules_text);
    jQuery(codecorun_por_offer_container).append(rules_wrapper);
    jQuery(codecorun_por_offer_container).append(no_rules_parent);
    


}

/**
 * 
 * create elements
 * @since 1.0.0
 * 
 */
function codecorun_por_elementor__(args = new Object){
    
    if(Object.keys(args).length == 0)
        return;
    
    var element = document.createElement(args.type);
    var text = (args.text)? document.createTextNode(args.text) : document.createTextNode('');
    if(args.attributes){
        for(var x in args.attributes){
            element.setAttribute(args.attributes[x].attr, args.attributes[x].value);
        }
    }
    element.appendChild(text);
    if(args.type == 'select'){
        //create options
        if(args.options){
            for(var y in args.options){
                var option = document.createElement('option');
                option.value = args.options[y].value;
                option.text = args.options[y].text;
                
                var check_value = Array.isArray(args.value);
                if(check_value){
                    for(var b in args.value){
                        if(args.value[b] == args.options[y].value){
                            option.defaultSelected = true;
                        }
                    }
                }else{
                    if(args.value == args.options[y].value){
                        option.defaultSelected = true;
                    }
                }
                element.appendChild(option);
            }
        }
    }
    return element;
}


/**
 * 
 * create unique id
 * @since 1.0.0
 * 
 */

 function codecorun_por_unique_name()
 {
     return Date.now().toString() + Math.random().toString(10).substring(2);
 }

/**
 * 
 * initialize the selectWoo
 * @since 1.0.0
 * 
 */
function codecorun_por_init_selectwoo(field_class = null)
{
    if(!field_class)
        return;

    jQuery(field_class.el).selectWoo({
        minimumInputLength: 3,
        placeholder: field_class.placeholder,
        ajax: {
            url: codecorun_por_ajax.ajaxurl,
            data: function (params) {
                var query = {
                    search: params.term,
                    action: field_class.action,
                    post_type: field_class.post_type,
                    nonce: codecorun_por_nonce
                }
                return query;
            },
            processResults: function( data ) {
              
				var options = [];
				if ( data ) {
			
					var parsed = JSON.parse(data);
                    for(var x in parsed){
                        options.push(
                            {
                                id: parsed[x].id,
                                text: parsed[x].text
                            }
                        );
                    } 
				
				}
				return {
					results: options
				};
			},
			cache: true
        }
    });

}

/**
 * 
 * render conditional fields
 * @since 1.0.0
 */

 function codecorun_por_create_conditions(obj){

    var el_con_div = codecorun_por_elementor__(
        {
            type: 'div',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_rule_condition'
                }
            ]
        }
    );

    var el_con_label = codecorun_por_elementor__(
        {
            type: 'label',
            text: 'condition'
        }
    );

    var el_con_field = codecorun_por_elementor__(
        {
            type: 'select',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_condition_field'
                },
                {
                    attr: 'name',
                    value: 'codecorun_por_field[condition-'+codecorun_por_unique_name()+']'
                }
            ],
            options: [
                {
                    text: 'And',
                    value: 'and'
                },
                {
                    text: 'Or',
                    value: 'or'
                }
            ]
        }
    );
    el_con_label.appendChild(el_con_field);
    el_con_div.appendChild(el_con_label);
    jQuery(obj).prepend(el_con_div);

     //add remove button
     var el_con_field = codecorun_por_elementor__(
        {
            type: 'a',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_condition_remove_field'
                },
                {
                    attr: 'href',
                    value: 'javascript:void(0);'

                }
            ],
            text: 'Remove'
        }
    );

    jQuery(obj).prepend(el_con_field);
    codecorun_por_remove_first_condition();
}



/**
 * 
 * 
 * 
 */
 function codecorun_por_remove_first_condition()
 {
     jQuery('#codecorun_por_admin_offer').find('.codecorun_por_rule_field').each(function(index){
         if(index == 0){
             jQuery(this).find('.codecorun_por_rule_condition').remove();
         }
     });
 }


/**
 * 
 * 
 * 
 * 
*/
function codecorun_por_add_tooltip(args = null){
    if(!args)
        return;

    var tooltip_icon = codecorun_por_elementor__(
        {
            type: 'span',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_tooltip dashicons dashicons-editor-help'
                }
            ]
        }
    );

    var tooltip_text = codecorun_por_elementor__(
        {
            type: 'i',
            text: args.text
        }
    );
    
    tooltip_icon.appendChild(tooltip_text);
    args.parent.appendChild(tooltip_icon);

}


/**
 * 
 * initialize
 * @since 1.0.0
 * 
 */

var codecorun_field_label_payload = {
    last_views: {
        field_id: 'codecorun_dy_field_last_views',
        label: 'Last viewed product(s)',
        tooltip: 'Offer product(s) if the user viewed one or all selected product(s)',
        multiple: true,
        placeholder: 'Select Product(s)',
        post_type: 'product'
    },
    had_purchased: {
        field_id: 'codecorun_dy_field_had_purchased',
        label: 'Latest purchased product(s)',
        tooltip: 'Offer the product(s) if the user purchased one or all selected product(s)',
        multiple: true,
        placeholder: 'Select Product(s)',
        post_type: 'product'
    },
    in_post: {
        field_id: 'codecorun_dy_field_in_post',
        label: 'In post(s)',
        tooltip: 'Offer product(s) if the user is in post(s)',
        multiple: true,
        placeholder: 'Select post(s)',
        post_type: 'post'
    },
    in_page: {
        field_id: 'codecorun_dy_field_in_page',
        label: 'In page(s)',
        tooltip: 'Offer product(s) if the user is in page(s)',
        multiple: true,
        placeholder: 'Select page(s)',
        post_type: 'page'
    },
    in_product_page: {
        field_id: 'codecorun_dy_field_in_product_page',
        label: 'In product page',
        tooltip: 'Offer product(s) if the user is in product page',
        multiple: false,
        placeholder: 'Select product',
        post_type: 'product'
    },
    in_cart_products: {
        field_id: 'codecorun_dy_field_in_cart_products',
        label: 'In cart product(s)',
        tooltip: 'Offer product(s) if the user added on or all selected products',
        multiple: true,
        placeholder: 'Select product',
        post_type: 'product'
    },
    have_url_param: {
        field_id: 'codecorun_dy_field_params_url',
        label: 'Has URL parameters',
        tooltip: 'Offer product(s) if the URL parameters are present',
        multiple: false,
        placeholder_key: 'Key',
        placeholder_value: 'Value'
    }
}

jQuery(document).ready(function(){
    //initialize fields
    codecorun_init_to_offer_fields();
    codecorun_init_to_rules_fields();

    jQuery('#codecorun_por_add_rules').click( function(){

        var value = jQuery('#codecorun_por_rules_select').val();
        if(!value)
            return;

        switch(value){
            case 'date':
                codecorun_por_render_date('');
                break;
            case 'date_range':
                codecorun_por_render_date_range(['','']);
                break;
            case 'is_logged_in':
                codecorun_por_render_is_logged_in(0);
                break;
            case 'in_cart_products':
            case 'in_product_page':
            case 'in_page':
            case 'in_post':
            case 'last_views':
            case 'had_purchased':
                codecorun_por_generate_fields( [], value, codecorun_field_label_payload[value] );
                break;
            case 'have_url_param':
                codecorun_por_url_param( [], value, codecorun_field_label_payload[value] );
                break;
        }
        jQuery('#codecorun_por_rules_select').val('');

        check_rules_available();

    } );


    jQuery('body').on('click','.codecorun_add_list_field', function(){
        var parent = jQuery(this).closest('table');
        var cloned = jQuery(this).closest('tr').clone(true);
        jQuery(parent).append(cloned);
    });

    jQuery('body').on('click','.codecorun_remove_list_field', function(){
        var get_con = jQuery('.codecorun_por_table_list tr').length;
        if(get_con <= 1){
          
            jQuery('.codecorun_por_table_list tr').each(function(i){
                if(i == 0){
                    var td = jQuery(this).find('td:nth-child(1)');
                    jQuery(td).find('input[type=text]').focus();
                }
                jQuery(this).find('input[type=text]').val('');
            });
        }else{
            var tr = jQuery(this).closest('tr');
            var get_key = jQuery(tr[0]).find('input').val();
            if(confirm('You are about to remove '+get_key+' from the list.')){
                jQuery(this).closest('tr').remove();
            }
        }
        
    });

    jQuery('body').on('click','.codecorun_por_condition_remove_field', function(){
        var parent = jQuery(this).closest('.codecorun_por_rule_field');
        var type = jQuery(parent).attr('data-rule-type');
        type = type.replaceAll('_',' ');
        type = type[0].toUpperCase() + type.slice(1);
        if( confirm('You are about to remove '+type+' rule.') ){
            jQuery(parent).remove();
            check_rules_available();
        }
    });

    

});


/**
 * 
 * render date rule
 * @since 1.0.0
 * 
 */
 var codecorun_por_render_date = function(value){

    var parent = codecorun_por_elementor__(
        {
            type: 'div',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_fields codecorun_por_rule_field'
                },
                {
                    attr: 'data-rule-type',
                    value: 'date'
                }
            ]
        }
    );

    var rule_label = codecorun_por_elementor__(
        {
            type: 'label',
            text: 'Date'
        }
    );

    codecorun_por_add_tooltip({
        parent: rule_label,
        text: 'Date Tooltip'
    });

    var el = codecorun_por_elementor__(
        {
            type: 'input',
            attributes: [
                {
                    attr: 'class',
                    value: 'wcdr_date_rule_el'
                },
                {
                    attr: 'type',
                    value: 'date'
                },
                {
                    attr: 'name',
                    value: 'codecorun_por_field[date-'+codecorun_por_unique_name()+']'
                },
                {
                    attr: 'value',
                    value: value
                }
            ]
        }
    );
    parent.appendChild(rule_label);
    rule_label.appendChild(el);
    jQuery(codecorun_por_offer_container).append(parent);
    codecorun_por_create_conditions(parent);
}

/**
 * 
 * 
 */

var codecorun_por_render_date_range = function(values = []){
    var parent = codecorun_por_elementor__(
        {
            type: 'div',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_fields codecorun_por_rule_field'
                },
                {
                    attr: 'data-rule-type',
                    value: 'date_range'
                }
            ]
        }
    );

    var rule_label = codecorun_por_elementor__(
        {
            type: 'label',
            text: 'Date Range',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_block'
                }
            ]
        }
    );

    codecorun_por_add_tooltip({
        parent: rule_label,
        text: 'Date Range Tooltip'
    });

    parent.appendChild(rule_label);

    var label_from = codecorun_por_elementor__(
        {
            type: 'label',
            text: 'From',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_label_inline'
                }
            ]
        }
    );

    var unique_id = codecorun_por_unique_name();

    var from = codecorun_por_elementor__(
        {
            type: 'input',
            attributes: [
                {
                    attr: 'class',
                    value: 'wcdr_date_rule_el'
                },
                {
                    attr: 'type',
                    value: 'date'
                },
                {
                    attr: 'name',
                    value: 'codecorun_por_field[date_range-'+unique_id+'][from]'
                },
                {
                    attr: 'value',
                    value: values[0]
                }
            ]
        }
    );
    
    label_from.appendChild(from);
    parent.appendChild(label_from);

    var label_to = codecorun_por_elementor__(
        {
            type: 'label',
            text: 'To',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_label_inline'
                }
            ]
        }
    );
    var to = codecorun_por_elementor__(
        {
            type: 'input',
            attributes: [
                {
                    attr: 'class',
                    value: 'wcdr_date_rule_el'
                },
                {
                    attr: 'type',
                    value: 'date'
                },
                {
                    attr: 'name',
                    value: 'codecorun_por_field[date_range-'+unique_id+'][to]'
                },
                {
                    attr: 'value',
                    value: values[1]
                }
            ]
        }
    );

    label_to.appendChild(to);
    parent.appendChild(label_to);
    jQuery(codecorun_por_offer_container).append(parent);
    codecorun_por_create_conditions(parent);

}


var codecorun_por_render_is_logged_in = function(user_id = 0){
    var parent = codecorun_por_elementor__(
        {
            type: 'div',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_fields codecorun_por_rule_field'
                },
                {
                    attr: 'data-rule-type',
                    value: 'in_product_page'
                }
            ],
            text: 'User is logged in (no field to setup)'
        }
    );

    jQuery(codecorun_por_offer_container).append(parent);
    codecorun_por_create_conditions(parent);
}


var codecorun_por_generate_fields = function(product_ids = [], type = '', attrs = null){
    var unique_id = codecorun_por_unique_name();
    var parent = codecorun_por_elementor__(
        {
            type: 'div',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_fields codecorun_por_rule_field'
                },
                {
                    attr: 'data-rule-type',
                    value: type
                }
            ]
        }
    );

    var label = codecorun_por_elementor__(
        {
            type: 'label',
            text: attrs.label
        }
    );

    codecorun_por_add_tooltip({
        parent: label,
        text: attrs.tooltip
    });

    var attr_values = [
        {
            attr: 'class',
            value: 'codecorun_por_fields codecorun_por_rule_field codecorun_por_block widefat'
        },
        {
            attr: 'id',
            value: attrs.field_id+'-'+unique_id
        },
        {
            attr: 'name',
            value: 'codecorun_por_field['+attrs.field_id+'-'+unique_id+']'
        }
    ];

    if( attrs.multiple ){
        attr_values.push(
            {
                attr: 'multiple',
                value: attrs.multiple
            }
        )
    }

    var select_products = codecorun_por_elementor__(
        {
            type: 'select',
            attributes: attr_values
        }
    );

    label.appendChild(select_products);
    parent.appendChild(label);
    jQuery(codecorun_por_offer_container).append(parent);
    codecorun_por_create_conditions(parent);
    codecorun_por_init_selectwoo(
        {
            el: '#'+attrs.field_id+'-'+unique_id,
            action: 'codecorun_offer_post_page_options',
            placeholder: attrs.placeholder,
            post_type: attrs.post_type
        }
    );
}

var codecorun_por_url_param = function( params = [], rule = '', attrs = [] ){

    var unique_id = codecorun_por_unique_name();
    var parent = codecorun_por_elementor__(
        {
            type: 'div',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_fields codecorun_por_rule_field'
                },
                {
                    attr: 'data-rule-type',
                    value: rule
                }
            ]
        }
    );

    var el_label = codecorun_por_elementor__(
        {
            type: 'label',
            text: attrs.label
        }
    );

    codecorun_por_add_tooltip({
        parent: el_label,
        text: attrs.tooltip
    }); 

    var el_field_wrapper = codecorun_por_elementor__(
        {
            type: 'table',
            attributes: [
                {
                    attr: 'class',
                    value: 'codecorun_por_table codecorun_por_table_list widefat'
                }
            ]
        }
    );

    var to_loop = (params.length > 0)? params.length : 1; 

    for(var z = 0; z < to_loop; z++){

        var el_field_wrapper_tr = codecorun_por_elementor__(
            {
                type: 'tr'
            }
        );
        
        for(var x = 1; x <= 2; x++){        
            var to_add_field = null;
            if(x == 1){
                to_add_field = codecorun_por_elementor__(
                    {
                        type: 'input',
                        attributes: [
                            {
                                attr: 'type',
                                value: 'text'
                            },  
                            {
                                attr: 'class',
                                value: 'codecorun_por_field_list codecorun_por_field_list_key widefat'
                            },
                            {
                                attr: 'name',
                                value: 'codecorun_por_field['+rule+'-'+unique_id+'][]'
                            },
                            {
                                attr: 'placeholder',
                                value: attrs.placeholder_key
                            },
                            {
                                attr: 'value',
                                value: (params.length > 0)? params[z].key : ''
                            }
                        ]
                    }
                );
            }else{
                to_add_field = codecorun_por_elementor__(
                    {
                        type: 'input',
                        attributes: [
                            {
                                attr: 'type',
                                value: 'text'
                            },  
                            {
                                attr: 'class',
                                value: 'codecorun_por_field_list codecorun_por_field_list_value widefat'
                            },
                            {
                                attr: 'name',
                                value: 'codecorun_por_field['+rule+'-'+unique_id+'][]'
                            },
                            {
                                attr: 'placeholder',
                                value: attrs.placeholder_value
                            },
                            {
                                attr: 'value',
                                value: (params.length > 0)? params[z].value : ''
                            }
                        ]
                    }
                );
            }
            var el_field_td = codecorun_por_elementor__(
                {
                    type: 'td',
                    attributes: [
                        {
                            attr: 'width',
                            value: '45%'
                        }
                    ]
                }
            );
            el_field_td.appendChild(to_add_field);
            el_field_wrapper_tr.appendChild(el_field_td);
        }
        
        var el_field_td_last = codecorun_por_elementor__(
            {
                type: 'td',
                attributes: [
                    {
                        attr: 'width',
                        value: '10%'
                    }
                ]
            }  
        );     

        //add add button
        var el_add_btn = codecorun_por_elementor__(
            {
                type: 'span',
                attributes: [
                    {
                        attr: 'class',
                        value: 'button button-primary codecorun_add_list_field code-por-add-'+rule+'-el'
                    },
                    {
                        attr: 'title',
                        value: 'Add'
                    }
                ],
                text: '+'
            }
        )
        var el_remove_btn = codecorun_por_elementor__(
            {
                type: 'span',
                attributes: [
                    {
                        attr: 'class',
                        value: 'button button-secondary codecorun_remove_list_field code-por-remove-'+rule+'-el'
                    },
                    {
                        attr: 'title',
                        value: 'Remove'
                    }
                ],
                text: '-'
            }
        )
        el_field_td_last.appendChild(el_add_btn);
        el_field_td_last.appendChild(el_remove_btn);
        el_field_wrapper_tr.appendChild(el_field_td_last); 
        el_field_wrapper.appendChild(el_field_wrapper_tr);

    //end of loop
    }

    parent.appendChild(el_label);
    parent.appendChild(el_field_wrapper);
    jQuery(codecorun_por_offer_container).append(parent);
    codecorun_por_create_conditions(parent);

}


function check_rules_available()
{
    if(jQuery('.codecorun_por_rule_field').length > 0){
        jQuery('.codecorun_por_no_values').hide();
    }else{
        jQuery('.codecorun_por_no_values').show();
    }
}