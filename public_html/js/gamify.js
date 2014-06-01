/**
 * This file is part of gamify project.
 * Copyright (C) 2014  Paco Orozco <paco_@_pacoorozco.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * 
 * @category   Pakus
 * @package    Gamify JavaScript
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
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

