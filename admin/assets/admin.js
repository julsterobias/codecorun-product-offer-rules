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
            action: 'codecorun_offer_product_options'
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

    var offer_hr = codecorun_por_elementor__(
        {
            type: 'hr'
        }
    );

    jQuery(codecorun_por_offer_container).append(offer_hr);
    
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


    var rule_header = codecorun_por_elementor__(
        {
            type: 'label',
            text: 'Rules'
        }
    );

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
                    value: 'codecorun_por_fields codecorun_por_rules_select'
                },
                {
                    attr: 'id',
                    value: 'codecorun_por_rules_select'
                }
            ],
            options: options
        }
    );
    
    rules_wrapper.appendChild(rule_header);
    rule_header.appendChild(rules_select);
    jQuery(codecorun_por_offer_container).append(rules_wrapper);


}

/**
 * 
 * render date rule
 * @since 1.0.0
 * 
 */
var codecorun_render_date = function(value){

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

    /*wcdr_add_tooltip({
        parent: rule_label,
        text: wcdr_label_factory.tooltip_date
    });*/

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
                    value: 'wcdr_field[date-'+codecorun_por_unique_name()+']'
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
        placeholder: 'Select product',
        ajax: {
            url: codecorun_por_ajax.ajaxurl,
            data: function (params) {
                var query = {
                    search: params.term,
                    action: field_class.action,
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
                attr: 'id',
                value: 'codecorun_por_condition_remove_field'
                },
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
 * initialize
 * @since 1.0.0
 * 
 */
jQuery(document).ready(function(){
    //initialize fields
    codecorun_init_to_offer_fields();
    codecorun_init_to_rules_fields();

    jQuery('#codecorun_por_rules_select').change(function(){
        var value = jQuery(this).val();
        if(!value)
            return;

        switch(value){
            case 'date':
                codecorun_render_date('');
                break;
            case 'date_range':
                break;
            case 'in_cart_products':
                break;
            case 'in_product_page':
                break;
            case 'is_logged_in':
                break;
            case 'in_page':
                break;
            case 'in_post':
                break;
            case 'last_views':
                break;
            case 'had_purchased':
                break;
            
        }
    });
});