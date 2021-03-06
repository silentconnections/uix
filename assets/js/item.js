var uix_item_control_modal, uix_item_control_modal_handler;
(function($){

    var current_item = null
        flush_current = false;

    uix_item_control_modal = function( obj ){
        var template_ele = $('#' + obj.modal + '-tmpl'),
            template = Handlebars.compile( template_ele.html() ),
            data = {},
            state,
            html;

        if( null !== current_item && flush_current === false ){
            data = { config : current_item.data( 'config' ) };
            state = 'add';
        }else{
            current_item = null;
            flush_current = false;
            state = 'update';
            data = obj.trigger.data('default');
        }

        html = template( data );

        $('.uix-modal-footer [data-state="' + state + '"]').remove();

        return html;
    }

    uix_item_control_modal_handler = function( data, obj ){

        var item = create_item( obj.params.requestData.control, data.data ),
            target;
        if( null !== current_item ){
            target = current_item;
            current_item = null;
            target.replaceWith( item );
        }else {
            target = $('#' + obj.params.requestData.control);
            item.appendTo( target );
        }

        save_current_edit( $( '#' + obj.params.requestData.control ) );
    }

    var create_item = function( target, data ){

        var template_ele = $('#' + target + '-tmpl'),
            template = Handlebars.compile( template_ele.html() ),
            item = $( template( data )  );
        item.data( 'config', data );
        $(document).trigger('uix.init');
        return item;
    }

    var save_current_edit = function ( parent ) {
        var holders;
        if( parent ){
            holders = $( parent );
        }else{
            holders = $( '.uix-control-item' );
        }

        for( var i = 0; i < holders.length; i++ ){

            var holder = $( holders[ i ] ),
                input = $( '#' + holder.prop('id') + '-control' ),
                items = holder.find('.uix-item'),
                configs = [];

                for( var c = 0; c < items.length; c++ ){
                    var item = $( items[ c ] );
                    configs.push( item.data('config') );
                }
            input.val( JSON.stringify( configs ) ).trigger('change');
        }
        $( document ).trigger('uix.save');
    }

    $( document ).on( 'click', '.uix-item-edit', function( ){
        var clicked = $( this ),
            control = clicked.closest('.uix-control-item'),
            trigger = $('button[data-modal="' + control.prop('id') + '-config"]');

        current_item = clicked.closest('.uix-item');
        flush_current = false;

        trigger.trigger('click');
    });

    $( document ).on( 'click', '.uix-item-remove', function( ){
        var clicked = $( this ),
            control = clicked.closest('.uix-control-item'),
            trigger = $('button[data-modal="' + control.prop('id') + '-config"]'),
            item = clicked.closest('.uix-item');

        if( clicked.data('confirm') ){
            if( ! confirm( clicked.data('confirm') ) ){
                return;
            }
        }

        item.fadeOut( 200, function(){
            item.remove();
            save_current_edit( control );
        });
    });

    // clear edit
    $(window).on( 'modals.closed', function(){
        flush_current = true;
    });

    // init
    $(window).load(function () {
        $(document).on('uix.init', function () {
            $('.uix-control-item').not('._uix_item_init').each( function(){
                var holder = $( this ),
                    input = $( '#' + holder.prop('id') + '-control' ),
                    data;

                try {
                    data = JSON.parse( input.val() );
                }catch (err) {

                }
                holder.addClass('_uix_item_init');

                if( typeof data === 'object' && data.length ){
                    for( var i = 0; i < data.length; i++ ){
                        var item = create_item( holder.prop('id'), data[ i ] );
                        item.appendTo( holder );
                    }
                }
                holder.removeClass('processing');
            });
        });
    });
})(jQuery);
