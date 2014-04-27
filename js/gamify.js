/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

gamify_helper = {
    init: function(){
        $('#live-search').liveSearch({url: 'member.php?a=search&q='});
        $(".chosen-select").chosen({no_results_text: "Oops, no he trobat res!"});
        
        $( document ).on( 'click', '.btn-add', function ( event ) {
            event.preventDefault();
            
            var field = $(this).closest( '.clonable' );
            var field_new = field.clone();
            
            $(this)
		.toggleClass( 'btn-default' )
		.toggleClass( 'btn-add' )
		.toggleClass( 'btn-danger' )
		.toggleClass( 'btn-remove' )
		.html( '–' );
        
            field_new.find( 'input' ).val( '' );
            field_new.insertAfter( field );
        } );
        
        $( document ).on( 'click', '.btn-remove', function ( event ) {
            event.preventDefault();
            $(this).closest( '.clonable' ).remove();
        } );
        
        tinymce.init({
            selector: "textarea.tinymce",
            width:      '100%',
            height:     270,
            statusbar:  false,
            menubar:    false,                
            plugins: [
                "link",
                "code"
            ],
            toolbar: "bold italic underline strikethrough | removeformat | undo redo | bullist numlist | link code"
        });
    }
};

