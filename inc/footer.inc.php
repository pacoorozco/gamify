<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: footer.inc.php 65 2014-04-21 18:09:54Z paco $
 *
 */

/* Check if this is a valid include */
defined('IN_SCRIPT') or die('Invalid attempt');

?>
    </div><!--/.container -->
        <div id="push"></div>
    </div><!--/wrap -->

    
      <footer id="footer">
          <div class="container">
              <p class="text-muted credit">GoW! v<?php echo $CONFIG['version']; ?> - Powered by <a href="https://git.upcnet.es/paco.orozco/gamify/blob/master/README.md" title="Gamify Project">gamify</a> - <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.ca">Llicència Creative Commons Reconeixement-CompartirIgual</a></p>
          </div>
      </footer>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script src="js/jquery.liveSearch.js"></script>
    <script src="js/chosen/chosen.jquery.min.js"></script>
    <script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>
    <script src="holder.js"></script>
    <script>
        $('#live-search').liveSearch({url: 'member.php?a=search&q='});
        var config_chosen = {
            '.chosen-select'           : {no_results_text:'Oops, no he trobat res!'}
        }
        for (var selector in config_chosen) {
            $(selector).chosen(config_chosen[selector]);
        }
        

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
    </script>
    
    <!-- Google Analytics -->
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-45534443-3', 'upcnet.es');
  ga('send', 'pageview');
    </script>
    
  </body>
</html>

